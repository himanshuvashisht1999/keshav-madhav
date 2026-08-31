<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\SalesAgentService as Service;
use App\Models\Brand;
use App\Requests\Admin\Master\SalesAgentStoreRequest;
use App\Requests\Admin\Master\SalesAgentUpdateRequest;
use Auth;
use App\Models\SalesAgent;
use App\Models\MasterOpeningBalance;
use App\Models\MasterCustomer;

class SalesAgentController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('sales_agent');
        $response['total_current_balance'] = MasterCustomer::where('status', '!=', 3)->whereNotNull('sales_agent_id')->sum('balance');
        return view('admin.master.sales_agent.index', $response);
    }
    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }
    public function downloadPdf(Request $request)
    {
        return $this->service->downloadPdf($request);
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
        $agent = \App\Models\SalesAgent::find($request->id);
        if ($agent) {
            log_deletion('Master Sales Agent', $request->id, [
                'sales_agent' => $agent->toArray()
            ]);
        }
        $this->service->delete($request);
        return redirect()->route('admin.master.sales-agent.index')->withSuccess('The sales agent has been successfully deactivated.');
    }
    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        $response['brands'] = Brand::where('status', 1)->get();
        return view('admin.master.sales_agent.edit', $response);
    }
    public function view(Request $request)
    {
        $response['data'] = $this->service->viewDetails($request);
        return view('admin.master.sales_agent.view', $response);
    }
    public function update(SalesAgentUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.master.sales-agent.index')->withSuccess('The sales agent has been successfully updated.');
    }
}
