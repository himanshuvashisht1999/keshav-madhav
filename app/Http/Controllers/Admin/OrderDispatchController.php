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
    public function createDispatch(Request $request){
        $response['customers'] = $this->productOrderService->customers();
        // $response['order_main_id'] = $request->id ?? 0;
        // // $response['order_main'] = $this->productOrderService->orderMainDetails($request);
        return view('admin.order_dispatch.create-dispatch', $response);
    } 
    public function store(Request $request){
        $data = $this->service->store($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.order_digitalization.create-slips-production')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
        // return view('admin.order_dispatch.create-dispatch');
    } 

    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function getCustomerOrders(Request $request){
        $response['data'] = $this->service->getCustomerOrders($request);
        return response()->json($response);
    }

    public function getOrdersDetails(Request $request){
        $response['data'] = $this->service->getOrdersDetails($request);
        return response()->json($response);
    }
    
 
}