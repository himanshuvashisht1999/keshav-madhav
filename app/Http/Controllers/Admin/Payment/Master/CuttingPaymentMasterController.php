<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Requests\Admin\Master\CuttingPaymentMasterStoreRequest;
use App\Requests\Admin\Master\CuttingPaymentMasterUpdateRequest;
use App\Services\Admin\Payment\Master\CuttingPaymentMasterService;
use Illuminate\Http\Request;

class CuttingPaymentMasterController extends Controller
{
    protected $service;

    public function __construct(CuttingPaymentMasterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.cutting_payment.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.cutting_payment.create');
    }

    public function store(CuttingPaymentMasterStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.cutting_payment.index')->with('success', 'Cutting Payment Master added successfully.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.cutting_payment.edit', $response);
    }

    public function update(CuttingPaymentMasterUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.cutting_payment.index')->with('success', 'Cutting Payment Master updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.cutting_payment.index')->with('success', 'Cutting Payment Master deleted successfully.');
    }
}
