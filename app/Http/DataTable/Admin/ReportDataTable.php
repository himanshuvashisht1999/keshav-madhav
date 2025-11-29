<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\FabricReceipt;
use App\Models\PurchaseOrder;
use App\Models\Fabric;
use App\Models\Stock;
use App\Models\ItemStock;
use App\Models\ItemAttributeValue;
use App\Models\Order;
use App\Models\OrderStageTransaction;
use App\Models\OrderProduct;
use App\Models\PurchaseOrderMaterial;
use App\Models\ItemReceipt;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;


class ReportDataTable  {

    public function fabricReceiptList($request){
        $queue = FabricReceipt::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                
                if ($request->has('vendor_id') && $request->filled('vendor_id')) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                }
                if ($request->has('truck_number') && !empty($request->truck_number)) {
                    $query->where('truck_number', 'like', "%{$request->get('truck_number')}%");
                }
                if ($request->has('received_by') && !empty($request->received_by)) {
                    $query->where('received_by', 'like', "%{$request->get('received_by')}%");
                }
                if ($request->has('time') && !empty($request->time)) {
                    $query->where('time', 'like', "%{$request->get('time')}%");
                }
                if ($request->has('roll') && !empty($request->roll)) {
                    $query->where('roll', 'like', "%{$request->get('roll')}%");
                }

