<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use App\Models\BankAccount;
use App\Models\CashPayment;
use App\Models\PaymentAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Owner "Payment History": read-only copy of the admin Payment Adjust list
 * (Admin\Payment\PaymentAdjustmentController@index / show), the screen where every payment is entered.
 * Same filters (date, type, account, amount, search) and the same Debit / Credit totals, but the batches are
 * grouped and paged in SQL and the party names are looked up with one query per master (no per-row queries).
 * Debit = money paid out of the account, Credit = money received into it.
 */
class PaymentAdjustmentController extends Controller
{
    /** Batch key exactly as the admin list groups: batch_id, or 'unique_<id>' for a line without a batch. */
    private const BATCH_KEY = "COALESCE(batch_id, CONCAT('unique_', id))";

    public function index(Request $request)
    {
        $masters = AdjustmentMaster::orderBy('id')->get()->keyBy('id');
        $f = $this->filters($request);
        $base = $this->filteredLines($f, $masters);

        $sum = (clone $base)->selectRaw("COUNT(*) as line_count, COUNT(DISTINCT " . self::BATCH_KEY . ") as batch_count,
                COALESCE(SUM(CASE WHEN type = 'debit' THEN amount ELSE 0 END), 0) as debit,
                COALESCE(SUM(CASE WHEN type = 'credit' THEN amount ELSE 0 END), 0) as credit")->first();
        $totals = [
            'batches' => (int) $sum->batch_count,
            'lines' => (int) $sum->line_count,
            'debit' => (float) $sum->debit,
            'credit' => (float) $sum->credit,
        ];

        // newest entry first, as the admin list (it sorts by the id of the batch's newest line)
        $batches = (clone $base)
            ->selectRaw(self::BATCH_KEY . " as batch_key, MAX(id) as last_id, MIN(date) as date, MIN(type) as type,
                MIN(payment_mode) as payment_mode, MIN(payment_account_id) as payment_account_id, COUNT(*) as line_count,
                SUM(amount) as total")
            ->groupBy(DB::raw(self::BATCH_KEY))
            ->orderByDesc('last_id')
            ->paginate(50)
            ->withQueryString();

        // parties and masters of the batches on this page
        $keys = $batches->pluck('batch_key')->all();
        $lines = $keys
            ? (clone $base)->whereIn(DB::raw(self::BATCH_KEY), $keys)->orderByDesc('id')
                ->selectRaw(self::BATCH_KEY . ' as batch_key, id, adjustment_master_id, ref_id')->get()
            : collect();
        $names = $this->entityNames($lines, $masters);
        $linesByBatch = $lines->groupBy('batch_key');
        $accountNames = $this->accountNames();
        foreach ($batches as $b) {
            $batchLines = $linesByBatch[$b->batch_key] ?? collect();
            $b->parties = $batchLines->map(fn ($l) => $names[$l->id]['name'])->unique()->values();
            $b->masters = $batchLines->map(fn ($l) => $masters[$l->adjustment_master_id]->name ?? 'N/A')->unique()->values();
            $b->account = $this->accountName($accountNames, $b->payment_mode, $b->payment_account_id);
            $b->is_grouped = !str_starts_with($b->batch_key, 'unique_');
        }

        $bankAccounts = BankAccount::where('status', 1)->orderBy('bank_name')->get(['id', 'bank_name']);
        $cashAccounts = CashPayment::where('status', 1)->orderBy('name')->get(['id', 'name']);

        return view('owner.reports.payment_adjustments', compact('batches', 'totals', 'f', 'bankAccounts', 'cashAccounts'));
    }

    public function show($batchId)
    {
        $query = DB::table('payment_adjustments');
        if (str_starts_with($batchId, 'unique_')) {
            $query->whereNull('batch_id')->where('id', (int) substr($batchId, 7));
        } else {
            $query->where('batch_id', $batchId);
        }
        $lines = $query->orderBy('id')->get();
        if ($lines->isEmpty()) {
            abort(404);
        }

        $masters = AdjustmentMaster::orderBy('id')->get()->keyBy('id');
        $names = $this->entityNames($lines, $masters);
        $accountNames = $this->accountNames();
        foreach ($lines as $l) {
            $master = $masters[$l->adjustment_master_id] ?? null;
            $l->master_name = $master->name ?? 'N/A';
            $l->party = $names[$l->id]['name'];
            $l->ledger_url = $names[$l->id]['found'] && $master ? $this->ledgerUrl($master->name, $l->ref_id) : null;
            $l->remarks_text = str_replace('[Dist] ', '', (string) $l->remarks);
            $l->account = $this->accountName($accountNames, $l->payment_mode, $l->payment_account_id);
        }

        $first = $lines->first();
        $totals = [
            'lines' => $lines->count(),
            'amount' => (float) $lines->sum('amount'),
            'debit' => (float) $lines->where('type', 'debit')->sum('amount'),
            'credit' => (float) $lines->where('type', 'credit')->sum('amount'),
        ];

        return view('owner.reports.payment_adjustment_details', compact('lines', 'batchId', 'first', 'totals'));
    }

    /** Filter values, checked (a wrong value is ignored instead of giving an error or an empty page). */
    private function filters(Request $request): array
    {
        $date = function ($value) {
            if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            [$y, $m, $d] = array_map('intval', explode('-', $value));
            return checkdate($m, $d, $y) ? $value : null;
        };
        $num = fn ($v) => is_numeric($v) ? (float) $v : null;

        // account: 'bank:5' / 'cash:2' (the admin form sends the bare id as payment_account_id, also accepted)
        $account = (string) $request->query('account', '');
        $accountMode = null;
        $accountId = null;
        if (preg_match('/^(bank|cash):(\d+)$/', $account, $m)) {
            [$accountMode, $accountId] = [$m[1], (int) $m[2]];
        } elseif (ctype_digit((string) $request->query('payment_account_id'))) {
            $accountId = (int) $request->query('payment_account_id');
            $account = '';
        } else {
            $account = '';
        }

        return [
            'from_date' => $date($request->query('from_date')),
            'to_date' => $date($request->query('to_date')),
            'type' => in_array($request->query('type'), ['debit', 'credit'], true) ? $request->query('type') : '',
            'account' => $account,
            'account_mode' => $accountMode,
            'account_id' => $accountId,
            'amount_from' => $num($request->query('amount_from')),
            'amount_to' => $num($request->query('amount_to')),
            'search' => trim((string) $request->query('search')),
        ];
    }

    /** payment_adjustments lines that pass the filters; the search keeps whole batches, as on the admin list. */
    private function filteredLines(array $f, $masters)
    {
        $query = DB::table('payment_adjustments');
        $this->applyLineFilters($query, $f);

        if ($f['search'] !== '') {
            $like = '%' . addcslashes($f['search'], '%_\\') . '%';
            $matches = DB::table('payment_adjustments');
            $this->applyLineFilters($matches, $f);
            $matches->where(function ($w) use ($like, $masters) {
                $w->where('batch_id', 'like', $like)
                    ->orWhere(fn ($q) => $q->whereNull('batch_id')->whereRaw("CONCAT('unique_', id) LIKE ?", [$like]))
                    ->orWhere('remarks', 'like', $like)
                    ->orWhere('payment_mode', 'like', $like)
                    ->orWhereRaw('CAST(amount AS CHAR) LIKE ?', [$like])
                    ->orWhereIn('adjustment_master_id', DB::table('adjustment_masters')->where('name', 'like', $like)->select('id'))
                    ->orWhere(fn ($q) => $q->where('payment_mode', 'bank')
                        ->whereIn('payment_account_id', DB::table('bank_accounts')->where('bank_name', 'like', $like)->select('id')))
                    ->orWhere(fn ($q) => $q->where('payment_mode', '!=', 'bank')
                        ->whereIn('payment_account_id', DB::table('cash_payments')->where('name', 'like', $like)->select('id')));
                // party name (the name shown in the Party column)
                foreach ($this->entityTables($masters) as $masterId => $et) {
                    if (!$et) {
                        $w->orWhere(fn ($q) => $q->where('adjustment_master_id', $masterId)->where('ref_id', 'like', $like));
                        continue;
                    }
                    $w->orWhere(fn ($q) => $q->where('adjustment_master_id', $masterId)
                        ->whereIn('ref_id', DB::table($et['table'])->whereRaw($et['label'] . ' LIKE ?', [$like])->select('id')));
                    $w->orWhere(fn ($q) => $q->where('adjustment_master_id', $masterId)->where('ref_id', 'like', $like)
                        ->whereNotIn('ref_id', DB::table($et['table'])->select('id')));
                }
            });
            // read the matching batch keys first: as an IN (subquery) MariaDB re-runs the search for every line (seconds)
            $keys = $matches->selectRaw(self::BATCH_KEY . ' as batch_key')->distinct()->pluck('batch_key')->all();
            if ($keys) {
                $query->whereIn(DB::raw(self::BATCH_KEY), $keys);
            } else {
                $query->whereRaw('1 = 0');
            }
        }
        return $query;
    }

    private function applyLineFilters($query, array $f): void
    {
        $query->when($f['from_date'], fn ($q, $v) => $q->whereDate('date', '>=', $v))
            ->when($f['to_date'], fn ($q, $v) => $q->whereDate('date', '<=', $v))
            ->when($f['type'] !== '', fn ($q) => $q->where('type', $f['type']))
            ->when($f['account_id'] !== null, fn ($q) => $q->where('payment_account_id', $f['account_id']))
            ->when($f['account_mode'] === 'bank', fn ($q) => $q->where('payment_mode', 'bank'))
            ->when($f['account_mode'] === 'cash', fn ($q) => $q->where('payment_mode', '!=', 'bank'))
            ->when($f['amount_from'] !== null, fn ($q) => $q->where('amount', '>=', $f['amount_from']))
            ->when($f['amount_to'] !== null, fn ($q) => $q->where('amount', '<=', $f['amount_to']));
    }

    /**
     * Table and name expression of each master's parties, as PaymentAdjustment::entity_name shows them:
     * name, else shipment_id, else sku, else '#id'. Bank accounts show their bank name (the admin shows '#id' there).
     * null = the master's model does not exist (the admin then shows the ref id).
     */
    private function entityTables($masters): array
    {
        static $cache = [];
        $tables = [];
        foreach ($masters as $m) {
            $model = $m->model_name;
            $tables[$m->id] = ($model && class_exists($model)) ? (new $model)->getTable() : null;
        }
        $wanted = array_values(array_unique(array_filter($tables)));
        $missing = array_diff($wanted, array_keys($cache));
        if ($missing) {
            $cols = DB::table('information_schema.COLUMNS')
                ->where('TABLE_SCHEMA', DB::getDatabaseName())
                ->whereIn('TABLE_NAME', $missing)
                ->whereIn('COLUMN_NAME', ['name', 'bank_name', 'shipment_id', 'sku'])
                ->get(['TABLE_NAME as t', 'COLUMN_NAME as c'])
                ->groupBy('t');
            foreach ($missing as $t) {
                $have = isset($cols[$t]) ? $cols[$t]->pluck('c')->all() : [];
                $parts = array_values(array_intersect(['bank_name', 'name', 'shipment_id', 'sku'], $have));
                $parts[] = "CONCAT('#', id)";
                $cache[$t] = 'COALESCE(' . implode(', ', $parts) . ')';
            }
        }
        $out = [];
        foreach ($tables as $masterId => $t) {
            $out[$masterId] = $t ? ['table' => $t, 'label' => $cache[$t]] : null;
        }
        return $out;
    }

    /** [line id => ['name' => party name, 'found' => bool]] with one query per master. */
    private function entityNames($lines, $masters): array
    {
        $tables = $this->entityTables($masters);
        $names = [];
        foreach ($lines->groupBy('adjustment_master_id') as $masterId => $group) {
            $et = $tables[$masterId] ?? null;
            $plain = $group->filter(fn ($l) => ctype_digit((string) $l->ref_id));
            $labels = ($et && $plain->isNotEmpty())
                ? DB::table($et['table'])->whereIn('id', $plain->pluck('ref_id')->map(fn ($v) => (int) $v)->unique()->all())
                    ->selectRaw('id, ' . $et['label'] . ' as label')->pluck('label', 'id')
                : collect();
            $isReceiptMaster = ($masters[$masterId]->model_name ?? '') === 'App\Models\FabricReceipt';
            foreach ($group as $l) {
                if (ctype_digit((string) $l->ref_id) && !$isReceiptMaster) {
                    $found = isset($labels[(int) $l->ref_id]);
                    $names[$l->id] = ['name' => $found ? (string) $labels[(int) $l->ref_id] : (string) $l->ref_id, 'found' => $found];
                } else {
                    // order / dispatch references (OD:, AOD:, OR:) and shipments: the model works out the party (rare)
                    $adj = PaymentAdjustment::with('master')->find($l->id);
                    $names[$l->id] = ['name' => (string) ($adj ? $adj->entity_name : $l->ref_id), 'found' => false];
                }
            }
        }
        return $names;
    }

    private function accountNames(): array
    {
        return [
            'bank' => BankAccount::pluck('bank_name', 'id')->all(),
            'cash' => CashPayment::pluck('name', 'id')->all(),
        ];
    }

    /** Same rule as PaymentAdjustment::account(): mode 'bank' = bank account, anything else = cash account. */
    private function accountName(array $accountNames, $mode, $id): string
    {
        $list = $mode === 'bank' ? $accountNames['bank'] : $accountNames['cash'];
        return $list[$id] ?? 'N/A';
    }

    /** Owner ledger statement of a party (bank / cash accounts open the bank & cash ledger). */
    private function ledgerUrl(string $masterName, $refId): ?string
    {
        $type = strtolower(trim($masterName));
        if (in_array($type, ['bank account', 'cash master'], true)) {
            return route('owner.bank-cash-ledger.show', ['type' => $type, 'id' => $refId]);
        }
        return route('owner.party-ledger.show', ['type' => $type, 'id' => $refId]);
    }
}
