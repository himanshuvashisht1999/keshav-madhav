<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Company;
use Yajra\DataTables\Facades\DataTables;

class CompanyDataTable {

    public function indexList($request){
        $queue = Company::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%")
                          ->orWhere('gst_number', 'like', "%{$searchValue}%")
                          ->orWhere('id', 'like', "%{$searchValue}%");
                    });
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
                    <a href="' . route('admin.master.company.edit',['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteItem(' . $parameter . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
