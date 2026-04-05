<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\DomesticInventory;
use App\Models\PackingCarton;
use App\Models\Rack;
use App\Models\Storeroom;
use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class WarehouseInventoryController extends Controller
{
    public function index()
    {
        $storerooms = Storeroom::where('status', '1')->get();
        // Use all master records for filters to ensure they are visible
        $size_sets = \App\Models\MasterSizeMeasurement::all();
        $products = \App\Models\ProductionGoods::with('series')->get();
        $colors = \App\Models\MasterColor::all();
        $designs = \App\Models\ProductionGoods::select('design_number')->distinct()->orderBy('design_number')->get();

        return view('admin.inventory.warehouse_stock.index', compact('storerooms', 'size_sets', 'products', 'colors', 'designs'));
    }

    public function indexList(Request $request)
    {
        if ($request->ajax()) {
            $query = DomesticInventory::with(['product.series', 'sizeSet', 'color', 'rack.storeroom'])
                ->select(
                    'product_id',
                    'color_id',
                    'size_set_id',
                    'rack_id',
                    DB::raw('COUNT(id) as total_boxes'),
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('GROUP_CONCAT(packing_carton_id) as carton_ids_list')
                )
                ->groupBy('product_id', 'color_id', 'size_set_id', 'rack_id');

            if ($request->has('storeroom_id') && !empty($request->storeroom_id)) {
                $query->whereHas('rack', function($q) use($request) {
                    $q->where('storeroom_id', $request->storeroom_id);
                });
            }

            if ($request->has('rack_id') && !empty($request->rack_id)) {
                $query->where('rack_id', $request->rack_id);
            }

            if ($request->has('size_set_id') && !empty($request->size_set_id)) {
                $query->where('size_set_id', $request->size_set_id);
            }

            if ($request->has('design_filter') && !empty($request->design_filter)) {
                $query->whereHas('product', function($q) use($request) {
                    $q->where('design_number', $request->design_filter);
                });
            }

            if ($request->has('product_id') && !empty($request->product_id)) {
                $query->where('product_id', $request->product_id);
            }

            if ($request->has('color_id') && !empty($request->color_id)) {
                $query->where('color_id', $request->color_id);
            }

            return datatables()->of($query->get())
                ->addIndexColumn()
                ->addColumn('checkbox', function ($row) {
                    return '<input type="checkbox" class="carton-checkbox" 
                        data-product-id="' . $row->product_id . '"
                        data-color-id="' . $row->color_id . '"
                        data-size-set-id="' . $row->size_set_id . '"
                        data-rack-id="' . $row->rack_id . '"
                        data-product-name="' . $row->product_name . '"
                        data-design-no="' . $row->design_number . '"
                        data-color-name="' . $row->color_name . '"
                        data-size-set-name="' . $row->size_set_name . '"
                        data-total-boxes="' . $row->total_boxes . '"
                        value="' . $row->carton_ids_list . '">';
                })
                ->addColumn('product_name', function ($row) {
                    return $row->product_name; // Uses accessor in model
                })
                ->addColumn('design_number', function ($row) {
                    return $row->design_number; // Uses accessor in model
                })
                ->addColumn('size_set_name', function ($row) {
                    return $row->size_set_name; // Uses accessor
                })
                ->addColumn('color_name', function ($row) {
                    return $row->color_name; // Uses accessor
                })
                ->addColumn('location', function ($row) {
                    $wh = $row->rack->storeroom->name ?? 'N/A';
                    $rk = $row->rack->name ?? 'N/A';
                    return $wh . ' / ' . $rk;
                })
                ->rawColumns(['checkbox'])
                ->make(true);
        }
    }

    public function transfer(Request $request)
    {
        $request->validate([
            'carton_ids' => 'required', // Now a comma separated list
            'rack_id' => 'required|exists:racks,id'
        ]);

        try {
            DB::beginTransaction();
            
            $carton_ids = explode(',', $request->carton_ids);
            $target_rack = Rack::with('storeroom')->find($request->rack_id);

            // Get source info before update
            $items = DomesticInventory::whereIn('packing_carton_id', $carton_ids)->with('rack.storeroom')->get();
            $from_storeroom_id = $items->first()->rack->storeroom_id ?? null;

            // Record history
            $transfer = WarehouseTransfer::create([
                'transfer_no' => 'TRF-' . strtoupper(uniqid()),
                'from_storeroom_id' => $from_storeroom_id,
                'to_storeroom_id' => $target_rack->storeroom_id,
                'to_rack_id' => $request->rack_id,
                'transferred_by' => auth()->id() ?? 1,
                'notes' => $request->notes ?? ''
            ]);

            foreach ($items as $item) {
                WarehouseTransferItem::create([
                    'warehouse_transfer_id' => $transfer->id,
                    'domestic_inventory_id' => $item->id,
                    'packing_carton_id' => $item->packing_carton_id,
                    'from_rack_id' => $item->rack_id,
                    'product_id' => $item->product_id,
                    'color_id' => $item->color_id,
                    'size_set_id' => $item->size_set_id,
                    'quantity' => $item->quantity
                ]);
            }

            // Update locations
            PackingCarton::whereIn('id', $carton_ids)->update(['rack_id' => $request->rack_id]);
            DomesticInventory::whereIn('packing_carton_id', $carton_ids)->update(['rack_id' => $request->rack_id]);

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Cartons transferred successfully.', 'transfer_id' => $transfer->id]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function history()
    {
        return view('admin.inventory.warehouse_stock.history');
    }

    public function indexHistoryList(Request $request)
    {
        if ($request->ajax()) {
            $query = WarehouseTransfer::with(['toStoreroom', 'toRack', 'user'])
                ->withCount('items')
                ->latest();

            return datatables()->of($query->get())
                ->addIndexColumn()
                ->addColumn('action', function ($row) {
                    $btn = '<a href="' . route('admin.inventory.warehouse_stock.download_slip', $row->id) . '" class="btn btn-sm btn-info" target="_blank"><i class="fa fa-download"></i> Slip</a>';
                    return $btn;
                })
                ->addColumn('to_location', function ($row) {
                    $wh = $row->toStoreroom->name ?? 'N/A';
                    $rk = $row->toRack->name ?? 'N/A';
                    return $wh . ' / ' . $rk;
                })
                ->rawColumns(['action'])
                ->make(true);
        }
    }

    public function downloadSlip($id)
    {
        $transfer = WarehouseTransfer::with(['items.product.series', 'items.color', 'items.sizeSet', 'items.fromRack.storeroom', 'toStoreroom', 'toRack'])->findOrFail($id);
        
        $data = [
            'transfer' => $transfer,
            'items' => $transfer->items()->select(
                    'product_id', 'color_id', 'size_set_id', 
                    DB::raw('count(id) as box_count'), 
                    DB::raw('sum(quantity) as total_qty')
                )
                ->groupBy('product_id', 'color_id', 'size_set_id')
                ->get()
        ];

        $pdf = Pdf::loadView('admin.inventory.warehouse_stock.slip', $data);
        return $pdf->download($transfer->transfer_no . '.pdf');
    }
    
    public function getRacksByStoreroom($id)
    {
        $racks = \App\Models\Rack::where('storeroom_id', $id)->where('status', '1')->get(['id', 'name']);
        return response()->json($racks);
    }
}
