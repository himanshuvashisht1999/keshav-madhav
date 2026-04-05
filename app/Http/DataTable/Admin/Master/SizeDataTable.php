<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterSize;
use Yajra\DataTables\Facades\DataTables;

class SizeDataTable  {
    protected $master_size;
    public function __construct(MasterSize $master_size) {
        $this->master_size = $master_size;
    }

    public function indexList($request){
        $queue = MasterSize::where('status', '!=', 3);
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('size') && $request->filled('size')) {
                    $query->where('size', 'like', "%" . $request->get('size') . "%");
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
                <a href="' . route('admin.master.size.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}