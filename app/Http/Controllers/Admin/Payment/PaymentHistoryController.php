<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\PaymentAdjustment;
use Illuminate\Http\Request;

// One payment's view and edit pages, opened from the Party Ledger ("View Details").
// The old Payment History list and its delete were removed.
class PaymentHistoryController extends Controller
{
    public function show(Payment $payment)
    {
        $payment->load([
            'party',
            'paymentable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\AgentOrder::class => ['shop', 'agent'],
                ]);
            }
        ]);

        // Payment Adjust rows are changed on their batch page, journal voucher lines on their voucher, not here
        $batchId = $this->batchIdOf($payment);
        $isBatchRow = $this->isBatchRow($payment, $batchId);
        $isJournalRow = $this->isJournalRow($payment);
        $journalVoucher = $isJournalRow ? $payment->paymentable : null;
        $backUrl = $this->backUrl($payment);

        return view('admin.payment.show', compact('payment', 'batchId', 'isBatchRow', 'isJournalRow', 'journalVoucher', 'backUrl'));
    }

    public function edit(Payment $payment)
    {
        if ($refused = $this->refuseBatchRow($payment)) {
            return $refused;
        }

        return view('admin.payment.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        // checked first, so nothing is saved for a Payment Adjust row or a journal voucher line
        if ($refused = $this->refuseBatchRow($payment)) {
            return $refused;
        }

        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'payee_name' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $payment->update($request->only([
            'payment_date',
            'amount',
            'payment_mode',
            'payee_name',
            'reference_id',
            'remarks'
        ]));

        return redirect()->route('admin.payment.history.show', $payment->id)->with('success', 'Payment updated successfully.');
    }

    // The Payment Adjust batch this payment belongs to (its reference is the batch id), or null
    private function batchIdOf(Payment $payment): ?string
    {
        $ref = (string) $payment->reference_id;
        if ($ref === '') {
            return null;
        }

        return PaymentAdjustment::where('batch_id', $ref)->exists() ? $ref : null;
    }

    // Made by Payment Adjust: mode 'Adjustment', or the reference is a batch id
    private function isBatchRow(Payment $payment, ?string $batchId): bool
    {
        return $batchId !== null || strcasecmp((string) $payment->payment_mode, 'Adjustment') === 0;
    }

    // A line of a journal voucher (the voucher saves one payment row per line, by its voucher no)
    private function isJournalRow(Payment $payment): bool
    {
        return $payment->paymentable_type === \App\Models\JournalVoucher::class;
    }

    // This form changes only the payment row (and has no 'Adjustment' mode): not the batch,
    // the party's balance or the bank side. So a Payment Adjust row goes to its batch instead.
    // A journal voucher line goes to its voucher: the ledgers read the voucher's own lines, not this row,
    // and the voucher page rewrites and deletes these rows, so a change here would show nowhere.
    private function refuseBatchRow(Payment $payment)
    {
        if ($this->isJournalRow($payment)) {
            if (\App\Models\JournalVoucher::whereKey($payment->paymentable_id)->exists()) {
                return redirect()->route('admin.payment.journal-voucher.edit', $payment->paymentable_id)
                    ->with('error', 'This payment is a line of Journal Voucher ' . $payment->reference_id . ', please change it there so the ledgers show the change.');
            }
            return redirect()->route('admin.payment.history.show', $payment->id)
                ->with('error', 'This payment was made by a Journal Voucher that no longer exists, so it cannot be changed here.');
        }

        $batchId = $this->batchIdOf($payment);
        if ($batchId !== null) {
            return redirect()->route('admin.payment.adjustment.edit', $batchId)
                ->with('error', 'This payment is part of Payment Adjust batch ' . $batchId . ', please change it there so the bank and balances stay correct.');
        }

        if ($this->isBatchRow($payment, null)) {
            // made by Payment Adjust but its batch is gone: still not changed here
            return redirect()->route('admin.payment.history.show', $payment->id)
                ->with('error', 'This payment was made by Payment Adjust and its batch no longer exists, so it cannot be changed here.');
        }

        return null;
    }

    // 'Back' goes to the page the user came from when it is a page of this site (a statement with its dates,
    // an agent statement, the detailed report), but not to this payment's own view or edit page (after a save
    // that is the edit form). Otherwise to the party's ledger, or the party ledger list.
    private function backUrl(Payment $payment): string
    {
        $fallback = $this->partyLedgerUrl($payment) ?? route('admin.ledger.party.index');
        $previous = url()->previous($fallback);

        // a link from another site (the browser's Referer) is never the Back link
        $sameSite = in_array(strtolower((string) parse_url($previous, PHP_URL_SCHEME)), ['http', 'https'], true)
            && strcasecmp((string) parse_url($previous, PHP_URL_HOST), request()->getHost()) === 0;
        $ownPaths = [
            rtrim((string) parse_url(route('admin.payment.history.show', $payment->id), PHP_URL_PATH), '/'),
            rtrim((string) parse_url(route('admin.payment.history.edit', $payment->id), PHP_URL_PATH), '/'),
        ];
        $ownPage = in_array(rtrim((string) parse_url($previous, PHP_URL_PATH), '/'), $ownPaths, true);

        return $sameSite && !$ownPage ? $previous : $fallback;
    }

    // The party's statement on the Party Ledger (the type in its link is the master name), or null
    // (no party, or a bank / cash account, which has its own ledger)
    private function partyLedgerUrl(Payment $payment): ?string
    {
        if (!$payment->party) {
            return null;
        }
        $master = \App\Models\AdjustmentMaster::where('model_name', $payment->party_type)->where('status', 1)
            ->whereNotIn('name', ['Bank Account', 'Cash Master'])->orderBy('id')->first();

        return $master ? route('admin.ledger.party.show', [strtolower($master->name), $payment->party_id]) : null;
    }
}
