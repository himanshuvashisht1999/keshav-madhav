<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\PurchaseOrder;
use Yajra\DataTables\Facades\DataTables;

class PurchaseOrderDataTable  {

    public function indexList($request){
        $queue = PurchaseOrder::where('status', 1);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->where('status', 1);
                
                if (!empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function($q) use ($searchValue) {
                        $q->where('sku', 'like', "%{$searchValue}%");
                    });
                }

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
                
                $query->orderBy('id','desc');
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
                <a href="' . route('admin.purchase_order.view',['id' => $parameter]) . '" class="text-info mx-1" data-toggle="tooltip" title="View"><i class="fas fa-eye"></i></a>
                <a href="' . route('admin.purchase_order.edit',['id' => $parameter]) . '" class="text-primary mx-1" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="text-danger mx-1" data-toggle="tooltip" title="Delete"><i class="fas fa-trash"></i></a>
                <button type="button" class="btn-send-email btn btn-sm btn-outline-primary ml-1" data-id="' . $parameter . '" title="Resend PO">
                    <i class="fas fa-envelope"></i>
                </button>
                ';
            })
            
            ->rawColumns(['action', 'status','vendor_id'])
            ->make(true);
    }
}