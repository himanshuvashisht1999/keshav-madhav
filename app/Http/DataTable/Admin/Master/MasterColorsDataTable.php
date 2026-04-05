<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterColor;
use Yajra\DataTables\Facades\DataTables;

class MasterColorsDataTable  {

    public function indexList($request){
        $queue = MasterColor::where('status', '!=', 3);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if (!empty($request->get('search')['value'])) {
                    $query->where('name', 'like', "%{$request->get('search')['value']}%");
                }
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                if ($request->has('status') && ($request->status === '0' || !empty($request->status))) {
                    $query->where('status', $request->status);
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
                <a href="' . route('admin.master.colors.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}