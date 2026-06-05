<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventory;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\MasterSizeMeasurement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // Fetch Filter Options
        $size_sets = DomesticInventory::join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->select('domestic_inventories.size_set_id', 'master_size_measurements.name as size_set_name')
            ->distinct()->get();

        $products = DomesticInventory::join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->select('domestic_inventories.product_id', 'production_goods.name_of_garment as product_name', 'production_goods.design_number')
            ->distinct()->get();

        $colors = DomesticInventory::join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
            ->select('domestic_inventories.color_id', 'master_colors.name as color_name')
            ->distinct()->get();

        $agent_discount = Auth::guard('sales_agent')->user()->discount_percentage ?? 0;

        // Build Query
        $query = DomesticInventory::select(
            'domestic_inventories.size_set_id',
            'domestic_inventories.color_id',
            'domestic_inventories.product_id',
            'pg.design_number',
            'pg.name_of_garment as product_name',
            'mc.name as color_name',
            'msm.name as size_set_name',
            'fittings.name as fitting_name',
            'patterns.name as pattern_name',
            'variants.mrp as mrp',
            DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes'),
            DB::raw('SUM(domestic_inventories.total_boxes * domestic_inventories.quantity) as total_qty'),
            DB::raw('(MAX(COALESCE(variants.mrp, 0)) * (100 - ' . $agent_discount . ') / 100) as selling_price')
        )
            ->leftJoin('production_goods as pg', 'domestic_inventories.product_id', '=', 'pg.id')
            ->leftJoin('master_colors as mc', 'domestic_inventories.color_id', '=', 'mc.id')
            ->leftJoin('master_size_measurements as msm', 'domestic_inventories.size_set_id', '=', 'msm.id')
            ->leftJoin('master_product_fittings as fittings', 'pg.master_product_fitting_id', '=', 'fittings.id')
            ->leftJoin('master_design_patterns as patterns', 'pg.master_pattern_id', '=', 'patterns.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
            })
            ->where('domestic_inventories.status', 1);

        // Apply Filters
        if ($request->filled('design_number')) {
            $query->where('pg.design_number', 'LIKE', '%' . $request->design_number . '%');
        }
        if ($request->filled('product_id')) {
            $query->where('domestic_inventories.product_id', $request->product_id);
        }
        if ($request->filled('color_id')) {
            $query->where('domestic_inventories.color_id', $request->color_id);
        }
        if ($request->filled('size_set_id')) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }

        $query->groupBy(
            'domestic_inventories.size_set_id',
            'domestic_inventories.color_id',
            'domestic_inventories.product_id',
            'pg.design_number',
            'pg.name_of_garment',
            'mc.name',
            'msm.name',
            'fittings.name',
            'patterns.name',
            'variants.mrp'
        )->orderBy('pg.design_number', 'asc');

        if ($request->ajax() && $request->has('load_more')) {
            $inventories = $query->paginate(20);
            $html = '';
            foreach ($inventories as $index => $row) {
                $html .= view('sales_agent.inventory.partials.row', [
                    'row' => $row,
                    'index' => ($inventories->currentPage() - 1) * 20 + ($index + 1)
                ])->render();
            }
            return response()->json([
                'html' => $html,
                'next_page' => $inventories->nextPageUrl() ? $inventories->currentPage() + 1 : null
            ]);
        }

        $inventories = $query->paginate(20);

        return view('sales_agent.inventory.index', compact('inventories', 'size_sets', 'products', 'colors'));
    }

    public function show(Request $request)
    {
        $agent_discount = Auth::guard('sales_agent')->user()->discount_percentage ?? 0;

        $query = DomesticInventory::where('domestic_inventories.status', 1)
            ->leftJoin('production_goods as pg', 'domestic_inventories.product_id', '=', 'pg.id')
            ->leftJoin('master_colors as mc', 'domestic_inventories.color_id', '=', 'mc.id')
            ->leftJoin('master_size_measurements as msm', 'domestic_inventories.size_set_id', '=', 'msm.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
            });

        if ($request->filled('product_id')) {
            $query->where('domestic_inventories.product_id', $request->product_id);
        }
        if ($request->filled('color_id')) {
            $query->where('domestic_inventories.color_id', $request->color_id);
        }
        if ($request->filled('size_set_id')) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }
        if ($request->filled('fitting_id')) {
            $query->where('pg.master_product_fitting_id', $request->fitting_id);
        }
        if ($request->filled('pattern_id')) {
            $query->where('pg.master_pattern_id', $request->pattern_id);
        }

        $items = $query->select(
            'domestic_inventories.packing_box_id',
            'domestic_inventories.box_no',
            'domestic_inventories.carton_no',
            'domestic_inventories.quantity as total_qty',
            'pg.design_number',
            'pg.name_of_garment as product_name',
            'mc.name as color_name',
            'msm.name as size_set_name',
            DB::raw('(COALESCE(variants.mrp, 0) * (100 - ' . $agent_discount . ') / 100) as price')
        )
            ->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Inventory not found');
        }

        $variation = $items->first();

        return view('sales_agent.inventory.show', compact('items', 'variation'));
    }
}
