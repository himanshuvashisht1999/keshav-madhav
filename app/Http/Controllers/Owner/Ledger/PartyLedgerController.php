<?php

namespace App\Http\Controllers\Owner\Ledger;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use App\Models\SalesAgent;
use App\Services\Ledger\PartyLedgerService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Owner Party Ledger (read only). Shows the same parties and the same figures as the admin
 * Party Ledger (Admin\Ledger\PartyLedgerController::getPartyListData): opening, period debit,
 * period credit and closing of every party, from PartyLedgerService::listFigures (the records of
 * each statement added up per party type, so every party of the list takes about a second, with or without dates).
 * Every active party is listed, and an inactive or deleted party too while its closing balance is not 0.
 */
class PartyLedgerController extends Controller
{
    use OwnerLedgerHelpers;

    public function index(Request $request)
    {
        $typeId = $request->query('type_id');
        $search = $request->query('search');
        [$startDate, $endDate] = $this->listPeriod($request);
        $dated = $startDate || $endDate;

        $masters = AdjustmentMaster::whereNotIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();

        // Bank and cash accounts have their own ledger page
        $picked = $typeId && $typeId !== 'sales_agent' ? AdjustmentMaster::find($typeId) : null;
        if ($picked && in_array($picked->name, ['Bank Account', 'Cash Master'])) {
            return redirect()->route('owner.bank-cash-ledger.index', $request->query());
        }

        // Customer type / Vendor type only filter their own type, as on the admin list (the hidden select of another type is ignored)
        $customerType = $picked && ($picked->model_name === 'App\Models\MasterCustomer' || strtolower($picked->name) === 'customer')
            ? $request->query('customer_type', $request->query('type')) : null;
        $vendorType = $picked && ($picked->model_name === 'App\Models\Vendor' || strtolower($picked->name) === 'vendor')
            ? $request->query('vendor_type', $request->query('purchase_agent_id')) : null;
        $parties = $this->partyList($typeId, $search, $customerType, $vendorType, $masters);

        app(PartyLedgerService::class)->fillListFigures($parties, $startDate, $endDate);
        $parties = $parties->filter(fn($p) => (int) ($p->status ?? 1) === 1 || abs((float) $p->balance) >= 0.005);
        $parties = $this->applyClosingFilters($parties, $request)->sortBy('name')->values();

        $perPage = $this->listPerPage();
        $page = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil($parties->count() / $perPage)));
        $rows = new LengthAwarePaginator($parties->forPage($page, $perPage)->values(), $parties->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.party-ledger.partials.ledger_list', [
                    'parties' => $rows, 'startDate' => $startDate, 'endDate' => $endDate,
                ])->render(),
                'next_page' => $rows->hasMorePages() ? $rows->currentPage() + 1 : null,
            ]);
        }

        // Totals of the whole filtered list (every party, not only this page), each shop counted once: with
        // "All types" a sales agent adds only its shops that are not listed on their own (same rule as admin).
        $money = app(PartyLedgerService::class)->totalsRows($parties, !$typeId, $startDate, $endDate,
            fn($party) => $this->applyClosingFilters(collect([$party]), $request)->isNotEmpty());
        $summary = [
            'count' => $parties->count(),
            'agent_note' => !$typeId && $parties->contains('party_type', 'sales_agent'),
            'not_active' => $parties->filter(fn($p) => (int) ($p->status ?? 1) !== 1)->count(),
            'debit' => (float) abs($money->where('balance', '<', 0)->sum('balance')),
            'credit' => (float) $money->where('balance', '>', 0)->sum('balance'),
            'net' => (float) $money->sum('balance'),
            'dated' => $dated,
            'opening' => $dated ? (float) $money->sum('opening_balance') : null,
            'period_debit' => $dated ? (float) $money->sum('period_debit') : null,
            'period_credit' => $dated ? (float) $money->sum('period_credit') : null,
        ];

        $selectedMaster = $masters->firstWhere('id', $typeId);
        $purchaseAgents = ($selectedMaster && $selectedMaster->model_name === 'App\Models\Vendor')
            ? \App\Models\PurchaseAgent::where('status', 1)->orderBy('name')->get(['id', 'name'])
            : collect();

        return view('owner.party-ledger.index', [
            'parties' => $rows,
            'masters' => $masters,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'typeId' => $typeId,
            'selectedMaster' => $selectedMaster,
            'customerType' => $customerType,
            'vendorType' => $vendorType,
            'purchaseAgents' => $purchaseAgents,
        ]);
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->ledger($request, $type, $id);
        $transactions = collect($data['transactions']);
        [$entries, $broughtForward] = $this->statementPage($transactions, $request, (float) $data['openingBalAmount']);

        return view('owner.party-ledger.show', $data + [
            'entries' => $entries,
            'broughtForward' => $broughtForward,
            'periodDebit' => (float) ($data['periodTotalDebit'] ?? $transactions->sum('debit')),
            'periodCredit' => (float) ($data['periodTotalCredit'] ?? $transactions->sum('credit')),
            'pdfTooBig' => $this->pdfRefusal($transactions->count(), $data['startDate'], $data['endDate']) !== null,
        ]);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->ledger($request, $type, $id);
        $refusal = $this->pdfRefusal(count($data['transactions']), $data['startDate'], $data['endDate']);
        if ($refusal) {
            return redirect()
                ->route('owner.party-ledger.show', array_merge($request->query(), ['type' => $data['type'], 'id' => $id]))
                ->with('error', $refusal);
        }
        $pdf = \PDF::loadView('owner.party-ledger.download', $data);
        return $pdf->download($this->pdfFileName($data['party']->name));
    }

    /** The statement of one party: the same calculation as the admin statement. */
    private function ledger(Request $request, $type, $id): array
    {
        $this->cleanPeriod($request);
        $data = app(PartyLedgerService::class)->build($request, $type, $id);
        // a bank account (reached through its type) has no name column
        if (empty($data['party']->name) && !empty($data['party']->bank_name)) {
            $data['party']->name = $data['party']->bank_name;
        }
        return $data;
    }

    /**
     * The parties of the list: same masters, search and customer / vendor type filters as the
     * admin list, any status (index() keeps the active ones and those with a balance).
     */
    private function partyList($typeId, $search, $customerType, $vendorType, Collection $masters): Collection
    {
        $parties = collect();
        $agents = function () use ($search) {
            return SalesAgent::query()
                ->when($search, fn($q) => $q->where('name', 'LIKE', "%$search%"))
                ->get()
                ->map(function ($v) {
                    $v->party_type = 'sales_agent';
                    $v->master_id_val = 'sales_agent';
                    return $v;
                });
        };

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                return $agents();
            }
            $master = AdjustmentMaster::find($typeId);
            return $master ? $this->masterParties($master, $search, $customerType, $vendorType) : $parties;
        }

        foreach ($masters as $master) {
            $parties = $parties->concat($this->masterParties($master, $search, $customerType, $vendorType));
        }
        return $parties->concat($agents());
    }

    private function masterParties(AdjustmentMaster $master, $search, $customerType, $vendorType): Collection
    {
        $modelName = $master->model_name;
        if (!$modelName || !class_exists($modelName)) {
            return collect();
        }
        $isCustomer = ($modelName === 'App\Models\MasterCustomer' || strtolower($master->name) === 'customer');
        $isVendor = ($modelName === 'App\Models\Vendor' || strtolower($master->name) === 'vendor');

        return $modelName::query()
            ->when($search, function ($q) use ($search, $modelName) {
                $q->where($modelName === 'App\Models\BankAccount' ? 'bank_name' : 'name', 'LIKE', "%$search%");
            })
            ->when($isCustomer && !empty($customerType), function ($q) use ($customerType) {
                if ($customerType == 'direct') {
                    $q->where('subtype', 'direct');
                } elseif ($customerType == 'agent') {
                    $q->where('subtype', 'agent');
                } else {
                    $q->where('type', $customerType);
                }
            })
            ->when($isVendor && !empty($vendorType), function ($q) use ($vendorType) {
                if ($vendorType === 'direct') {
                    $q->where(fn($sub) => $sub->whereNull('purchase_agent_id')->orWhere('purchase_agent_id', 0));
                } elseif ($vendorType === 'agent') {
                    $q->whereNotNull('purchase_agent_id')->where('purchase_agent_id', '!=', 0);
                } elseif (str_starts_with($vendorType, 'pa_')) {
                    $q->where('purchase_agent_id', (int) substr($vendorType, 3));
                } elseif (is_numeric($vendorType)) {
                    $q->where('purchase_agent_id', (int) $vendorType);
                }
            })
            ->get()
            ->map(function ($v) use ($master) {
                $v->party_type = strtolower($master->name);
                $v->master_id_val = $master->id;
                if (!isset($v->name) && isset($v->bank_name)) {
                    $v->name = $v->bank_name;
                }
                return $v;
            });
    }
}
