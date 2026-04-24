<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use App\Http\DataTable\Admin\Payment\Master\BankAccountDataTable as DataTable;

class BankAccountService
{
    protected $datatable;

    public function __construct(DataTable $datatable)
    {
        $this->datatable = $datatable;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {
        $bankAccount = new BankAccount();
        $bankAccount->bank_name = $request->bank_name;
        $bankAccount->account_name = $request->account_name;
        $bankAccount->account_number = $request->account_number;
        $bankAccount->ifsc_code = $request->ifsc_code;
        $bankAccount->branch_name = $request->branch_name;
        $balance = $request->balance ?? 0;
        if ($request->balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $bankAccount->balance = $balance;
        $bankAccount->status = 1;
        $bankAccount->save();
        return true;
    }

    public function edit(Request $request)
    {
        return BankAccount::find($request->id);
    }

    public function update(Request $request)
    {
        $bankAccount = BankAccount::find($request->id);
        $bankAccount->bank_name = $request->bank_name;
        $bankAccount->account_name = $request->account_name;
        $bankAccount->account_number = $request->account_number;
        $bankAccount->ifsc_code = $request->ifsc_code;
        $bankAccount->branch_name = $request->branch_name;
        $balance = $request->balance ?? 0;
        if ($request->balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $bankAccount->balance = $balance;
        $bankAccount->save();
        return true;
    }

    public function delete(Request $request)
    {
        return BankAccount::where('id', $request->id)->update(['status' => 0]);
    }
}
