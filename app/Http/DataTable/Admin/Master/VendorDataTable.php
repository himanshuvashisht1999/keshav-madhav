<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Vendor;
use Yajra\DataTables\Facades\DataTables;

class VendorDataTable  {

    public function __construct(Vendor $vendor) {
        $this->vendor = $vendor;
    }

    public function indexList($request){
        $queue = Vendor::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                $query->orWhere('name', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                if ($request->has('phone') && !empty($request->phone)) {
                    $query->where('phone', 'like', "%{$request->get('phone')}%");
                }
                if ($request->has('email') && !empty($request->email)) {
                    $query->where('email', 'like', "%{$request->get('email')}%");
                }

                if ($request->has('status') && $request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
                if ($request->has('type') && $request->filled('type')) {
                    $query->where('type', $request->get('type'));
                }
                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.vendor.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}