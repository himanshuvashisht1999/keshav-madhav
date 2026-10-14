<?php

namespace App\Services\Ledger;

use App\Models\AdjustmentMaster;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherItem;
use App\Models\MasterOpeningBalance;
use App\Models\Payment;
use App\Models\PaymentAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * The ONE bank / cash account ledger calculator (Admin + Owner bank-cash ledger, the party
 * ledger of a bank, and the balance check all use it).
 *
 * Money reaches a bank / cash account in two ways and BOTH are counted:
 *   1. as the paying account of a payment or adjustment batch (payment_method / payment_account_id)
 *   2. as the party of an adjustment or payment (e.g. bank-to-bank transfers entered with this bank as party)
 * Journal voucher lines are counted once.
 */
class BankLedgerService
{
    public function build(Request $request, $type, $id, bool $includePartySide = true): array
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        $master = AdjustmentMaster::where('name', 'LIKE', $type)->first();
        if (!$master || !in_array(strtolower($master->name), ['bank account', 'cash master'])) {
            abort(404, "Invalid Ledger Type");
        }

        $modelName = $master->model_name;
        $party = $modelName::findOrFail($id);
        $mode = strtolower($master->name) === 'bank account' ? 'bank' : 'cash';

        $all = $this->movements($master, $modelName, $id, $mode, $includePartySide);

        // Opening balance (one row per account, not year based)
        $lookupType = str_replace(' ', '_', strtolower($type));
        $openingBalance = MasterOpeningBalance::whereIn('master_type', [$type, $lookupType, str_replace('_', ' ', $lookupType)])
            ->where('master_id', $id)
            ->where('financial_year', MasterOpeningBalance::openingKey())
            ->first();

        $openingBalAmount = 0;
        if ($openingBalance) {
            $openingBalAmount = (float) $openingBalance->amount;
            if (strtolower(trim($openingBalance->balance_type)) === 'debit') {
                $openingBalAmount = -$openingBalAmount;
            }
        }

        // Everything before the period start is part of the period's opening balance
        if ($startDate) {
            foreach ($all as $tx) {
                if ($this->day($tx->date) < $startDate) {
                    $openingBalAmount += ($tx->credit - $tx->debit);
                }
            }
        }

        $transactions = $all->filter(function ($tx) use ($startDate, $endDate) {
            $d = $this->day($tx->date);
            return (!$startDate || $d >= $startDate) && (!$endDate || $d <= $endDate);
        });

        $transactions = $transactions->sort(function ($a, $b) {
            $dateA = $this->day($a->date);
            $dateB = $this->day($b->date);
            if ($dateA != $dateB) {
                return $dateA <=> $dateB;
            }
            return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
        })->values();

