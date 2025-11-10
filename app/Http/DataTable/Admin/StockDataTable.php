<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\Stock;
use Yajra\DataTables\Facades\DataTables;

class StockDataTable  {

    public function indexList($request){
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
            ->editColumn('date', function ($queue) {
				return getformatDate($queue->date);
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            // ->editColumn('date', function ($queue) {
			// 	return $queue?->date->name;
            // })
            
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.stock.view',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}