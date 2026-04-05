<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\PaymentTypeService as Service;
use App\Requests\Admin\Master\PaymentTypeStoreRequest;
use App\Requests\Admin\Master\PaymentTypeUpdateRequest;
use Auth;

class PaymentTypeController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.payment_type.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.payment_type.create');
    }

    public function store(PaymentTypeStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.payment_type.index')->withSuccess('The payment type has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.payment_type.edit', $response);
    }

    public function update(PaymentTypeUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.payment_type.index')->withSuccess('The payment type has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.payment_type.index')->withSuccess('The payment type has been successfully deleted/deactivated.');
    }
}
