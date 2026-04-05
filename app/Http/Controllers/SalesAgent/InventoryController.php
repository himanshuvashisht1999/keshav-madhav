<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Fetch Filter Options from domestic_inventories
        $designs = DomesticInventory::distinct()->pluck('design_number');
        $colors = DomesticInventory::distinct()->pluck('color_name');
        $size_sets = DomesticInventory::distinct()->pluck('size_set_name');

        $agent_discount = Auth::guard('sales_agent')->user()->discount_percentage ?? 0;

        // Build Query - Join with domestic_inventory_images to get prices
        $prices = DB::table('domestic_inventory_images')
            ->select('product_id', 'color_id', 'product_name', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('product_id', 'color_id', 'product_name');

        $query = DomesticInventory::where('domestic_inventories.status', 1)
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
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
            'domestic_inventories.product_name',
            'domestic_inventories.design_number',
            'domestic_inventories.color_id',
            'domestic_inventories.color_name',
            'domestic_inventories.size_set_id',
            'domestic_inventories.size_set_name',
            DB::raw('MAX(COALESCE(ip.mrp, 0)) as mrp'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $agent_discount . ') / 100) as selling_price'),
            DB::raw('COUNT(DISTINCT domestic_inventories.packing_box_id) as available_boxes'),
            DB::raw('SUM(domestic_inventories.quantity) as total_qty')
        )
            ->groupBy(
                'domestic_inventories.product_id',
                'domestic_inventories.product_name',
                'domestic_inventories.design_number',
                'domestic_inventories.color_id',
                'domestic_inventories.color_name',
                'domestic_inventories.size_set_id',
                'domestic_inventories.size_set_name'
            )
            ->orderBy('domestic_inventories.design_number')
            ->get();

        // Fetch images for the variations
        $boxImages = [];
        foreach ($inventories as $variation) {
            $image = DB::table('domestic_inventory_images')
                ->where('product_id', $variation->product_id)
                ->where('color_id', $variation->color_id)
                ->where('is_main', 1)
                ->value('image_path');

            $key = $variation->product_id . '_' . $variation->color_id . '_' . $variation->size_set_id;
            $boxImages[$key] = $image;
        }

        return view('sales_agent.inventory.index', compact('inventories', 'designs', 'colors', 'size_sets', 'boxImages'));
    }

    public function show(Request $request)
    {
        $agent_discount = Auth::guard('sales_agent')->user()->discount_percentage ?? 0;

        // Join with prices to show correct discounted price per box
        $prices = DB::table('domestic_inventory_images')
            ->select('product_id', 'color_id', DB::raw('MAX(mrp) as mrp'))
            ->groupBy('product_id', 'color_id');

        $query = DomesticInventory::where('domestic_inventories.status', 1)
            ->leftJoinSub($prices, 'ip', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'ip.product_id')
                    ->on('domestic_inventories.color_id', '=', 'ip.color_id');
            });

        if ($request->filled('product_name')) {
            $query->where('domestic_inventories.product_name', $request->product_name);
        }
        if ($request->filled('design_number')) {
            $query->where('domestic_inventories.design_number', $request->design_number);
        }
        if ($request->filled('color_id')) {
            $query->where('domestic_inventories.color_id', $request->color_id);
        }
        if ($request->filled('size_set_id')) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }

        $items = $query->select(
            'domestic_inventories.packing_box_id',
            'domestic_inventories.box_no',
            'domestic_inventories.carton_no',
            DB::raw('SUM(domestic_inventories.quantity) as total_qty'),
            DB::raw('(MAX(COALESCE(ip.mrp, 0)) * (100 - ' . $agent_discount . ') / 100) as price')
        )
            ->groupBy(
                'domestic_inventories.packing_box_id',
                'domestic_inventories.box_no',
                'domestic_inventories.carton_no'
            )
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
