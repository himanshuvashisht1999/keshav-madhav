<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\GeneralExpenseDataTable;
use App\Requests\Admin\Master\GeneralExpenseStoreRequest;
use App\Requests\Admin\Master\GeneralExpenseUpdateRequest;
use App\Services\Admin\Payment\Master\GeneralExpenseService;
use Illuminate\Http\Request;

class GeneralExpenseController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(GeneralExpenseService $service, GeneralExpenseDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.general_expense.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.general_expense.create');
    }

    public function store(GeneralExpenseStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.general_expense.index')->with('success', 'General Expense created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.general_expense.edit', compact('data'));
    }

    public function update(GeneralExpenseUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.general_expense.index')->with('success', 'General Expense updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.general_expense.index')->with('success', 'General Expense deleted successfully.');
    }
}
