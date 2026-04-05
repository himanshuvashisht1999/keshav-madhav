<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\HulayatiDataTable;
use App\Requests\Admin\Master\HulayatiStoreRequest;
use App\Requests\Admin\Master\HulayatiUpdateRequest;
use App\Services\Admin\Payment\Master\HulayatiService;
use Illuminate\Http\Request;

class HulayatiController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(HulayatiService $service, HulayatiDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.hulayati.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.hulayati.create');
    }

    public function store(HulayatiStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.hulayati.index')->with('success', 'Hulayati created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.hulayati.edit', compact('data'));
    }

    public function update(HulayatiUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.hulayati.index')->with('success', 'Hulayati updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.hulayati.index')->with('success', 'Hulayati deleted successfully.');
    }
}
