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
        if ($request->has('agent_ids') && !empty($request->agent_ids)) {
            $agentIds = is_array($request->agent_ids) ? $request->agent_ids : explode(',', $request->agent_ids);
            $agentIds = array_filter($agentIds);
            if (!empty($agentIds)) {
                $queue->whereIn('sales_agent_id', $agentIds);
            }
        } elseif ($request->has('agent_name') && !empty($request->agent_name)) {
            $queue->whereHas('agent', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('agent_name')}%");
            });
        }
        if ($request->has('status') && $request->filled('status')) {
            $queue->where('status', $request->get('status'));
        }
        if ($request->has('type') && $request->filled('type')) {
            $type = $request->get('type');
            if ($type == 'direct') {
                $queue->where('subtype', 'direct');
            } else {
                $queue->where('type', $type);
            }
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $hasDateFilter = !empty($startDate) || !empty($endDate);
        
        $calculatedBalances = [];
        $totalOpeningBalance = 0;
        $totalCurrentBalance = 0;

        if ($hasDateFilter) {
            $customerIds = (clone $queue)->pluck('id')->toArray();
            if (!empty($customerIds)) {
                $calculatedBalances = app(\App\Services\Admin\Master\CustomerService::class)->calculateCustomerBalances($customerIds, $startDate, $endDate);
                foreach ($calculatedBalances as $cb) {
                    $totalOpeningBalance += $cb['opening_balance'];
                    $totalCurrentBalance += $cb['closing_balance'];
                }
            }
        } else {
            $customerIds = (clone $queue)->pluck('id')->toArray();
            if (!empty($customerIds)) {
                $calculatedBalances = app(\App\Services\Admin\Master\CustomerService::class)->calculateCustomerBalances($customerIds, null, null);
                foreach ($calculatedBalances as $cb) {
                    $totalOpeningBalance += $cb['opening_balance'];
                    $totalCurrentBalance += $cb['closing_balance'];
                }
            }
        }

        return DataTables::of($queue)->addIndexColumn()
            ->with('total_opening_balance', $totalOpeningBalance)
            ->with('total_current_balance', $totalCurrentBalance)
            ->order(function ($query) {
                $query->orderBy('id', 'asc');
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.customer.edit',['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })
            
            ->editColumn('balance', function ($queue) use ($hasDateFilter, $calculatedBalances) {
                if (isset($calculatedBalances[$queue->id])) {
                    $balance = $calculatedBalances[$queue->id]['closing_balance'];
                } else {
                    $balance = $queue->balance;
                }
                $color = ($balance >= 0) ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:700;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->addColumn('opening_balance', function ($queue) use ($hasDateFilter, $calculatedBalances) {
                if ($hasDateFilter && isset($calculatedBalances[$queue->id])) {
                    $opBal = $calculatedBalances[$queue->id]['opening_balance'];
                    $color = ($opBal >= 0) ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                    $label = ($opBal >= 0) ? 'Credit' : 'Debit';
                    return '<span style="color:' . $color . '; font-weight:700;">' . number_format(abs($opBal), 2) . ' (' . $label . ')</span>';
                }
                if ($queue->currentOpeningBalance) {
                    $color = $queue->currentOpeningBalance->balance_type == 'Credit' ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                    $label = $queue->currentOpeningBalance->balance_type;
                    return '<span style="color:' . $color . '; font-weight:700;">' . number_format($queue->currentOpeningBalance->amount, 2) . ' (' . $label . ')</span>';
                }
                return '<span style="color:var(--erp-green-primary); font-weight:700;">0.00 (Credit)</span>';
            })
            ->addColumn('agent_name', function ($queue) {
                return $queue->agent ? $queue->agent->name : '';
            })
            ->rawColumns(['action', 'status', 'balance', 'opening_balance'])
            ->make(true);
    }
}