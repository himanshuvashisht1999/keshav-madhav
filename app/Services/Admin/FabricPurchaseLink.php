<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Keeps fabric PO lines and received rolls in step.
 *  - "left to receive" of a PO line = ordered meters - meters of the rolls linked to that line (never below 0)
 *  - a new roll is linked to the first line of its PO with the same fabric that still has meters left
 *  - a roll that is used anywhere (cut, sold, returned, transferred, disposed) must not be deleted
 */
class FabricPurchaseLink
{
    private static $tables = [];

    /** Meters received on each of these PO lines (line id => meters). */
    public static function receivedByLine(array $lineIds): array
    {
        $lineIds = self::ids($lineIds);
        if (!$lineIds) {
            return [];
        }
        return DB::table('fabric_receipt_details')->whereIn('purchase_order_item_id', $lineIds)
            ->groupBy('purchase_order_item_id')
            ->selectRaw('purchase_order_item_id, SUM(meter) AS received')
            ->pluck('received', 'purchase_order_item_id')
            ->map(fn($m) => (float) $m)->all();
    }

    /** Recalculate "left to receive" of these PO lines from the rolls linked to them. */
    public static function recalcLines(array $lineIds): void
    {
        $lineIds = self::ids($lineIds);
        if (!$lineIds) {
            return;
        }
        $correct = 'GREATEST(meter - (SELECT COALESCE(SUM(d.meter), 0) FROM fabric_receipt_details d WHERE d.purchase_order_item_id = purchase_order_items.id), 0)';
        DB::table('purchase_order_items')->whereIn('id', $lineIds)
            ->whereRaw("(remaining_quantity IS NULL OR ABS(remaining_quantity - $correct) > 0.001)")
            ->update(['remaining_quantity' => DB::raw($correct), 'updated_at' => now()]);
    }

    /** Lines of a PO with the meters still to receive, for picking a line while rolls are saved. */
    public static function openLines(int $poId): array
    {
        $lines = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->orderBy('id')->get(['id', 'fabric_id', 'meter']);
        $received = self::receivedByLine($lines->pluck('id')->all());
        $out = [];
        foreach ($lines as $line) {
            $out[$line->id] = ['fabric_id' => (int) $line->fabric_id, 'left' => (float) $line->meter - ($received[$line->id] ?? 0)];
        }
        return $out;
    }

    /** PO line for a roll of this fabric: the first one with meters left, else the first one; 0 = the PO has no line for it. */
    public static function pickLine(array &$lines, int $fabricId, float $meter): int
    {
        $first = 0;
        foreach ($lines as $id => $line) {
            if ($line['fabric_id'] !== $fabricId) {
                continue;
            }
            if (!$first) {
                $first = $id;
            }
            if ($line['left'] > 0.0001) {
                $lines[$id]['left'] -= $meter;
                return $id;
            }
        }
        if ($first) {
            $lines[$first]['left'] -= $meter;
        }
        return $first;
    }

    /** Of these rolls, the ones already used or referenced anywhere (they must not be deleted). */
    public static function usedRolls(array $rollIds): array
    {
        $rollIds = self::ids($rollIds);
        if (!$rollIds) {
            return [];
        }
        return self::ids(DB::table('fabric_receipt_details as d')->whereIn('d.id', $rollIds)
            ->whereRaw(self::usedSql())->pluck('d.id')->all());
    }

    /** True when any roll of this shipment is already used or referenced anywhere. */
    public static function shipmentHasUsedRolls(int $receiptId): bool
    {
        return DB::table('fabric_receipt_details as d')->where('d.fabric_receipt_id', $receiptId)
            ->whereRaw(self::usedSql())->exists();
    }

    /** SQL condition on roll "d": cut / sold / returned (meters gone) or listed in a return, transfer, cutting, order or disposal. */
    private static function usedSql(): string
    {
        $or = ['d.remaining_quantity < d.meter'];
        foreach (['fabric_return_details', 'fabric_transfer_items', 'production_fabric_roll_assigning', 'agent_order_fabric_items'] as $table) {
            if (self::hasTable($table)) {
                $or[] = "EXISTS (SELECT 1 FROM {$table} x WHERE x.fabric_receipt_detail_id = d.id)";
            }
        }
        if (self::hasTable('stock_disposals')) {
            $or[] = "EXISTS (SELECT 1 FROM stock_disposals x WHERE x.item_type = 'fabric' AND x.item_id = d.id)";
        }
        return '(' . implode(' OR ', $or) . ')';
    }

    /** A PO made automatically for a shipment received without a PO (the PO form always asks for company and warehouse). */
    public static function isAutoPo($po): bool
    {
        return $po && $po->master_company_id === null && $po->fabric_warehouse_id === null;
    }

