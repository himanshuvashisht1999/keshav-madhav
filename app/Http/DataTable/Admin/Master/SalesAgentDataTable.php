<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\SalesAgent;
use Yajra\DataTables\Facades\DataTables;

class SalesAgentDataTable
{

    public function indexList($request)
    {
        $queue = SalesAgent::where('status', '!=', 3)->withSum('shops as total_balance', 'balance')->with('currentOpeningBalance')->withCount('shops');

        if ($request->has('search') && !empty($request->get('search')['value'])) {
            $searchValue = $request->get('search')['value'];
            $queue->where(function ($q) use ($searchValue) {
                $q->where('name', 'like', "%{$searchValue}%")
                    ->orWhere('phone', 'like', "%{$searchValue}%");
            });
        }
        if ($request->has('name') && !empty($request->name)) {
            $queue->where('name', 'like', "%{$request->get('name')}%");
        }

        $filteredAgents = (clone $queue)->get();
        $totalCurrentBalance = $filteredAgents->sum('total_balance');
        
        $totalOpeningBalance = 0;
        $agentIds = $filteredAgents->pluck('id')->toArray();
        if (!empty($agentIds)) {
            $opBalances = \App\Models\MasterOpeningBalance::where('master_type', 'sales_agent')
                ->whereIn('master_id', $agentIds)
                ->where('financial_year', \App\Models\MasterOpeningBalance::openingKey())
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

            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->editColumn('total_balance', function ($queue) {
                $balance = $queue->total_balance ?? 0;
                $color = ($balance >= 0) ? 'var(--erp-green-primary)' : '#dc2626';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:bold;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->addColumn('opening_balance', function ($queue) {
                if ($queue->currentOpeningBalance) {
                    $type = $queue->currentOpeningBalance->balance_type == 'Credit' ? 'var(--erp-green-primary)' : '#dc2626';
                    $label = $queue->currentOpeningBalance->balance_type;
                    return '<span style="color:' . $type . '; font-weight:bold;">' . number_format($queue->currentOpeningBalance->amount, 2) . ' (' . $label . ')</span>';
                }
                return '<span class="text-muted">0.00</span>';
            })
            ->addColumn('shops_count', function ($queue) {
                return $queue->shops_count ?? 0;
            })
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.sales-agent.view', ['id' => $parameter]) . '" class="erp-action-btn erp-btn-view" title="View"><i class="fas fa-eye"></i></a>
                    <a href="' . route('admin.master.sales-agent.edit', ['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="' . route('admin.master.sales-agent.delete', ['id' => $parameter]) . '" class="erp-action-btn erp-btn-delete" onclick="return confirm(\'Are you sure?\')" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })

            ->rawColumns(['action', 'status', 'total_balance', 'opening_balance'])
            ->make(true);
    }
}
