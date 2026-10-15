<?php

namespace App\Services\Admin;

use App\Models\DomesticInventory;
use App\Models\DomesticInventoryHistory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Warehouse stock (domestic_inventories) rules, used by every inventory screen:
 *  - a stock row is one item (product + size set + colour) with one pieces-per-box in one rack;
 *  - boxes are only added to / taken from the row of the right rack with the same pieces-per-box;
 *  - rows are never deleted when they reach 0 (disposals, picks and returns point to them);
 *  - stock never goes below 0;
 *  - every box picked for an order line is remembered with the row it came from
 *    (agent_order_item_picks), so un-picking or deleting a dispatch puts it back exactly there.
 * All methods must run inside a DB transaction (rows are locked while they change).
 */
class InventoryStockService
{
    private static ?bool $picksTable = null;

    public static function barcode($productId, $sizeSetId, $colorId): string
    {
        return 'D' . $productId . 'S' . $sizeSetId . 'C' . $colorId;
    }

    private static ?array $duplicateVariantIds = null;

    /**
     * For a join on production_goods_variants as "variants": when a product + size set was entered twice,
     * only the first variant (lowest id) is joined, so its stock is not listed twice.
     */
    public static function skipDuplicateVariants($join): void
    {
        self::$duplicateVariantIds ??= DB::table('production_goods_variants as v')
            ->join('production_goods_variants as first_v', function ($j) {
                $j->on('first_v.production_goods_id', '=', 'v.production_goods_id')
                  ->on('first_v.master_size_measurement_id', '=', 'v.master_size_measurement_id')
                  ->on('first_v.id', '<', 'v.id');
            })
            ->distinct()->pluck('v.id')->all();
        if (!empty(self::$duplicateVariantIds)) {
            $join->whereNotIn('variants.id', self::$duplicateVariantIds);
        }
    }

