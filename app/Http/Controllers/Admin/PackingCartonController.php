<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\PackingCartonService as Service;
use App\Services\Admin\ProductOrderService;
use Illuminate\Support\Facades\Crypt;
use Auth;
use PDF;



class PackingCartonController extends Controller
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
        // $response['order_main_id'] = $request->id ?? 0;
        // // $response['order_main'] = $this->productOrderService->orderMainDetails($request);
        return view('admin.packing_carton.create', $response);
    }
    public function store(Request $request)
    {
        $data = $this->service->store($request);
        if ($data['status_code'] == 1) {
            return redirect()->route('admin.packing-carton.view', ['id' => $data['id']])->withSuccess($data['message']);
        } else {
            return redirect()->back()->withError($data['message']);
        }
        // return view('admin.packing_carton.create-dispatch');
    }
    public function index(Request $request)
    {
        $response['customers'] = $this->productOrderService->customers();
        $response['orders'] = $this->service->getOrders();
        // dd($response['orders']);
        return view('admin.packing_carton.index', $response);
    }
    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }

    public function view(Request $request)
    {
        $response['data'] = $this->service->view($request);
        // dd($response['data']);
        return view('admin.packing_carton.view', $response);
    }
    public function downloadPdf($id)
    {
        $request = new \Illuminate\Http\Request();
        $request->merge(['id' => $id]);
        $response['data'] = $this->service->view($request);
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.packing_carton.pdf', $response)
            ->setPaper('a4', 'portrait');
        return $pdf->download('Packing_Detail_' . $id . '.pdf');
    }
    public function getCustomerOrders(Request $request)
    {
        $response['data'] = $this->service->getCustomerOrders($request);
        return response()->json($response);
    }
    public function getCustomersBybarcode(Request $request)
    {
        $response['data'] = $this->service->getCustomersBybarcode($request);
        return response()->json($response);
    }
    public function getOrdersDetails(Request $request)
    {
        $response['data'] = $this->service->getOrdersDetails($request);
        return response()->json($response);
    }


}