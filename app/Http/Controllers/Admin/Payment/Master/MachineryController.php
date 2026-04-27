<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\MachineryDataTable;
use App\Requests\Admin\Master\MachineryStoreRequest;
use App\Requests\Admin\Master\MachineryUpdateRequest;
use App\Models\MachineryMaster;
use App\Models\MasterOpeningBalance;
use App\Services\Admin\Payment\Master\MachineryService;
use Illuminate\Http\Request;

class MachineryController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(MachineryService $service, MachineryDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('machinery');
        $response['total_current_balance'] = MachineryMaster::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.machinery.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.machinery.create');
    }

    public function store(MachineryStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.machinery.index')->with('success', 'Machinery Account created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.machinery.edit', compact('data'));
    }

    public function update(MachineryUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.machinery.index')->with('success', 'Machinery Account updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.machinery.index')->with('success', 'Machinery Account deleted successfully.');
    }
}
