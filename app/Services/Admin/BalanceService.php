<?php

namespace App\Services\Admin;

use App\Models\BankAccount;
use App\Models\CashPayment;
use Exception;
use Illuminate\Support\Facades\DB;

class BalanceService
{
    /**
     * Update the balance of a bank or cash account.
     * 
     * @param string $type 'Bank' or 'Cash'
     * @param int $id The ID of the account
     * @param float $amount The amount to add or deduct
     * @param string $action 'add' or 'deduct'
     * @throws Exception
     */
    public function updateBalance(string $type, int $id, float $amount, string $action)
    {
        if ($amount <= 0) {
            return;
        }

        $model = ($type === 'Bank') ? BankAccount::find($id) : CashPayment::find($id);

        if (!$model) {
            throw new Exception("Payment method ({$type} ID: {$id}) not found.");
        }

        if ($action === 'add') {
            $model->balance += $amount;
        } elseif ($action === 'deduct') {
            $model->balance -= $amount;
        } else {
            throw new Exception("Invalid balance action: {$action}");
        }

        $model->save();
    }

    /**
     * Re-calculate all balances based on CompanyCapital and Payments.
     * Use this for initialization.
     */
    public function syncBalances()
    {
        DB::beginTransaction();
        try {
            // Reset all balances to 0
            BankAccount::query()->update(['balance' => 0]);
            CashPayment::query()->update(['balance' => 0]);

            // Add from Company Capital
            $capitals = DB::table('company_capitals')->get();
            foreach ($capitals as $capital) {
                $this->updateBalance($capital->payment_method_type, $capital->payment_method_id, $capital->amount, 'add');
            }

            // Update from Payments
            $payments = DB::table('payments')
                ->whereNotNull('payment_method_type')
                ->whereNotNull('payment_method_id')
                ->get();

            foreach ($payments as $payment) {
                $action = in_array($payment->payment_type, ['received', 'credit']) ? 'add' : 'deduct';
                $this->updateBalance($payment->payment_method_type, $payment->payment_method_id, $payment->amount, $action);
            }

            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
