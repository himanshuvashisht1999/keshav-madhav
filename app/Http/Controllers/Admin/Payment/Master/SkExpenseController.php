<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Requests\Admin\Master\SkExpenseStoreRequest;
use App\Requests\Admin\Master\SkExpenseUpdateRequest;
use App\Services\Admin\Payment\Master\SkExpenseService;
use Illuminate\Http\Request;

class SkExpenseController extends Controller
{
    protected $service;

    public function __construct(SkExpenseService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.sk_expense.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.sk_expense.create');
    }

    public function store(SkExpenseStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.sk_expense.index')->with('success', 'SK Expense added successfully.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.sk_expense.edit', $response);
    }

    public function update(SkExpenseUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.sk_expense.index')->with('success', 'SK Expense updated successfully.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.sk_expense.index')->with('success', 'SK Expense deleted successfully.');
    }
}
