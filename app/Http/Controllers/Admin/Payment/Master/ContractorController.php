<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\ContractorService as Service;
use App\Requests\Admin\Master\ContractorStoreRequest;
use App\Requests\Admin\Master\ContractorUpdateRequest;
use App\Models\MasterOpeningBalance;
use App\Models\Contractor;
use Auth;

class ContractorController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('contractor');
        $response['total_current_balance'] = Contractor::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.contractor.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function allContractors()
    {
        $contractors = Contractor::select('id', 'name')->where('status', 1)->get();
        return response()->json($contractors);
    }

    public function create()
    {
        return view('admin.payment.master.contractor.create');
    }

    public function store(ContractorStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.contractor.index')->withSuccess('The contractor has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.contractor.edit', $response);
    }

    public function update(ContractorUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.contractor.index')->withSuccess('The contractor has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.contractor.index')->withSuccess('The contractor has been successfully deleted/deactivated.');
    }
}
