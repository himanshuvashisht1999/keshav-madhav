<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\ElectricityExpenseDataTable;
use App\Requests\Admin\Master\ElectricityExpenseStoreRequest;
use App\Requests\Admin\Master\ElectricityExpenseUpdateRequest;
use App\Services\Admin\Payment\Master\ElectricityExpenseService;
use Illuminate\Http\Request;

class ElectricityExpenseController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(ElectricityExpenseService $service, ElectricityExpenseDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.electricity_expense.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.electricity_expense.create');
    }

    public function store(ElectricityExpenseStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.electricity_expense.index')->with('success', 'Electricity Expense created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.electricity_expense.edit', compact('data'));
    }

    public function update(ElectricityExpenseUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.electricity_expense.index')->with('success', 'Electricity Expense updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.electricity_expense.index')->with('success', 'Electricity Expense deleted successfully.');
    }
}
