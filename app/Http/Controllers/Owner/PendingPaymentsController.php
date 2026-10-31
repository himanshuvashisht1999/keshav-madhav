<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use App\Services\Ledger\PartyLedgerService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Owner "Pending Payments", built from party balances = the same figures as the party ledgers.
 *  - They owe us (tab receivable): customers and vendors whose balance is below 0 (Dr).
 *  - We owe (tab payable): vendors, customers (advance received) and every other party master whose balance is above 0 (Cr).
 * Ledger rule: balance = credit - debit, so a negative balance means the party owes us.
 * Masters with a balance column use the stored balance (it equals the ledger; balances:check reconciles it daily).
 * Masters without one (expense heads) use the PartyLedgerService closing. Only a party with a credit entry can be above 0,
 * so the ledger is built only for those few parties.
 * Bank and cash accounts are our own money, not parties, so they are left out, and so is the partners' capital
 * (Capital master). Inactive and deleted parties are listed with a badge while their balance is not 0.
 * Figures come from party balances, not per-bill payments (payments are booked against the party, never a bill).
 */
class PendingPaymentsController extends Controller
{
    private const PER_PAGE = 50;

    public function index(Request $request)
    {
        $tab = $request->query('tab') === 'payable' ? 'payable' : 'receivable';
        $search = trim((string) $request->query('search'));
        $type = strtolower(trim((string) $request->query('type')));

        $types = $this->partyTypes();
        $tabTypes = $tab === 'receivable'
            ? array_intersect_key($types, array_flip(['customer', 'vendor']))
            : $types;
        if (!isset($tabTypes[$type])) {
            $type = '';
        }

        $rows = collect();
        foreach ($tabTypes as $key => $t) {
            if ($type !== '' && $type !== $key) {
                continue;
            }
            $rows = $rows->concat($t['has_balance']
                ? $this->storedBalances($key, $t, $tab, $search)
                : $this->ledgerBalances($key, $t, $tab, $search));
        }
        $this->addLastDates($rows, $tab);

        $rows = $rows->sort(fn ($a, $b) => [$b->amount, $a->name] <=> [$a->amount, $b->name])->values();

        $byType = $rows->groupBy('type')->map(fn ($g) => (object) [
            'label' => $g->first()->type_label,
            'count' => $g->count(),
            'amount' => round($g->sum('amount'), 2),
        ])->sortByDesc('amount')->values();
        $totals = [
            'parties' => $rows->count(),
            'amount' => round($rows->sum('amount'), 2),
            'by_type' => $byType,
        ];

        $page = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil($rows->count() / self::PER_PAGE)));
        $parties = new LengthAwarePaginator(
            $rows->forPage($page, self::PER_PAGE)->values(),
            $rows->count(),
            self::PER_PAGE,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $typeOptions = collect($tabTypes)->map(fn ($t) => $t['label'])->sort();

        return view('owner.reports.pending_payments', compact('tab', 'search', 'type', 'typeOptions', 'parties', 'totals'));
    }

    /**
     * Party masters keyed by ledger type (lower-case master name, as the party ledger URL uses it), one per model.
     * Bank Account / Cash Master are our own accounts and Capital is the partners' own money, so they are skipped.
     */
    private function partyTypes(): array
    {
        $masters = AdjustmentMaster::orderByDesc('status')->orderBy('id')->get();
        $tables = [];
        foreach ($masters as $m) {
            if ($m->model_name && class_exists($m->model_name)) {
                $tables[$m->model_name] = (new $m->model_name)->getTable();
            }
        }
        $columns = DB::table('information_schema.COLUMNS')
            ->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', array_values($tables))
            ->whereIn('COLUMN_NAME', ['name', 'balance', 'status', 'phone'])
            ->get(['TABLE_NAME as t', 'COLUMN_NAME as c'])
            ->groupBy('t')
            ->map(fn ($g) => $g->pluck('c')->all());

        $types = [];
        $seenModels = [];
        foreach ($masters as $m) {
            $key = strtolower(trim($m->name));
            $model = $m->model_name;
            if (in_array($key, ['bank account', 'cash master'], true) || $model === \App\Models\CapitalMaster::class || !isset($tables[$model])) {
                continue;
            }
            if (isset($seenModels[$model])) {
                $types[$seenModels[$model]]['master_ids'][] = $m->id; // e.g. the old inactive 'Vendor' master
                continue;
            }
            $cols = $columns[$tables[$model]] ?? [];
            if (!in_array('name', $cols, true)) {
                continue;
            }
            $seenModels[$model] = $key;
            $types[$key] = [
                'label' => $m->name,
                'model' => $model,
                'table' => $tables[$model],
                'master_ids' => [$m->id],
                'has_balance' => in_array('balance', $cols, true),
                'has_status' => in_array('status', $cols, true),
                'has_phone' => in_array('phone', $cols, true),
            ];
        }
        return $types;
    }

    private function row(string $key, array $t, $p, float $balance): object
    {
        return (object) [
            'type' => $key,
            'type_label' => $t['label'],
            'model' => $t['model'],
            'id' => $p->id,
            'name' => $p->name,
            'status' => (int) ($p->status ?? 1),
            'phone' => $p->phone ?? null,
            'balance' => round($balance, 2),
            'amount' => round(abs($balance), 2),
            'last_date' => null,
        ];
    }

    private function partyQuery(array $t, string $search)
    {
        $columns = ['id', 'name'];
        if ($t['has_phone']) {
            $columns[] = 'phone';
        }
        if ($t['has_balance']) {
            $columns[] = 'balance';
        }
        if ($t['has_status']) {
            $columns[] = 'status';
        }
        // any status: a party that still has a balance is listed even when inactive or deleted
        return DB::table($t['table'])
            ->when($search !== '', fn ($q) => $q->where('name', 'like', '%' . addcslashes($search, '%_\\') . '%'))
            ->select($columns);
    }

    /** Parties whose stored balance is below 0 (receivable) or above 0 (payable). */
    private function storedBalances(string $key, array $t, string $tab, string $search)
    {
        return $this->partyQuery($t, $search)
            ->where('balance', $tab === 'receivable' ? '<' : '>', 0)
            ->get()
            ->map(fn ($p) => $this->row($key, $t, $p, (float) $p->balance));
    }

    /** Masters without a balance column: ledger closing of the parties that have any credit entry (payable tab only). */
    private function ledgerBalances(string $key, array $t, string $tab, string $search)
    {
        if ($tab !== 'payable') {
            return collect();
        }
        $candidateIds = DB::table('payment_adjustments')
            ->whereIn('adjustment_master_id', $t['master_ids'])->where('type', 'credit')
            ->distinct()->pluck('ref_id')
            ->merge(DB::table('journal_voucher_items')
                ->whereIn('master_type', $t['master_ids'])->whereRaw("LOWER(type) = 'credit'")
                ->distinct()->pluck('master_id'))
            ->merge(DB::table('payments')
                ->where('party_type', $t['model'])->whereIn('payment_type', ['received', 'credit'])
                ->where(fn ($q) => $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)->orWhereNull('paymentable_type'))
                ->distinct()->pluck('party_id'))
            ->merge(DB::table('payment_opening_balances')
                ->whereIn('master_type', [$key, str_replace(' ', '_', $key)])
                ->whereRaw("LOWER(balance_type) = 'credit'")->where('amount', '>', 0)
                ->distinct()->pluck('master_id'))
            ->map(fn ($id) => (int) $id)->filter()->unique()->values()->all();
        if (!$candidateIds) {
            return collect();
        }

        $ledger = app(PartyLedgerService::class);
        $rows = collect();
        foreach ($this->partyQuery($t, $search)->whereIn('id', $candidateIds)->get() as $p) {
            $data = $ledger->build(Request::create('/', 'GET'), $key, $p->id);
            $balance = round((float) ($data['party']->balance ?? 0), 2);
            if ($balance > 0) {
                $rows->push($this->row($key, $t, $p, $balance));
            }
        }
        return $rows;
    }

    /**
     * Last dispatch (they owe us) or last bill (we owe) per party, one grouped query per source.
     * Receivable: agent order dispatches + sales-order dispatches. Payable: fabric receipts (vendors) and
     * contractor / washing / consumable vouchers. Other parties show '-'.
     */
    private function addLastDates($rows, string $tab): void
    {
        $sources = $tab === 'receivable'
            ? [
                \App\Models\MasterCustomer::class => [
                    ['table' => 'agent_order_dispatches', 'id' => 'master_customer_id', 'date' => 'dispatch_date', 'where' => ['status' => 'dispatched', 'party_type' => 'customer']],
                    ['table' => 'order_dispatch', 'id' => 'customer_id', 'date' => 'dispatch_date', 'where' => []],
                ],
                \App\Models\Vendor::class => [
                    ['table' => 'agent_order_dispatches', 'id' => 'master_vendor_id', 'date' => 'dispatch_date', 'where' => ['status' => 'dispatched', 'party_type' => 'vendor']],
                ],
            ]
            : [
                \App\Models\Vendor::class => [['table' => 'fabric_receipts', 'id' => 'vendor_id', 'date' => 'COALESCE(time, DATE(created_at))', 'where' => []]],
                \App\Models\Contractor::class => [['table' => 'contractor_vouchers', 'id' => 'contractor_id', 'date' => 'voucher_date', 'where' => []]],
                \App\Models\WashingMaster::class => [['table' => 'washing_vouchers', 'id' => 'washing_master_id', 'date' => 'voucher_date', 'where' => []]],
                \App\Models\ConsumableGood::class => [['table' => 'consumable_vouchers', 'id' => 'consumable_good_id', 'date' => 'voucher_date', 'where' => []]],
            ];

        foreach ($rows->groupBy('model') as $model => $group) {
            $ids = $group->pluck('id')->all();
            $last = [];
            foreach ($sources[$model] ?? [] as $s) {
                $dates = DB::table($s['table'])
                    ->where($s['where'])
                    ->whereIn($s['id'], $ids)
                    ->groupBy($s['id'])
                    ->selectRaw("{$s['id']} as pid, MAX({$s['date']}) as d")
                    ->pluck('d', 'pid');
                foreach ($dates as $pid => $d) {
                    if ($d && (!isset($last[$pid]) || $d > $last[$pid])) {
                        $last[$pid] = $d;
                    }
                }
            }
            foreach ($group as $r) {
                $r->last_date = $last[$r->id] ?? null;
            }
        }
    }
}
