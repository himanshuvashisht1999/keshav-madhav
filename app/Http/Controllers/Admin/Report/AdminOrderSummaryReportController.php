<?php

namespace App\Http\Controllers\Admin\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Admin\Report\OrderSummaryReportService;
use App\Services\Admin\ReportService;
use App\Models\MasterCustomer as Customer;
use PDF;

class AdminOrderSummaryReportController extends Controller
{
    protected $service;

    public function __construct(OrderSummaryReportService $service, ReportService $reportService  )
    {
        $this->service = $service;
        $this->reportService = $reportService;
    }

    public function index()
    {
        $customers = Customer::where('status', 1)->get();
        return view('admin.report.order_summary.index', compact('customers'));
    }

    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function view(Request $request)
    {
        $data = $this->service->view($request->id);
        $data['lotsData'] = $this->service->lots($request->id);
        if (!$data) {
            return redirect()->back()->with('error', 'Order not found');
        }
        // dd($data);
        return view('admin.report.order_summary.view', $data);
    }

    public function downloadOrderSummaryPdf(Request $request)
    {
        $id = $request->id;

        $data = $this->service->view($id);

        if (!$data) {
            return redirect()->back()->with('error', 'Order not found');
        }

        $data['lotsData']   = $this->service->lots($id);
        $data['order']      = $data['order']; // already coming from service
        $data['cartons']    = $data['cartons'] ?? [];
        $data['dispatches'] = $data['dispatches'] ?? [];

        $pdf = Pdf::loadView(
            'admin.report.order_summary.order-summary-pdf',
            $data
        )->setPaper('A4', 'portrait');
        
        return $pdf->download('order-summary.pdf');

    }

}
