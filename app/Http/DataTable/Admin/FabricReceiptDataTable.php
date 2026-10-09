<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\FabricReceipt;
use App\Models\MasterFabricWarehouse;
use Yajra\DataTables\Facades\DataTables;

class FabricReceiptDataTable
{

    public function indexList($request)
    {
        $queue = FabricReceipt::query();
        $this->applyFilters($queue, $request);

        // Calculate totals across ALL filtered records
        $summaryQueue = clone $queue;
        $totals = $summaryQueue->selectRaw('COALESCE(SUM(roll), 0) as total_rolls, COALESCE(SUM(total_amount), 0) as total_amount')->first();
        $totalRolls = (int) ($totals->total_rolls ?? 0);
        $totalAmount = (float) ($totals->total_amount ?? 0);

        $queue->orderBy('id', 'desc');

        return DataTables::of($queue)->addIndexColumn()
            ->with('total_rolls', $totalRolls)
            ->with('total_amount', $totalAmount)
            ->with('formatted_total_rolls', number_format($totalRolls))
            ->with('formatted_total_amount', getIndianCurrency($totalAmount))
            ->editColumn('time', function ($queue) {
                return getformatDate($queue->time);
            })
            ->editColumn('master_fabric_warehouse_id', function ($queue) {
                $master_fabric_warehouse_id = $queue->master_fabric_warehouse_id;
                $fabric_warehouse = MasterFabricWarehouse::where('id', $master_fabric_warehouse_id)->first();
                return $fabric_warehouse ? $fabric_warehouse->cutting_master_name : 'N/A';
            })
            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
                return $queue?->vendor->name ?? 'N/A';
            })
            ->editColumn('total_amount', function ($queue) {
                return $queue->total_amount > 0 ? getIndianCurrency($queue->total_amount) : '0.00';
            })
            ->editColumn('roll', function ($queue) {
                return $queue->roll ? number_format($queue->roll) : '0';
            })
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                $paid = $queue->paid_amount;
                $total = $queue->total_amount;
                $is_paid = ($paid >= $total && $total > 0);

                $action = '<div class="d-inline-flex align-items-center">';
                $action .= '<a href="' . route('admin.fabric_receipt.view', ['id' => $parameter]) . '" class="erp-action-btn erp-btn-view" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>';

                if (!$is_paid) {
                    $action .= '<a href="' . route('admin.fabric_receipt.edit', ['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" data-toggle="tooltip" title="Edit"><i class="fas fa-pencil-alt"></i></a>';
                }

                if ($queue->can_delete) {
                    $action .= '<a href="javascript:void(0)" onclick="deleteData(\'' . $parameter . '\')" class="erp-action-btn erp-btn-delete" data-toggle="tooltip" title="Delete"><i class="fas fa-trash-alt"></i></a>';
                }
                $action .= '</div>';

                return $action;
            })
            ->rawColumns(['action', 'status', 'vendor_id'])
            ->make(true);
    }

    private function applyFilters($query, $request)
    {
        $query->where('status', 1);

        if (!empty($request->get('search')['value'])) {
            $searchValue = $request->get('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('shipment_id', 'like', "%{$searchValue}%")
                  ->orWhere('bill_no', 'like', "%{$searchValue}%");
            });
        }

        if ($request->has('shipment_id') && !empty($request->shipment_id)) {
            $query->where('shipment_id', 'like', "%{$request->get('shipment_id')}%");
        }
        if ($request->has('bill_no') && !empty($request->bill_no)) {
            $query->where('bill_no', 'like', "%{$request->get('bill_no')}%");
        }
        if ($request->has('vendor_id') && !empty($request->vendor_id)) {
            $query->where('vendor_id', $request->get('vendor_id'));
        }
        if ($request->has('master_fabric_warehouse_id') && !empty($request->master_fabric_warehouse_id)) {
            $query->where('master_fabric_warehouse_id', $request->get('master_fabric_warehouse_id'));
        }
        if ($request->has('from_date') && !empty($request->from_date)) {
            $query->whereDate('time', '>=', $request->from_date);
        }
        if ($request->has('to_date') && !empty($request->to_date)) {
            $query->whereDate('time', '<=', $request->to_date);
        }
        if ($request->has('roll') && !empty($request->roll)) {
            $query->where('roll', 'like', "%{$request->get('roll')}%");
        }
        if ($request->has('total_amount') && !empty($request->total_amount)) {
            $query->where('total_amount', 'like', "%{$request->get('total_amount')}%");
        }
    }
}