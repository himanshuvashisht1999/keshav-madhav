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
            
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                
                return '
                    <button class="btn btn-sm btn-primary viewBtn" 
                            data-id="'.$queue->order_product_id.'" 
                            data-stage_id="'.$queue->from_stage_id.'"
                            data-toggle="modal" 
                            data-target="#viewModal">
                        Transfer
                    </button>
                ';
            })
            
            ->rawColumns(['action','order_no','order_product_id','from_stage_id','created_at'])
            ->make(true);
    }
}