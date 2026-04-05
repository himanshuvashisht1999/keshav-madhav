<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\SalesAgentService as Service;
use App\Models\Brand;
use App\Requests\Admin\Master\SalesAgentStoreRequest;
use App\Requests\Admin\Master\SalesAgentUpdateRequest;
use Auth;

class SalesAgentController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        return view('admin.master.sales_agent.index');
    }
    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }
    public function create()
    {
        $response['brands'] = Brand::where('status', 1)->get();
        return view('admin.master.sales_agent.create', $response);
    }
    public function store(SalesAgentStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.master.sales-agent.index')->withSuccess('The sales agent has been successfully created.');
    }
    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.master.sales-agent.index')->withSuccess('The sales agent has been successfully deactivated.');
    }
    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        $response['brands'] = Brand::where('status', 1)->get();
        return view('admin.master.sales_agent.edit', $response);
    }
    public function update(SalesAgentUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.master.sales-agent.index')->withSuccess('The sales agent has been successfully updated.');
    }
}
