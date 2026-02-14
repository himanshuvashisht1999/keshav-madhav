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
            // Group by Product Name, Design Number, Size Set, MRP, Selling Price
            $query = DomesticInventory::select(
                'product_name',
                'design_number',
                'size_set_name',
                'mrp',
                'selling_price',
                DB::raw('SUM(quantity) as total_qty'),
                DB::raw('COUNT(DISTINCT packing_box_id) as total_boxes'),
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

            // Filter by Design Number
            if ($request->has('design_number') && !empty($request->design_number)) {
                $query->where('design_number', 'LIKE', '%' . $request->design_number . '%');
            }

            // Filter by MRP
            if ($request->has('mrp') && !empty($request->mrp)) {
                $query->where('mrp', '>=', $request->mrp);
            }

            // Filter by Selling Price
            if ($request->has('selling_price') && !empty($request->selling_price)) {
                $query->where('selling_price', '>=', $request->selling_price);
            }

            $data = $query->groupBy('product_name', 'design_number', 'size_set_name', 'mrp', 'selling_price')
                ->get();

            return datatables()->of($data)
                ->addIndexColumn()
                ->addColumn('mrp_display', function ($row) {
                    return '₹' . number_format($row->mrp, 2);
                })
                ->addColumn('selling_price_display', function ($row) {
                    return '₹' . number_format($row->selling_price, 2);
                })
                ->addColumn('action', function ($row) {
                    $params = [
                        'product_name' => $row->product_name,
                        'design_number' => $row->design_number,
                        'size_set_name' => $row->size_set_name,
                        'mrp' => $row->mrp,
                        'selling_price' => $row->selling_price,
                    ];
                    $btn = '<a href="' . route('admin.inventory.show', $params) . '" class="btn btn-primary btn-sm"><i class="fas fa-eye"></i> View Details</a>';
                    return $btn;
                })
                ->rawColumns(['box_display', 'action'])
                ->make(true);
        }
    }

    public function show(Request $request)
    {
        $query = DomesticInventory::query();

        if ($request->has('product_name')) {
            $query->where('product_name', $request->product_name);
        }
        if ($request->has('design_number')) {
            $query->where('design_number', $request->design_number);
        }
        if ($request->has('size_set_name')) {
            $query->where('size_set_name', $request->size_set_name);
        }
        if ($request->has('mrp')) {
            $query->where('mrp', $request->mrp);
        }
        if ($request->has('selling_price')) {
            $query->where('selling_price', $request->selling_price);
        }

        $items = $query->with(['orderMain', 'carton', 'box'])->get();

        if ($items->isEmpty()) {
            return redirect()->back()->with('error', 'Inventory records not found.');
        }

        $group_info = $items->first();

        return view('admin.inventory.show', compact('items', 'group_info'));
    }
}
