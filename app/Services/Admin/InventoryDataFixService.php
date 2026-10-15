<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One-time repair of old inventory data left by bugs that are now fixed.
 *
 *  1. Order lines of a DELETED dispatch: deleting the dispatch put the boxes back in stock but the lines still said
 *     "picked". Those lines are un-picked (the boxes are already in stock).
 *  2. Such lines were then dispatched again without being picked again, so their boxes were never taken out of
 *     stock: they are taken out now (from the line's rack first) and saved as the line's picks, which also marks
 *     the line as repaired (running the repair again changes nothing).
 *  Undo log: rows listed with '_inserted' were added by the repair (undo = delete them).
 *  3. Purchase lines saved by the old "edit purchase" lost their type / user / date, so Inbound History stopped
 *     showing them: they get them back from their purchase.
 *  4. Negative fabric rolls / stock rows are set to 0; stock rows saved inactive are made active.
 *  Lists only (nothing changed): duplicated product variants, open order lines with 0 pieces, stock without a rack.
 *
 * run(false) = preview (everything is done inside a transaction and rolled back),
 * run(true)  = apply (committed, and every old value is saved to storage/app/inventory-fix/).
 */
class InventoryDataFixService
{
    public function run(bool $apply): array
    {
        @set_time_limit(900);

        $tables = ['agent_order_items', 'domestic_inventories', 'domestic_inventory_histories', 'fabric_receipt_details'];
        $notSafe = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)->where('ENGINE', '!=', 'InnoDB')->pluck('TABLE_NAME');
        if ($notSafe->isNotEmpty()) {
            throw new \RuntimeException('These tables cannot undo changes (not InnoDB), so the repair will not run: ' . $notSafe->implode(', '));
        }

        DB::beginTransaction();
        try {
            $undo = [];
            [$unpicked, $redispatched] = $this->deletedDispatchLines($undo);
            $result = [
                'unpicked' => $unpicked,
                'redispatched' => $redispatched,
                'purchase_lines' => $this->purchaseLines($undo),
                'negatives' => $this->negatives($undo),
                'duplicate_variants' => $this->duplicateVariants(),
                'zero_pcs_lines' => $this->zeroPcsLines(),
                'no_rack_stock' => DB::table('domestic_inventories')->whereNull('rack_id')->where('total_boxes', '>', 0)->count(),
                'picks_table' => Schema::hasTable('agent_order_item_picks'),
                'log_file' => null,
            ];

            if ($apply) {
                $file = 'inventory-fix/inventory-fix-' . now()->format('Ymd-His') . '.json';
                \Illuminate\Support\Facades\Storage::disk('local')->put($file, json_encode([
                    'run_at' => now()->toDateTimeString(),
                    'run_by' => auth()->id(),
                    'old_values' => $undo,
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

    /** Sections 1 and 2: order lines of deleted dispatches. */
    private function deletedDispatchLines(array &$undo): array
    {
        $picks = Schema::hasTable('agent_order_item_picks');
        // last deletion of each line
        $last = [];
        foreach (DB::table('deletion_logs')->where('module', 'Agent Dispatch')->orderBy('id')->get() as $log) {
            $p = json_decode($log->payload, true);
            foreach ($p['items'] ?? [] as $it) {
                $last[$it['id']] = ['at' => $log->created_at, 'dispatch' => $log->record_id];
            }
        }

        $unpicked = [];
        $redispatched = [];
        foreach ($last as $itemId => $then) {
            $line = DB::table('agent_order_items')->where('id', $itemId)->lockForUpdate()->first();
            if (!$line) continue; // line removed later: its boxes are in stock and nothing holds them - nothing to do
            // picked again (or already repaired) after the deletion: real picks, leave alone
            if ($picks && DB::table('agent_order_item_picks')->where('agent_order_item_id', $itemId)->where('created_at', '>', $then['at'])->exists()) {
                continue;
            }
            $label = trim("{$line->design_number} / {$line->color_name} / {$line->size_set_name}");

            if (!$line->dispatched_at) {
                if ((int) $line->scanned_box_qty <= 0) continue;
                // the deletion put these boxes back in stock: the line must not count them as picked as well
                $undo[] = ['table' => 'agent_order_items', 'id' => $line->id, 'scanned_box_qty' => $line->scanned_box_qty, 'scanned_quantity' => $line->scanned_quantity];
                DB::table('agent_order_items')->where('id', $line->id)->update(['scanned_box_qty' => 0, 'scanned_quantity' => 0]);
                $unpicked[] = ['order' => $line->agent_order_id, 'line' => $line->id, 'item' => $label, 'dispatch' => $then['dispatch'], 'deleted_at' => $then['at'],
                    'picked' => (int) $line->scanned_box_qty];
            } elseif ($picks && (int) $line->agent_order_dispatch_id !== (int) $then['dispatch']) {
                // dispatched again without being picked again: its boxes are still counted in stock.
                // They are taken out now and recorded as this line's picks (so a later dispatch delete puts them back there).
                $boxes = (int) $line->box_qty;
                $taken = $this->takeBoxes($line, $boxes, $undo);
                $redispatched[] = ['order' => $line->agent_order_id, 'line' => $line->id, 'item' => $label, 'deleted_dispatch' => $then['dispatch'],
                    'new_dispatch' => $line->agent_order_dispatch_id, 'boxes' => $boxes, 'taken' => $taken, 'short' => $boxes - $taken];
            }
        }
        return [$unpicked, $redispatched];
    }

    /**
     * Take boxes of an order line's item out of stock (the line's rack first, then the rows with most boxes)
     * and record them as the line's picks. Returns boxes taken.
     */
    private function takeBoxes($line, int $boxes, array &$undo): int
    {
        $rows = DB::table('domestic_inventories')
            ->where('product_id', $line->product_id)->where('color_id', $line->color_id)->where('size_set_id', $line->size_set_id)
            ->where('total_boxes', '>', 0)
            ->orderByRaw('CASE WHEN rack_id = ? THEN 0 ELSE 1 END', [(int) $line->rack_id])->orderByDesc('total_boxes')->orderBy('id')
            ->lockForUpdate()->get();
        $taken = 0;
        foreach ($rows as $row) {
            if ($taken >= $boxes) break;
            $n = min((int) $row->total_boxes, $boxes - $taken);
            $undo[] = ['table' => 'domestic_inventories', 'id' => $row->id, 'total_boxes' => $row->total_boxes];
            DB::table('domestic_inventories')->where('id', $row->id)->update(['total_boxes' => $row->total_boxes - $n]);
            $pickId = DB::table('agent_order_item_picks')->insertGetId([
                'agent_order_item_id' => $line->id,
                'domestic_inventory_id' => $row->id,
                'rack_id' => $row->rack_id,
                'boxes' => $n,
                'pcs_per_box' => (int) $row->quantity,
                'created_by' => auth()->id(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $undo[] = ['table' => 'agent_order_item_picks', 'id' => $pickId, '_inserted' => true];
            $taken += $n;
        }
        if ($taken < $boxes) {
            // not enough left in stock: still mark the line as handled so the repair does not run for it again
            $pickId = DB::table('agent_order_item_picks')->insertGetId([
                'agent_order_item_id' => $line->id, 'domestic_inventory_id' => null, 'rack_id' => $line->rack_id,
                'boxes' => 0, 'pcs_per_box' => 0, 'created_by' => auth()->id(), 'created_at' => now(), 'updated_at' => now(),
            ]);
            $undo[] = ['table' => 'agent_order_item_picks', 'id' => $pickId, '_inserted' => true];
        }
        return $taken;
    }

    /** Section 3: purchase lines re-saved by the old purchase edit. */
    private function purchaseLines(array &$undo): array
    {
        $out = [];
        $lines = DB::table('domestic_inventory_histories')->where('purchase_id', '>', 0)->where('type', 'attribute_change')
            ->whereNull('old_product_id')->whereNotNull('new_product_id')->orderBy('id')->get();
        foreach ($lines->groupBy('purchase_id') as $purchaseId => $group) {
            $purchase = DB::table('domestic_inventory_purchases')->where('id', $purchaseId)->first();
            if (!$purchase) continue;
            $session = DB::table('packing_mains')->where('order_main_id', 0)->where('slip_id', 0)
                ->whereBetween('created_at', [date('Y-m-d H:i:s', strtotime($purchase->created_at) - 10), date('Y-m-d H:i:s', strtotime($purchase->created_at) + 10)])
                ->orderBy('created_at')->value('created_at');
            $createdAt = $session ?: $purchase->created_at;
            foreach ($group as $h) {
                $undo[] = ['table' => 'domestic_inventory_histories', 'id' => $h->id, 'type' => $h->type, 'user_id' => $h->user_id,
                    'vendor_id' => $h->vendor_id, 'customer_id' => $h->customer_id, 'created_at' => $h->created_at];
                DB::table('domestic_inventory_histories')->where('id', $h->id)->update([
                    'type' => 'creation',
                    'user_id' => $h->user_id ?: $purchase->user_id,
                    'vendor_id' => $h->vendor_id ?: $purchase->vendor_id,
                    'customer_id' => $h->customer_id ?: $purchase->customer_id,
                    'created_at' => $createdAt,
                ]);
            }
            $out[] = ['purchase' => $purchaseId, 'bill_no' => $purchase->bill_no, 'lines' => $group->count(), 'boxes' => (int) $group->sum('box_quantity'), 'date' => $createdAt];
        }
        return $out;
    }

    /** Section 4: negative fabric rolls / stock rows, inactive stock rows. */
    private function negatives(array &$undo): array
    {
        $out = [];
        foreach (DB::table('fabric_receipt_details')->where('remaining_quantity', '<', 0)->get() as $roll) {
            $undo[] = ['table' => 'fabric_receipt_details', 'id' => $roll->id, 'remaining_quantity' => $roll->remaining_quantity];
            DB::table('fabric_receipt_details')->where('id', $roll->id)->update(['remaining_quantity' => 0]);
            $out[] = ['what' => 'Fabric roll ' . ($roll->roll_number ?? '#' . $roll->id), 'was' => (float) $roll->remaining_quantity . ' m', 'now' => '0 m'];
        }
        foreach (DB::table('domestic_inventories')->where(fn($q) => $q->where('total_boxes', '<', 0)->orWhere('status', '!=', 1))->get() as $row) {
            $undo[] = ['table' => 'domestic_inventories', 'id' => $row->id, 'total_boxes' => $row->total_boxes, 'status' => $row->status];
            DB::table('domestic_inventories')->where('id', $row->id)->update(['total_boxes' => max(0, (int) $row->total_boxes), 'status' => 1]);
            $out[] = ['what' => "Stock row {$row->barcode} (rack #{$row->rack_id})", 'was' => "{$row->total_boxes} boxes" . ($row->status != 1 ? ', inactive' : ''), 'now' => max(0, (int) $row->total_boxes) . ' boxes, active'];
        }
        return $out;
    }

    private function duplicateVariants(): array
    {
        return DB::table('production_goods_variants as v')
            ->leftJoin('production_goods as g', 'g.id', '=', 'v.production_goods_id')
            ->leftJoin('master_size_measurements as s', 's.id', '=', 'v.master_size_measurement_id')
            ->select('g.design_number', 's.name as size_set', DB::raw('GROUP_CONCAT(v.id ORDER BY v.id) as variant_ids'), DB::raw('GROUP_CONCAT(v.mrp ORDER BY v.id) as mrps'))
            ->groupBy('v.production_goods_id', 'v.master_size_measurement_id', 'g.design_number', 's.name')
            ->havingRaw('COUNT(*) > 1')->get()->map(fn($r) => (array) $r)->all();
    }

    private function zeroPcsLines(): array
    {
        return DB::table('agent_order_items as i')->join('agent_orders as o', 'o.id', '=', 'i.agent_order_id')
            ->whereNull('i.dispatched_at')->where('i.box_qty', '>', 0)->where(fn($q) => $q->whereNull('i.quantity')->orWhere('i.quantity', '<=', 0))
            ->select('o.id as order', 'i.design_number', 'i.color_name', 'i.size_set_name', 'i.box_qty')->get()->map(fn($r) => (array) $r)->all();
    }
}
