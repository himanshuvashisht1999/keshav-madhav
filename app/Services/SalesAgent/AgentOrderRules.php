<?php

namespace App\Services\SalesAgent;

use App\Models\DomesticInventory;
use App\Models\MasterColor;
use App\Models\MasterCustomer;
use App\Models\MasterDesignPattern;
use App\Models\MasterProductFitting;
use App\Models\MasterSizeMeasurement;
use App\Models\ProductionGoods;
use App\Models\Storeroom;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Rules for orders booked in the sales agent app (online screens and offline sync).
 *
 * The server decides who the agent may order for, the price, pieces per box, stock and the totals.
 * What the phone sends is only kept when it is something the app really offers
 * (e.g. the price shown for that shop); anything else gets the normal value.
 * Stock-changing calls must run inside a DB transaction.
 */
class AgentOrderRules
{
    private ?object $settings = null;
    private ?bool $hasFairTables = null;

    public function settings(): object
    {
        return $this->settings ??= (DB::table('settings')->first() ?: (object) []);
    }

    /** GST % on agent app orders: the order GST setting, the same default admin orders use (5%). */
    public function gstPercent(): float
    {
        $v = $this->settings()->gst_order ?? null;
        return ($v === null || $v === '') ? 5.0 : (float) $v;
    }

    /** The customer this agent may order for: active, and one of the agent's own shops unless it is a master agent. */
    public function customerFor($agent, $customerId): ?MasterCustomer
    {
        if (empty($customerId) || !is_numeric($customerId)) return null;
        return MasterCustomer::where('id', $customerId)->where('status', 1)
            ->when(!$agent->is_master_agent, fn($q) => $q->where('sales_agent_id', $agent->id))
            ->first();
    }

    /** Only master agents order for vendors (active vendors, as in their party list). */
    public function vendorFor($agent, $vendorId)
    {
        if (!$agent->is_master_agent || empty($vendorId) || !is_numeric($vendorId)) return null;
        return DB::table('vendors')->where('id', $vendorId)->where('status', 1)->first();
    }

    /** An active sales man, else none. */
    public function salesManId($id)
    {
        if (empty($id) || !is_numeric($id)) return null;
        return DB::table('sales_men')->where('id', $id)->where('status', 1)->value('id');
    }

    public function brandDiscount(string $partyType, $customerId, $brandId): float
    {
        if ($partyType === 'vendor' || empty($customerId)) return 0.0;
        return (float) (DB::table('customer_brand_discounts')->where('customer_id', $customerId)
            ->where('brand_id', $brandId)->value('discount_percentage') ?? 0);
    }

    /** Sample-set (fair batch) discounts this agent may give on a product + size set (active batches given to the agent). */
    public function fairDiscounts($agent, $productId, $sizeSetId): array
    {
        $this->hasFairTables ??= Schema::hasTable('fair_products') && Schema::hasTable('fair_batches');
        if (!$this->hasFairTables) return [];
        $rows = DB::table('fair_products')->join('fair_batches', 'fair_batches.id', '=', 'fair_products.fair_batch_id')
            ->where('fair_products.product_id', $productId)->where('fair_products.size_set_id', $sizeSetId)
            ->where('fair_batches.status', 1)
            ->get(['fair_products.discount_percent', 'fair_batches.sales_agent_ids']);
        $out = [];
        foreach ($rows as $r) {
            if (!$agent->is_master_agent && !self::batchIsForAgent($r->sales_agent_ids, $agent->id)) continue;
            $out[] = (float) $r->discount_percent;
        }
        return $out;
    }

    /** A sample-set batch given to no agent in particular is for everyone. */
    public static function batchIsForAgent($salesAgentIds, $agentId): bool
    {
        $ids = is_array($salesAgentIds) ? $salesAgentIds : (json_decode((string) $salesAgentIds, true) ?: []);
        return empty($ids) || in_array((string) $agentId, array_map('strval', $ids), true);
    }

    /** Price after a discount, rounded up like the app (both ways of writing it, so float noise never matters). */
    private static function discounted(float $mrp, float $pct): array
    {
        return array_unique([
            ceil(round($mrp - ($mrp * $pct / 100), 4)),
            ceil(round($mrp * (100 - $pct) / 100, 4)),
            ceil($mrp - ($mrp * $pct / 100)),
            ceil($mrp * (100 - $pct) / 100),
        ]);
    }

