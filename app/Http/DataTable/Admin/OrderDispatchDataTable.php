<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use App\Models\OrderDispatchCarton;
use App\Models\CartonPackingSession;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class OrderDispatchDataTable  {

    public function indexList($request){
        $queue = CartonPackingSession::with([
            'orderMain.customer'
        ]);
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                if ($request->has('carton_packing_session_no') && !empty($request->carton_packing_session_no)) {
                    $query->where('carton_packing_session_no', 'like', "%{$request->get('carton_packing_session_no')}%");
                }
                if ($request->has('main_order_id') && $request->filled('main_order_id')) {
                    $query->where('main_order_id', $request->get('main_order_id'));
                }
                if ($request->has('master_customer_id') && $request->filled('master_customer_id')) {
                    $query->where('customer_id', $request->get('master_customer_id'));
                }
            }) 
            
            ->editColumn('main_order_id', function ($queue) {
                // dd($queue->orderMain->sku);
				return $queue->orderMain->sku ?? '';
            })
            ->editColumn('master_customer_id', function ($queue) {
				return $queue->orderMain->customer->name ?? '';
                
            })
            ->editColumn('total_cartons', function ($queue) {
				return $queue->total_quantity ?? 0; 
            })
            ->addColumn('status', function ($queue) {
                if ($queue->status == 1) {
                    return '<span class="badge badge-primary">Not Issued</span>';
                }elseif($queue->status == 3){
                    return '<span class="badge badge-success">Completed</span>';
                } else {
                    return '<span class="badge badge-warning">In Progress</span>';
                }
            })

            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                
                $view = '<a href="' . route('admin.order_dispatch.view',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted" title="View"></i></a>';
                $status = '<a href="javascript:void(0);" data-id="'.$parameter.'" data-order_sku="'.$queue->sku.'" title="Status" class="statusLink" style="margin-left: 8px;"><i class="fas fa-chart-line text-muted"></i> </a>';
                
                return $view . ' ' . (($queue->status != 1) ? $status : '');
            })
            
            ->rawColumns(['action','main_order_id', 'master_customer_id', 'total_cartons', 'status'])
            ->make(true);
    }
}