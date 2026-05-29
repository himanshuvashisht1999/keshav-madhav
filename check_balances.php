<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\MasterCustomer;
use App\Models\MasterOpeningBalance;
use App\Models\AgentOrderDispatch;
use App\Models\OrderDispatch;
use App\Models\AgentOrderReturn;
use App\Models\Payment;
use App\Models\DomesticInventoryPurchase;
use App\Models\JournalVoucherItem;
use App\Models\AdjustmentMaster;

$customers = MasterCustomer::all();
$customerMaster = AdjustmentMaster::where('model_name', 'App\Models\MasterCustomer')->first();
$customerMasterId = $customerMaster ? $customerMaster->id : 18;

$mismatches = [];

foreach ($customers as $c) {
    $id = $c->id;
    
    // 1. Sales (Dispatches) - DEBIT
    $dispatches = AgentOrderDispatch::where('master_customer_id', $id)
        ->where('party_type', 'customer')
        ->where('status', 'dispatched')
        ->sum('grand_total');

    // 2. Standard Order Dispatches - DEBIT
    $orderDispatches = OrderDispatch::where('customer_id', $id)
        ->sum('total_amount');

    // 3. Sales Returns - CREDIT
    $salesReturns = AgentOrderReturn::whereHas('dispatch', function ($q) use ($id) {
        $q->where('master_customer_id', $id)->where('party_type', 'customer');
    })->sum('grand_total');

    // 4. Payments
    $payments = Payment::where('party_id', $id)
        ->where('party_type', 'App\Models\MasterCustomer')
        ->where(function($q) {
            $q->where('paymentable_type', '!=', 'App\Models\JournalVoucher')
              ->orWhereNull('paymentable_type');
        })->get();

    $paymentDebit = 0;
    $paymentCredit = 0;
    
    foreach ($payments as $p) {
        $isCredit = in_array($p->payment_type, ['received', 'credit']);
        $isDebit = in_array($p->payment_type, ['paid', 'debit']);

        if ($isCredit) {
            $paymentCredit += (float) $p->amount;
        } elseif ($isDebit) {
            $paymentDebit += (float) $p->amount;
        } else {
            $paymentDebit += (float) $p->amount;
        }
    }

    // 5. Inventory Purchases - CREDIT
    $inventoryPurchases = DomesticInventoryPurchase::where('customer_id', $id)
        ->sum('total_amount');

    // 6. Journal Vouchers
    $vouchers = JournalVoucherItem::where('master_type', $customerMasterId)
        ->where('master_id', $id)
        ->get();

    $voucherDebit = 0;
    $voucherCredit = 0;
    
    foreach ($vouchers as $v) {
        $isCredit = strtolower($v->type) === 'credit';
        if ($isCredit) {
            $voucherCredit += (float) $v->amount;
        } else {
            $voucherDebit += (float) $v->amount;
        }
    }

    // 7. Opening Balance
    $openingBalanceAmount = 0;
    $openingBalanceRecord = MasterOpeningBalance::where('master_type', 'customer')
        ->where('master_id', $id)
        ->where('financial_year', MasterOpeningBalance::getCurrentFinancialYear())
        ->first();

    if ($openingBalanceRecord) {
        $balanceType = strtolower(trim($openingBalanceRecord->balance_type));
        $obAmount = (float) $openingBalanceRecord->amount;
        if ($balanceType === 'debit') {
            $openingBalanceAmount -= $obAmount;
        } else {
            $openingBalanceAmount += $obAmount;
        }
    }

    $totalDebit = (float)$dispatches + (float)$orderDispatches + $paymentDebit + $voucherDebit;
    $totalCredit = (float)$salesReturns + $paymentCredit + (float)$inventoryPurchases + $voucherCredit;

    // Running Balance Logic from PartyLedgerController:
    // balance += (credit - debit)
    $computedBalance = $openingBalanceAmount + ($totalCredit - $totalDebit);
    
    // In database, is balance stored the same way? Let's check DB value
    $dbBalance = (float) $c->balance;
    
    // Allow small floating point differences
    if (abs($computedBalance - $dbBalance) > 0.01) {
        $mismatches[] = [
            'customer_id' => $id,
            'name' => $c->name,
            'db_balance' => $dbBalance,
            'computed_balance' => $computedBalance,
            'diff' => $computedBalance - $dbBalance,
            'opening_balance' => $openingBalanceAmount,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit
        ];
    }
}

echo json_encode(['mismatches' => $mismatches], JSON_PRETTY_PRINT);
