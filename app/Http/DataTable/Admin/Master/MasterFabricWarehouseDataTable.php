<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterFabricWarehouse;
use Yajra\DataTables\Facades\DataTables;

class MasterFabricWarehouseDataTable  {

    protected $warehouse;
    public function __construct(MasterFabricWarehouse $warehouse) {
        $this->warehouse = $warehouse;
    }

    public function indexList($request){
        $queue = MasterFabricWarehouse::where('status', '!=', 3);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('cutting_master_name') && !empty($request->cutting_master_name)) {
                    $query->where('cutting_master_name', 'like', "%{$request->get('cutting_master_name')}%");
                }
                if ($request->has('address') && !empty($request->address)) {
                    $query->where('address', 'like', "%{$request->get('address')}%");
                }
                if ($request->has('status') && $request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
            }) 
            ->order(function ($query) {
                $query->orderBy('id', 'asc');
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.fabric_warehouse.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}