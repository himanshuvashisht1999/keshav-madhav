<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Fetch Filter Options
        $designs = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('design_number');
        $colors = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('color_name');
        $size_sets = DomesticInventory::whereNotNull('packing_box_id')->distinct()->pluck('size_set_name');

        // Build Query - Join with inventory_prices to get prices
        $prices = DB::table('inventory_prices')
            ->select('design_id', 'color_id', DB::raw('MAX(selling_price) as selling_price'), DB::raw('MAX(mrp) as mrp'))
            ->groupBy('design_id', 'color_id');

        $query = DomesticInventory::whereNotNull('packing_box_id')
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.design_id')
                    ->on('domestic_inventories.color_id', '=', 'ip.color_id');
            });

        // Apply Filters
        if ($request->filled('design_number')) {
            $query->where('domestic_inventories.design_number', $request->design_number);
        }
        if ($request->filled('color_name')) {
            $query->where('domestic_inventories.color_name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('domestic_inventories.size_set_name', $request->size_set_name);
        }

        // Group by variation
        $inventories = $query->select(
            'domestic_inventories.product_id',
            'domestic_inventories.color_id',
            'domestic_inventories.size_set_id',
            'domestic_inventories.design_number',
            'domestic_inventories.color_name',
            'domestic_inventories.size_set_name',
            DB::raw('COUNT(DISTINCT domestic_inventories.packing_box_id) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) / COUNT(DISTINCT domestic_inventories.packing_box_id) as pcs_per_box'),
            DB::raw('MAX(COALESCE(ip.selling_price, 0)) as unit_price')
        )
            ->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.color_id',
                'domestic_inventories.size_set_id',
                'domestic_inventories.design_number',
                'domestic_inventories.color_name',
                'domestic_inventories.size_set_name'
            )
            ->orderBy('design_number')
            ->get();

        // Fetch images for the variations
        $boxImages = [];
        foreach ($inventories as $variation) {
            $image = DB::table('inventory_price_images as ipi')
                ->join('inventory_prices as ip', 'ipi.inventory_price_id', '=', 'ip.id')
                ->where('ip.design_id', $variation->product_id)
                ->where('ip.color_id', $variation->color_id)
                ->where('ipi.is_main', 1)
                ->value('ipi.image_path');

            if (!$image) {
                $image = DB::table('inventory_prices')
                    ->where('design_id', $variation->product_id)
                    ->where('color_id', $variation->color_id)
                    ->value('image');
            }

            $key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
            $boxImages[$key] = $image;
        }

        return view('sales_agent.inventory.index', compact('inventories', 'designs', 'colors', 'size_sets', 'boxImages'));
    }

    public function show($box_id)
    {
        $variation = DomesticInventory::where('packing_box_id', $box_id)->first();
        if (!$variation) {
            return redirect()->back()->with('error', 'Inventory not found');
        }

        $items = DomesticInventory::select(
            'packing_box_id',
            'box_no',
            'carton_no',
            DB::raw('SUM(quantity) as total_qty'),
            DB::raw('MAX(selling_price) as price')
        )
            ->where('product_id', $variation->product_id)
            ->where('color_id', $variation->color_id)
            ->where('size_set_id', $variation->size_set_id)
            ->groupBy('packing_box_id', 'box_no', 'carton_no')
            ->get();

        return view('sales_agent.inventory.show', compact('items', 'variation'));
    }
}
