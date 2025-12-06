<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterWarehouseBlock;
use Yajra\DataTables\Facades\DataTables;

class MasterWarehouseBlocksDataTable  {

    public function indexList($request){
        $queue = MasterWarehouseBlock::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','asc');
                $query->orWhere('name', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('master_warehouse_id') && !empty($request->master_warehouse_id)) {
                    $query->where('master_warehouse_id', 'like', "%{$request->get('master_warehouse_id')}%");
                }
                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('master_warehouse_id', function ($queue) {
				$master_warehouse_id= $queue->masterWarehouse->name;
                return $master_warehouse_id;
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.warehouse-blocks.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}