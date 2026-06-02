<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\ConsumableGoodService as Service;
use App\Requests\Admin\Master\ConsumableGoodStoreRequest;
use App\Requests\Admin\Master\ConsumableGoodUpdateRequest;
use App\Models\MasterOpeningBalance;
use App\Models\ConsumableGood;
use Auth;

class ConsumableGoodController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('consumable_good');
        $response['total_current_balance'] = ConsumableGood::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.consumable_good.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function allConsumables()
    {
        $consumables = ConsumableGood::select('id', 'name')->where('status', 1)->get();
        return response()->json($consumables);
    }

    public function create()
    {
        return view('admin.payment.master.consumable_good.create');
    }

    public function store(ConsumableGoodStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.consumable_good.index')->withSuccess('The consumable good has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.consumable_good.edit', $response);
    }

    public function update(ConsumableGoodUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.consumable_good.index')->withSuccess('The consumable good has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.consumable_good.index')->withSuccess('The consumable good has been successfully deleted/deactivated.');
    }
}
