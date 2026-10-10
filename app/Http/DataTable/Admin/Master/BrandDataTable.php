<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Brand;
use Yajra\DataTables\Facades\DataTables;

class BrandDataTable  {

    public function indexList($request){
        $queue = Brand::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%")
                          ->orWhere('id', 'like', "%{$searchValue}%");
                    });
                }
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
            }) 
         
            ->editColumn('status', function ($queue) {
				$status = $queue->status;
                return ($status == 'active') 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('logo', function ($queue) {
                $src = $queue->logo ? asset('assets/brands/' . $queue->logo) : asset('assets/products/default-image.png');
                return '<img src="'.$src.'" alt="Logo" style="height:32px;width:auto;border-radius:4px;border:1px solid #e2e8f0;padding:2px;background:#fff;">';
            })
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.brand.edit',['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteItem(' . $parameter . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })
            ->rawColumns(['action', 'status', 'logo'])
            ->make(true);
    }
}
