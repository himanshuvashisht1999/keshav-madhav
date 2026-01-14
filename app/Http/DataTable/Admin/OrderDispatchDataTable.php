<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use App\Models\PackingCarton;
use App\Models\CartonPackingSession;
use App\Models\OrderDispatch;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class OrderDispatchDataTable  {

    public function indexList($request){
        $queue = OrderDispatch::with([
            'dispatchDetails',
            'orderMain.customer',
            'orderMain.dispatchCartons'
        ]);
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                if ($request->has('order_dispatch_no') && !empty($request->order_dispatch_no)) {
                    $query->where('sku', 'like', '%' . $request->order_dispatch_no . '%');
                }
                if ($request->filled('main_order_id')) {
                    $query->whereHas('orderMain', function ($q) use ($request) {
                        $q->where('sku', 'like', '%' . $request->main_order_id . '%');
                    });
                }

                if ($request->has('customer_id') && $request->filled('customer_id')) {
                    $query->where('customer_id', $request->get('customer_id'));
                }
            }) 
            ->editColumn('order_dispatch_no', function ($queue) {
                // dd($queue->orderMain->sku);
				return $queue->sku ?? '';
            })
            ->editColumn('main_order_id', function ($queue) {
                // dd($queue->orderMain->sku);
				return $queue->orderMain->sku ?? '';
            })
            ->editColumn('customer_id', function ($queue) {
				return $queue->orderMain->customer->name ?? '';
                
            })
            ->editColumn('dispatch_address', function ($queue) {
				return $queue->orderMain->customer->address ?? '';
                
            })
            ->editColumn('dispatch_date', function ($queue) {
				return date('d M, Y h:i A', strtotime( $queue->dispatch_date )) ?? '';
            })
            ->addColumn('status', function ($queue) {
                if ($queue->status == 1) {
                    return '<span class="badge badge-success px-3 py-1">Dispatched</span>';
                } elseif($queue->status == 2){
                    return '<span class="badge badge-secondary px-3 py-1">Complete</span>';
                } 
                return '<span class="badge badge-light">Unknown</span>';
            })

            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                
                $view = '<a href="' . route('admin.order-dispatch.view',['id' => $parameter]) . '" class="btn btn-sm btn-outline-primary" data-toggle="tooltip" title="View Details"><i class="fas fa-eye"></i> View</a>';
                
                return $view;
            })
            
            ->rawColumns(['action','main_order_id', 'customer_id', 'status'])
            ->make(true);
    }
}