    /**
     * SQL of the boxes open orders still have to take from stock, grouped by the given order-line columns:
     * lines not dispatched yet, minus boxes already picked (picked boxes have already left the stock).
     */
    public static function openOrderBoxesSql(array $groupBy, string $alias, string $extraWhere = ''): string
    {
        $cols = implode(', ', array_map(fn($c) => "aoi.$c", $groupBy));
        return "(SELECT $cols, SUM(GREATEST(aoi.box_qty - aoi.scanned_box_qty, 0)) AS $alias
            FROM agent_order_items aoi
            JOIN agent_orders ao ON aoi.agent_order_id = ao.id
            WHERE ao.status != 'dispatched' AND aoi.dispatched_at IS NULL $extraWhere
            GROUP BY $cols)";
    }

    public function picksEnabled(): bool
    {
        return self::$picksTable ??= Schema::hasTable('agent_order_item_picks');
    }

    /** The row of this item in this rack with this pieces-per-box (an empty one is made when there is none). */
    public function rowAt($productId, $colorId, $sizeSetId, $rackId, $pcsPerBox, array $extra = []): DomesticInventory
    {
        $row = DomesticInventory::where('product_id', $productId)->where('color_id', $colorId)->where('size_set_id', $sizeSetId)
            ->when($rackId, fn($q) => $q->where('rack_id', $rackId), fn($q) => $q->whereNull('rack_id'))
            ->where('quantity', (int) $pcsPerBox)
            ->orderByRaw('CASE WHEN order_main_id = 0 THEN 0 ELSE 1 END')->orderBy('id')
            ->lockForUpdate()->first();
        if ($row) {
            return $row;
        }

        $barcode = self::barcode($productId, $sizeSetId, $colorId);
        $row = new DomesticInventory(array_merge([
            'order_main_id' => 0,
            'product_id' => $productId,
            'color_id' => $colorId,
            'size_set_id' => $sizeSetId,
            'rack_id' => $rackId ?: null,
            'quantity' => (int) $pcsPerBox,
            'total_boxes' => 0,
            'barcode' => $barcode,
            'status' => 1,
        ], $extra));
        $row->qrcode = $barcode;
        $row->save();
        return $row;
    }

    public function lockRow($id): ?DomesticInventory
    {
        return $id ? DomesticInventory::lockForUpdate()->find($id) : null;
    }

    public function add(DomesticInventory $row, int $boxes): void
    {
        if ($boxes <= 0) return;
        $row->total_boxes = (int) $row->total_boxes + $boxes;
        $row->status = 1;
        $row->save();
    }

    public function take(DomesticInventory $row, int $boxes): void
    {
        if ($boxes <= 0) return;
        if ((int) $row->total_boxes < $boxes) {
            throw new \Exception("Only {$row->total_boxes} box(es) of {$row->barcode} are left in that rack, {$boxes} needed.");
        }
        $row->total_boxes = (int) $row->total_boxes - $boxes;
        $row->save();
    }

    /**
     * Rows an order can take boxes from, best first (storeroom priority, then most boxes).
     * $rule 'dispatch' = storeroom allows order dispatch (picking); 'sale' = storeroom takes orders (direct sale).
     */
    public function pickableRows($productId, $colorId, $sizeSetId, string $rule = 'dispatch')
    {
        return DomesticInventory::select('domestic_inventories.*')
            ->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->where('domestic_inventories.product_id', $productId)
            ->where('domestic_inventories.color_id', $colorId)
            ->where('domestic_inventories.size_set_id', $sizeSetId)
            ->where('domestic_inventories.total_boxes', '>', 0)
            ->where(function ($q) use ($rule) {
                $q->whereNull('storerooms.id');
                if ($rule === 'sale') {
                    $q->orWhere('storerooms.order_taken', '=', 'Yes');
                } else {
                    $q->orWhere('storerooms.order_dispatch', '!=', 'No');
                }
            })
            ->orderByRaw("CASE WHEN storerooms.order_priority IS NULL OR storerooms.order_priority = '' THEN 9999 ELSE CAST(storerooms.order_priority AS UNSIGNED) END ASC")
            ->orderBy('domestic_inventories.total_boxes', 'desc')
            ->orderBy('domestic_inventories.id')
            ->lockForUpdate()
            ->get();
    }

    public function pickableBoxes($productId, $colorId, $sizeSetId, string $rule = 'dispatch'): int
    {
        return (int) $this->pickableRows($productId, $colorId, $sizeSetId, $rule)->sum('total_boxes');
    }

    /**
     * Take up to $boxes boxes for an order line from the best rows and remember where each came from.
     * @return array [boxes taken, pieces taken, last row used]
     */
    public function pickForLine($item, int $boxes, string $rule = 'dispatch'): array
    {
        $taken = 0;
        $pcs = 0;
        $last = null;
        if ($boxes <= 0) return [0, 0, null];
        foreach ($this->pickableRows($item->product_id, $item->color_id, $item->size_set_id, $rule) as $row) {
            if ($taken >= $boxes) break;
            $n = min((int) $row->total_boxes, $boxes - $taken);
            if ($n <= 0) continue;
            $this->take($row, $n);
            $this->recordPick($item->id, $row, $n);
            $taken += $n;
            $pcs += $n * (int) $row->quantity;
            $last = $row;
        }
        return [$taken, $pcs, $last];
    }

    public function recordPick($itemId, DomesticInventory $row, int $boxes): void
    {
        if (!$this->picksEnabled() || $boxes <= 0) return;
        DB::table('agent_order_item_picks')->insert([
            'agent_order_item_id' => $itemId,
            'domestic_inventory_id' => $row->id,
            'rack_id' => $row->rack_id,
            'boxes' => $boxes,
            'pcs_per_box' => (int) $row->quantity,
            'created_by' => auth()->id(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** Move the pick records of one order line to another (lines merged / replaced). */
    public function movePicks($fromItemId, $toItemId): void
    {
        if (!$this->picksEnabled() || $fromItemId == $toItemId) return;
        DB::table('agent_order_item_picks')->where('agent_order_item_id', $fromItemId)->update(['agent_order_item_id' => $toItemId, 'updated_at' => now()]);
    }

    /**
     * Put $boxes picked boxes of an order line back where they came from (last picked first).
     * Boxes picked before pick tracking existed go back to the line's rack.
     * @return int pieces given back
     */
    public function unpickLine($item, int $boxes): int
    {
        $left = $boxes;
        $pcs = 0;
        if ($left <= 0) return 0;

        if ($this->picksEnabled()) {
            $picks = DB::table('agent_order_item_picks')->where('agent_order_item_id', $item->id)->orderByDesc('id')->lockForUpdate()->get();
            foreach ($picks as $p) {
                if ($left <= 0) break;
                if ((int) $p->boxes <= 0) { // marker without boxes
                    DB::table('agent_order_item_picks')->where('id', $p->id)->delete();
                    continue;
                }
                $n = min((int) $p->boxes, $left);
                $row = $this->lockRow($p->domestic_inventory_id)
                    ?: $this->rowAt($item->product_id, $item->color_id, $item->size_set_id, $p->rack_id, $p->pcs_per_box);
                $this->add($row, $n);
                $pcs += $n * (int) $p->pcs_per_box;
                if ($n >= (int) $p->boxes) {
                    DB::table('agent_order_item_picks')->where('id', $p->id)->delete();
                } else {
                    DB::table('agent_order_item_picks')->where('id', $p->id)->update(['boxes' => (int) $p->boxes - $n, 'updated_at' => now()]);
                }
                $left -= $n;
            }
        }

        if ($left > 0) {
            $pcsPerBox = $this->linePcsPerBox($item);
            $this->add($this->legacyRow($item, $pcsPerBox), $left);
            $pcs += $left * $pcsPerBox;
        }
        return $pcs;
    }

    /** Pieces per box of an order line (from what was picked, else from the order quantity). */
    public function linePcsPerBox($item): int
    {
        if ((int) $item->scanned_box_qty > 0 && (float) $item->scanned_quantity > 0) {
            return (int) round($item->scanned_quantity / $item->scanned_box_qty);
        }
        return (int) $item->box_qty > 0 ? (int) round($item->quantity / $item->box_qty) : 0;
    }

    /** Row for boxes picked before pick tracking: the line's rack (same pieces-per-box first), else the item's best row. */
    private function legacyRow($item, int $pcsPerBox): DomesticInventory
    {
        $base = fn() => DomesticInventory::where('product_id', $item->product_id)->where('color_id', $item->color_id)->where('size_set_id', $item->size_set_id)
            ->orderByRaw('CASE WHEN quantity = ? THEN 0 ELSE 1 END', [$pcsPerBox]);
        if (!empty($item->rack_id)) {
            return $base()->where('rack_id', $item->rack_id)->orderBy('id')->lockForUpdate()->first()
                ?: $this->rowAt($item->product_id, $item->color_id, $item->size_set_id, $item->rack_id, $pcsPerBox);
        }
        return $base()->whereNotNull('rack_id')->orderByDesc('total_boxes')->orderBy('id')->lockForUpdate()->first()
            ?: $this->rowAt($item->product_id, $item->color_id, $item->size_set_id, null, $pcsPerBox);
    }

    /** A history line with the product and rack filled in. */
    public function history(string $type, DomesticInventory $row, int $boxes, array $extra = []): void
    {
        DomesticInventoryHistory::create(array_merge([
            'user_id' => auth()->id(),
            'type' => $type,
            'old_product_id' => $row->product_id,
            'old_size_set_id' => $row->size_set_id,
            'old_color_id' => $row->color_id,
            'old_rack_id' => $row->rack_id,
            'old_warehouse_id' => $row->rack_id ? DB::table('racks')->where('id', $row->rack_id)->value('storeroom_id') : null,
            'box_quantity' => $boxes,
            'pieces_per_box' => (int) $row->quantity,
        ], $extra));
    }
}
