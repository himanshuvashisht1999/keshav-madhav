<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\CompanyCapital;
use App\Models\BankAccount;
use App\Models\CashPayment;
use App\Http\DataTable\Admin\Payment\Master\CompanyCapitalDataTable as DataTable;
use DB;

class CompanyCapitalService
{
    protected $datatable;
    protected $balanceService;

    public function __construct(DataTable $datatable, \App\Services\Admin\BalanceService $balanceService)
    {
        $this->datatable = $datatable;
        $this->balanceService = $balanceService;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function getSummary()
    {
        $bankCapital = BankAccount::sum('balance');
        $cashCapital = CashPayment::sum('balance');
        $totalCapital = $bankCapital + $cashCapital;

        return [
            'total' => $totalCapital,
            'bank' => $bankCapital,
            'cash' => $cashCapital,
        ];
    }

    public function getPaymentMethods()
    {
        return [
            'banks' => BankAccount::where('status', 1)->get(),
            'cash' => CashPayment::where('status', 1)->get(),
        ];
    }

    public function store(Request $request)
    {
        $capital = new CompanyCapital();
        $capital->amount = $request->amount;
        $capital->payment_method_type = $request->payment_method_type;
        $capital->payment_method_id = $request->payment_method_id;
        $capital->transaction_date = $request->transaction_date;
        $capital->remarks = $request->remarks;
        $capital->save();

        // Update Balance
        $this->balanceService->updateBalance($capital->payment_method_type, $capital->payment_method_id, $capital->amount, 'add');

        return true;
    }
}
