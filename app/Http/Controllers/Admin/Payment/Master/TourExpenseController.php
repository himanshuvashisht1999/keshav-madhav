<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Payment\Master\TourExpenseService as Service;
use App\Requests\Admin\Master\TourExpenseStoreRequest;
use App\Requests\Admin\Master\TourExpenseUpdateRequest;
use Auth;

class TourExpenseController extends Controller
{
    protected $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return view('admin.payment.master.tour_expense.index');
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function create()
    {
        return view('admin.payment.master.tour_expense.create');
    }

    public function store(TourExpenseStoreRequest $request)
    {
        $this->service->store($request);
        return redirect()->route('admin.payment.master.tour_expense.index')->withSuccess('The tour expense has been successfully created.');
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        return view('admin.payment.master.tour_expense.edit', $response);
    }

    public function update(TourExpenseUpdateRequest $request)
    {
        $this->service->update($request);
        return redirect()->route('admin.payment.master.tour_expense.index')->withSuccess('The tour expense has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $this->service->delete($request);
        return redirect()->route('admin.payment.master.tour_expense.index')->withSuccess('The tour expense has been successfully deleted/deactivated.');
    }
}
