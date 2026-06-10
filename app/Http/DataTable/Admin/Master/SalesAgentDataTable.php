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

            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('total_balance', function ($queue) {
                $balance = $queue->total_balance ?? 0;
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
            ->addColumn('shops_count', function ($queue) {
                return $queue->shops_count ?? 0;
            })
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                <a href="' . route('admin.master.sales-agent.view', ['id' => $parameter]) . '" class="mr-2" data-toggle="tooltip" data-placement="top" title="View"><i class="fas fa-eye text-primary"></i></a>
                <a href="' . route('admin.master.sales-agent.edit', ['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.master.sales-agent.delete', ['id' => $parameter]) . '" class="ml-2" onclick="return confirm(\'Are you sure?\')" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })

            ->rawColumns(['action', 'status', 'total_balance', 'opening_balance'])
            ->make(true);
    }
}
