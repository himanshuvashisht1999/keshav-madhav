<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PackingMain extends Model
{
    use HasFactory, \App\Traits\TrackCreator;

    protected $fillable = [
        'order_main_id', 'slip_id', 'packing_date', 'remarks', 'status', 'created_by'
    ];

    public function order()
    {
        return $this->belongsTo(OrderMain::class , 'order_main_id');
    }

    public function cartons()
    {
        return $this->hasMany(PackingCarton::class , 'packing_main_id');
    }

    public function items()
    {
        return $this->hasMany(PackingItem::class , 'packing_main_id');
    }

    public function outflows()
    {
        return $this->hasMany(ProductionOutflowInventory::class, 'slip_id', 'slip_id');
    }

    public function domesticInventories()
    {
        return $this->hasMany(DomesticInventory::class, 'packing_main_id');
    }

    /**
     * Retrieve all inbound items for this session with intelligent fallback to domestic_inventories.
     */
    public function getInboundItems()
    {
        // 1. Look for DomesticInventoryHistory creation/sample/stock_consume items
        $items = DomesticInventoryHistory::with([
            'newProduct.series',
            'newProduct.fitting',
            'newProduct.pattern',
            'newSizeSet',
            'newColor',
            'newRack.storeroom'
        ])->where('created_at', $this->created_at)
          ->where(function ($q) {
              $q->whereIn('type', ['creation', 'sample'])
                ->orWhere(function ($sub) {
                    $sub->where('type', 'stock_consume')
                        ->whereNotNull('new_product_id');
                });
          })
          ->get();

        $cartonsCount = $this->cartons()->count();

        // If creation/sample matches carton count (or cartonsCount is 0 and items not empty), return
        if ($items->isNotEmpty() && ($cartonsCount == 0 || $items->sum('box_quantity') >= $cartonsCount)) {
            return $items;
        }

        // 2. Check if DomesticInventory records exist for this session
        $invs = $this->domesticInventories()->with([
            'product.series',
            'product.fitting',
            'product.pattern',
            'sizeSet',
            'color',
            'rack.storeroom'
        ])->get();

        if ($invs->isNotEmpty()) {
            $hasIndividualBoxRows = ($invs->count() == $cartonsCount && $cartonsCount > 1);
            $grouped = [];

            foreach ($invs as $inv) {
                $key = $inv->product_id . '_' . $inv->size_set_id . '_' . $inv->color_id . '_' . $inv->rack_id . '_' . $inv->quantity;
                if (!isset($grouped[$key])) {
                    $mrp = 0;
                    $variant = ProductionGoodVariant::where('production_goods_id', $inv->product_id)
                        ->where('master_size_measurement_id', $inv->size_set_id)
                        ->first();
                    if ($variant) {
                        $mrp = $variant->mrp ?? 0;
                    }

                    $itemObj = new \stdClass();
                    $itemObj->id = $inv->id;
                    $itemObj->new_product_id = $inv->product_id;
                    $itemObj->new_size_set_id = $inv->size_set_id;
                    $itemObj->new_color_id = $inv->color_id;
                    $itemObj->new_rack_id = $inv->rack_id;
                    $itemObj->newProduct = $inv->product;
                    $itemObj->newSizeSet = $inv->sizeSet;
                    $itemObj->newColor = $inv->color;
                    $itemObj->newRack = $inv->rack;
                    $itemObj->pieces_per_box = $inv->quantity;
                    $itemObj->box_quantity = 0;
                    $itemObj->mrp = $mrp;
                    $itemObj->type = 'creation';

                    $grouped[$key] = $itemObj;
                }

                if ($hasIndividualBoxRows) {
                    $grouped[$key]->box_quantity += 1;
                } else {
                    $boxQty = $inv->total_boxes > 0 ? (int)$inv->total_boxes : 1;
                    $grouped[$key]->box_quantity += $boxQty;
                }
            }

            $sumBoxes = array_sum(array_map(fn($o) => $o->box_quantity, $grouped));
            if ($cartonsCount > 0 && $sumBoxes != $cartonsCount) {
                if (count($grouped) > 0 && $cartonsCount % count($grouped) == 0) {
                    $perVariant = (int) ($cartonsCount / count($grouped));
                    foreach ($grouped as $g) {
                        $g->box_quantity = $perVariant;
                    }
                }
            }

            return collect(array_values($grouped));
        }

        // 3. Fallback to any history records (even if stock_consume)
        if ($items->isNotEmpty()) {
            return $items;
        }

        return DomesticInventoryHistory::with([
            'newProduct.series',
            'newProduct.fitting',
            'newProduct.pattern',
            'newSizeSet',
            'newColor',
            'newRack.storeroom'
        ])->where('created_at', $this->created_at)
          ->whereIn('type', ['creation', 'sample', 'stock_consume'])
          ->whereNotNull('new_product_id')
          ->get();
    }
}