    /**
     * A PO made automatically for a direct shipment only records what that shipment brought in: one line per fabric
     * with the meters of its rolls, so nothing stays "left to receive". Called after its shipment is edited or deleted.
     * When no roll is left on it (shipment deleted), the PO is removed and closed. Returns 'removed', 'updated' or null.
     */
    public static function syncAutoPo(int $poId): ?string
    {
        $po = DB::table('purchase_orders')->where('id', $poId)->first();
        if (!self::isAutoPo($po) || (int) $po->status === 0) {
            return null;
        }
        $rolls = DB::table('fabric_receipt_details')->where('purchase_order_id', $poId)->orderBy('id')
            ->get(['id', 'fabric_id', 'fabric_sku', 'meter', 'price_per_meter', 'purchase_order_item_id']);
        if ($rolls->isEmpty()) {
            DB::table('purchase_orders')->where('id', $poId)->update(['status' => 0, 'is_closed' => 1, 'updated_at' => now()]);
            return 'removed';
        }
        $lines = DB::table('purchase_order_items')->where('purchase_order_id', $poId)->orderBy('id')->get();
        $changed = false;
        $keep = [];
        foreach ($rolls->groupBy('fabric_id') as $fabricId => $fabricRolls) {
            $meter = round((float) $fabricRolls->sum('meter'), 2);
            $line = $lines->firstWhere('fabric_id', $fabricId);
            if (!$line) {
                $price = (float) $fabricRolls->first()->price_per_meter;
                $lineId = DB::table('purchase_order_items')->insertGetId([
                    'purchase_order_id' => $poId, 'fabric_sku' => $fabricRolls->first()->fabric_sku, 'fabric_id' => $fabricId,
                    'meter' => $meter, 'remaining_quantity' => 0, 'price' => $price, 'total_price' => round($meter * $price, 2),
                    'status' => 1, 'created_at' => now(), 'updated_at' => now(),
                ]);
                $changed = true;
            } else {
                $lineId = $line->id;
                if (abs((float) $line->meter - $meter) > 0.001 || (float) $line->remaining_quantity != 0.0) {
                    DB::table('purchase_order_items')->where('id', $lineId)->update([
                        'meter' => $meter, 'remaining_quantity' => 0, 'total_price' => round($meter * (float) $line->price, 2), 'updated_at' => now(),
                    ]);
                    $changed = true;
                }
            }
            $keep[] = $lineId;
            $unlinked = $fabricRolls->filter(fn($r) => (int) $r->purchase_order_item_id !== (int) $lineId)->pluck('id')->all();
            if ($unlinked) {
                DB::table('fabric_receipt_details')->whereIn('id', $unlinked)->update(['purchase_order_item_id' => $lineId, 'updated_at' => now()]);
                $changed = true;
            }
        }
        // lines of a fabric no longer in the shipment (no roll points to them any more)
        $gone = $lines->pluck('id')->diff($keep)->all();
        if ($gone && !DB::table('fabric_receipt_details')->whereIn('purchase_order_item_id', $gone)->exists()) {
            DB::table('purchase_order_items')->whereIn('id', $gone)->delete();
            $changed = true;
        }
        return $changed ? 'updated' : null;
    }

    private static $returned = null;

    /**
     * Value of the fabric returned from a shipment (with the return's GST / charges / discount), so its pending
     * amount = bill - paid - returned. A return with rolls of several shipments is shared by the value of each
     * shipment's rolls (the last shipment gets the paisa left over, so the shares add up to the return exactly).
     */
    public static function returnedAmount(int $receiptId): float
    {
        if (self::$returned === null) {
            self::$returned = self::allReturned();
        }
        return self::$returned[$receiptId] ?? 0.0;
    }

    /** Call after a return is saved, changed or deleted. */
    public static function forgetReturned(): void
    {
        self::$returned = null;
    }

    private static function allReturned(): array
    {
        $returns = DB::table('fabric_returns')->get(['id', 'fabric_receipt_id', 'total_amount'])->keyBy('id');
        if ($returns->isEmpty()) {
            return [];
        }
        $parts = DB::table('fabric_return_details as rd')->join('fabric_receipt_details as d', 'd.id', '=', 'rd.fabric_receipt_detail_id')
            ->groupBy('rd.fabric_return_id', 'd.fabric_receipt_id')
            ->selectRaw('rd.fabric_return_id, d.fabric_receipt_id, SUM(rd.return_meter * rd.price_per_meter) AS value')
            ->orderBy('rd.fabric_return_id')->orderBy('d.fabric_receipt_id')
            ->get()->groupBy('fabric_return_id');
        $out = [];
        foreach ($returns as $id => $return) {
            $total = round((float) $return->total_amount, 2);
            $rows = $parts->get($id, collect())->values();
            $all = (float) $rows->sum('value');
            if ($rows->isEmpty() || $all <= 0) {
                // no roll values to share by: the whole return belongs to its shipment
                $to = $return->fabric_receipt_id ?: optional($rows->first())->fabric_receipt_id;
                if ($to) {
                    $out[$to] = round(($out[$to] ?? 0) + $total, 2);
                }
                continue;
            }
            $given = 0;
            foreach ($rows as $i => $row) {
                $share = $i === $rows->count() - 1 ? round($total - $given, 2) : round($total * (float) $row->value / $all, 2);
                $given += $share;
                $out[$row->fabric_receipt_id] = round(($out[$row->fabric_receipt_id] ?? 0) + $share, 2);
            }
        }
        return $out;
    }

    private static function hasTable(string $table): bool
    {
        return self::$tables[$table] ??= Schema::hasTable($table);
    }

    private static function ids(array $ids): array
    {
        return array_values(array_unique(array_filter(array_map('intval', $ids))));
    }
}
