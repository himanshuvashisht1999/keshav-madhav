<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\LoanDataTable;
use App\Requests\Admin\Master\LoanStoreRequest;
use App\Requests\Admin\Master\LoanUpdateRequest;
use App\Services\Admin\Payment\Master\LoanService;
use Illuminate\Http\Request;

class LoanController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(LoanService $service, LoanDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        return view('admin.payment.master.loan.index');
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.loan.create');
    }

    public function store(LoanStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.loan.index')->with('success', 'Loan Account created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.loan.edit', compact('data'));
    }

    public function update(LoanUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.loan.index')->with('success', 'Loan Account updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.loan.index')->with('success', 'Loan Account deleted successfully.');
    }
}