    /**
     * Price of one piece for a new order line, and the MRP saved with it.
     * The price the phone sent is kept only when the app offers it for this party: MRP less the customer's brand
     * discount, or less a sample-set discount given to this agent. Anything else gets the normal price
     * (MRP less the customer's brand discount; vendors pay MRP).
     * @return array [price, mrp]
     */
    public function linePrice($agent, string $partyType, $customerId, ProductionGoods $product, $sizeSetId, $sentPrice): array
    {
        $mrps = DB::table('production_goods_variants')->where('production_goods_id', $product->id)
            ->where('master_size_measurement_id', $sizeSetId)->orderBy('id')->pluck('mrp')->map(fn($m) => (float) $m)->all();
        $mrp = $mrps[0] ?? 0.0;
        $brand = $this->brandDiscount($partyType, $customerId, $product->brand_id);
        $normal = ceil($mrp - ($mrp * $brand / 100));
        if ($sentPrice === null || $sentPrice === '' || !is_numeric($sentPrice)) {
            return [$normal, $mrp];
        }
        $sent = (float) $sentPrice;
        $discounts = array_merge([$brand], $this->fairDiscounts($agent, $product->id, $sizeSetId));
        foreach ($mrps ?: [0.0] as $m) {
            foreach ($discounts as $d) {
                foreach (self::discounted($m, $d) as $offered) {
                    if (abs($offered - $sent) < 0.005) return [$offered, $mrp];
                }
            }
        }
        return [$normal, $mrp];
    }

    /**
     * Pieces in one box: the number the phone sent when a stock box of this item really has it
     * (or the size set's own pieces), else the biggest box in stock, else the size set's pieces.
     */
    public function piecesPerBox($productId, $colorId, $sizeSetId, $sent, $sizeSet): float
    {
        $rows = fn() => DB::table('domestic_inventories')->where('product_id', $productId)
            ->where('color_id', $colorId)->where('size_set_id', $sizeSetId)->where('quantity', '>', 0);
        $sizePcs = (float) ($sizeSet->no_of_pcs ?? 0);
        $known = $rows()->distinct()->pluck('quantity')->map(fn($q) => (float) $q)->all();
        if ($sizePcs > 0) $known[] = $sizePcs;
        $sent = is_numeric($sent) ? (float) $sent : 0.0;
        foreach ($known as $k) {
            if ($sent > 0 && abs($k - $sent) < 0.0001) return $k;
        }
        $best = (float) ($rows()->where('status', 1)->max('quantity') ?? 0);
        if ($best <= 0) $best = (float) ($rows()->max('quantity') ?? 0);
        if ($best <= 0) $best = $sizePcs;
        return $best > 0 ? $best : 1.0;
    }

    /**
     * Boxes of an item free to order: stock in storerooms that take orders, less the boxes open orders
     * (other than $exceptOrderId) still have to take. Locking reads, so two agents never get the same last box.
     */
    public function freeBoxes($productId, $colorId, $sizeSetId, $exceptOrderId = null): int
    {
        $stock = (int) DB::table('domestic_inventories as d')
            ->leftJoin('racks as r', 'r.id', '=', 'd.rack_id')
            ->leftJoin('storerooms as s', 's.id', '=', 'r.storeroom_id')
            ->where('d.product_id', $productId)->where('d.color_id', $colorId)->where('d.size_set_id', $sizeSetId)
            ->where('d.status', 1)
            ->where(fn($q) => $q->whereNull('s.id')->orWhere('s.order_taken', 'Yes'))
            ->lockForUpdate()
            ->sum('d.total_boxes');
        $open = (int) DB::table('agent_order_items as aoi')
            ->join('agent_orders as ao', 'ao.id', '=', 'aoi.agent_order_id')
            ->where('ao.status', '!=', 'dispatched')->whereNull('aoi.dispatched_at')
            ->where('aoi.product_id', $productId)->where('aoi.color_id', $colorId)->where('aoi.size_set_id', $sizeSetId)
            ->when($exceptOrderId, fn($q) => $q->where('ao.id', '!=', $exceptOrderId))
            ->lockForUpdate()
            ->sum(DB::raw('GREATEST(aoi.box_qty - aoi.scanned_box_qty, 0)'));
        return max(0, $stock - $open);
    }

    /** Item kept in a storeroom set to "Order more than stock" (no stock limit for it). */
    public function isOverStockItem($productId, $colorId, $sizeSetId): bool
    {
        return DB::table('domestic_inventories')
            ->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
            ->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
            ->where('domestic_inventories.product_id', $productId)
            ->where('domestic_inventories.color_id', $colorId)
            ->where('domestic_inventories.size_set_id', $sizeSetId)
            ->where('domestic_inventories.status', 1)
            ->whereRaw(Storeroom::overStockSql())
            ->exists();
    }

