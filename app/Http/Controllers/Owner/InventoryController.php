<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        // For filters
        $size_sets = DomesticInventory::join('master_size_measurements', 'domestic_inventories.size_set_id', '=', 'master_size_measurements.id')
            ->select('domestic_inventories.size_set_id', 'master_size_measurements.name as size_set_name')
            ->distinct()->get();

        $products = DomesticInventory::join('production_goods', 'domestic_inventories.product_id', '=', 'production_goods.id')
            ->select('domestic_inventories.product_id', 'production_goods.name_of_garment as product_name')
            ->distinct()->get();

        return view('owner.reports.inventory', compact('size_sets', 'products'));
    }

    public function indexList(Request $request)
    {
        $query = DomesticInventory::select(
            'domestic_inventories.size_set_id',
            'domestic_inventories.color_id',
            'domestic_inventories.fitting_id',
            'domestic_inventories.pattern_id',
            'domestic_inventories.product_id',
            'products.design_number',
            'products.name_of_garment as product_name',
            'series.name as series_name',
            'colors.name as color_name',
            'sizes.name as size_set_name',
            'fittings.name as fitting_name',
            'patterns.name as pattern_name',
            'variants.mrp as mrp',
            DB::raw('SUM(domestic_inventories.total_boxes) as total_boxes')
        )
            ->leftJoin('production_goods as products', 'domestic_inventories.product_id', '=', 'products.id')
            ->leftJoin('master_series as series', 'products.master_series_id', '=', 'series.id')
            ->leftJoin('master_colors as colors', 'domestic_inventories.color_id', '=', 'colors.id')
            ->leftJoin('master_size_measurements as sizes', 'domestic_inventories.size_set_id', '=', 'sizes.id')
            ->leftJoin('master_product_fittings as fittings', 'domestic_inventories.fitting_id', '=', 'fittings.id')
            ->leftJoin('master_design_patterns as patterns', 'domestic_inventories.pattern_id', '=', 'patterns.id')
            ->leftJoin('production_goods_variants as variants', function ($join) {
                $join->on('domestic_inventories.product_id', '=', 'variants.production_goods_id')
                    ->on('domestic_inventories.size_set_id', '=', 'variants.master_size_measurement_id');
            });

        if ($request->filled('size_set_id')) {
            $query->where('domestic_inventories.size_set_id', $request->size_set_id);
        }
        if ($request->filled('product_id')) {
            $query->where('domestic_inventories.product_id', $request->product_id);
        }
        if ($request->filled('design_number')) {
            $query->where('products.design_number', 'LIKE', '%' . $request->design_number . '%');
        }

        $query->groupBy(
            'domestic_inventories.size_set_id',
            'domestic_inventories.color_id',
            'domestic_inventories.fitting_id',
            'domestic_inventories.pattern_id',
            'domestic_inventories.product_id',
            'products.design_number',
            'products.name_of_garment',
            'series.name',
            'colors.name',
            'sizes.name',
            'fittings.name',
            'patterns.name',
            'variants.mrp'
        )->orderBy('products.design_number', 'asc');

        $data = $query->paginate(20)->withQueryString();

        return view('owner.reports.inventory_list', compact('data'));
    }
}
