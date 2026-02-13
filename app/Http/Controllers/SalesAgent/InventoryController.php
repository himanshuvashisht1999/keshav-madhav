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
        // Fetch Filter Options from domestic_inventories
        $designs = DomesticInventory::distinct()->pluck('design_number');
        $colors = DomesticInventory::distinct()->pluck('color_name');
        $size_sets = DomesticInventory::distinct()->pluck('size_set_name');

        $query = DomesticInventory::query();

        // Apply Filters
        if ($request->filled('design_number')) {
            $query->where('design_number', $request->design_number);
        }
        if ($request->filled('color_name')) {
            $query->where('color_name', $request->color_name);
        }
        if ($request->filled('size_set_name')) {
            $query->where('size_set_name', $request->size_set_name);
        }

        // Group by variation
        $inventories = $query->select(
            'product_id',
            'product_name',
            'design_number',
            'color_id',
            'color_name',
            'size_set_id',
            'size_set_name',
            'mrp',
            'selling_price',
            DB::raw('COUNT(DISTINCT packing_box_id) as available_boxes'),
            DB::raw('SUM(quantity) as total_qty')
        )
            ->groupBy(
                'product_id',
                'product_name',
                'design_number',
                'color_id',
                'color_name',
                'size_set_id',
                'size_set_name',
                'mrp',
                'selling_price'
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

    public function show(Request $request)
    {
        $query = DomesticInventory::query();

        if ($request->filled('product_name')) {
            $query->where('product_name', $request->product_name);
        }
        if ($request->filled('design_number')) {
            $query->where('design_number', $request->design_number);
        }
        if ($request->filled('color_id')) {
            $query->where('color_id', $request->color_id);
        }
        if ($request->filled('size_set_id')) {
            $query->where('size_set_id', $request->size_set_id);
        }
        if ($request->filled('mrp')) {
            $query->where('mrp', $request->mrp);
        }
        if ($request->filled('selling_price')) {
            $query->where('selling_price', $request->selling_price);
        }

        $items = $query->select(
            'packing_box_id',
            'box_no',
            'carton_no',
            DB::raw('SUM(quantity) as total_qty'),
            'selling_price as price'
        )
            ->groupBy('packing_box_id', 'box_no', 'carton_no', 'selling_price')
            ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Inventory not found');
        }

        $variation = $items->first();
        // Add more context for the header if needed
        $variation->design_number = $request->design_number;
        $variation->color_name = DomesticInventory::where('color_id', $request->color_id)->value('color_name');
        $variation->size_set_name = DomesticInventory::where('size_set_id', $request->size_set_id)->value('size_set_name');

        return view('sales_agent.inventory.show', compact('items', 'variation'));
    }
}
