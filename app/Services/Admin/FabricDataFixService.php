<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * One-time repair of fabric PO data left by bugs that are now fixed.
 *
 *  1. Rolls whose PO line was deleted by the old "edit PO" (it deleted and re-created every line):
 *     linked to the line of the same PO and fabric.
 *  2. Rolls added by the old "edit shipment" lost their PO: linked to the PO of the other rolls of that shipment.
 *     A shipment received without a PO gets an automatic PO made at the same second; when all its rolls lost the
 *     link, they are linked back to that PO.
 *  3. Automatic POs (made for a shipment received without a PO) follow their shipment: one line per fabric with the
 *     meters of its rolls; an automatic PO whose shipment was deleted is removed.
 *  4. "Left to receive" of every PO line = ordered - received (the same figure as Reports > Purchase Order).
 *  Lists only (nothing changed): rolls on a PO line of another fabric, shipments whose rolls do not match the bill.
 *
 * run(false) = preview (everything is done inside a transaction and rolled back),
 * run(true)  = apply (committed, and every old value is saved to storage/app/fabric-fix/).
 */
class FabricDataFixService
{
    private const TABLES = ['purchase_orders', 'purchase_order_items', 'fabric_receipt_details'];

    public function run(bool $apply): array
    {
        @set_time_limit(600);

        $notSafe = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', self::TABLES)->where('ENGINE', '!=', 'InnoDB')->pluck('TABLE_NAME');
        if ($notSafe->isNotEmpty()) {
            throw new \RuntimeException('These tables cannot undo changes (not InnoDB), so the repair will not run: ' . $notSafe->implode(', '));
        }

        DB::beginTransaction();
        try {
            $before = $this->snapshot();

            $relinked = array_merge($this->deletedLines(), $this->editedShipments(), $this->autoPoShipments());
            $autoPos = $this->autoPos();
            $openPos = DB::table('purchase_orders')->where('status', '!=', 0)->pluck('id');
            FabricPurchaseLink::recalcLines(DB::table('purchase_order_items')->whereIn('purchase_order_id', $openPos)->pluck('id')->all());

            $after = $this->snapshot();
            $result = [
                'relinked' => $relinked,
                'auto_pos' => $autoPos,
                'lines' => $this->lineChanges($before, $after),
                'wrong_fabric' => $this->wrongFabricRolls(),
                'bill_mismatch' => $this->billMismatch(),
                'log_file' => null,
            ];

            if ($apply) {
                $file = 'fabric-fix/fabric-fix-' . now()->format('Ymd-His') . '.json';
                Storage::disk('local')->put($file, json_encode([
                    'run_at' => now()->toDateTimeString(),
                    'run_by' => auth()->id(),
                    'undo' => $this->undoLog($before, $after),
                ], JSON_PRETTY_PRINT));
                $result['log_file'] = $file;
                DB::commit();
            } else {
                DB::rollBack();
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** 1. Rolls pointing to a PO line that no longer exists. */
    private function deletedLines(): array
    {
        $rolls = DB::table('fabric_receipt_details as d')
            ->leftJoin('purchase_order_items as i', 'i.id', '=', 'd.purchase_order_item_id')
            ->where('d.purchase_order_item_id', '>', 0)->where('d.purchase_order_id', '>', 0)->whereNull('i.id')
            ->orderBy('d.id')->get(['d.id', 'd.purchase_order_id', 'd.fabric_id', 'd.meter']);
        return $this->link($rolls, fn($r) => (int) $r->purchase_order_id, 'Its PO line was deleted by the old "edit PO"');
    }

    /** 2a. Rolls without a PO in a shipment whose other rolls are on one PO (added by the old "edit shipment"). */
    private function editedShipments(): array
    {
        $shipments = DB::table('fabric_receipt_details')->groupBy('fabric_receipt_id')
            ->havingRaw('SUM(purchase_order_id IS NULL OR purchase_order_id = 0) > 0 AND COUNT(DISTINCT NULLIF(purchase_order_id, 0)) = 1')
            ->selectRaw('fabric_receipt_id, MAX(NULLIF(purchase_order_id, 0)) AS po_id')->get();
        $out = [];
        foreach ($shipments as $s) {
            $rolls = DB::table('fabric_receipt_details')->where('fabric_receipt_id', $s->fabric_receipt_id)
                ->where(fn($q) => $q->whereNull('purchase_order_id')->orWhere('purchase_order_id', 0))
                ->orderBy('id')->get(['id', 'fabric_id', 'meter']);
            $out = array_merge($out, $this->link($rolls, fn() => (int) $s->po_id, 'Added by the old "edit shipment" without its PO'));
        }
        return $out;
    }

    /** 2b. An automatic PO without rolls, made at the same second as a shipment whose rolls all lost their PO. */
    private function autoPoShipments(): array
    {
        $out = [];
        $pos = DB::table('purchase_orders as p')->where('p.status', 1)
            ->whereNull('p.master_company_id')->whereNull('p.fabric_warehouse_id')
            ->whereNotExists(fn($q) => $q->from('fabric_receipt_details as d')->whereColumn('d.purchase_order_id', 'p.id'))
            ->get(['p.id', 'p.vendor_id', 'p.created_at']);
        foreach ($pos as $po) {
            $shipment = DB::table('fabric_receipts as r')->where('r.vendor_id', $po->vendor_id)
                ->whereRaw('ABS(TIMESTAMPDIFF(SECOND, r.created_at, ?)) <= 3', [$po->created_at])
                ->whereNotExists(fn($q) => $q->from('fabric_receipt_details as d')->whereColumn('d.fabric_receipt_id', 'r.id')->where('d.purchase_order_id', '>', 0))
                ->get(['r.id']);
            if ($shipment->count() !== 1) {
                continue; // none, or not sure which one
            }
            $rolls = DB::table('fabric_receipt_details')->where('fabric_receipt_id', $shipment->first()->id)->orderBy('id')->get(['id', 'fabric_id', 'meter']);
            $poFabrics = DB::table('purchase_order_items')->where('purchase_order_id', $po->id)->pluck('fabric_id')->map(fn($f) => (int) $f)->all();
            if ($rolls->isEmpty() || $rolls->contains(fn($r) => !in_array((int) $r->fabric_id, $poFabrics, true))) {
                continue;
            }
            $out = array_merge($out, $this->link($rolls, fn() => (int) $po->id, 'Its shipment was edited and all rolls lost the automatic PO'));
        }
        return $out;
    }

    /** Links rolls to a PO and to the first line of their fabric with meters left. */
    private function link($rolls, callable $poOf, string $why): array
    {
        $out = [];
        $lines = [];
        foreach ($rolls as $roll) {
            $poId = $poOf($roll);
            if (!isset($lines[$poId])) {
                $lines[$poId] = FabricPurchaseLink::openLines($poId);
            }
            $lineId = FabricPurchaseLink::pickLine($lines[$poId], (int) $roll->fabric_id, (float) $roll->meter);
            if (!$lineId) {
                continue; // the PO has no line for this fabric: left as it is
            }
            DB::table('fabric_receipt_details')->where('id', $roll->id)->update(['purchase_order_id' => $poId, 'purchase_order_item_id' => $lineId]);
            $out[] = ['roll_id' => $roll->id, 'po_id' => $poId, 'line_id' => $lineId, 'why' => $why];
        }
        if ($out) {
            $info = DB::table('fabric_receipt_details as d')->join('fabric_receipts as r', 'r.id', '=', 'd.fabric_receipt_id')
                ->leftJoin('fabrics as f', 'f.id', '=', 'd.fabric_id')->leftJoin('purchase_orders as p', 'p.id', '=', 'd.purchase_order_id')
                ->whereIn('d.id', array_column($out, 'roll_id'))
                ->get(['d.id', 'd.roll_number', 'd.meter', 'r.id as shipment_id', 'r.sku as shipment', 'f.name as fabric', 'p.sku as po'])->keyBy('id');
            foreach ($out as &$row) {
                $i = $info[$row['roll_id']];
                $row += ['roll_no' => $i->roll_number, 'meter' => (float) $i->meter, 'shipment_id' => $i->shipment_id, 'shipment' => $i->shipment, 'fabric' => $i->fabric, 'po' => $i->po];
            }
        }
        return $out;
    }

    /** 3. Every automatic PO follows its shipment (or is removed when the shipment is gone). */
    private function autoPos(): array
    {
        $out = [];
        $pos = DB::table('purchase_orders')->where('status', 1)->whereNull('master_company_id')->whereNull('fabric_warehouse_id')->orderBy('id')->get(['id', 'sku']);
        foreach ($pos as $po) {
            $done = FabricPurchaseLink::syncAutoPo($po->id);
            if ($done) {
                $out[] = ['po_id' => $po->id, 'po' => $po->sku, 'done' => $done];
            }
        }
        return $out;
    }

    private function snapshot(): array
    {
        return [
            'purchase_orders' => DB::table('purchase_orders')->get()->keyBy('id')->map(fn($r) => (array) $r)->all(),
            'purchase_order_items' => DB::table('purchase_order_items')->get()->keyBy('id')->map(fn($r) => (array) $r)->all(),
            'fabric_receipt_details' => DB::table('fabric_receipt_details')->get(['id', 'purchase_order_id', 'purchase_order_item_id', 'updated_at'])
                ->keyBy('id')->map(fn($r) => (array) $r)->all(),
        ];
    }

    /** 4. PO lines whose ordered / left-to-receive meters change (and lines added or removed). */
    private function lineChanges(array $before, array $after): array
    {
        $fabrics = DB::table('fabrics')->pluck('name', 'id');
        $pos = DB::table('purchase_orders')->pluck('sku', 'id');
        $received = FabricPurchaseLink::receivedByLine(array_keys($after['purchase_order_items']));
        $out = [];
        foreach ($before['purchase_order_items'] + $after['purchase_order_items'] as $id => $any) {
            $old = $before['purchase_order_items'][$id] ?? null;
            $new = $after['purchase_order_items'][$id] ?? null;
            $same = $old && $new && abs((float) $old['meter'] - (float) $new['meter']) < 0.001
                && abs((float) $old['remaining_quantity'] - (float) $new['remaining_quantity']) < 0.001 && $old['remaining_quantity'] !== null;
            if ($same) {
                continue;
            }
            $row = $new ?? $old;
            $out[] = [
                'line_id' => $id,
                'po_id' => $row['purchase_order_id'],
                'po' => $pos[$row['purchase_order_id']] ?? ('#' . $row['purchase_order_id']),
                'fabric' => $fabrics[$row['fabric_id']] ?? ('#' . $row['fabric_id']),
                'ordered_was' => $old ? (float) $old['meter'] : null,
                'ordered_now' => $new ? (float) $new['meter'] : null,
                'received' => $received[$id] ?? 0,
                'left_was' => $old ? ($old['remaining_quantity'] === null ? null : (float) $old['remaining_quantity']) : null,
                'left_now' => $new ? (float) $new['remaining_quantity'] : null,
            ];
        }
        usort($out, fn($a, $b) => [$a['po_id'], $a['line_id']] <=> [$b['po_id'], $b['line_id']]);
        return $out;
    }

    /** Old values of every changed / removed row, and the ids of added rows (undo = delete them). */
    private function undoLog(array $before, array $after): array
    {
        $undo = [];
        foreach (self::TABLES as $table) {
            foreach ($before[$table] as $id => $old) {
                $new = $after[$table][$id] ?? null;
                if ($new === null) {
                    $undo[] = ['table' => $table, 'id' => $id, '_deleted' => true, 'old' => $old];
                } elseif ($new != $old) {
                    $changed = array_keys(array_diff_assoc(array_map('strval', $new), array_map('strval', $old)));
                    $undo[] = ['table' => $table, 'id' => $id, 'old' => array_intersect_key($old, array_flip(array_diff($changed, ['updated_at'])) + ['updated_at' => 1])];
                }
            }
            foreach (array_diff_key($after[$table], $before[$table]) as $id => $new) {
                $undo[] = ['table' => $table, 'id' => $id, '_inserted' => true];
            }
        }
        return $undo;
    }

    /** List only: rolls on a PO line of another fabric (made by the old PO "adjustment" page). */
    private function wrongFabricRolls(): array
    {
        return DB::table('fabric_receipt_details as d')->join('purchase_order_items as i', 'i.id', '=', 'd.purchase_order_item_id')
            ->join('fabric_receipts as r', 'r.id', '=', 'd.fabric_receipt_id')->leftJoin('purchase_orders as p', 'p.id', '=', 'i.purchase_order_id')
            ->leftJoin('fabrics as f', 'f.id', '=', 'd.fabric_id')->leftJoin('fabrics as lf', 'lf.id', '=', 'i.fabric_id')
            ->whereColumn('i.fabric_id', '!=', 'd.fabric_id')
            ->orderBy('d.id')
            ->get(['d.roll_number', 'd.meter', 'r.id as shipment_id', 'r.sku as shipment', 'p.sku as po', 'f.name as roll_fabric', 'lf.name as line_fabric'])
            ->map(fn($r) => (array) $r)->all();
    }


    /** List only: shipments whose rolls do not add up to the bill (meters or amount). */
    private function billMismatch(): array
    {
        return DB::table('fabric_receipts as r')
            ->joinSub(DB::table('fabric_receipt_details')->groupBy('fabric_receipt_id')
                ->selectRaw('fabric_receipt_id, COUNT(*) AS rolls, SUM(meter) AS meters, SUM(ROUND(meter * price_per_meter, 2)) AS amount'), 'x', 'x.fabric_receipt_id', '=', 'r.id')
            ->whereRaw('(ABS(r.total_meter - x.meters) > 0.01 OR ABS(r.amount - x.amount) > 1 OR r.roll <> x.rolls)')
            ->orderBy('r.id')
            ->get(['r.id', 'r.sku', 'r.bill_no', 'r.roll', 'r.total_meter', 'r.amount', 'x.rolls', 'x.meters', 'x.amount as rolls_amount'])
            ->map(fn($r) => (array) $r)->all();
    }
}
