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
        // dd($response);
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

            $discountAmount = $request->discount_amount ?? 0;
            $gstPercentage = $request->gst_percentage ?? 5;

            $gstAmount = (($subtotal - $discountAmount) * $gstPercentage) / 100;
            $newGrandTotal = ($subtotal - $discountAmount) + $gstAmount;

            // Update dispatch
            $dispatch->discount_amount = $discountAmount;
            $dispatch->gst_percentage = $gstPercentage;
            $dispatch->total_amount = $newGrandTotal;
            $dispatch->save();

            // Update Customer Balance
            $diff = $oldGrandTotal - $newGrandTotal;
            $customer = \App\Models\MasterCustomer::find($dispatch->customer_id);
            if ($customer) {
                // If new total is higher, they owe more (balance decreases)
                // If old was 1000, new is 1200, diff is -200. balance += -200 => balance - 200.
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