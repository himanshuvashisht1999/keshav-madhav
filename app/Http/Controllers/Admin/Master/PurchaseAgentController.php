<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\PurchaseAgentService as Service;
use App\Requests\Admin\Master\PurchaseAgentStoreRequest;
use App\Requests\Admin\Master\PurchaseAgentUpdateRequest;

class PurchaseAgentController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.master.purchase_agent.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.master.purchase_agent.create');
    }

    public function store(PurchaseAgentStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.master.purchase-agent.index')->withSuccess('Purchase agent created successfully.');
    }

    public function edit(Request $request)
    {
        $data = $this->service->edit($request);
        return view('admin.master.purchase_agent.edit', compact('data'));
    }

    public function update(PurchaseAgentUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.master.purchase-agent.index')->withSuccess('Purchase agent updated successfully.');
    }

    public function delete(Request $request)
    {
        $response = $this->service->delete($request);
        if ($response['status']) {
            return redirect()->route('admin.master.purchase-agent.index')->withSuccess($response['message']);
        } else {
            return redirect()->route('admin.master.purchase-agent.index')->withError($response['message']);
        }
    }
}
