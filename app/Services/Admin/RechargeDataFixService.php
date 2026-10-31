<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

/**
 * One-time correction asked by the client (29 Oct 2026): adjustment lines booked to
 * Telephone Expense > RECHARGE were really rickshaw expense, so they move to General Expense > RIKSHA KHARCH.
 *
 * Only the expense head of each line changes. Amount, date, type and the bank / cash account stay the same,
 * so the cash book does not move. Neither head keeps a stored balance (their figures come only from the
 * lines), so no balance can drift; the repair refuses to run if that is no longer true.
 *
 * run([], false) = preview, run($ids, true) = apply to the ticked lines (old values saved to storage/app/recharge-fix/).
 */
class RechargeDataFixService
{
    public function run(array $ids, bool $apply): array
    {
        [$from, $to] = $this->heads();

        $rows = DB::table('payment_adjustments')
            ->where('adjustment_master_id', $from['master_id'])->where('ref_id', (string) $from['party_id'])
            ->orderBy('date')->orderBy('id')->get();

        $result = ['from' => $from, 'to' => $to, 'rows' => $rows, 'moved' => 0, 'amount' => 0, 'log_file' => null];
        if (!$apply) {
            return $result;
        }

        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            throw new \RuntimeException('Tick at least one line.');
        }

        DB::beginTransaction();
        try {
            $pick = DB::table('payment_adjustments')->whereIn('id', $ids)->lockForUpdate()->get();
            $wrong = $pick->filter(fn($r) => (int) $r->adjustment_master_id !== $from['master_id'] || (string) $r->ref_id !== (string) $from['party_id']);
            if ($pick->count() !== count($ids) || $wrong->isNotEmpty()) {
                throw new \RuntimeException('Some ticked lines are no longer RECHARGE lines (changed meanwhile). Open the page again.');
            }

            $undo = [];
            foreach ($pick as $r) {
                $undo[] = ['id' => $r->id, 'batch_id' => $r->batch_id, 'adjustment_master_id' => $r->adjustment_master_id, 'ref_id' => $r->ref_id,
                    'amount' => $r->amount, 'date' => $r->date, 'updated_at' => $r->updated_at];
            }
            DB::table('payment_adjustments')->whereIn('id', $ids)->update([
                'adjustment_master_id' => $to['master_id'],
                'ref_id' => (string) $to['party_id'],
                'updated_at' => now(),
            ]);

            $file = 'recharge-fix/recharge-fix-' . now()->format('Ymd-His') . '.json';
            Storage::disk('local')->put($file, json_encode([
                'run_at' => now()->toDateTimeString(),
                'run_by' => auth()->id(),
                'moved_from' => $from,
                'moved_to' => $to,
                'old_values' => $undo,
            ], JSON_PRETTY_PRINT));
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }

        $result['moved'] = count($undo);
        $result['amount'] = array_sum(array_column($undo, 'amount'));
        $result['log_file'] = $file;
        return $result;
    }

    /** The two heads, found by name; refuses when anything is not as checked on 29 Oct 2026. */
    private function heads(): array
    {
        $find = function (string $masterName, string $partyName) {
            $masters = DB::table('adjustment_masters')->where('name', $masterName)->where('status', 1)->get();
            if ($masters->count() !== 1 || !class_exists($masters[0]->model_name)) {
                throw new \RuntimeException("The master type \"{$masterName}\" was not found exactly once.");
            }
            $model = $masters[0]->model_name;
            $table = (new $model)->getTable();
            foreach (['balance', 'amount'] as $col) {
                if (Schema::hasColumn($table, $col)) {
                    throw new \RuntimeException("\"{$masterName}\" now keeps a stored {$col}; moving lines would need it updated too, so nothing is done.");
                }
            }
            $parties = DB::table($table)->where('name', $partyName)->get();
            if ($parties->count() !== 1) {
                throw new \RuntimeException("\"{$partyName}\" was not found exactly once under {$masterName}.");
            }
            return ['master_id' => (int) $masters[0]->id, 'master' => $masterName, 'model' => $model, 'party_id' => (int) $parties[0]->id, 'party' => $partyName];
        };

        $from = $find('Telephone Expense', 'RECHARGE');
        $to = $find('General Expense', 'RIKSHA KHARCH');

        $payments = DB::table('payments')->where(fn($q) => $q->where('party_type', $from['model'])->orWhere('paymentable_type', $from['model']))
            ->where(fn($q) => $q->where('party_id', $from['party_id'])->orWhere('paymentable_id', $from['party_id']))->count();
        if ($payments > 0) {
            throw new \RuntimeException("RECHARGE also has {$payments} payment record(s); those are not handled here, so nothing is done.");
        }
        return [$from, $to];
    }
}
