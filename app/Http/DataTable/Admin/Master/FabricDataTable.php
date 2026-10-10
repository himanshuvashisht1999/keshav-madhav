<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Fabric;
use Yajra\DataTables\Facades\DataTables;

class FabricDataTable
{

    public function indexList($request)
    {
        $queue = Fabric::with(['fabric_unit', 'fabric_vendor', 'fabric_composition'])->where('status', '!=', 3);

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
            ->order(function ($query) {
                $query->orderBy('id', 'asc');
            }) 

            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->editColumn('vendor_id', function ($queue) {
                return $queue?->fabric_vendor->name ?? 'N/A';
            })
            ->editColumn('composition_id', function ($queue) {
                return $queue?->fabric_composition->name ?? 'N/A';
            })
            ->editColumn('fabric_unit_id', function ($queue) {
                return $queue?->fabric_unit->name ?? 'N/A';
            })
            ->addColumn('image', function ($queue) {
                $img = $queue->image; // relationship
              
                $src = $img ? $queue->image : asset('assets/products/default-image.png');

                return '<img src="'.$src.'" class="img-thumbnail fabric-img" alt="Main Image" style="height:40px;width:40px;object-fit:cover;border-radius:4px;cursor:pointer;">';
            })

            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.fabric.edit', ['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })

            ->rawColumns(['action', 'status', 'vendor_id','composition_id', 'image'])
            ->make(true);
    }
}
