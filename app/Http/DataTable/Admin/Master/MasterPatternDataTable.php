<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterPattern;
use Yajra\DataTables\Facades\DataTables;

class MasterPatternDataTable  {

    public function __construct(MasterPattern $masterPattern) {
        $this->masterPattern = $masterPattern;
    }

    public function indexList($request){
        $queue = MasterPattern::query();

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
                
            }) 
            ->editColumn('sku', function ($queue) {
				$sku= $queue->sku;
                return $sku;
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.pattern.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['sku', 'action', 'status'])
            ->make(true);
    }
}