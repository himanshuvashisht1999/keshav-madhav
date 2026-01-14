<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderDispatchService as Service;
use App\Services\Admin\ProductOrderService;
use Illuminate\Support\Facades\Crypt;
use Auth;
use PDF;



class OrderDispatchController extends Controller { 
    protected $service;
    public function __construct(Service $service, ProductOrderService $ProductOrderService) {
        $this->service = $service;
        $this->productOrderService = $ProductOrderService;
    }
    public function create(Request $request){
        $response['customers'] = $this->productOrderService->customers();
        // dd($response);
        return view('admin.order_dispatch.create', $response);
    } 
    public function store(Request $request){
        $data = $this->service->store($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.order-dispatch.index')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
        // return view('admin.order_dispatch.create-dispatch');
    } 
    public function index(Request $request){
        $response['customers'] = $this->productOrderService->customers();
        // dd($response);
        return view('admin.order_dispatch.index', $response);
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }

    public function view(Request $request){
        $data = $this->service->view($request);
        if (!$data) {
            return redirect()->back()->with('error', 'Dispatch not found');
        }
        return view('admin.order_dispatch.view', $data);
    }
    
    public function getOrderPackingData(Request $request){
        $response['data'] = $this->service->getOrderPackingData($request);
        return response()->json($response);
    }

    public function getOrdersByCustomer(Request $request){
        $response['data'] = $this->service->getOrdersByCustomer($request);
        return response()->json($response);
    }
}