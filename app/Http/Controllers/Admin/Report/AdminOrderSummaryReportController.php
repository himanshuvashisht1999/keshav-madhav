<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Report\OrderSummaryReportService;
use App\Models\MasterCustomer as Customer;

class AdminOrderSummaryReportController extends Controller
{
    protected $service;

    public function __construct(OrderSummaryReportService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        $customers = Customer::where('status', 1)->get();
        return view('admin.reports.order_summary.index', compact('customers'));
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function view(Request $request)
    {
        $data = $this->service->view($request->id);
        if (!$data) {
            return redirect()->back()->with('error', 'Order not found');
        }
        return view('admin.reports.order_summary.view', $data);
    }
}
