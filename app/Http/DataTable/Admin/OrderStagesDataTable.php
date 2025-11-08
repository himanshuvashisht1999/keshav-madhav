<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderStageTransaction;
use App\Models\OrderProduct;
use Yajra\DataTables\Facades\DataTables;

class OrderStagesDataTable  {

    public function indexList($request){
        $queue = OrderStageTransaction::query();
        
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');

                // ✅ Filter by order_no (from related Order model)
                // if ($request->has('order_no') && !empty($request->order_no)) {
                //     $query->whereHas('orderProduct.order', function ($q) use ($request) {
                //         $q->where('sku', 'like', '%' . $request->get('order_no') . '%');
                //     });
                // }
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }

                // ✅ Filter by order_product_id (product_sku in OrderProduct)
                if ($request->has('order_product_id') && !empty($request->order_product_id)) {
                    $query->whereHas('orderProduct', function ($q) use ($request) {
                        $q->where('product_sku', 'like', '%' . $request->get('order_product_id') . '%');
                    });
                }
                if ($request->has('quantity') && !empty($request->quantity)) {
                    $query->where('quantity', $request->get('quantity'));
                }

                if ($request->has('remaining_quantity') && !empty($request->remaining_quantity)) {
                    $query->where('remaining_quantity', $request->get('remaining_quantity'));
                }
                if ($request->has('from_stage_id') && $request->filled('from_stage_id')) {
                    $query->where('from_stage_id', $request->get('from_stage_id'));
                }
                if ($request->has('created_at') && !empty($request->created_at)) {
                    $query->where('created_at', 'like', "%{$request->get('created_at')}%");
                }
                if ($request->has('updated_at') && !empty($request->updated_at)) {
                    $query->where('updated_at', 'like', "%{$request->get('updated_at')}%");
                }
                if ($request->has('status') && !empty($request->status)) {
                    if ($request->status === 'in_progress') {
                        $query->where('remaining_quantity', '>', 0);
                    } elseif ($request->status === 'completed') {
                        $query->where('remaining_quantity', '=', 0);
                    }
                }
                
                $query->where('to_stage_id',$request->stage_id);
            }) 

            ->editColumn('order_no', function ($queue) {
				$order_product_id= $queue->order_product_id;
                $order_product_data = OrderProduct::with('order')->where('id',$order_product_id)->first();
                return $order_product_data->order->sku;
            })
            ->editColumn('order_product_id', function ($queue) {
				$order_product_id= $queue->order_product_id;
                $order_product_data = OrderProduct::where('id',$order_product_id)->first();
                return $order_product_data->product_sku;
            })
            ->editColumn('from_stage_id', function ($queue) {
				$from_stage_id= $queue->from_stage_id;
                if($from_stage_id == 0){
                    return 'Stock';
                }else{
                    return $queue->from_stage?->name;
                }
                
            })
         
           
            ->editColumn('created_at', function ($queue) {
                return $queue->created_at ? $queue->created_at->format('d-m-Y H:i A') : '-';
            })
            ->editColumn('updated_at', function ($queue) {
                return $queue->remaining_quantity == 0 
                ? \Carbon\Carbon::parse($queue->updated_at)->format('d M Y h:i A')
                : '-';
            })
            ->addColumn('status', function ($queue) {
                if ($queue->remaining_quantity > 0) {
                    return '<span class="badge badge-warning">In Progress</span>';
                } else {
                    return '<span class="badge badge-success">Completed</span>';
                }
            })

            
            ->addColumn('action', function ($queue) {
                // Common variables
                $transferBtn = '';
                $downloadBtn = '';

                // When work still in progress
                if ($queue->remaining_quantity > 0) {
                    $transferBtn = '
                        <button class="btn btn-sm btn-primary viewBtn" 
                                data-id="'.$queue->order_product_id.'" 
                                data-stage_id="'.$queue->from_stage_id.'"
                                data-order_transaction_id="'.$queue->id.'"
                                data-total_remaining_qty="'.$queue->remaining_quantity.'"
                                data-toggle="modal" 
                                data-target="#viewModal">
                            <i class="fas fa-exchange-alt"></i> Transfer
                        </button>';
                }

                // When completed
                if ($queue->remaining_quantity == 0) {
                    $downloadBtn = '
                        <a href="'.route('admin.order-stages.downLoadReceipt', ['order_transaction_id' => $queue->id]).'" 
                        class="btn btn-sm btn-success">
                        <i class="fas fa-download"></i> Receipt
                        </a>';
                }

                return '<div class="btn-group">'.$transferBtn.' '.$downloadBtn.'</div>';
            })

            
            ->rawColumns(['action','status','order_no','order_product_id','from_stage_id','created_at','updated_at'])
            ->make(true);
    }
}