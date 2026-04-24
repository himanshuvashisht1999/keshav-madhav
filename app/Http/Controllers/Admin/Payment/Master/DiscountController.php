<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\DiscountDataTable;
use App\Requests\Admin\Master\DiscountStoreRequest;
use App\Requests\Admin\Master\DiscountUpdateRequest;
use App\Services\Admin\Payment\Master\DiscountService;
use Illuminate\Http\Request;

class DiscountController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(DiscountService $service, DiscountDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.discount.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.discount.create');
    }

    public function store(DiscountStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.discount.index')->with('success', 'Discount Account created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.discount.edit', compact('data'));
    }

    public function update(DiscountUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.discount.index')->with('success', 'Discount Account updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.discount.index')->with('success', 'Discount Account deleted successfully.');
    }
}
