<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Fabric;
use Yajra\DataTables\Facades\DataTables;

class FabricDataTable  {

    public function indexList($request){
        $queue = Fabric::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('name', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('dye_id') && $request->filled('dye_id')) {
                    $query->where('dye_id', $request->get('dye_id'));
                }
                if ($request->has('width_id') && $request->filled('width_id')) {
                    $query->where('width_id', $request->get('width_id'));
                }
                if ($request->has('weave_type_id') && $request->filled('weave_type_id')) {
                    $query->where('weave_type_id', $request->get('weave_type_id'));
                }
                if ($request->has('gsm_id') && $request->filled('gsm_id')) {
                    $query->where('gsm_id', $request->get('gsm_id'));
                }
                if ($request->has('composition_id') && $request->filled('composition_id')) {
                    $query->where('composition_id', $request->get('composition_id'));
                }
                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.fabric.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}