<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Services\Admin\InventoryStockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Ready stock (domestic finished goods) for the owner: one row per product + size set + colour,
 * the same groups and box counts as admin Browse Inventory with "size set + colour" grouping.
 */
class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $list = $this->listQuery($request);

        if ($request->ajax()) {
            return $this->rowsJson($list);
        }

        $totals = DB::query()->fromSub(clone $list, 'x')->selectRaw('
            COUNT(*) as row_count,
            COUNT(DISTINCT product_id) as products,
            COALESCE(SUM(total_boxes), 0) as boxes,
            COALESCE(SUM(pieces), 0) as pieces,
            COALESCE(SUM(pieces * COALESCE(mrp, 0)), 0) as mrp_value,
            COALESCE(SUM(total_order), 0) as order_boxes
        ')->first();

        $rows = $list->paginate(50)->withQueryString();

        // filter options straight from the tables (model accessors would load each row's product again)
        $products = DB::table('domestic_inventories as d')
            ->join('production_goods as p', 'p.id', '=', 'd.product_id')
            ->leftJoin('master_series as s', 's.id', '=', 'p.master_series_id')
            ->select('p.id', 'p.design_number', 'p.name_of_garment', 's.name as series_name')
            ->distinct()->orderBy('p.design_number')->orderBy('p.id')->get();
        $sizeSets = DB::table('domestic_inventories as d')
            ->join('master_size_measurements as m', 'm.id', '=', 'd.size_set_id')
            ->select('m.id', 'm.name')->distinct()->orderBy('m.name')->get();
        $colors = DB::table('domestic_inventories as d')
            ->join('master_colors as c', 'c.id', '=', 'd.color_id')
            ->select('c.id', 'c.name')->distinct()->orderBy('c.name')->get();
        $storerooms = DB::table('storerooms')->where('status', '1')->orderBy('name')->get(['id', 'name']);

        return view('owner.reports.inventory', compact('rows', 'totals', 'products', 'sizeSets', 'colors', 'storerooms'));
    }

    private function rowsJson($list)
    {
        $rows = $list->paginate(50)->withQueryString();
        return response()->json([
            'html' => view('owner.reports.inventory_list', compact('rows'))->render(),
            'next_page' => $rows->hasMorePages() ? $rows->currentPage() + 1 : null,
            'left' => max(0, $rows->total() - (int) $rows->lastItem()),
        ]);
    }

    /**
     * Stock is summed per product + size set + colour first (8.7k rows -> about 5k groups), then the names are joined once.
     * MRP comes from the first variant of the product + size set (lowest id), the same variant admin keeps
     * (InventoryStockService::skipDuplicateVariants). "On order" = boxes open agent orders still need (admin's Total order).
     */
    private function listQuery(Request $request)
    {
        $stock = DB::table('domestic_inventories')
            ->select(
                'product_id',
                'size_set_id',
                'color_id',
                DB::raw('SUM(total_boxes) as total_boxes'),
                DB::raw('SUM(total_boxes * quantity) as pieces')
            )
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('size_set_id'), fn($q) => $q->where('size_set_id', $request->size_set_id))
            ->when($request->filled('color_id'), fn($q) => $q->where('color_id', $request->color_id))
            ->when($request->filled('storeroom_id'), fn($q) => $q->whereIn('rack_id', DB::table('racks')->where('storeroom_id', $request->storeroom_id)->select('id')))
            ->groupBy('product_id', 'size_set_id', 'color_id');

        $firstVariant = DB::table('production_goods_variants')
            ->select('production_goods_id', 'master_size_measurement_id', DB::raw('MIN(id) as id'))
            ->groupBy('production_goods_id', 'master_size_measurement_id');

        $openOrders = InventoryStockService::openOrderBoxesSql(['product_id', 'size_set_id', 'color_id'], 'total_order');

        return DB::query()->fromSub($stock, 'di')
            ->leftJoin('production_goods as products', 'products.id', '=', 'di.product_id')
            ->leftJoin('master_series as series', 'series.id', '=', 'products.master_series_id')
            ->leftJoin('master_size_measurements as sizes', 'sizes.id', '=', 'di.size_set_id')
            ->leftJoin('master_colors as colors', 'colors.id', '=', 'di.color_id')
            ->leftJoin('master_product_fittings as fittings', 'fittings.id', '=', 'products.master_product_fitting_id')
            ->leftJoin('master_design_patterns as patterns', 'patterns.id', '=', 'products.master_pattern_id')
            ->leftJoinSub($firstVariant, 'fv', function ($join) {
                $join->on('fv.production_goods_id', '=', 'di.product_id')
                    ->on('fv.master_size_measurement_id', '=', 'di.size_set_id');
            })
            ->leftJoin('production_goods_variants as variants', 'variants.id', '=', 'fv.id')
            ->leftJoin(DB::raw($openOrders . ' as order_totals'), function ($join) {
                $join->on('order_totals.product_id', '=', 'di.product_id')
                    ->on('order_totals.size_set_id', '=', 'di.size_set_id')
                    ->on('order_totals.color_id', '=', 'di.color_id');
            })
            ->select([
                'di.product_id',
                'di.size_set_id',
                'di.color_id',
                'di.total_boxes',
                'di.pieces',
                'products.design_number',
                DB::raw("TRIM(CONCAT(COALESCE(series.name, ''), ' ', COALESCE(products.name_of_garment, ''))) as product_name"),
                'sizes.name as size_set_name',
                'colors.name as color_name',
                'fittings.name as fitting_name',
                'patterns.name as pattern_name',
                'variants.mrp',
                DB::raw('COALESCE(order_totals.total_order, 0) as total_order'),
            ])
            ->when($request->filled('design_number'), fn($q) => $q->where('products.design_number', 'LIKE', '%' . $request->design_number . '%'))
            ->when($request->boolean('in_stock'), fn($q) => $q->where('di.total_boxes', '>', 0))
            ->orderBy('products.design_number')
            ->orderBy('di.product_id')
            ->orderBy('di.size_set_id')
            ->orderBy('di.color_id');
    }
}
