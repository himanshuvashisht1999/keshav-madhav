<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterCustomer;
use Yajra\DataTables\Facades\DataTables;

class CustomerDataTable  {

    protected $customer;
    public function __construct(MasterCustomer $customer) {
        $this->customer = $customer;
    }

    public function indexList($request){
        $queue = MasterCustomer::with('currentOpeningBalance')->where('status', '!=', 3);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
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
                <a href="' . route('admin.master.customer.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            
            ->editColumn('balance', function ($queue) {
                $balance = $queue->balance;
                $color = ($balance >= 0) ? 'green' : 'red';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:bold;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->addColumn('opening_balance', function ($queue) {
                if ($queue->currentOpeningBalance) {
                    $type = $queue->currentOpeningBalance->balance_type == 'Credit' ? 'green' : 'red';
                    $label = $queue->currentOpeningBalance->balance_type;
                    return '<span style="color:' . $type . '; font-weight:bold;">' . number_format($queue->currentOpeningBalance->amount, 2) . ' (' . $label . ')</span>';
                }
                return '0.00';
            })
            ->rawColumns(['action', 'status', 'balance', 'opening_balance'])
            ->make(true);
    }
}