<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\RentDataTable;
use App\Requests\Admin\Master\RentStoreRequest;
use App\Requests\Admin\Master\RentUpdateRequest;
use App\Services\Admin\Payment\Master\RentService;
use Illuminate\Http\Request;

class RentController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(RentService $service, RentDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.rent.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.rent.create');
    }

    public function store(RentStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.rent.index')->with('success', 'Rent created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.rent.edit', compact('data'));
    }

    public function update(RentUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.rent.index')->with('success', 'Rent updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.rent.index')->with('success', 'Rent deleted successfully.');
    }
}
