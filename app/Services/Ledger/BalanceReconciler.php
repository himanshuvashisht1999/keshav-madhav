<?php

namespace App\Services\Ledger;

use App\Models\AdjustmentMaster;
use App\Models\MasterOpeningBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Compares every party's stored running balance with its ledger (the records), explains each
 * difference and repairs it on request.
 *
 * The stored balance is kept as an independent running total on purpose: if the two ever
 * disagree, something is wrong and it must be visible, not silently overwritten.
 *
 * Groups:
 *   A  capital account whose opening balance was never saved (the ledger starts from 0).
 *      Fix: save opening = stored - transactions. Proven exact.
 *   B  the old edit form overwrote the running balance with the opening amount.
 *      Fix: stored balance = ledger. Proven by an exact match of the history.
 *   C  no record explains the difference. Needs a person to decide; default fix: stored = ledger.
 *   D  journal vouchers were posted with the opposite sign (old bug) for committee / bank / hulayati /
 *      machinery / loan / factory head / discount / salary accounts. Fix: stored = ledger. Proven exact.
 */
class BalanceReconciler
{
    public const REPORT_FILE = 'balance_check.json';

    /** Every account type that keeps a running balance. */
    public function accountTypes(): array
    {
        $types = [];
        foreach (AdjustmentMaster::orderByDesc('status')->orderBy('id')->get() as $master) {
            $model = $master->model_name;
            if (!$model || !class_exists($model) || isset($types[$model])) {
                continue;
            }
            $table = (new $model)->getTable();
            if (!Schema::hasColumn($table, 'balance')) {
                continue;
            }
            $types[$model] = [
                'label' => $master->name,
                'ledger_type' => strtolower($master->name),
                'model' => $model,
                'table' => $table,
                'has_status' => Schema::hasColumn($table, 'status'),
            ];
        }
        return array_values($types);
    }

    /**
     * Compare every account. Returns ['checked' => n, 'mismatches' => [...]].
     */
    public function scan(?callable $progress = null): array
    {
        $checked = 0;
        $mismatches = [];
        $partyLedger = app(PartyLedgerService::class);

        foreach ($this->accountTypes() as $t) {
            $query = $t['model']::query();
            if ($t['has_status']) {
                $query->where('status', '!=', 3);
            }

            foreach ($query->orderBy('id')->get() as $rec) {
                $checked++;
                $data = $partyLedger->build(Request::create('/', 'GET'), $t['ledger_type'], $rec->id);
                $ledger = round((float) $data['party']->balance, 2);
                $stored = round((float) DB::table($t['table'])->where('id', $rec->id)->value('balance'), 2);

                if (abs($ledger - $stored) >= 0.01) {
                    $mismatches[] = $this->explain($t, $rec, $stored, $ledger, $data);
                }
                if ($progress) {
                    $progress($checked);
                }
            }
        }

        return ['checked' => $checked, 'mismatches' => $mismatches];
    }

    /** Run the scan and save the report used by the dashboard badge and the review page. */
    public function checkAndStore(?callable $progress = null): array
    {
        $result = $this->scan($progress);
        $report = [
            'checked_at' => now()->toDateTimeString(),
            'checked' => $result['checked'],
            'mismatch_count' => count($result['mismatches']),
            'mismatches' => $result['mismatches'],
        ];
        file_put_contents(storage_path('app/' . self::REPORT_FILE), json_encode($report, JSON_PRETTY_PRINT));
        return $report;
    }

    public function lastReport(): ?array
    {
        $file = storage_path('app/' . self::REPORT_FILE);
        if (!is_file($file)) {
            return null;
        }
        return json_decode(file_get_contents($file), true) ?: null;
    }

    /**
     * Apply the proposed fix for the given mismatches. Every change is written to
     * balance_corrections (old value, new value, reason) so it can always be traced.
     */
    public function apply(array $mismatches, string $appliedBy): array
    {
        $done = [];
        DB::transaction(function () use ($mismatches, $appliedBy, &$done) {
            foreach ($mismatches as $m) {
                $model = $m['model'];
                $party = $model::find($m['id']);
                if (!$party) {
                    continue;
                }

                if ($m['action'] === 'set_opening') {
                    $old = MasterOpeningBalance::signedOpening($m['opening_type'], $m['id']);
                    $new = round($m['proposed_opening'], 2);
                    MasterOpeningBalance::updateOrCreate(
                        ['master_type' => $m['opening_type'], 'master_id' => $m['id'], 'financial_year' => MasterOpeningBalance::OPENING_KEY],
                        ['amount' => abs($new), 'balance_type' => $new < 0 ? 'Debit' : 'Credit']
                    );
                    $this->log($m, 'opening', $old, $new, $appliedBy);
                } else {
                    $old = (float) DB::table($m['table'])->where('id', $m['id'])->value('balance');
                    DB::table($m['table'])->where('id', $m['id'])->update(['balance' => $m['ledger']]);
                    $this->log($m, 'balance', $old, $m['ledger'], $appliedBy);
                }
                $done[] = $m;
            }
        });
        return $done;
    }

