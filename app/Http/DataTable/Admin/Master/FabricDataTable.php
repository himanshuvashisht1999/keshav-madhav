<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Fabric;
use Yajra\DataTables\Facades\DataTables;

class FabricDataTable
{

    public function indexList($request)
    {
        $queue = Fabric::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','asc');
                $query->orWhere('name', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                // if ($request->has('sku') && !empty($request->sku)) {
                //     $query->where('sku', 'like', "%{$request->get('sku')}%");
                // }
                // if ($request->has('dye_id') && $request->filled('dye_id')) {
                //     // dd($request->get('dye_id'));
                //     $query->where('dye_id', $request->get('dye_id'));
                // }
                // if ($request->has('width_id') && $request->filled('width_id')) {
                //     $query->where('width_id', $request->get('width_id'));
                // }
                // if ($request->has('weave_type_id') && $request->filled('weave_type_id')) {
                //     $query->where('weave_type_id', $request->get('weave_type_id'));
                // }
                // if ($request->has('gsm_id') && $request->filled('gsm_id')) {
                //     $query->where('gsm_id', $request->get('gsm_id'));
                // }
                if ($request->has('composition_id') && $request->filled('composition_id')) {
                    $query->where('composition_id', $request->get('composition_id'));
                }
                if ($request->has('path') && $request->filled('path')) {
                    $query->where('path', $request->get('path'));
                }
                if ($request->has('vendor_id') && $request->filled('vendor_id')) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                }
            })

            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
                return $queue?->fabric_vendor->name ?? 'N/A';
            })
            // ->editColumn('dye_id', function ($queue) {
            //     return $queue?->fabric_dye->sku;
            // })
            // ->editColumn('gsm_id', function ($queue) {
            //     return $queue?->fabric_gsm->name;
            // })
            ->editColumn('composition_id', function ($queue) {
                return $queue?->fabric_composition->name ?? 'N/A';
            })
            // ->editColumn('image', function ($queue) {
            //     $image = $queue->image;
            //     return '<a href="'.$image.'" target="_blank">Image</a>';
            // })
            ->addColumn('image', function ($queue) {
                $img = $queue->image; // relationship
              
                $src = $img ? $queue->image : asset('assets/products/default-image.png');

                return '<img src="'.$src.'" class="img-thumbnail fabric-img" alt="Main Image" style="height:50px;width:50px;border-radius:4px;">';
            })

            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                <a href="' . route('admin.master.fabric.edit', ['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })

            ->rawColumns(['action', 'status', 'vendor_id','composition_id', 'image'])
            ->make(true);
    }
}
