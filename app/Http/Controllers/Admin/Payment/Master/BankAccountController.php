<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\BankAccountService as Service;
use App\Requests\Admin\Master\BankAccountStoreRequest;
use App\Requests\Admin\Master\BankAccountUpdateRequest;
use Auth;
use App\Models\BankAccount;
use App\Models\MasterOpeningBalance;

class BankAccountController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('bank_account');
        $response['total_current_balance'] = BankAccount::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.bank_account.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.bank_account.create');
    }

    public function store(BankAccountStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.bank_account.index')->withSuccess('The bank account has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.bank_account.edit', $response);
    }

    public function update(BankAccountUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.bank_account.index')->withSuccess('The bank account has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.bank_account.index')->withSuccess('The bank account has been successfully deleted/deactivated.');
    }
}
