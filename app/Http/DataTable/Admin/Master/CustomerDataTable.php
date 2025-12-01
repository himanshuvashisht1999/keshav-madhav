<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterCustomer;
use Yajra\DataTables\Facades\DataTables;

class CustomerDataTable  {

    public function __construct(MasterCustomer $customer) {
        $this->customer = $customer;
    }

    public function indexList($request){
        $queue = MasterCustomer::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','asc');
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

                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.customer.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}