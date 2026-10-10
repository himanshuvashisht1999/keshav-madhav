<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\PurchaseAgent;
use Yajra\DataTables\Facades\DataTables;

class PurchaseAgentDataTable
{
    public function indexList($request)
    {
        $queue = PurchaseAgent::withSum('vendors', 'balance')->with('currentOpeningBalance');

        if ($request->has('search') && !empty($request->get('search')['value'])) {
            $searchValue = $request->get('search')['value'];
            $queue->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                  ->orWhere('email', 'like', "%{$searchValue}%")
                  ->orWhere('phone', 'like', "%{$searchValue}%");
            });
        }

        $filteredAgents = (clone $queue)->get();
        $totalCurrentBalance = $filteredAgents->sum('vendors_sum_balance');
        
        $totalOpeningBalance = 0;
        $agentIds = $filteredAgents->pluck('id')->toArray();
        if (!empty($agentIds)) {
            $opBalances = \App\Models\MasterOpeningBalance::where('master_type', 'purchase_agent')
                ->whereIn('master_id', $agentIds)
                ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
                ->get();
            foreach ($opBalances as $ob) {
                $amt = (float) $ob->amount;
                if (strtolower(trim($ob->balance_type)) === 'debit') {
                    $amt = -$amt;
                }
                $totalOpeningBalance += $amt;
            }
        }

        return DataTables::of($queue)->addIndexColumn()
            ->with('total_opening_balance', $totalOpeningBalance)
            ->with('total_current_balance', $totalCurrentBalance)
            ->filter(function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->editColumn('balance', function ($queue) {
                $balance = $queue->vendors_sum_balance ?? 0;
                $color = ($balance >= 0) ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:700;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->addColumn('opening_balance', function ($queue) {
                if ($queue->currentOpeningBalance) {
                    $color = $queue->currentOpeningBalance->balance_type == 'Credit' ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                    $label = $queue->currentOpeningBalance->balance_type;
                    return '<span style="color:' . $color . '; font-weight:700;">' . number_format($queue->currentOpeningBalance->amount, 2) . ' (' . $label . ')</span>';
                }
                return '<span style="color:var(--erp-green-primary); font-weight:700;">0.00 (Credit)</span>';
            })
            ->editColumn('status', function ($queue) {
                return ($queue->status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.purchase-agent.edit', ['id' => $queue->id]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $queue->id . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })
            ->rawColumns(['action', 'status', 'balance', 'opening_balance'])
            ->make(true);
    }
}
