<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomesticInventory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class InventoryController extends Controller
{
    public function index()
    {
        // Fetch unique size sets and products for filters
        $size_sets = DomesticInventory::select('size_set_id', 'size_set_name')
            ->whereNotNull('size_set_id')
            ->distinct()
            ->get();

        $products = DomesticInventory::select('product_id', 'product_name')
            ->whereNotNull('product_id')
            ->distinct()
            ->get();

        return view('admin.inventory.index', compact('size_sets', 'products'));
    }

    public function indexList(Request $request)
    {
        if ($request->ajax()) {
            // Group by Box and Order
            $query = DomesticInventory::with(['orderMain'])
                ->select(
                    'packing_box_id',
                    'packing_carton_id',
                    'order_main_id',
                    'box_no',
                    'carton_no',
                    DB::raw('SUM(quantity) as total_qty'),
                    DB::raw('GROUP_CONCAT(DISTINCT product_name) as products'),
                    DB::raw('GROUP_CONCAT(DISTINCT size_set_name) as size_sets'),
                    DB::raw('MAX(mrp) as max_mrp'),
                    DB::raw('MAX(selling_price) as max_selling_price'),
                    DB::raw('MAX(created_at) as recent_date')
                );

            // Filter by Size Set
            if ($request->has('size_set_id') && !empty($request->size_set_id)) {
                $query->where('size_set_id', $request->size_set_id);
            }

            // Filter by Product
            if ($request->has('product_id') && !empty($request->product_id)) {
                $query->where('product_id', $request->product_id);
            }

            // Filter by Box No
            if ($request->has('box_no') && !empty($request->box_no)) {
                $query->where('box_no', 'LIKE', '%' . $request->box_no . '%');
            }

            // Filter by Order No
            if ($request->has('order_no') && !empty($request->order_no)) {
                $query->whereHas('orderMain', function ($q) use ($request) {
                    $q->where('sku', 'LIKE', '%' . $request->order_no . '%');
                });
            }

            // Filter by MRP
            if ($request->has('mrp') && !empty($request->mrp)) {
                $query->where('mrp', '>=', $request->mrp);
            }

            // Filter by Selling Price
            if ($request->has('selling_price') && !empty($request->selling_price)) {
                $query->where('selling_price', '>=', $request->selling_price);
            }

            $data = $query->groupBy('packing_box_id', 'packing_carton_id', 'order_main_id', 'box_no', 'carton_no')
                ->get();

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('order_no', function ($row) {
                    return $row->orderMain ? $row->orderMain->sku : 'N/A';
                })
                ->addColumn('box_display', function ($row) {
                    return $row->box_no ? $row->box_no : '<span class="text-muted">No Box (Direct)</span>';
                })
                ->addColumn('products_summary', function ($row) {
                    return $row->products;
                })
                ->addColumn('size_sets_summary', function ($row) {
                    return $row->size_sets;
                })
                ->addColumn('mrp_display', function ($row) {
                    return '₹' . number_format($row->max_mrp, 2);
                })
                ->addColumn('selling_price_display', function ($row) {
                    return '₹' . number_format($row->max_selling_price, 2);
                })
                ->addColumn('action', function ($row) {
                    $box_id = $row->packing_box_id ?: 0;
                    $carton_id = $row->packing_carton_id;
                    $btn = '<a href="' . route('admin.inventory.show', ['box' => $box_id, 'carton' => $carton_id]) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Details</a>';
                    return $btn;
                })
                ->rawColumns(['box_display', 'action'])
                ->make(true);
        }
    }

    public function show(Request $request, $box_id)
    {
        $carton_id = $request->carton;

        $query = DomesticInventory::where('packing_carton_id', $carton_id);
        if ($box_id != 0) {
            $query->where('packing_box_id', $box_id);
        } else {
            $query->whereNull('packing_box_id');
        }

        $items = $query->get();
        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Inventory record not found.');
        }

        $box_info = $items->first();

        return view('admin.inventory.show', compact('items', 'box_info'));
    }
}
