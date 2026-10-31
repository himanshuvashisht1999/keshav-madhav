<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use Illuminate\Support\Facades\RateLimiter;
use App\Models\OrderMain;

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

        // at most 10 wrong tries a minute from one address; the form shows how long to wait (no bare error page)
        $key = 'owner-login|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 10)) {
            return redirect()->back()->withInput($request->only('email'))
                ->withError('Too many wrong tries. Please wait ' . RateLimiter::availableIn($key) . ' seconds and try again.');
        }

        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password, 'role_id' => 3, 'status' => 1])) {
            RateLimiter::clear($key);
            $request->session()->regenerate();
            return redirect()->route('owner.dashboard')->withSuccess('Welcome to Owner Dashboard');
        }

        RateLimiter::hit($key, 60);
        return redirect()->back()->withInput($request->only('email'))->withError('Invalid credentials or you are not authorized as Owner.');
    }

    /**
     * Home page: a few business figures (one SQL sum/count each) + the menu.
     * Money figures come from the stored party balances, which equal the ledger (checked daily by balances:check).
     * Sign rule of the app: balance = credit - debit, so a negative balance is Dr.
     */
    public function dashboard()
    {
        // customers in debit owe us; vendors in credit are owed by us. Every status counts: an inactive or deleted
        // party that still has a balance is still owed (same rule as Pending Payments and the party lists)
        $data['receivable'] = -(float) \App\Models\MasterCustomer::where('balance', '<', 0)->sum('balance');
        $data['payable'] = (float) \App\Models\Vendor::where('balance', '>', 0)->sum('balance');

        $data['fabric_metres'] = (float) \DB::table('fabric_receipt_details')->sum('remaining_quantity');
        $data['ready_boxes'] = (int) \DB::table('domestic_inventories')->sum('total_boxes');
        $data['open_orders'] = OrderMain::where('status', '!=', 3)->count(); // status 3 = completed

        // lots not yet fully dispatched / put in stock, leaving out lots of orders marked Completed
        // (one rule with the Lots page: ReportController::lotCounts)
        $data['lots_in_production'] = ReportController::lotCounts($this->reportService)['in_production'];

        // bank and cash accounts (negative = Dr = money held; positive = Cr = used from the bank: CC / OD / loan)
        $data['accounts'] = \DB::table('bank_accounts')
            ->where(function ($q) {
                $q->where('status', 1)->orWhere('balance', '!=', 0);
            })
            ->orderBy('bank_name')
            ->get(['id', 'bank_name', 'account_name', 'balance']);

        return view('owner.dashboard', $data);
    }

    /**
     * Sales Orders: one row per lot of each order. Same rows, figures and Delayed / On time filter as the admin
     * Sales Order report (ReportService::salesOrder): a lot of an order marked Completed is not delayed (same rule
     * as the Lots page and Home).
     */
    public function orders(Request $request)
    {
        // all filtered orders (order_no, lot_no, customer_id, delay_status, date_from, date_to), grouped by order no.
        $all = $this->reportService->salesOrder($request);
        $rows = $all->flatten(1);

        // header totals over the whole filtered set (not only this page)
        $data['totals'] = [
            'orders' => $all->count(),
            'ordered' => (int) $all->sum(fn($lots) => $lots->first()['total_pcs_in_order']),
            'lots' => $rows->where('lot_no', '!=', '-')->count(),
            'cut' => (int) $rows->sum('pieces_in_lot'),
            'dispatched' => (int) $rows->sum('dispatched'),
            'delayed' => $rows->where('isDelayed', 'Yes')->count(),
        ];

        $perPage = 50;
        $page = min(max(1, (int) $request->query('page', 1)), max(1, (int) ceil($all->count() / $perPage))); // a huge page number stays on the last page
        $data['orders'] = new \Illuminate\Pagination\LengthAwarePaginator(
            $all->forPage($page, $perPage), $all->count(), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->except('page')]
        );
        // customers that have orders (the filter list)
        $data['customers'] = Customer::whereIn('id', OrderMain::select('master_customer_id'))->orderBy('name')->get(['id', 'name']);
        return view('owner.reports.orders', $data);
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('owner.login')->withSuccess('Logged out successfully.');
    }

    /*
     * Order Summary list. Filters and figures as the admin Order Summary (OrderSummaryReportService::indexList):
     * Total pcs = ordered pieces, done = pieces in cartons dispatched (status 2) or put in domestic stock (status 3),
     * Remaining = ordered - done (not below 0), status Completed / Partial / In Progress (getOrderDispatchData rule).
     */
    public function orderSummary(Request $request)
    {
        $query = OrderMain::query()
            ->when($request->filled('order_no'), function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            })
            ->when($request->filled('po_number'), function ($q) use ($request) {
                $q->where('po_number', 'like', '%' . $request->po_number . '%');
            })
            ->when($request->filled('design_number'), function ($q) use ($request) {
                $q->whereHas('OrderProductSets', function ($sq) use ($request) {
                    $sq->where('design_number', 'like', '%' . $request->design_number . '%');
                });
            })
            ->when($request->filled('lot_no'), function ($q) use ($request) {
                $lotNo = trim($request->lot_no);
                $q->where(function ($lq) use ($lotNo) {
                    $lq->whereHas('orderLots', function ($olq) use ($lotNo) {
                        $olq->where('lot_no', 'like', '%' . $lotNo . '%');
                    })
                        ->orWhereIn('sku', function ($frq) use ($lotNo) {
                            $frq->select('order_no')->from('production_fabric_roll_assigning')
                                ->where('lot_no', 'like', '%' . $lotNo . '%');
                        })
                        ->orWhereHas('OrderProductSets', function ($osq) use ($lotNo) {
                            $osq->whereIn('id', function ($frq2) use ($lotNo) {
                                $frq2->select('order_products_set_id')->from('production_fabric_roll_assigning')
                                    ->where('lot_no', 'like', '%' . $lotNo . '%');
                            });
                        });
                });
            })
            ->when($request->filled('customer_id'), function ($q) use ($request) {
                $q->where('master_customer_id', $request->customer_id);
            });
        $ids = (clone $query)->orderBy('id', 'desc')->pluck('id');

        // pieces of every filtered order in two grouped queries
        $ordered = \DB::table('order_products_sets')->whereIn('order_main_id', $ids)
            ->groupBy('order_main_id')->selectRaw('order_main_id, SUM(total_quantity) as pcs')->pluck('pcs', 'order_main_id');
        $packing = \DB::table('packing_mains as pm')
            ->join('packing_cartons as c', 'c.packing_main_id', '=', 'pm.id')
            ->join('packing_items as i', 'i.packing_carton_id', '=', 'c.id')
            ->whereIn('pm.order_main_id', $ids)->groupBy('pm.order_main_id')
            ->selectRaw('pm.order_main_id, SUM(i.quantity) as packed, SUM(CASE WHEN c.status = 2 THEN i.quantity ELSE 0 END) as dispatched, SUM(CASE WHEN c.status = 3 THEN i.quantity ELSE 0 END) as to_stock')
            ->get()->keyBy('order_main_id');
        $figures = [];
        foreach ($ids as $id) {
            $p = $packing[$id] ?? null;
            $f = [
                'total' => (int) ($ordered[$id] ?? 0),
                'packed' => (int) ($p->packed ?? 0),
                'dispatched' => (int) ($p->dispatched ?? 0),
                'to_stock' => (int) ($p->to_stock ?? 0),
            ];
            $f['remaining'] = max(0, $f['total'] - $f['dispatched'] - $f['to_stock']);
            $f['status'] = $f['remaining'] === 0 ? 'Completed' : ($f['dispatched'] + $f['to_stock'] > 0 ? 'Partial' : 'In Progress');
            $figures[$id] = $f;
        }
        $status = $request->query('status');
        $statusFilter = is_string($status) ? (['completed' => 'Completed', 'partial' => 'Partial', 'in_progress' => 'In Progress'][$status] ?? null) : null;
        if ($statusFilter) {
            $ids = $ids->filter(fn($id) => $figures[$id]['status'] === $statusFilter)->values();
        }

        $set = collect($figures)->only($ids->all());
        $totals = [
            'orders' => $ids->count(),
            'total' => $set->sum('total'),
            'packed' => $set->sum('packed'),
            'dispatched' => $set->sum('dispatched'),
            'to_stock' => $set->sum('to_stock'),
            'remaining' => $set->sum('remaining'),
            'completed' => $set->where('status', 'Completed')->count(),
        ];

        $orders = OrderMain::with('customer:id,name')->whereIn('id', $ids)->orderBy('id', 'desc')
            ->paginate(50)->withQueryString();

        // designs and lots count of the orders on this page (lots count as admin: order lots or roll-sheet lots, whichever is more)
        $pageIds = $orders->pluck('id');
        $designs = \DB::table('order_products_sets')->whereIn('order_main_id', $pageIds)->orderBy('id')
            ->get(['order_main_id', 'design_number'])->groupBy('order_main_id')
            ->map(fn($sets) => $sets->pluck('design_number')->filter(fn($d) => $d !== null && $d !== '')->unique()->values());
        $orderLotCounts = \DB::table('order_lots')->whereIn('order_main_id', $pageIds)->whereNotNull('lot_no')->where('lot_no', '!=', '')
            ->groupBy('order_main_id')->selectRaw('order_main_id, COUNT(DISTINCT lot_no) as n')->pluck('n', 'order_main_id');
        $rollLotCounts = \DB::table('production_fabric_roll_assigning')->whereIn('order_no', $orders->pluck('sku')->filter()->values())
            ->groupBy('order_no')->selectRaw('order_no, COUNT(DISTINCT lot_no) as n')->get()
            ->mapWithKeys(fn($r) => [mb_strtolower(rtrim((string) $r->order_no)) => (int) $r->n]);
        foreach ($orders as $order) {
            $order->figures = $figures[$order->id];
            $order->design_list = $designs[$order->id] ?? collect();
            $order->lots_count = max((int) ($orderLotCounts[$order->id] ?? 0), (int) ($rollLotCounts[mb_strtolower(rtrim((string) $order->sku))] ?? 0));
        }

        $customers = Customer::whereIn('id', OrderMain::select('master_customer_id'))->orderBy('name')->get(['id', 'name']);
        return view('owner.reports.order_summary_index', compact('customers', 'orders', 'totals'));
    }

    /** Old DataTables endpoint (no owner page uses it; it ran 330 queries per call): open the Order Summary list with the same filters. */
    public function orderSummaryList(Request $request)
    {
        return redirect()->route('owner.order-summary.index', $request->only(['order_no', 'po_number', 'design_number', 'lot_no', 'customer_id', 'status']));
    }

    /** One order: lines, lots, cartons and dispatches (same service and data as the admin Order Summary view). */
    public function orderSummaryView(Request $request)
    {
        $id = (int) $request->query('id');
        $data = $id > 0 ? $this->orderSummaryService->view($id) : null;
        if (!$data || !$data['order']) {
            return redirect()->route('owner.order-summary.index')->with('error', 'Order not found');
        }

        $data['lotsData'] = $this->orderSummaryService->lots($id);
        // progress of each lot from the lot records (packed / dispatched / to stock / delay), as on the Sales Orders page
        $data['lotFacts'] = $this->reportService->lotProgress(collect($data['lotsData'])->pluck('lot_no'));

        $cartonPcs = fn($cartons) => (int) $cartons->sum(fn($c) => $c->items->sum('quantity'));
        $ordered = (int) $data['order']->orderProductSets->sum('total_quantity');
        $dispatched = $cartonPcs($data['cartons']->where('status', 2));
        $toStock = $cartonPcs($data['cartons']->where('status', 3));
        $remaining = max(0, $ordered - $dispatched - $toStock); // = getOrderDispatchData()['remaining']
        $data['figures'] = [
            'ordered' => $ordered,
            'cut' => (int) collect($data['lotsData'])->sum('lot_quantity'),
            'packed' => $cartonPcs($data['cartons']),
            'dispatched' => $dispatched,
            'to_stock' => $toStock,
            'remaining' => $remaining,
            'bills' => round((float) $data['dispatches']->sum('total_amount'), 2),
            'status' => $remaining === 0 ? 'Completed' : ($dispatched + $toStock > 0 ? 'Partial' : 'In Progress'),
        ];

        return view('owner.reports.order_summary_view', compact('data'));
    }

    public function orderSummaryPdf(Request $request)
    {
        $id = (int) $request->query('id');
        $data = $id > 0 ? $this->orderSummaryService->view($id) : null;
        if (!$data || !$data['order']) {
            return redirect()->route('owner.order-summary.index')->with('error', 'Order not found');
        }

        $data['lotsData'] = $this->orderSummaryService->lots($id);

        $pdf = PDF::loadView('admin.report.order_summary.order-summary-pdf', $data)->setPaper('A4', 'portrait');
        return $pdf->download('order-summary-' . trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $data['order']->sku), '-') . '.pdf');
    }


}
