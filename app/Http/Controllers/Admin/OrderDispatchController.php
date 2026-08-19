<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderDispatchService as Service;
use App\Services\Admin\ProductOrderService;
use App\Models\OrderDispatch;
use Illuminate\Support\Facades\Crypt;
use Auth;
use Barryvdh\DomPDF\Facade\Pdf;



class OrderDispatchController extends Controller
{
    protected $service;
    protected $productOrderService;
    public function __construct(Service $service, ProductOrderService $ProductOrderService)
    {
        $this->service = $service;
        $this->productOrderService = $ProductOrderService;
    }
    public function create(Request $request)
    {
        $response['customers'] = $this->productOrderService->customers();
        $response['orders'] = $this->service->getOrders();
        $response['companies'] = \App\Models\Company::where('status', 1)->get();
        return view('admin.order_dispatch.create', $response);
    }
    public function store(Request $request)
    {
        $data = $this->service->store($request);
        if ($data['status_code'] == 1) {
            return redirect()->route('admin.order-dispatch.index')->withSuccess($data['message']);
        } else {
            return redirect()->back()->withError($data['message']);
        }
        // return view('admin.order_dispatch.create-dispatch');
    }
    public function index(Request $request)
    {
        $response['customers'] = $this->productOrderService->customers();
        return view('admin.order_dispatch.index', $response);
    }
    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function view(Request $request)
    {
        $data = $this->service->view($request);
        if (!$data) {
            return redirect()->back()->with('error', 'Dispatch not found');
        }
        $data['companies'] = \App\Models\Company::where('status', 1)->get();
        return view('admin.order_dispatch.view', $data);
    }

    public function downloadPdf(Request $request)
    {
        $data = $this->service->view($request);
        if (!$data) {
            return redirect()->back()->with('error', 'Dispatch not found');
        }

        $pdf = Pdf::loadView('admin.order_dispatch.pdf', $data)->setPaper('A4', 'portrait');
        $fileName = 'dispatch-' . ($data['order_dispatch_data']['order_dispatch_no'] ?? $request->id) . '.pdf';
        $fileName = str_replace(['/', '\\'], '-', $fileName);
        return $pdf->download($fileName);
    }

    public function downloadInvoice(Request $request)
    {
        $data = $this->service->view($request);
        if (!$data) {
            return redirect()->back()->with('error', 'Dispatch not found');
        }

        $pdf = Pdf::loadView('admin.order_dispatch.invoice-pdf', $data)->setPaper('A4', 'portrait');
        $fileName = 'invoice-' . ($data['order_dispatch_data']['order_dispatch_no'] ?? $request->id) . '.pdf';
        $fileName = str_replace(['/', '\\'], '-', $fileName);
        return $pdf->download($fileName);
    }

    public function downloadPackingSlip(Request $request)
    {
        $data = $this->service->view($request);
        if (!$data) {
            return redirect()->back()->with('error', 'Dispatch not found');
        }

        $pdf = Pdf::loadView('admin.order_dispatch.packing-slip-pdf', $data)->setPaper('A4', 'portrait');
        $fileName = 'packing-slip-' . ($data['order_dispatch_data']['order_dispatch_no'] ?? $request->id) . '.pdf';
        $fileName = str_replace(['/', '\\'], '-', $fileName);
        return $pdf->download($fileName);
    }

    public function getOrderPackingData(Request $request)
    {
        $response['data'] = $this->service->getOrderPackingData($request);
        return response()->json($response);
    }

    public function getOrdersByCustomer(Request $request)
    {
        $response['data'] = $this->service->getOrdersByCustomer($request);
        return response()->json($response);
    }

    public function comppleteOrder(Request $request)
    {
        $response['data'] = $this->service->comppleteOrder();
        return response()->json($response);
    }

    public function updateInvoice(Request $request)
    {
        try {
            $dispatch = OrderDispatch::findOrFail($request->dispatch_id);
            $oldGrandTotal = $dispatch->total_amount;

            // Recalculate based on items
            $data = $this->service->view(new Request(['id' => $dispatch->id]));
            $subtotal = $data['order_dispatch_data']['total_dispatch_amount'];

            $discountAmount = floatval($request->discount_amount) ?? 0;
            $discountPercentage = floatval($request->discount_percentage) ?? 0;

            // Bi-directional calculation
            if ($request->filled('discount_percentage') && !$request->filled('discount_amount')) {
                $discountAmount = ($subtotal * $discountPercentage) / 100;
            } else if ($request->filled('discount_amount')) {
                $discountPercentage = $subtotal > 0 ? ($discountAmount / $subtotal) * 100 : 0;
            }

            $otherCharges = floatval($request->other_charges) ?? 0;
            $baseForGst = ($subtotal - $discountAmount) + $otherCharges;

            $gstPercentage = floatval($request->gst_percentage) ?? 0;
            $gstAmount = floatval($request->gst_amount) ?? 0;

            if ($request->filled('gst_percentage') && !$request->filled('gst_amount')) {
                $gstAmount = ($baseForGst * $gstPercentage) / 100;
            } else if ($request->filled('gst_amount')) {
                $gstPercentage = $baseForGst > 0 ? ($gstAmount / $baseForGst) * 100 : 0;
            } else {
                $gstAmount = ($baseForGst * $gstPercentage) / 100;
            }

            $newGrandTotal = $baseForGst + $gstAmount;

            // Update dispatch
            $dispatch->company_id = $request->company_id;
            if ($request->filled('dispatch_date')) {
                $dispatch->dispatch_date = $request->dispatch_date;
            }
            $dispatch->discount_percentage = $discountPercentage;
            $dispatch->discount_amount = $discountAmount;
            $dispatch->gst_percentage = $gstPercentage;
            $dispatch->gst_amount = $gstAmount;
            $dispatch->other_charges = $otherCharges;
            $dispatch->remark = $request->remark;
            $dispatch->total_amount = $newGrandTotal;
            $dispatch->save();

            // Update Customer Balance
            $diff = $oldGrandTotal - $newGrandTotal;
            $customer = \App\Models\MasterCustomer::find($dispatch->customer_id);
            if ($customer) {
                $customer->balance += $diff;
                $customer->save();
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice updated successfully',
                'new_total' => number_format($newGrandTotal, 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}