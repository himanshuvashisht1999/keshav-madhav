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
                return ($status == 'active') ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-primary">Inactive</span>';
            })
            ->addColumn('logo', function ($queue) {
                $src = $queue->logo ? asset('assets/brands/' . $queue->logo) : asset('assets/products/default-image.png');
                return '<img src="'.$src.'" alt="Logo" style="height:40px;width:auto;border-radius:4px;">';
            })
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                return '
                <a href="' . route('admin.master.brand.edit',['id' => $parameter]) . '" class="mx-2" data-toggle="tooltip" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteItem(' . $parameter . ')" class="mx-2" data-toggle="tooltip" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'status', 'logo'])
            ->make(true);
    }
}
