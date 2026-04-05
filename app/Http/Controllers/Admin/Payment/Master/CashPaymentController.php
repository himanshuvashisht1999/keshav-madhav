<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\CashPaymentService as Service;
use App\Requests\Admin\Master\CashPaymentStoreRequest;
use App\Requests\Admin\Master\CashPaymentUpdateRequest;
use Auth;

class CashPaymentController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.cash_payment.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.cash_payment.create');
    }

    public function store(CashPaymentStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.cash_payment.index')->withSuccess('The cash payment record has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.cash_payment.edit', $response);
    }

    public function update(CashPaymentUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.cash_payment.index')->withSuccess('The cash payment record has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.cash_payment.index')->withSuccess('The cash payment record has been successfully deleted/deactivated.');
    }
}
