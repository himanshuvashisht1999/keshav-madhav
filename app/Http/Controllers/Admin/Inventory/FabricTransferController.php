<?php

namespace App\Http\Controllers\Admin\Inventory;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FabricTransfer;
use App\Models\FabricTransferItem;
use App\Models\MasterFabricWarehouse;
use App\Models\FabricReceiptDetail;
use App\Models\Fabric;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade\Pdf;

class FabricTransferController extends Controller
{
    public function index()
    {
        $warehouses = MasterFabricWarehouse::where('status', 1)->get();
        return view('admin.inventory.fabric_transfer.index', compact('warehouses'));
    }

    public function getFabrics(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        
        $fabrics = Fabric::whereHas('receiptDetails', function($q) use ($warehouse_id) {
            $q->where('master_fabric_warehouse_id', $warehouse_id)
              ->where('remaining_quantity', '>', 0);
        })->get(['id', 'name']);

        return response()->json($fabrics);
    }

    public function getRolls(Request $request)
    {
        $warehouse_id = $request->warehouse_id;
        $fabric_ids = $request->fabric_ids; // Now expects an array

        $rolls = FabricReceiptDetail::with('fabric')
            ->where('master_fabric_warehouse_id', $warehouse_id)
            ->whereIn('fabric_id', (array)$fabric_ids)
            ->where('remaining_quantity', '>', 0)
            ->get();

        return response()->json($rolls);
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_warehouse_id' => 'required|exists:master_fabric_warehouse,id',
            'to_warehouse_id' => 'required|exists:master_fabric_warehouse,id|different:from_warehouse_id',
            'transfer_date' => 'required|date',
            'roll_ids' => 'required|array',
            'roll_ids.*' => 'exists:fabric_receipt_details,id'
        ]);

        DB::beginTransaction();
        try {
            $transfer_no = 'FT-' . time() . '-' . rand(1000, 9999);

            $transfer = FabricTransfer::create([
                'transfer_no' => $transfer_no,
                'from_warehouse_id' => $request->from_warehouse_id,
                'to_warehouse_id' => $request->to_warehouse_id,
                'transfer_date' => $request->transfer_date,
                'transferred_by' => auth()->id(),
                'remarks' => $request->remarks
            ]);

            foreach ($request->roll_ids as $roll_id) {
                $roll = FabricReceiptDetail::findOrFail($roll_id);
                
                // Record item
                FabricTransferItem::create([
                    'fabric_transfer_id' => $transfer->id,
                    'fabric_receipt_detail_id' => $roll->id,
                    'fabric_id' => $roll->fabric_id,
                    'meter' => $roll->remaining_quantity
                ]);

                // Update roll warehouse
                $roll->update([
                    'master_fabric_warehouse_id' => $request->to_warehouse_id
                ]);
            }

            DB::commit();
            return response()->json(['status' => 'success', 'message' => 'Fabric transferred successfully.', 'redirect' => route('admin.inventory.fabric_transfer.history')]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => 'error', 'message' => 'Transfer failed: ' . $e->getMessage()]);
        }
    }

    public function history(Request $request)
    {
        $warehouses = MasterFabricWarehouse::where('status', 1)->get();
        $fabrics = Fabric::where('status', 1)->get();

        $query = FabricTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'items.fabric'])->latest();

        if ($request->filled('start_date')) {
            $query->whereDate('transfer_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transfer_date', '<=', $request->end_date);
        }
        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }
        if ($request->filled('fabric_id')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('fabric_id', $request->fabric_id);
            });
        }

        $transfers = $query->paginate(20)->withQueryString();

        $total_meters_query = \App\Models\FabricTransferItem::query()
            ->join('fabric_transfers', 'fabric_transfer_items.fabric_transfer_id', '=', 'fabric_transfers.id');

        if ($request->filled('start_date')) {
            $total_meters_query->whereDate('fabric_transfers.transfer_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $total_meters_query->whereDate('fabric_transfers.transfer_date', '<=', $request->end_date);
        }
        if ($request->filled('from_warehouse_id')) {
            $total_meters_query->where('fabric_transfers.from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->filled('to_warehouse_id')) {
            $total_meters_query->where('fabric_transfers.to_warehouse_id', $request->to_warehouse_id);
        }
        if ($request->filled('fabric_id')) {
            $total_meters_query->where('fabric_transfer_items.fabric_id', $request->fabric_id);
        }

        $total_meters = $total_meters_query->sum('fabric_transfer_items.meter');

        return view('admin.inventory.fabric_transfer.history', compact('warehouses', 'fabrics', 'transfers', 'total_meters'));
    }

    public function historyList(Request $request)
    {
        $query = FabricTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'items.fabric'])->latest();

        if ($request->filled('start_date')) {
            $query->whereDate('transfer_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('transfer_date', '<=', $request->end_date);
        }
        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->from_warehouse_id);
        }
        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->to_warehouse_id);
        }
        if ($request->filled('fabric_id')) {
            $query->whereHas('items', function($q) use ($request) {
                $q->where('fabric_id', $request->fabric_id);
            });
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('from_warehouse', function($row) {
                return $row->fromWarehouse->cutting_master_name ?? 'N/A';
            })
            ->addColumn('to_warehouse', function($row) {
                return $row->toWarehouse->cutting_master_name ?? 'N/A';
            })
            ->addColumn('fabric_details', function($row) {
                $fabrics = $row->items->pluck('fabric.name')->filter()->unique()->toArray();
                return implode(', ', $fabrics) ?: 'N/A';
            })
            ->addColumn('total_rolls', function($row) {
                return $row->items->count();
            })
            ->addColumn('total_qty', function($row) {
                return $row->items->sum('meter');
            })
            ->addColumn('action', function($row) {
                return '<a href="' . route('admin.inventory.fabric_transfer.show', $row->id) . '" class="btn btn-xs btn-primary"><i class="fas fa-eye"></i> View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function show($id)
    {
        $transfer = FabricTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'items.fabricReceiptDetail', 'items.fabric'])->findOrFail($id);
        return view('admin.inventory.fabric_transfer.show', compact('transfer'));
    }

    public function downloadPdf($id)
    {
        $transfer = FabricTransfer::with(['fromWarehouse', 'toWarehouse', 'user', 'items.fabricReceiptDetail', 'items.fabric'])->findOrFail($id);
        $pdf = Pdf::loadView('admin.inventory.fabric_transfer.pdf', compact('transfer'));
        return $pdf->download('Fabric_Transfer_' . $transfer->transfer_no . '.pdf');
    }
}
