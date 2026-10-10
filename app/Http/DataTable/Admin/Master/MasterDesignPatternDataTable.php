<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterDesignPattern;
use Yajra\DataTables\Facades\DataTables;

class MasterDesignPatternDataTable  {

    public function indexList($request){
        $queue = MasterDesignPattern::where('status', '!=', 3);  
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
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
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.design-pattern.edit',['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}