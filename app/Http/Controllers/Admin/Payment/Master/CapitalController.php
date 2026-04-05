<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\CapitalDataTable;
use App\Requests\Admin\Master\CapitalStoreRequest;
use App\Requests\Admin\Master\CapitalUpdateRequest;
use App\Services\Admin\Payment\Master\CapitalService;
use Illuminate\Http\Request;

class CapitalController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(CapitalService $service, CapitalDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.capital.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.capital.create');
    }

    public function store(CapitalStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.capital.index')->with('success', 'Capital created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.capital.edit', compact('data'));
    }

    public function update(CapitalUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.capital.index')->with('success', 'Capital updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.capital.index')->with('success', 'Capital deleted successfully.');
    }
}
