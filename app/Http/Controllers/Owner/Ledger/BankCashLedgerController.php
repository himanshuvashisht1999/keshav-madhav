<?php

namespace App\Http\Controllers\Owner\Ledger;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use App\Services\Ledger\BankLedgerService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Owner Bank & Cash ledger (read only): only Bank Account / Cash Master accounts, with the same
 * figures as the admin Bank & Cash ledger (Admin\Ledger\BankCashLedgerController::getBankCashListData,
 * BankLedgerService for every account: opening, period debit (money in), period credit (money out), closing).
 */
class BankCashLedgerController extends Controller
{
    use OwnerLedgerHelpers;

    private const TYPES = ['Bank Account', 'Cash Master'];

    public function index(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');
        [$startDate, $endDate] = $this->listPeriod($request);
        $ledgerRequest = new Request(array_filter(['start_date' => $startDate, 'end_date' => $endDate]));

        $masters = AdjustmentMaster::whereIn('name', self::TYPES)->where('status', 1)->get();
        $useMasters = $masters;
        if ($typeId) {
            // only a bank / cash type can be picked here (any other type gave links that end in 404)
            $master = AdjustmentMaster::find($typeId);
            $useMasters = ($master && in_array($master->name, self::TYPES)) ? collect([$master]) : collect();
        }

        $accounts = collect();
        foreach ($useMasters as $master) {
            $accounts = $accounts->concat($this->masterAccounts($master, $search, $ledgerRequest));
        }
        $accounts = $this->applyClosingFilters($accounts, $request)->sortBy('name')->values();

        $perPage = $this->listPerPage();
        $page = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil($accounts->count() / $perPage)));
        $rows = new LengthAwarePaginator($accounts->forPage($page, $perPage)->values(), $accounts->count(), $perPage, $page, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.bank-cash-ledger.partials.ledger_list', [
                    'parties' => $rows, 'startDate' => $startDate, 'endDate' => $endDate,
                ])->render(),
                'next_page' => $rows->hasMorePages() ? $rows->currentPage() + 1 : null,
            ]);
        }

        $summary = [
            'count' => $accounts->count(),
            'debit' => (float) abs($accounts->where('balance', '<', 0)->sum('balance')),
            'credit' => (float) $accounts->where('balance', '>', 0)->sum('balance'),
            'net' => (float) $accounts->sum('balance'),
            'opening' => (float) $accounts->sum('opening_balance'),
            'period_debit' => (float) $accounts->sum('period_debit'),
            'period_credit' => (float) $accounts->sum('period_credit'),
            'dated' => $startDate || $endDate,
        ];

        return view('owner.bank-cash-ledger.index', [
            'parties' => $rows,
            'masters' => $masters,
            'summary' => $summary,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'typeId' => $typeId,
        ]);
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->ledger($request, $type, $id);
        $transactions = collect($data['transactions']);
        [$entries, $broughtForward] = $this->statementPage($transactions, $request, (float) $data['openingBalAmount']);

        return view('owner.bank-cash-ledger.show', $data + [
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
                ->route('owner.bank-cash-ledger.show', array_merge($request->query(), ['type' => strtolower(trim((string) $type)), 'id' => $id]))
                ->with('error', $refusal);
        }
        $pdf = \PDF::loadView('owner.bank-cash-ledger.download', $data);
        return $pdf->download($this->pdfFileName($data['party']->name));
    }

    /** Statement of one bank / cash account (404 for any other type, as on the admin). */
    private function ledger(Request $request, $type, $id): array
    {
        $this->cleanPeriod($request);
        $data = app(BankLedgerService::class)->build($request, $type, $id);
        // bank accounts have no name column: show the bank name on the page and in the PDF
        if (empty($data['party']->name)) {
            $data['party']->name = $data['party']->bank_name ?? $data['party']->account_name ?? 'Account';
        }
        return $data;
    }

    /** Active accounts of one bank / cash type with their ledger figures, exactly as Admin withLedgerFigures. */
    private function masterAccounts(AdjustmentMaster $master, $search, Request $ledgerRequest): Collection
    {
        $modelName = $master->model_name;
        if (!$modelName || !class_exists($modelName)) {
            return collect();
        }
        return $modelName::where('status', 1)
            ->when($search, function ($q) use ($search, $modelName) {
                if ($modelName === 'App\Models\BankAccount') {
                    $q->where(function ($sub) use ($search) {
                        $sub->where('bank_name', 'LIKE', "%$search%")
                            ->orWhere('account_name', 'LIKE', "%$search%")
                            ->orWhere('account_number', 'LIKE', "%$search%")
                            ->orWhere('branch_name', 'LIKE', "%$search%");
                    });
                } else {
                    $q->where('name', 'LIKE', "%$search%");
                }
            })
            ->get()
            ->map(function ($v) use ($master, $ledgerRequest) {
                $v->party_type = strtolower($master->name);
                $v->master_id_val = $master->id;
                if (!isset($v->name) && isset($v->bank_name)) {
                    $v->name = $v->bank_name;
                }
                $data = $this->ledger($ledgerRequest, $v->party_type, $v->id);
                $v->opening_balance = (float) ($data['openingBalAmount'] ?? 0);
                $v->period_debit = (float) ($data['periodTotalDebit'] ?? 0);
                $v->period_credit = (float) ($data['periodTotalCredit'] ?? 0);
                $v->balance = (float) ($data['party']->balance ?? 0);
                return $v;
            });
    }
}
