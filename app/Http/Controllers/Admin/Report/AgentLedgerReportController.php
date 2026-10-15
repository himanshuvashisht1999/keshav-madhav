<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use App\Models\SalesAgent;
use App\Services\Ledger\PartyLedgerService;
use Illuminate\Http\Request;

/**
 * Sales agent ledger = the Party Ledger of the agent's shops (same service, same figures):
 * sales dispatched (debit), payments received, sale returns and other credits, opening balances.
 * Balance follows the Party Ledger sign: below 0 = the shops owe us (DR), above 0 = we owe them (CR).
 */
class AgentLedgerReportController extends Controller
{
    public function __construct(private PartyLedgerService $ledger)
    {
    }

    public function index(Request $request)
    {
        $query = SalesAgent::withCount('shops');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%')
                    ->orWhere('phone', 'like', '%' . $request->search . '%');
            });
        }

        $agents = $query->get()->map(function ($agent) {
            $data = $this->ledger->build(new Request(), 'sales_agent', $agent->id);
            $tx = collect($data['transactions'] ?? []);
            $agent->opening_balance = (float) ($data['openingBalAmount'] ?? 0);
            $agent->total_debit = (float) $tx->sum('debit');
            $agent->total_credit = (float) $tx->sum('credit');
            $agent->balance = (float) ($data['party']->balance ?? 0);
            return $agent;
        });

        return view('admin.report.agent_ledger.index', compact('agents'));
    }

    public function show(Request $request, $id)
    {
        $agent = SalesAgent::with('shops')->findOrFail($id);
        $shops = $agent->shops;

        $date = fn($v) => is_string($v) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $v) && checkdate((int) substr($v, 5, 2), (int) substr($v, 8, 2), (int) substr($v, 0, 4)) ? $v : null;
        $startDate = $date($request->query('start_date'));
        $endDate = $date($request->query('end_date'));
        if ($startDate && $endDate && $startDate > $endDate) {
            [$startDate, $endDate] = [$endDate, $startDate];
        }
        $shopId = $shops->contains('id', (int) $request->query('shop_id')) ? (int) $request->query('shop_id') : null;

        // Shop-wise summary over all dates: one ledger split by shop
        $all = $this->ledger->build(new Request(['view_mode' => 'party_wise']), 'sales_agent', $agent->id);
        $shopSummary = collect($all['groupedLedgers'] ?? [])->map(function ($g) {
            $tx = collect($g->transactions);
            return (object) [
                'id' => $g->shop->id,
                'name' => $g->shop->name,
                'opening_balance' => (float) $g->opening_balance,
                'total_debit' => (float) $tx->sum('debit'),
                'total_credit' => (float) $tx->sum('credit'),
                'balance' => (float) $g->closing_balance,
            ];
        });

        // Statement for the chosen period (and shop)
        $data = $this->ledger->build(new Request(array_filter([
            'start_date' => $startDate,
            'end_date' => $endDate,
            'customer_id' => $shopId,
        ])), 'sales_agent', $agent->id);
        $shopNames = $shops->pluck('name', 'id');
        $transactions = collect($data['transactions'] ?? [])->map(function ($tx) use ($shopNames) {
            $tx->shop_name = $shopNames[$tx->customer_id ?? 0] ?? 'N/A';
            return $tx;
        })->values();
        $openingBalance = (float) ($data['openingBalAmount'] ?? 0);
        $closingBalance = $transactions->isNotEmpty() ? (float) $transactions->last()->running_balance : $openingBalance;

        return view('admin.report.agent_ledger.show', compact('agent', 'transactions', 'startDate', 'endDate', 'shops', 'shopId', 'shopSummary', 'openingBalance', 'closingBalance'));
    }
}