    /** App settings "allow ordering more than stock" (all orders) and "... for sample-set orders" (as the order screens allow). */
    public function stockLimitOff(bool $sampleSet): bool
    {
        $s = $this->settings();
        return !empty($s->agent_app_allow_over_stock) || ($sampleSet && !empty($s->agent_app_allow_over_stock_sample));
    }

    /**
     * Refuse boxes beyond the free stock.
     * @param array $need key => ['product_id', 'color_id', 'size_set_id', 'boxes' (still to come from stock), 'label']
     */
    public function checkStock(array $need, bool $sampleSet, $exceptOrderId = null): void
    {
        if ($this->stockLimitOff($sampleSet)) return;
        $short = [];
        foreach ($need as $n) {
            if ($n['boxes'] <= 0 || $this->isOverStockItem($n['product_id'], $n['color_id'], $n['size_set_id'])) continue;
            $free = $this->freeBoxes($n['product_id'], $n['color_id'], $n['size_set_id'], $exceptOrderId);
            if ($n['boxes'] > $free) {
                $short[] = "{$n['label']}: only {$free} box(es) free, {$n['boxes']} ordered";
            }
        }
        if ($short) {
            throw new AgentOrderException('Not enough stock. ' . implode('; ', $short) . '.');
        }
    }

    public static function itemLabel($designNumber, $colorName, $sizeSetName): string
    {
        return trim(($designNumber ?: '-') . ' ' . ($colorName ?? '') . ' ' . ($sizeSetName ?? ''));
    }

    /**
     * New order lines from what the phone sent (box quantities of the same item are added together).
     * @return array [lines (without agent_order_id), stock need per item]
     */
    public function newLines($agent, string $partyType, $customerId, array $variations, $exceptOrderId = null): array
    {
        $wanted = [];
        foreach ($variations as $var) {
            $key = (int) ($var['product_id'] ?? 0) . '_' . (int) ($var['color_id'] ?? 0) . '_' . (int) ($var['size_set_id'] ?? 0);
            $qty = (int) ($var['qty'] ?? 0);
            if ($qty <= 0) continue;
            if (isset($wanted[$key])) {
                $wanted[$key]['qty'] += $qty;
                if (!empty($var['remark'])) $wanted[$key]['remark'] = trim(($wanted[$key]['remark'] ?? '') . ', ' . $var['remark'], ', ');
            } else {
                $wanted[$key] = $var + ['remark' => null];
                $wanted[$key]['qty'] = $qty;
            }
        }

        $lines = [];
        $need = [];
        foreach ($wanted as $key => $var) {
            $line = $this->newLine($agent, $partyType, $customerId, $var, $var['qty'], $exceptOrderId);
            $lines[$key] = $line;
            $need[$key] = ['product_id' => $line['product_id'], 'color_id' => $line['color_id'], 'size_set_id' => $line['size_set_id'],
                'boxes' => $var['qty'], 'label' => self::itemLabel($line['design_number'], $line['color_name'], $line['size_set_name'])];
        }
        return [$lines, $need];
    }

    /** One new order line for $boxes boxes of an item (price, pieces and rack decided here). */
    public function newLine($agent, string $partyType, $customerId, array $var, int $boxes, $exceptOrderId = null): array
    {
        $product = ProductionGoods::with('series')->find($var['product_id'] ?? 0);
        $color = MasterColor::find($var['color_id'] ?? 0);
        $sizeSet = MasterSizeMeasurement::find($var['size_set_id'] ?? 0);
        if (!$product || !$color || !$sizeSet) {
            $code = 'D' . ($var['product_id'] ?? '?') . 'S' . ($var['size_set_id'] ?? '?') . 'C' . ($var['color_id'] ?? '?');
            throw new AgentOrderException("An item of this order no longer exists ({$code}). Remove it and try again.");
        }

        [$price, $mrp] = $this->linePrice($agent, $partyType, $customerId, $product, $sizeSet->id, $var['unit_price'] ?? null);
        $pcs = $this->piecesPerBox($product->id, $color->id, $sizeSet->id, $var['pcs_per_box'] ?? null, $sizeSet);
        $barcode = 'D' . $product->id . 'S' . $sizeSet->id . 'C' . $color->id;
        $fitting = $product->master_product_fitting_id ? MasterProductFitting::find($product->master_product_fitting_id) : null;
        $pattern = $product->master_pattern_id ? MasterDesignPattern::find($product->master_pattern_id) : null;
        $name = trim(($product->series->name ?? '') . ' ' . $product->name_of_garment);

        return [
            'rack_id' => $this->rackFor($product->id, $color->id, $sizeSet->id, $barcode, $exceptOrderId),
            'product_id' => $product->id,
            'color_id' => $color->id,
            'size_set_id' => $sizeSet->id,
            'product_name' => $name ?: 'N/A',
            'design_number' => $product->design_number,
            'color_name' => $color->name,
            'size_set_name' => $sizeSet->name,
            'fitting_name' => $fitting->name ?? null,
            'pattern_name' => $pattern->name ?? null,
            'quantity' => $boxes * $pcs,
            'box_qty' => $boxes,
            'mrp' => $mrp,
            'selling_price' => $price,
            'barcode' => $barcode,
            'packing_box_id' => null,
            'scanned_box_qty' => 0,
            'scanned_quantity' => 0,
            'dispatched_at' => null,
            'remark' => isset($var['remark']) && $var['remark'] !== '' ? mb_substr((string) $var['remark'], 0, 1000) : null,
        ];
    }

