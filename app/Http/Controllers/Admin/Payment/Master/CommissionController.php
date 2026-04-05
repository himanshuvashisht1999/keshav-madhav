<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\CommissionDataTable;
use App\Requests\Admin\Master\CommissionStoreRequest;
use App\Requests\Admin\Master\CommissionUpdateRequest;
use App\Services\Admin\Payment\Master\CommissionService;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(CommissionService $service, CommissionDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.commission.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.commission.create');
    }

    public function store(CommissionStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.commission.index')->with('success', 'Commission created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.commission.edit', compact('data'));
    }

    public function update(CommissionUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.commission.index')->with('success', 'Commission updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.commission.index')->with('success', 'Commission deleted successfully.');
    }
}
