<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\InterestService as Service;
use App\Requests\Admin\Master\InterestStoreRequest;
use App\Requests\Admin\Master\InterestUpdateRequest;
use Auth;

class InterestController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.interest.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.interest.create');
    }

    public function store(InterestStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.interest.index')->withSuccess('The interest has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.interest.edit', $response);
    }

    public function update(InterestUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.interest.index')->withSuccess('The interest has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.interest.index')->withSuccess('The interest has been successfully deleted/deactivated.');
    }
}
