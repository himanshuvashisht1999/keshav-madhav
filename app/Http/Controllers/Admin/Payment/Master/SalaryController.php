<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Http\DataTable\Admin\Payment\Master\SalaryDataTable;
use App\Requests\Admin\Master\SalaryStoreRequest;
use App\Requests\Admin\Master\SalaryUpdateRequest;
use App\Models\SalaryMaster;
use App\Models\MasterOpeningBalance;
use App\Services\Admin\Payment\Master\SalaryService;
use Illuminate\Http\Request;

class SalaryController extends Controller
{
    protected $service;
    protected $dataTable;

    public function __construct(SalaryService $service, SalaryDataTable $dataTable)
    {
        $this->service = $service;
        $this->dataTable = $dataTable;
    }

    public function index()
    {
        $response['total_opening_balance'] = MasterOpeningBalance::getTotalOpeningBalance('salary');
        $response['total_current_balance'] = SalaryMaster::where('status', '!=', 3)->sum('balance');
        return view('admin.payment.master.salary.index', $response);
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.salary.create');
    }

    public function store(SalaryStoreRequest $request)
    {
        $this->service->store($request->validated());
        return redirect()->route('admin.payment.master.salary.index')->with('success', 'Salary Account created successfully.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $data = $this->service->find($id);
        return view('admin.payment.master.salary.edit', compact('data'));
    }

    public function update(SalaryUpdateRequest $request)
    {
        $this->service->update($request->validated(), $request->id);
        return redirect()->route('admin.payment.master.salary.index')->with('success', 'Salary Account updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request->id);
        return redirect()->route('admin.payment.master.salary.index')->with('success', 'Salary Account deleted successfully.');
    }
}
