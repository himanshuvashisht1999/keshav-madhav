<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use App\Models\FabricRollAssigning;
use App\Models\ProductionSlipDigitization;
use App\Models\OrderMain;
use App\Models\Stock;
use App\Models\OrderLot;
use App\Models\Fabric;

use App\Models\MasterCustomer as Customer;
use App\Services\Admin\Report\OrderSummaryReportService;
use App\Services\Admin\ReportService;
use PDF;

class OwnerAuthController extends Controller
{
    protected $orderSummaryService;
    protected $reportService;

    public function __construct(OrderSummaryReportService $orderSummaryService, ReportService $reportService)
    {
        $this->orderSummaryService = $orderSummaryService;
        $this->reportService = $reportService;
    }
    public function showLogin()
    {
        if (Auth::guard('admin')->check() && Auth::guard('admin')->user()->role_id == 3) {
            return redirect()->route('owner.dashboard');
        }
        return view('owner.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password, 'role_id' => 3, 'status' => 1])) {
            return redirect()->route('owner.dashboard')->withSuccess('Welcome to Owner Dashboard');
        }

        return redirect()->back()->withError('Invalid credentials or you are not authorized as Owner.');
    }

    public function dashboard()
    {
        $data['total_orders'] = OrderMain::count();
        $data['total_lots'] = FabricRollAssigning::distinct('lot_no')->count();
        $data['total_stock'] = \App\Models\FabricReceiptDetail::sum('remaining_quantity');
        return view('owner.dashboard', $data);
    }

    public function orders(Request $request)
    {
        $data['orders'] = $this->reportService->salesOrder($request);
        $data['customers'] = $this->reportService->customers();
        $data['lotNos'] = $this->reportService->lot_numbers();
        $data['filters'] = $request->all();
        return view('owner.reports.orders', $data);
    }

    public function stock(Request $request)
    {
        $data['stocks'] = $this->reportService->stock($request);
        $data['warehouses'] = $this->reportService->warehouses();
        $data['fabrics'] = $this->reportService->fabrics();
        $data['filters'] = $request->all();
        return view('owner.reports.stock', $data);
    }

    public function stockRollDetails(Request $request)
    {
        return $this->reportService->fabricRollDetails(
            $request->fabric_sku,
            $request->warehouse_id
        );
    }

    public function lots(Request $request)
    {
        $data['lots'] = $this->reportService->orderLotsDetailed($request);
        $data['lotNos'] = $this->reportService->lot_numbers();
        return view('owner.reports.lots', $data);
    }

    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('owner.login')->withSuccess('Logged out successfully.');
    }

    /* Detailed Reports - Mirroring Admin */
    public function orderSummary(Request $request)
    {
        $salesOrders = OrderMain::with(['customer', 'orderProductSets'])
            ->when($request->filled('order_no'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('master_customer_id', $request->customer_id);
            })
            ->orderBy('id', 'desc')
            ->paginate(10)
            ->withQueryString();

        $salesOrders->getCollection()->transform(function ($order) {
            $total_pcs = $order->orderProductSets->sum('no_of_pcs');
            return [
                'id' => $order->id,
                'order_no' => $order->sku,
                'created_at' => $order->created_at,
                'customer' => $order->customer->name ?? 'N/A',
                'status' => $order->status,
                'total_pcs' => $total_pcs,
                'scanned_pcs' => 0, // Current logic doesn't summarize scanned_pcs easily at this level
                'set_type' => $order->set_type ?? 'N/A',
                'lots' => [] // Optional: if we need lot count
            ];
        });

        $customers = Customer::where('status', 1)->get();
        return view('owner.reports.order_summary_index', compact('customers', 'salesOrders'));
    }

    public function orderSummaryList(Request $request)
    {
        $dt = $this->orderSummaryService->indexList($request);
        $data = $dt->getData();
        foreach ($data->data as $row) {
            $row->action = '<a href="' . route('owner.order-summary.view', ['id' => $row->id]) . '" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i> View Summary</a>';
        }
        return response()->json($data);
    }

    public function orderSummaryView(Request $request)
    {
        $id = $request->id ?? array_key_first($request->query());
        $data = $this->orderSummaryService->view($id);

        if (!$data || !$data['order']) {
            return redirect()->back()->with('error', 'Order not found');
        }

        $data['lotsData'] = $this->orderSummaryService->lots($id);
        $data['history_data'] = $data['history_data'] ?? [];

        return view('owner.reports.order_summary_view', compact('data'));
    }

    public function orderSummaryPdf(Request $request)
    {
        $id = $request->id ?? array_key_first($request->query());
        $data = $this->orderSummaryService->view($id);

        if (!$data || !$data['order']) {
            return redirect()->back()->with('error', 'Order not found');
        }

        $data['lotsData'] = $this->orderSummaryService->lots($id);
        $data['cartons'] = $data['cartons'] ?? [];
        $data['dispatches'] = $data['dispatches'] ?? [];

        $pdf = PDF::loadView('admin.report.order_summary.order-summary-pdf', $data)->setPaper('A4', 'portrait');
        return $pdf->download('order-summary.pdf');
    }

    public function lotDetails(Request $request)
    {
        $response['data'] = $this->reportService->lotDetails($request->lot_no);
        $response['master_stages'] = $this->reportService->master_stages();

        if (!$response['data']) {
            return redirect()->back()->with('error', 'Lot not found');
        }

        return view('owner.reports.lot_details', $response);
    }
}
