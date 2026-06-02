<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Requests\Admin\Master\WashingMasterStoreRequest;
use App\Requests\Admin\Master\WashingMasterUpdateRequest;
use App\Services\Admin\Payment\Master\WashingMasterService;
use App\Models\MasterOpeningBalance;
use App\Models\WashingMaster;
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
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('washing_master');
        $response['total_current_balance'] = WashingMaster::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.washing_master.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function allWashingMasters()
    {
        $masters = WashingMaster::select('id', 'name')->where('status', 1)->get();
        return response()->json($masters);
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