    private function log(array $m, string $field, float $old, float $new, string $appliedBy): void
    {
        DB::table('balance_corrections')->insert([
            'ledger_type' => $m['ledger_type'],
            'party_id' => $m['id'],
            'party_name' => $m['name'],
            'field' => $field,
            'old_value' => $old,
            'new_value' => $new,
            'group' => $m['group'],
            'reason' => $m['reason'],
            'applied_by' => $appliedBy,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function explain(array $t, $rec, float $stored, float $ledger, array $data): array
    {
        $opening = (float) $data['openingBalAmount'];
        $transactions = collect($data['transactions']);
        $row = [
            'type' => $t['label'],
            'ledger_type' => $t['ledger_type'],
            'model' => $t['model'],
            'table' => $t['table'],
            'id' => $rec->id,
            'name' => $this->partyName($rec),
            'stored' => $stored,
            'ledger' => $ledger,
            'difference' => round($stored - $ledger, 2),
        ];

        // A. capital whose opening balance was never saved (no row, or a 0 row created by a later form save)
        $openingType = $this->openingType($t['ledger_type']);
        if ($t['ledger_type'] === 'capital' && abs(MasterOpeningBalance::signedOpening($openingType, $rec->id)) < 0.01) {
            return $row + [
                'group' => 'A',
                'reason' => 'Capital opening balance was never saved, so the ledger started from 0.',
                'action' => 'set_opening',
                'opening_type' => $openingType,
                'proposed_opening' => round($stored - ($ledger - $opening), 2),
            ];
        }

        // B. the old edit form replaced the running balance with the opening amount
        if ($this->isEditReset($opening, $transactions, $stored, (string) $rec->updated_at)) {
            return $row + [
                'group' => 'B',
                'reason' => 'The edit form replaced the running balance with the opening amount; the ledger has every record.',
                'action' => 'set_balance',
            ];
        }

        // D. old journal vouchers were posted with the opposite sign for these account types
        $jvSignTypes = ['committee', 'bank account', 'hulayati', 'machinery account', 'loan account', 'factory head', 'discount account', 'salary account'];
        if (in_array($t['ledger_type'], $jvSignTypes, true)) {
            $jvNet = $transactions->where('type', 'Journal Voucher')->sum(fn($tx) => (float) $tx->credit - (float) $tx->debit);
            if (abs($jvNet) >= 0.01 && abs(($ledger - 2 * $jvNet) - $stored) < 0.01) {
                return $row + [
                    'group' => 'D',
                    'reason' => 'Journal vouchers for this account were posted with the opposite sign (old bug); the ledger shows them correctly.',
                    'action' => 'set_balance',
                ];
            }
        }

        // C. nothing in the records explains it
        return $row + [
            'group' => 'C',
            'reason' => 'No record explains the difference. Check this account before accepting the ledger amount.',
            'action' => 'set_balance',
        ];
    }

    /**
     * True when stored = opening + only the transactions entered after some moment
     * (i.e. a save of the edit form wiped everything entered before it).
     */
    private function isEditReset(float $opening, $transactions, float $stored, string $partyUpdatedAt): bool
    {
        $orders = [
            fn($tx) => (string) ($tx->created_at ?? $tx->date),
            fn($tx) => substr((string) $tx->date, 0, 10) . (string) $tx->created_at,
        ];
        foreach ($orders as $key) {
            $nets = $transactions->sortBy($key)->values()->map(fn($tx) => (float) $tx->credit - (float) $tx->debit)->all();
            $suffix = array_sum($nets);
            foreach ($nets as $net) {
                $suffix -= $net;
                if (abs($opening + $suffix - $stored) < 0.01) {
                    return true;
                }
            }
        }

        // One transaction missing from the stored balance, entered before the party was last saved
        $diff = $stored - ($opening + $transactions->sum(fn($tx) => (float) $tx->credit - (float) $tx->debit));
        foreach ($transactions as $tx) {
            $missing = ($tx->debit > 0 && abs($diff - $tx->debit) < 0.01) || ($tx->credit > 0 && abs($diff + $tx->credit) < 0.01);
            if ($missing && (string) $tx->created_at < $partyUpdatedAt) {
                return true;
            }
        }
        return false;
    }

    /** master_type used in payment_opening_balances for a ledger type */
    public function openingType(string $ledgerType): string
    {
        $map = [
            'machinery account' => 'machinery',
            'loan account' => 'loan',
            'salary account' => 'salary',
            'discount account' => 'discount',
        ];
        return $map[$ledgerType] ?? str_replace(' ', '_', $ledgerType);
    }

    private function partyName($rec): string
    {
        foreach (['name', 'bank_name', 'account_name', 'title'] as $field) {
            if (!empty($rec->{$field})) {
                return (string) $rec->{$field};
            }
        }
        return '#' . $rec->id;
    }
}
