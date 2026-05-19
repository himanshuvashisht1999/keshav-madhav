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
        $queue = MasterCustomer::with(['currentOpeningBalance', 'agent'])->where('status', '!=', 3);

        if ($request->has('name') && !empty($request->name)) {
            $queue->where('name', 'like', "%{$request->get('name')}%");
        }
        if ($request->has('phone') && !empty($request->phone)) {
            $queue->where('phone', 'like', "%{$request->get('phone')}%");
        }
        if ($request->has('agent_name') && !empty($request->agent_name)) {
            $queue->whereHas('agent', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('agent_name')}%");
            });
        }
        if ($request->has('status') && $request->filled('status')) {
            $queue->where('status', $request->get('status'));
        }
        if ($request->has('type') && $request->filled('type')) {
            $queue->where('type', $request->get('type'));
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $hasDateFilter = !empty($startDate) || !empty($endDate);
        
        $calculatedBalances = [];
        if ($hasDateFilter) {
            $customerIds = (clone $queue)->pluck('id')->toArray();
            $calculatedBalances = app(\App\Services\Admin\Master\CustomerService::class)->calculateCustomerBalances($customerIds, $startDate, $endDate);
        }

        return DataTables::of($queue)->addIndexColumn()
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
            
            ->editColumn('balance', function ($queue) use ($hasDateFilter, $calculatedBalances) {
                if ($hasDateFilter && isset($calculatedBalances[$queue->id])) {
                    $balance = $calculatedBalances[$queue->id]['closing_balance'];
                } else {
                    $balance = $queue->balance;
                }
                $color = ($balance >= 0) ? 'green' : 'red';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:bold;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->addColumn('opening_balance', function ($queue) use ($hasDateFilter, $calculatedBalances) {
                if ($hasDateFilter && isset($calculatedBalances[$queue->id])) {
                    $opBal = $calculatedBalances[$queue->id]['opening_balance'];
                    $color = ($opBal >= 0) ? 'green' : 'red';
                    $label = ($opBal >= 0) ? 'Credit' : 'Debit';
                    return '<span style="color:' . $color . '; font-weight:bold;">' . number_format(abs($opBal), 2) . ' (' . $label . ')</span>';
                }
                if ($queue->currentOpeningBalance) {
                    $type = $queue->currentOpeningBalance->balance_type == 'Credit' ? 'green' : 'red';
                    $label = $queue->currentOpeningBalance->balance_type;
                    return '<span style="color:' . $type . '; font-weight:bold;">' . number_format($queue->currentOpeningBalance->amount, 2) . ' (' . $label . ')</span>';
                }
                return '0.00';
            })
            ->addColumn('agent_name', function ($queue) {
                return $queue->agent ? $queue->agent->name : '';
            })
            ->rawColumns(['action', 'status', 'balance', 'opening_balance'])
            ->make(true);
    }
}