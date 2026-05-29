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

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id', 'desc');

                $query->orWhere('id', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('id') && !empty($request->id)) {
                    $query->where('id', 'like', "%{$request->get('id')}%");
                }

                if ($request->has('vendor_id') && !empty($request->vendor_id)) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                }
                if ($request->has('master_fabric_warehouse_id') && !empty($request->master_fabric_warehouse_id)) {
                    $query->where('master_fabric_warehouse_id', $request->get('master_fabric_warehouse_id'));
                }
                if ($request->has('shipment_id') && !empty($request->shipment_id)) {
                    $query->where('shipment_id', $request->get('shipment_id'));
                }
                if ($request->has('bill_no') && !empty($request->bill_no)) {
                    $query->where('bill_no', 'like', "%{$request->get('bill_no')}%");
                }
                if ($request->has('truck_number') && !empty($request->truck_number)) {
                    $query->where('truck_number', 'like', "%{$request->get('truck_number')}%");
                }
                if ($request->has('received_by') && !empty($request->received_by)) {
                    $query->where('received_by', 'like', "%{$request->get('received_by')}%");
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


                $query->where('status', 1);
            })
            ->editColumn('time', function ($queue) {
                return getformatDate($queue->time);
            })

            ->editColumn('master_fabric_warehouse_id', function ($queue) {
                $master_fabric_warehouse_id = $queue->master_fabric_warehouse_id;
                $fabric_warehouse = MasterFabricWarehouse::where('id', $master_fabric_warehouse_id)->first();
                if ($fabric_warehouse) {
                    return $fabric_warehouse->cutting_master_name;
                } else {
                    return '';
                }
            })

            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
                return $queue?->vendor->name;
            })

            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                $paid = $queue->paid_amount;
                $total = $queue->total_amount;
                $is_paid = ($paid >= $total && $total > 0);

                $action = '<a href="' . route('admin.fabric_receipt.view', ['id' => $parameter]) . '" class="mr-2" data-toggle="tooltip" data-placement="top" title="View"><i class="fas fa-eye text-muted"></i></a>';
                
                if (!$is_paid) {
                    $action .= '<a href="' . route('admin.fabric_receipt.edit', ['id' => $parameter]) . '" class="mr-2" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-primary"></i></a>';
                }

                if ($queue->can_delete) {
                    $action .= '<a href="javascript:void(0)" onclick="deleteData(\'' . $parameter . '\')" class="text-danger" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash"></i></a>';
                }

                return $action;
            })

            ->rawColumns(['action', 'status', 'vendor_id'])
            ->make(true);
    }
}