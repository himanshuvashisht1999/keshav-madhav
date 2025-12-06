<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterWarehouse;
use Yajra\DataTables\Facades\DataTables;

class MasterWarehouseDataTable  {

    public function indexList($request){
        $queue = MasterWarehouse::query();

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
                if ($request->has('address') && !empty($request->address)) {
                    $query->where('address', 'like', "%{$request->get('address')}%");
                }
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.warehouse.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}