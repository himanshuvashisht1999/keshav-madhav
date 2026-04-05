<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Requests\Admin\Master\AgentPaymentMasterStoreRequest;
use App\Requests\Admin\Master\AgentPaymentMasterUpdateRequest;
use App\Services\Admin\Payment\Master\AgentPaymentMasterService;
use Illuminate\Http\Request;

class AgentPaymentMasterController extends Controller
{
    protected $service;

    public function __construct(AgentPaymentMasterService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.agent_payment.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.agent_payment.create');
    }

    public function store(AgentPaymentMasterStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.agent_payment.index')->with('success', 'Agent Payment Master added successfully.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.agent_payment.edit', $response);
    }

    public function update(AgentPaymentMasterUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.agent_payment.index')->with('success', 'Agent Payment Master updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.agent_payment.index')->with('success', 'Agent Payment Master deleted successfully.');
    }
}