        $balance = $openingBalAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->credit - $tx->debit);
            $tx->running_balance = $balance;
        }

        $party->balance = $balance;
        $periodTotalDebit = (float) $transactions->sum('debit');
        $periodTotalCredit = (float) $transactions->sum('credit');
        $closingBalance = $balance;
        $viewMode = 'mix';

        return compact(
            'party', 'transactions', 'type', 'startDate', 'endDate',
            'openingBalAmount', 'viewMode', 'periodTotalDebit', 'periodTotalCredit', 'closingBalance'
        );
    }

    /** Closing balance of the account over all time (opening + every movement). */
    public function closingBalance($type, $id): float
    {
        return (float) $this->build(Request::create('/', 'GET'), $type, $id)['party']->balance;
    }

    private function movements(AdjustmentMaster $master, string $modelName, $id, string $mode, bool $includePartySide): Collection
    {
        $transactions = collect();

        // 1. Payments made through this account
        $payments = Payment::where('payment_method_id', $id)
            ->where('payment_method_type', $modelName)
            ->where(function ($q) {
                $q->where('paymentable_type', '!=', JournalVoucher::class)->orWhereNull('paymentable_type');
            })
            ->get();

        foreach ($payments as $p) {
            $isCredit = in_array($p->payment_type, ['received', 'credit']);
            $isDebit = in_array($p->payment_type, ['paid', 'debit']);
            if ($isCredit) {
                $debit = 0;
                $credit = (float) $p->amount;
                $desc = 'Payment Received (' . $p->payment_mode . ')';
            } elseif ($isDebit) {
                $debit = (float) $p->amount;
                $credit = 0;
                $desc = 'Payment Paid (' . $p->payment_mode . ')';
            } else {
                $debit = (float) $p->amount;
                $credit = 0;
                $desc = 'Adjustment (' . $p->payment_mode . ')';
            }

            $transactions->push((object) [
                'date' => $p->payment_date,
                'created_at' => $p->created_at,
                'type' => 'Payment',
                'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                'debit' => $debit,
                'credit' => $credit,
                'description' => $desc . ($p->remarks ? ': ' . $p->remarks : ''),
                'view_url' => route('admin.payment.history.show', $p->id),
            ]);
        }

        // 2. Adjustment batches paid / received through this account
        //    (party type 'debit' = money into this account, 'credit' = money out of it)
        $adjustments = PaymentAdjustment::where('payment_mode', $mode)->where('payment_account_id', $id)->get();
        $grouped = $adjustments->groupBy(function ($item) {
            return $item->batch_id ?: 'single_' . $item->id;
        });

        foreach ($grouped as $key => $group) {
            if (str_starts_with($key, 'single_')) {
                $adj = $group->first();
                $isCredit = $adj->type === 'debit';
                $transactions->push((object) [
                    'date' => $adj->date,
                    'created_at' => $adj->created_at,
                    'type' => 'Adjustment',
                    'ref' => 'Adj #' . $adj->id,
                    'debit' => $isCredit ? 0 : (float) $adj->amount,
                    'credit' => $isCredit ? (float) $adj->amount : 0,
                    'description' => '[Dist] ' . ($adj->remarks ?: $adj->entity_name),
                    'view_url' => '#',
                ]);
            } else {
                $first = $group->first();
                $totalDebit = 0;
                $totalCredit = 0;
                foreach ($group as $adj) {
                    if ($adj->type === 'debit') {
                        $totalCredit += (float) $adj->amount;
                    } else {
                        $totalDebit += (float) $adj->amount;
                    }
                }
                $transactions->push((object) [
                    'date' => $first->date,
                    'created_at' => $first->created_at,
                    'type' => 'Adjustment',
                    'ref' => $key,
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                    'description' => 'Grouped Adjustments (' . $group->count() . ' entries)',
                    'view_url' => route('admin.payment.adjustment.show', $key),
                ]);
            }
        }

        // 3. Journal voucher lines for this account (counted once)
        $masterIds = AdjustmentMaster::where('model_name', $modelName)->pluck('id');
        $vouchers = JournalVoucherItem::with('voucher')
            ->whereIn('master_type', $masterIds)
            ->where('master_id', $id)
            ->whereHas('voucher')
            ->get();

        foreach ($vouchers->groupBy('journal_voucher_id') as $group) {
            $first = $group->first();
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($group as $v) {
                if (strtolower($v->type) === 'credit') {
                    $totalCredit += (float) $v->amount;
                } else {
                    $totalDebit += (float) $v->amount;
                }
            }
            $transactions->push((object) [
                'date' => $first->voucher->date,
                'created_at' => $first->created_at,
                'type' => 'Journal Voucher',
                'ref' => $first->voucher->voucher_no,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'description' => $first->narration ?: $first->voucher->narration ?: 'Journal Entry' . ($group->count() > 1 ? ' (' . $group->count() . ' items)' : ''),
                'view_url' => route('admin.payment.journal-voucher.show', $first->voucher->id),
            ]);
        }

        if (!$includePartySide) {
            return $transactions;
        }

        // 4. Adjustments where this account is the PARTY (e.g. transfer into / out of this bank)
        $partyAdjustments = PaymentAdjustment::whereIn('adjustment_master_id', $masterIds)
            ->where('ref_id', (string) $id)
            ->get();

        foreach ($partyAdjustments as $adj) {
            $isCredit = $adj->type === 'credit';
            $transactions->push((object) [
                'date' => $adj->date,
                'created_at' => $adj->created_at,
                'type' => 'Adjustment',
                'ref' => $adj->batch_id ?? ('Adj #' . $adj->id),
                'debit' => $isCredit ? 0 : (float) $adj->amount,
                'credit' => $isCredit ? (float) $adj->amount : 0,
                'description' => '[As party] ' . ($adj->remarks ?: ($isCredit ? 'Credit Adjustment' : 'Debit Adjustment')),
                'view_url' => $adj->batch_id ? route('admin.payment.adjustment.show', $adj->batch_id) : '#',
            ]);
        }

        // 5. Payments where this account is the PARTY
        $partyPayments = Payment::where('party_id', $id)
            ->where('party_type', $modelName)
            ->where(function ($q) {
                $q->where('paymentable_type', '!=', JournalVoucher::class)->orWhereNull('paymentable_type');
            })
            ->get();

        foreach ($partyPayments as $p) {
            $isCredit = in_array($p->payment_type, ['received', 'credit']);
            $transactions->push((object) [
                'date' => $p->payment_date,
                'created_at' => $p->created_at,
                'type' => $isCredit ? 'Receipt' : 'Payment',
                'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                'debit' => $isCredit ? 0 : (float) $p->amount,
                'credit' => $isCredit ? (float) $p->amount : 0,
                'description' => '[As party] ' . ($isCredit ? 'Payment Received' : 'Payment Paid') . ' (' . $p->payment_mode . ')' . ($p->remarks ? ': ' . $p->remarks : ''),
                'view_url' => route('admin.payment.history.show', $p->id),
            ]);
        }

        return $transactions;
    }

    private function day($date): string
    {
        return \Carbon\Carbon::parse($date)->format('Y-m-d');
    }
}
