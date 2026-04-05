<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\ConsumableGoodService as Service;
use App\Requests\Admin\Master\ConsumableGoodStoreRequest;
use App\Requests\Admin\Master\ConsumableGoodUpdateRequest;
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
        return view('admin.payment.master.consumable_good.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
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