    /**
     * Order totals like admin orders: discount between 0 and the item value, other charges 0 or more,
     * GST % (setting) on the value after discount, everything rounded up.
     */
    public function totals(float $itemsValue, $discount, $otherCharges): array
    {
        $total = ceil(round($itemsValue, 4));
        $discount = ceil((float) $discount);
        $other = ceil((float) $otherCharges);
        if ($discount < 0) throw new AgentOrderException('Discount cannot be less than 0.');
        if ($other < 0) throw new AgentOrderException('Other charges cannot be less than 0.');
        if ($discount > $total) throw new AgentOrderException("Discount ({$discount}) cannot be more than the order value ({$total}).");
        $taxable = $total - $discount;
        $gstPct = $this->gstPercent();
        $gst = ceil(round($taxable * $gstPct / 100, 4));
        return [
            'total_amount' => $total,
            'discount_amount' => $discount,
            'discount_percentage' => $total > 0 ? $discount / $total * 100 : 0,
            'gst_percentage' => $gstPct,
            'gst_amount' => $gst,
            'other_charges' => $other,
            'grand_total' => ceil($taxable + $gst + $other),
        ];
    }

    /**
     * Rack an order line is expected to be picked from: the best storeroom (priority, then most boxes) that takes
     * orders while the item has free stock, else where the item was last kept.
     */
    public function rackFor($product_id, $color_id, $size_set_id, $barcode, $exclude_order_id = null)
    {
        $allocated_qty = (int) DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_orders.status', '!=', 'dispatched')
            ->whereNull('agent_order_items.dispatched_at')
            ->where('agent_order_items.product_id', $product_id)
            ->where('agent_order_items.color_id', $color_id)
            ->where('agent_order_items.size_set_id', $size_set_id)
            ->when($exclude_order_id, fn($q) => $q->where('agent_orders.id', '!=', $exclude_order_id))
            ->sum(DB::raw('GREATEST(agent_order_items.box_qty - agent_order_items.scanned_box_qty, 0)'));

        $ordered = function ($q) {
            $q->leftJoin('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
                ->leftJoin('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
                ->where(fn($w) => $w->whereNull('storerooms.id')->orWhere('storerooms.order_taken', '=', 'Yes'))
                ->orderByRaw("CASE WHEN storerooms.order_priority IS NULL OR storerooms.order_priority = '' THEN 9999 ELSE CAST(storerooms.order_priority AS UNSIGNED) END ASC")
                ->orderBy('domestic_inventories.total_boxes', 'desc');
            return $q;
        };

        $inventories = $ordered(DomesticInventory::select('domestic_inventories.*')
            ->where('domestic_inventories.product_id', $product_id)
            ->where('domestic_inventories.color_id', $color_id)
            ->where('domestic_inventories.size_set_id', $size_set_id)
            ->where('domestic_inventories.total_boxes', '>', 0))->get();

        if ($inventories->sum('total_boxes') - $allocated_qty > 0 && $inventories->first()?->rack_id) {
            return $inventories->first()->rack_id;
        }

        $row = $ordered(DomesticInventory::select('domestic_inventories.*')->where('domestic_inventories.barcode', $barcode)
                ->where('domestic_inventories.total_boxes', '>', 0))->first()
            ?: $ordered(DomesticInventory::select('domestic_inventories.*')->where('domestic_inventories.barcode', $barcode))->first()
            ?: $ordered(DomesticInventory::select('domestic_inventories.*')->where('domestic_inventories.product_id', $product_id))->first();
        return $row ? $row->rack_id : null;
    }
}
