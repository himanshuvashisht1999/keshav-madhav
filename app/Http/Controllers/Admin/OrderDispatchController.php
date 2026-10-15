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

    public function delete(Request $request)
    {
        $id = $request->id ?? $request->dispatch_id;
        $result = $this->service->destroy($id);
        if ($result['status_code'] == 1) {
            return redirect()->route('admin.order-dispatch.index')->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
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
            $result = \DB::transaction(function () use ($request) {
                $dispatch = OrderDispatch::lockForUpdate()->findOrFail($request->dispatch_id);
                $oldGrandTotal = (float) $dispatch->total_amount;

                // Recalculate based on items
                $data = $this->service->view(new Request(['id' => $dispatch->id]));
                $subtotal = (float) $data['order_dispatch_data']['total_dispatch_amount'];

                // Discount: typed as an amount or as a %
                if ($request->filled('discount_percentage') && !$request->filled('discount_amount')) {
                    $discountAmount = round($subtotal * Service::charge($request->discount_percentage, 'discount %', 100) / 100, 2);
                } else {
                    $discountAmount = Service::charge($request->discount_amount, 'discount');
                }
                if ($discountAmount > $subtotal + 0.001) {
                    throw new \Exception('The discount is more than the bill.');
                }
                $otherCharges = Service::charge($request->other_charges, 'other charges');
                $baseForGst = ($subtotal - $discountAmount) + $otherCharges;

                // GST: typed as a % or as an amount
                if ($request->filled('gst_percentage') && !$request->filled('gst_amount')) {
                    $gstPercentage = Service::charge($request->gst_percentage, 'GST %', 100);
                } elseif ($request->filled('gst_amount')) {
                    $gstAmount = Service::charge($request->gst_amount, 'GST amount');
                    $gstPercentage = $baseForGst > 0 ? ($gstAmount / $baseForGst) * 100 : 0;
                } else {
                    $gstPercentage = Service::charge($request->gst_percentage, 'GST %', 100);
                }
                if ($gstPercentage > 100) {
                    throw new \Exception('The GST is not valid.');
                }

                $bill = Service::billTotals($subtotal, $discountAmount, $otherCharges, $gstPercentage);

                // Update dispatch
                $dispatch->company_id = $request->company_id;
                if ($request->filled('dispatch_date')) {
                    $dispatch->dispatch_date = $request->dispatch_date;
                }
                $dispatch->discount_percentage = $subtotal > 0 ? round($bill['discount'] / $subtotal * 100, 6) : 0;
                $dispatch->discount_amount = $bill['discount'];
                $dispatch->gst_percentage = $gstPercentage;
                $dispatch->gst_amount = $bill['gst'];
                $dispatch->other_charges = $bill['other'];
                $dispatch->remark = $request->remark;
                $dispatch->total_amount = $bill['total'];
                $dispatch->save();

                // Update Customer Balance
                $diff = $oldGrandTotal - $bill['total'];
                $customer = \App\Models\MasterCustomer::lockForUpdate()->find($dispatch->customer_id);
                if ($customer) {
                    $customer->balance += $diff;
                    $customer->save();
                }
                return $bill['total'];
            });

            return response()->json([
                'status' => 'success',
                'message' => 'Invoice updated successfully',
                'new_total' => number_format($result, 2)
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update invoice: ' . $e->getMessage()
            ], 500);
        }
    }
}
