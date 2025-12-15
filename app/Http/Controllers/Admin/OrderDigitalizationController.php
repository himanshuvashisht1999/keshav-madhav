<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderDigitalizationService as Service;
use App\Services\Admin\ProductOrderService;
use App\Services\Admin\FabricReceiptService;
use App\Requests\Admin\OrderDigitalizationStoreRequest;
use App\Requests\Admin\OrderDigitalizationUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;
use PDF;
use App\Models\OrderProduct;
use App\Models\OrderProductDetailStock;
use App\Models\OrderCuttingStage;
use App\Models\OrderMain;
use App\Models\MasterColor;



class OrderDigitalizationController extends Controller { 
    protected $service;
    public function __construct(Service $service, ProductOrderService $productOrderService, FabricReceiptService $fabricReceiptService ) {
        $this->service = $service;
        $this->productOrderService = $productOrderService;
        $this->fabricReceiptService = $fabricReceiptService;

    }
    public function index_slip_production(Request $request){
        // $response['customers'] = $this->productOrderService->customers();
        // $response['order_main_id'] = $request->id ?? 0;
        // // $response['order_main'] = $this->productOrderService->orderMainDetails($request);
        return view('admin.order_digitalization.index-slip-production');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
 
    public function createSlipsProduction(){
        $response['products'] = $this->productOrderService->products();
        // // dd( $response['products']);
        $response['product_size'] = $this->productOrderService->product_sizes();
        $response['colours'] = $this->productOrderService->getColours();
        $response['customers'] = $this->productOrderService->customers();
        $response['slip_img'] = 'production-slip-8640_1765712156.jpg';
        return view('admin.order_digitalization.create-slips-production',$response);
    }

    public function createRollsAssign(){
        $response['order_no_data'] = $this->service->orderMainForRollAssign();
        $response['cutting_units'] = $this->fabricReceiptService->cutting_units();
        $response['fabrics'] = $this->service->getFabricsData();
        $response['slip_img'] = 'production-slip-8640_1765712156.jpg';
        return view('admin.order_digitalization.create-rolls-assign', $response);
    }
    
    public function store(Request $request){
        
        $data = $this->service->store($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.order_digitalization.create-rolls-assign')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
        
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['products'] = $this->service->products();
        return view('admin.product_order.edit',$response);
    }
    public function update(Request $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.product_order.index')->withSuccess('The product order has been successfully updated.');
    }
    public function view(Request $request){
        $response['data'] = $this->service->view($request);
        return view('admin.product_order.view',$response);
    }
    
    public function getRollsData(Request $request){
        $response = $this->service->getRollsData($request);
        return response()->json($response);
    }
}