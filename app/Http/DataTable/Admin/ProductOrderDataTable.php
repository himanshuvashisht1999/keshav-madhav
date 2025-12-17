<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;

class ProductOrderDataTable  {

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
                if($queue->status == 2){
                    return '<span class="badge badge-success">Completed</span>';
                } else {
                    return '<span class="badge badge-primary">In Progress</span>';
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
            ->addColumn('total_pcs', function ($queue) {
                $total = DB::table('order_products_sets')
                    ->where('order_main_id', $queue->id)
                    ->sum('total_quantity');
                return $total;
            })
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                
                $view = '<a href="' . route('admin.product_order.indexOrderSet',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted" title="View"></i></a>';
                
                return $view;
            })
            
            ->rawColumns(['action','master_customer_id', 'total_pcs', 'created_at','status'])
            ->make(true);
    }

    public function indexListOrderSet($request){
        // dd($request->all());
        $queue = OrderProductSet::query()->where('order_main_id',$request->get('id'));

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','asc');
                // $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('bar_code') && !empty($request->bar_code)) {
                    $query->where('bar_code', 'like', "%{$request->get('bar_code')}%");
                }
                if ($request->has('design_number') && !empty($request->design_number)) {
                    $query->where('design_number', 'like', "%{$request->get('design_number')}%");
                }
                // if ($request->has('created_at') && !empty($request->created_at)) {
                //     $query->where('created_at', 'like', "%{$request->get('created_at')}%");
                // }
                // if ($request->has('expected_delivery_date') && !empty($request->expected_delivery_date)) {
                //     $query->where('expected_delivery_date', 'like', "%{$request->get('expected_delivery_date')}%");
                // }
                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->get('status'));
                }
                
            }) 
            ->addColumn('color_id', function ($queue) {
                $name = DB::table('master_colors')->where('id', $queue->color_id)
                    ->value('name');

                return $name ?? '';
            })
            ->addColumn('assign_to', function ($queue) {
                $cutting_master_name = DB::table('order_cutting_stage as ocs')
                    ->leftJoin('master_fabric_warehouse as cm', 'cm.id', '=', 'ocs.to_assign_id')
                    ->where('ocs.order_main_id', $queue->order_main_id)
                    ->value('cm.cutting_master_name');

                return $cutting_master_name ?? '';
            })
            ->addColumn('status', function ($queue) {
                $exists = DB::table('order_cutting_stage')
                    ->where('order_main_id', $queue->order_main_id)
                    ->exists(); 
                if($exists == true){
                    return '<span class="badge badge-success">Issued</span>';
                } else {
                    return '<span class="badge badge-primary">Not Issue</span>';
                }
            })
            ->addColumn('total_qty', function ($queue) {
                return $queue->set_quantity * $queue->no_of_pcs;
            })
            
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                
                $view = '<a href="' . route('admin.product_order.index',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted" title="View"></i></a>';
                $view = '';
                return $view;
            })
            
            ->rawColumns(['action','design_number','assign_to', 'total_qty', 'status'])
            ->make(true);
    }
}