                if ( 
                    $request->has('start_date') && $request->filled('start_date') && 
                    $request->has('end_date') && $request->filled('end_date')
                ) {
                    $query->whereBetween(DB::raw('DATE(time)'),
                        [$request->get('start_date'), $request->get('end_date')]
                    );
                }
                $query->where('status',1);
            }) 
            ->editColumn('time', function ($queue) {
				return getformatDateTime($queue->time);
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
				return $queue?->vendor->name;
            })
            ->addColumn('action', function ($queue) {
                $parameter= $queue->id;
                return '
                <a href="' . route('admin.reports.excel-fabric-receipt-report',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fa fa-download"></i></a>
                ';
            })
            
            ->rawColumns(['status','vendor_id', 'action'])
            ->make(true);
    }

    public function itemReceiptList($request){
        $queue = ItemReceipt::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                
                if ($request->has('vendor_id') && $request->filled('vendor_id')) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                }
                if ($request->has('truck_number') && !empty($request->truck_number)) {
                    $query->where('truck_number', 'like', "%{$request->get('truck_number')}%");
                }
                if ($request->has('received_by') && !empty($request->received_by)) {
                    $query->where('received_by', 'like', "%{$request->get('received_by')}%");
                }
                if ($request->has('time') && !empty($request->time)) {
                    $query->where('time', 'like', "%{$request->get('time')}%");
                }
                if ($request->has('box') && !empty($request->box)) {
                    $query->where('box', 'like', "%{$request->get('box')}%");
                }

                if ( 
                    $request->has('start_date') && $request->filled('start_date') && 
                    $request->has('end_date') && $request->filled('end_date')
                ) {
                    $query->whereBetween(DB::raw('DATE(time)'),
                        [$request->get('start_date'), $request->get('end_date')]
                    );
                }
                $query->where('status',1);
            }) 
            ->editColumn('time', function ($queue) {
				return getformatDateTime($queue->time);
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
				return $queue?->vendor->name;
            })
            ->addColumn('action', function ($queue) {
                $parameter= $queue->id;
                return '
                <a href="' . route('admin.reports.excel-item-receipt-report',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fa fa-download"></i></a>
                ';
            })
            
            ->rawColumns(['status','vendor_id', 'action'])
            ->make(true);
    }

    public function purchaseOrderList($request){
        $queue = PurchaseOrder::query();
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                
                if ($request->has('date') && $request->filled('date')) {
                    $query->where('date', $request->get('date'));
                }
                if ($request->has('vendor_id') && $request->filled('vendor_id')) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                }
                if ($request->has('delivery_date') && $request->filled('delivery_date')) {
                    $query->where('delivery_date', $request->get('delivery_date'));
                }
                
                if (
                    $request->has('selected_field') && $request->filled('selected_field') && 
                    $request->has('start_date') && $request->filled('start_date') && 
                    $request->has('end_date') && $request->filled('end_date')
                ) {
                    $query->whereBetween(
                        $request->get('selected_field'),
                        [$request->get('start_date'), $request->get('end_date')]
                    );
                }
            }) 
            ->editColumn('date', function ($queue) {
                return getformatDate($queue->date);
            })
            ->editColumn('delivery_date', function ($queue) {
                return getformatDate($queue->delivery_date);
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
				return $queue?->vendor->name;
            })
            ->addColumn('action', function ($queue) {
                $parameter= $queue->id;
                return '
                <a href="' . route('admin.reports.excel-purchase-order-report',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fa fa-download"></i></a>
                ';
            })
            ->rawColumns(['status','vendor_id', 'action'])
            ->make(true);
    }

    public function itemPurchaseOrderList($request){
        $queue = PurchaseOrderMaterial::query();
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                
                if ($request->has('date') && $request->filled('date')) {
                    $query->where('date', $request->get('date'));
                }
                if ($request->has('vendor_id') && $request->filled('vendor_id')) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                }
                if ($request->has('delivery_date') && $request->filled('delivery_date')) {
                    $query->where('delivery_date', $request->get('delivery_date'));
                }
                
                if (
                    $request->has('selected_field') && $request->filled('selected_field') && 
                    $request->has('start_date') && $request->filled('start_date') && 
                    $request->has('end_date') && $request->filled('end_date')
                ) {
                    $query->whereBetween(
                        $request->get('selected_field'),
                        [$request->get('start_date'), $request->get('end_date')]
                    );
                }
            }) 
            ->editColumn('date', function ($queue) {
                return getformatDate($queue->date);
            })
            ->editColumn('delivery_date', function ($queue) {
                return getformatDate($queue->delivery_date);
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
				return $queue?->vendor->name;
            })
            ->addColumn('action', function ($queue) {
                $parameter= $queue->id;
                return '
                <a href="' . route('admin.reports.item-excel-purchase-order-report',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fa fa-download"></i></a>
                ';
            })
            ->rawColumns(['status','vendor_id', 'action'])
            ->make(true);
    }

    public function itemStockList($request){
        $queue = ItemStock::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                
                if ($request->has('date') && !empty($request->date)) {
                    $query->where('date', 'like', "%{$request->get('date')}%");
                }
                if ($request->has('quantity') && !empty($request->quantity)) {
                    $query->where('quantity', 'like', "%{$request->get('quantity')}%");
                }
                if ($request->has('unique_number') && !empty($request->unique_number)) {
                    $query->where('unique_number', 'like', "%{$request->get('unique_number')}%");
                }
                
                if ($request->has('batch_no') && !empty($request->batch_no)) {
                    $query->where('batch_no', 'like', "%{$request->get('batch_no')}%");
                }
                $query->where('status',1);
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            
            ->rawColumns(['status'])
            ->make(true);
    }

    public function itemStockSkuList($request){
        $queue = ItemAttributeValue::withSum('item_stocks as total_quantity', 'quantity')->orderBy('total_quantity', 'asc');
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                // $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
            
                $query->where('status',1);
            })
            ->editColumn('total_quantity', function ($queue) {
                return $queue->total_quantity ?? 0;
            })
            ->addColumn('action', function($row){
                // Example: return button(s)
                return '<a href="' . route('admin.reports.itemStockDetails',['id' => $row->id]) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function fabricStockList($request){
        $queue = Stock::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                
                if ($request->has('date') && !empty($request->date)) {
                    $query->where('date', 'like', "%{$request->get('date')}%");
                }
                if ($request->has('meter') && !empty($request->meter)) {
                    $query->where('meter', 'like', "%{$request->get('meter')}%");
                }
                if ($request->has('unique_number') && !empty($request->unique_number)) {
                    $query->where('unique_number', 'like', "%{$request->get('unique_number')}%");
                }
                
                if ($request->has('batch_no') && !empty($request->batch_no)) {
                    $query->where('batch_no', 'like', "%{$request->get('batch_no')}%");
                }
                $query->where('status',1);
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            
            ->rawColumns(['status'])
            ->make(true);
    }

    public function fabricStockSkuList($request){
        $queue = Fabric::withSum('stocks as total_meter', 'meter')->orderBy('total_meter', 'asc');
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
            
                $query->where('status',1);
            })
            ->addColumn('action', function($row){
                // Example: return button(s)
                return '<a href="' . route('admin.reports.fabricStockDetails',['id' => $row->id]) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function productionList($request){
        $queue = Order::query();

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
            ->editColumn('expected_delivery_date', function ($queue) {
                return $queue->expected_delivery_date ? getformatDate($queue->expected_delivery_date) : '-';
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
            
            ->editColumn('master_customer_id', function ($queue) {
				return $queue->customer?->name;
                
            })
            ->editColumn('created_at', function ($queue) {
                return $queue->created_at ? getformatDateTime($queue->created_at) : '-';
            })
            
            ->rawColumns(['master_customer_id','created_at','status'])
            ->make(true);
    }

    public function stagesList($request){
        $queue = OrderStageTransaction::query();
        
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');

                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }

                // Filter by order_product_id (product_sku in OrderProduct)
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
                
                if (
                    $request->has('selected_field') && $request->filled('selected_field') && 
                    $request->has('start_date') && $request->filled('start_date') && 
                    $request->has('end_date') && $request->filled('end_date')
                ) {
                    $query->whereBetween(
                        DB::raw('DATE('.$request->get('selected_field').')'),
                        [$request->get('start_date'), $request->get('end_date')]
                    );
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
                return $queue->created_at ? getformatDateTime($queue->created_at) : '-';
            })
            ->editColumn('updated_at', function ($queue) {
                return $queue->remaining_quantity == 0 
                ? getformatDateTime($queue->updated_at)
                : 'In Progress';
            })
            ->addColumn('status', function ($queue) {
                if ($queue->remaining_quantity > 0) {
                    return '<span class="badge badge-warning">In Progress</span>';
                } else {
                    return '<span class="badge badge-success">Completed</span>';
                }
            })
            
            ->rawColumns(['status','order_no','order_product_id','from_stage_id','created_at','updated_at'])
            ->make(true);
    }
}