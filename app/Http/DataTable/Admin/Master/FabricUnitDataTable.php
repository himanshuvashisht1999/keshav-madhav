<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\FabricUnit;
use Yajra\DataTables\Facades\DataTables;

class FabricUnitDataTable  {

    public function indexList($request){
        $queue = FabricUnit::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if (!empty($request->get('search')['value'])) {
                    $query->where('name', 'like', "%{$request->get('search')['value']}%")
                          ->orWhere('symbol', 'like', "%{$request->get('search')['value']}%");
                }
                
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                if ($request->has('symbol') && !empty($request->symbol)) {
                    $query->where('symbol', 'like', "%{$request->get('symbol')}%");
                }
                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status = $queue->status;
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.fabric_unit.edit',['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                </div>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
