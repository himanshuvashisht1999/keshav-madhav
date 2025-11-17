<?php

namespace App\Http\DataTable\Admin;

use App\Models\ItemStock;
use App\Models\ItemAttributeValue;
use Yajra\DataTables\Facades\DataTables;

class ItemStockDataTable  {

    public function indexList($request){
        $queue = ItemStock::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('id') && !empty($request->id)) {
                    $query->where('id', 'like', "%{$request->get('id')}%");
                }
                
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
            ->editColumn('date', function ($queue) {
                return getformatDate($queue->date);
            })
            ->editColumn('status', function ($queue) {
                $status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            
            ->addColumn('action', function ($queue) {
                $parameter= $queue->id;
                return '
                <a href="' . route('admin.item_stock.view',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function itemIndexList($request){
        $queue = ItemAttributeValue::withSum('item_stocks as total_quantity', 'quantity');
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
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
                return '<a href="' . route('admin.item_stock.index',['id' => $row->id]) . '" class="btn btn-sm btn-primary">View</a>';
            })
            ->rawColumns(['total_quantity','action'])
            ->make(true);
    }

}
