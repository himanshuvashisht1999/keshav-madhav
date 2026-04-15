<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use App\Models\PackingMain;
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
        $queue = OrderMain::query()->where('order_main.status', '!=', 0);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('po_number') && !empty($request->po_number)) {
                    $query->where('po_number', 'like', "%{$request->get('po_number')}%");
                }
                if ($request->has('master_customer_id') && !empty($request->master_customer_id)) {
                    $query->where('master_customer_id', 'like', "%{$request->get('master_customer_id')}%");
                }
                if ($request->has('order_type') && !empty($request->order_type)) {
                    $query->where('order_type', 'like', "%{$request->get('order_type')}%");
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

                $stats = getOrderDispatchData($queue->id);

                if ($stats['remaining'] === 0) {
                    return '<span class="badge badge-success">Completed</span>';
                }

                if ($stats['packed'] > 0) {
                    return '<span class="badge badge-warning">Partial</span>';
                }

                return '<span class="badge badge-primary">In Progress</span>';
            })

            ->addColumn('dispatch_pcs', function ($queue) {
                return getOrderDispatchData($queue->id)['packed'] ?? 0;
            })
            
            ->editColumn('master_customer_id', function ($queue) {
				return $queue->customer?->name;
                
            })
            ->editColumn('order_type', function ($queue) {
				return ucfirst($queue->order_type);
            })
            ->editColumn('created_at', function ($queue) {
                return $queue->created_at ? getformatDate($queue->created_at) : '-';
            })
            ->editColumn('expected_delivery_date', function ($queue) {
                return getformatDate($queue->expected_delivery_date);
            })
            ->addColumn('total_pcs', function ($queue) {
                return getOrderDispatchData($queue->id)['total'] ?? 0;
            })
            ->editColumn('po_number', function ($queue) {
                return $queue->po_number ?? '-';
            })
            ->editColumn('po_date', function ($queue) {
                return $queue->po_date ? getformatDate($queue->po_date) : '-';
            })
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;

                $view = '<a href="' . route('admin.product_order.indexOrderSet',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="View"><i class="fas fa-eye text-muted" title="View"></i></a>';
                $report = '<a href="' . route('admin.report.order-summary.view',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Report" data-original-title="Report"><i class="fas fa-chart-bar text-muted" title="Report"></i></a>';
                
                $edit = '';
                $delete = '';
                
                if ($queue->orderLots()->count() == 0) {
                    $edit = '<a href="' . route('admin.product_order.editOrderMain',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted" title="Edit"></i></a>';
                    $delete = '<a href="javascript:void(0);" onclick="deleteOrder('.$parameter.')" class="" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-danger" title="Delete"></i></a>';
                }

                return $view . ' ' . $edit . ' ' . $report . ' ' . $delete;
            })
            
            ->rawColumns(['action','master_customer_id', 'total_pcs', 'dispatch_pcs', 'created_at','status'])
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

                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->get('status'));
                }

                // Assigned / Pending filter (based on actual assignment column)
                if ($request->filled('assigned_filter')) {
                    if ($request->assigned_filter === 'assigned') {
                        $query->whereNotNull('stage_master_unit_id');
                    } elseif ($request->assigned_filter === 'pending') {
                        $query->whereNull('stage_master_unit_id');
                    }
                }
                
            }) 
            ->addColumn('select', function ($queue) {
                $status = $queue->status;

                if ($status == 2) {
                    return '';
                }else{
                    return '<input type="checkbox" class="row-select" value="'.$queue->id.'">';
                }
                
            })
            ->addColumn('set_size', function ($queue) {
                $set_size = $queue->size_measurement;
                return $set_size->set_size ?? '';
            })
            ->addColumn('size_group', function ($queue) {
                $set_size = $queue->size_measurement;
                return $set_size->size_group ?? '';
            })
            ->addColumn('color_id', function ($queue) {
                $name = $queue->colors->name;
                return $name ?? '';
            })
            ->addColumn('assign_to', function ($queue) {
                // If the entire set is NOT assigned, or IF there's still partial remaining
                if ($queue->remain_total_quantity <= 0) {
                    return '<span class="badge badge-success">Fully Assigned</span>';
                }

                $color = $queue->colors->name ?? '';
                $set_size = $queue->size_measurement;
                return '
                    <button 
                        class="btn btn-sm btn-primary assign-btn"
                        data-id="'.$queue->id.'"
                        data-design="'.$queue->design_number.'"
                        data-set-size="'.$set_size?->set_size.'"
                        data-set-size-group="'.$set_size?->size_group.'"
                        data-color="'.$color.'"
                        data-total="'.$queue->total_quantity.'"
                        data-remain="'.$queue->remain_total_quantity.'">
                        Assign
                    </button>';
            })

            ->addColumn('status', function ($queue) {
                if ($queue->remain_total_quantity <= 0) {
                    return '<span class="badge badge-success">Assigned</span>';
                } elseif ($queue->remain_total_quantity < $queue->total_quantity) {
                    return '<span class="badge badge-warning">Partial</span>';
                } else {
                    return '<span class="badge badge-primary">Not Assigned</span>';
                }
            })

            ->addColumn('total_qty', function ($queue) {
                return $queue->total_quantity;
            })
            
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                
                // Show View/Download if at least some quantity has been assigned
                if ($queue->remain_total_quantity < $queue->total_quantity || $queue->status == 2) {
                    $view = '<a href="' . route('admin.product_order.viewCuttingSlip', ['id' => $parameter]) . '" class="btn btn-xs btn-info" data-toggle="tooltip" title="View Assignments">View</a>';
                } else {
                    $view = '';
                }
                
                return $view;
            })
            
            ->rawColumns(['select','action','design_number', 'size_set','size_group','assign_to', 'total_qty', 'status'])
            ->make(true);
    }



    private function getOrderPackingStats($orderMainId)
    {
        $total = DB::table('order_products_sets')
            ->where('order_main_id', $orderMainId)
            ->sum('total_quantity');

        $packed = DB::table('packing_items as pi')
            ->join('packing_mains as pm', 'pm.id', '=', 'pi.packing_main_id')
            ->where('pm.order_main_id', $orderMainId)
            ->where('pm.status', 1)
            ->whereIn('pm.id', function ($q) use ($orderMainId) {
                $q->select('pc.packing_main_id')
                    ->from('order_dispatch as od')
                    ->join('order_dispatch_details as odd', 'odd.order_dispatch_id', '=', 'od.id')
                    ->join('packing_cartons as pc', 'pc.id', '=', 'odd.carton_packing_id')
                    ->where('od.main_order_id', $orderMainId);
            })
            ->sum('pi.quantity');

        return [
            'total'     => (int) $total,
            'packed'    => (int) $packed,
            'remaining' => max(0, $total - $packed),
        ];
    }
}