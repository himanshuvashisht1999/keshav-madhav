<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\FactoryHeadDataTable;
use App\Requests\Admin\Master\FactoryHeadStoreRequest;
use App\Requests\Admin\Master\FactoryHeadUpdateRequest;
use App\Models\FactoryHeadMaster;
use App\Models\MasterOpeningBalance;
use App\Services\Admin\Payment\Master\FactoryHeadService;
use Illuminate\Http\Request;

class FactoryHeadController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(FactoryHeadService $service, FactoryHeadDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('factory_head');
        $response['total_current_balance'] = FactoryHeadMaster::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.factory_head.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.factory_head.create');
    }

    public function store(FactoryHeadStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.factory_head.index')->with('success', 'Factory Head created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.factory_head.edit', compact('data'));
    }

    public function update(FactoryHeadUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.factory_head.index')->with('success', 'Factory Head updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.factory_head.index')->with('success', 'Factory Head deleted successfully.');
    }
}
