<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Requests\Admin\Master\WashingMasterStoreRequest;
use App\Requests\Admin\Master\WashingMasterUpdateRequest;
use App\Services\Admin\Payment\Master\WashingMasterService;
use Illuminate\Http\Request;

class WashingMasterController extends Controller
{
    protected $service;

    public function __construct(WashingMasterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.washing_master.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.washing_master.create');
    }

    public function store(WashingMasterStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.washing_master.index')->with('success', 'Washing Master added successfully.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.washing_master.edit', $response);
    }

    public function update(WashingMasterUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.washing_master.index')->with('success', 'Washing Master updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.washing_master.index')->with('success', 'Washing Master deleted successfully.');
    }
}
