<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\WarehouseDetail;
use App\Models\OrderProduct;
use App\Models\MasterWarehouseBlock;
use Yajra\DataTables\Facades\DataTables;

class WarehouseDataTable  {

    public function indexList($request){
        $queue = Order::with('product');

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('product_sku') && !empty($request->product_sku)) {
                    $productSku = $request->product_sku;

                    $query->whereHas('product', function ($p) use ($productSku) {
                        $p->where('product_sku', 'like', "%$productSku%");
                    });
                }
                if ($request->has('quantity') && !empty($request->quantity)) {
                    $qty = $request->quantity;

                    $query->whereHas('product', function ($p) use ($qty) {
                        $p->where('quantity', $qty);
                    });
                }

                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->get('status'));
                }
                $query->where('order_main_id',$request->order_main_id);
                
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

            ->editColumn('product_sku', function ($queue) {
				return $queue->product?->product_sku;
                
            })
            ->editColumn('quantity', function ($queue) {
				return $queue->product?->quantity;
                
            })
            
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                
                $view = '<a href="' . route('admin.warehouse.produce',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted" title="View"></i></a>';
                $status = '<a href="javascript:void(0);" data-id="'.$parameter.'" data-order_sku="'.$queue->sku.'" title="Status" class="statusLink" style="margin-left: 8px;"><i class="fas fa-chart-line text-muted"></i> </a>';
                
                return $view . ' ' . (($queue->status != 1) ? $status : '');
            })
            
            ->rawColumns(['action','product_sku','quantity','status'])
            ->make(true);
    }

    public function indexListOrder($request){
        $queue = OrderMain::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('master_customer_id') && !empty($request->master_customer_id)) {
                    $query->where('master_customer_id', 'like', "%{$request->get('master_customer_id')}%");
                }
                if ($request->has('created_at') && !empty($request->created_at)) {
                    $query->where('created_at', 'like', "%{$request->get('created_at')}%");
                }
                if ($request->has('expected_delivery_date') && !empty($request->expected_delivery_date)) {
                    $query->where('expected_delivery_date', 'like', "%{$request->get('expected_delivery_date')}%");
                }
                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->get('status'));
                }
                
            }) 
         
            ->addColumn('status', function ($queue) {
                if ($queue->status == 1) {
                    return '<span class="badge badge-primary">In Progress</span>';
                }elseif($queue->status == 3){
                    return '<span class="badge badge-success">Completed</span>';
                } else {
                    return '<span class="badge badge-warning">In Progress</span>';
                }
            })
            
            ->editColumn('master_customer_id', function ($queue) {
				return $queue->customer?->name;
                
            })
            ->editColumn('created_at', function ($queue) {
                return $queue->created_at ? getformatDateTime($queue->created_at) : '-';
            })
            ->editColumn('expected_delivery_date', function ($queue) {
                return getformatDate($queue->expected_delivery_date);
            })
            
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                
                $view = '<a href="' . route('admin.warehouse.index',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted" title="View"></i></a>';
                
                return $view;
            })
            
            ->rawColumns(['action','master_customer_id','created_at','status'])
            ->make(true);
    }


    public function indexListListing($request){
        $queue = WarehouseDetail::query();
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');

                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }

                //  Filter by order_product_id (product_sku in OrderProduct)
                if ($request->has('order_product_id') && !empty($request->order_product_id)) {
                    $query->whereHas('orderProduct', function ($q) use ($request) {
                        $q->where('product_sku', 'like', '%' . $request->get('order_product_id') . '%');
                    });
                }
                if ($request->has('lot_no') && !empty($request->lot_no)) {
                    $query->where('lot_no', 'like', "%{$request->get('lot_no')}%");
                }
                if ($request->has('quantity') && !empty($request->quantity)) {
                    $query->where('quantity', $request->get('quantity'));
                }

                if ($request->has('from_stage_id') && $request->filled('from_stage_id')) {
                    $query->where('from_stage_id', $request->get('from_stage_id'));
                }
                if ($request->has('master_warehouse_block_id') && !empty($request->master_warehouse_block_id)) {
                    $query->where('master_warehouse_block_id', $request->get('master_warehouse_block_id'));
                }
                if ($request->has('created_at') && !empty($request->created_at)) {
                    $query->where('created_at', 'like', "%{$request->get('created_at')}%");
                }
                
            }) 

            ->editColumn('master_warehouse_block_id', function ($queue) {
				$master_warehouse_block_id= $queue->master_warehouse_block_id;
                $order_product_data = MasterWarehouseBlock::where('id',$master_warehouse_block_id)->first();
                return $order_product_data->name ?? '';
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
                return $queue->created_at ? getformatDateTime($queue->created_at) : '-';
            })
            
            ->addColumn('action', function ($queue) {
                return '';
            })

            
            ->rawColumns(['action','status','order_no','order_product_id','from_stage_id','created_at'])
            ->make(true);
    }
}