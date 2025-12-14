<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class OrderDigitalizationDataTable  {

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
                
                $view = '<a href="' . route('admin.product_order.produce',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted" title="View"></i></a>';
                $status = '<a href="javascript:void(0);" data-id="'.$parameter.'" data-order_sku="'.$queue->sku.'" title="Status" class="statusLink" style="margin-left: 8px;"><i class="fas fa-chart-line text-muted"></i> </a>';
                
                return $view . ' ' . (($queue->status != 1) ? $status : '');
            })
            
            ->rawColumns(['action','product_sku','quantity','status'])
            ->make(true);
    }
}