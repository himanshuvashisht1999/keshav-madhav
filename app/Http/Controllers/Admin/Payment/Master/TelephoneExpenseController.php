<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\TelephoneExpenseDataTable;
use App\Requests\Admin\Master\TelephoneExpenseStoreRequest;
use App\Requests\Admin\Master\TelephoneExpenseUpdateRequest;
use App\Services\Admin\Payment\Master\TelephoneExpenseService;
use Illuminate\Http\Request;

class TelephoneExpenseController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(TelephoneExpenseService $service, TelephoneExpenseDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.telephone_expense.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.telephone_expense.create');
    }

    public function store(TelephoneExpenseStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.telephone_expense.index')->with('success', 'Telephone Expense created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.telephone_expense.edit', compact('data'));
    }

    public function update(TelephoneExpenseUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.telephone_expense.index')->with('success', 'Telephone Expense updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.telephone_expense.index')->with('success', 'Telephone Expense deleted successfully.');
    }
}
