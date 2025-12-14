<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderDigitalizationService as Service;
use App\Services\Admin\ProductOrderService;
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
    public function __construct(Service $service, ProductOrderService $productOrderService ) {
        $this->service = $service;
        $this->productOrderService = $productOrderService;
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
    public function indexOrder(){
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.index-order',$response);
    } 
    
    public function indexListOrder(Request $request){
        return $this->service->indexListOrder($request);
    }
    public function indexOrderSet(Request $request){
        $response['order_main_id'] = $request->id ?? 0;
        $response['order_main'] = $this->service->orderMainDetails($request);
        $response['check_assign'] = $this->service->checkAssign($request);
        $response['cutting_units'] = $this->fabricReceiptService->cutting_units();
        return view('admin.product_order.index-order-set', $response);
    } 
    public function indexListOrderSet(Request $request){
        $response['order_main_id'] = $request->id ?? 0;
        return $this->service->indexListOrderSet($request);
    }
    public function create(){
        $response['products'] = $this->service->products();
        // dd( $response['products']);
        $response['product_size'] = $this->service->product_sizes();
        $response['colours'] = $this->service->getColours();
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.create',$response);
    } 
    public function createSlipsProduction(){
        $response['products'] = $this->productOrderService->products();
        // // dd( $response['products']);
        $response['product_size'] = $this->productOrderService->product_sizes();
        $response['colours'] = $this->productOrderService->getColours();
        $response['customers'] = $this->productOrderService->customers();
        return view('admin.order_digitalization.create-slips-production',$response);
    }
    public function store(ProductOrderStoreRequest $request){
        dd($request->all());
        
        $data = $this->service->store($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.product_order.indexOrder')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
        
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['products'] = $this->service->products();
        return view('admin.product_order.edit',$response);
    }
    public function update(ProductOrderUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.product_order.index')->withSuccess('The product order has been successfully updated.');
    }
    public function view(Request $request){
        $response['data'] = $this->service->view($request);
        return view('admin.product_order.view',$response);
    }
    
    // public function downloadCuttingSlip(Request $request){
    //     // dd($request->all());
    //     $res = OrderMain::with('customer')
    //     ->where('id', $request->id)
    //     ->first();
    //     $mainOrder = [
    //         'id'    => $res->id,
    //         'name'  => $res->sku,
    //         'expected_delivery_date'  => $res->expected_delivery_date,
    //         'company_name' => $res->customer->name,
    //         'corporate_order_file' => $res->corporate_order_file,
    //         'created_at'  =>  $res->created_at->format('d-m-Y'),
    //         'slip_no' => $res->id 
    //     ];
    //     $results = OrderCuttingStage::with([
    //         'productSet',
    //         'cuttingMaster'
    //     ])->where('order_main_id', $request->id)
    //     ->get();
    //     // dd($results);
    //     $master_name = '';
    //     $cuttingData = [];
    //     foreach($results as $res1){
    //         $color_data = MasterColor::where('id', $res1->productSet->color_id,)
    //             ->first();
    //         $master_name = $res1->cuttingMaster->cutting_master_name;
    //         $cuttingData[] = [
    //             'product_name' => $res1->sku,
    //             'remarks'  =>  $res1->remarks,
    //             'design_number' => $res1->productSet->design_number,
    //             'cuttingMaster'  =>  $master_name,
    //             'cutting_master_address' => $res1->cuttingMaster->address,
    //             'colour'  =>  $color_data->name,
    //             'set_size'  =>  $res1->productSet->set_size,
    //             'no_of_pcs'  =>  $res1->productSet->no_of_pcs,
    //             'set_quantity'  =>  $res1->productSet->set_quantity,
    //             'total_quantity'  =>  $res1->productSet->total_quantity,
    //             'delivery_time_allowed'  =>  $res1->delivery_time_allowed,
    //             // 'created_at'  =>  $res1->productSet->created_at,
    //         ];
    //     }
        
    //     $data = [
    //         'mainOrder' => $mainOrder,
    //         'cuttingData' => $cuttingData
    //     ];
    //     // dd($data);
    //     $pdf = PDF::loadView('admin.product_order.download-cutting-slip', $data);

    //     return $pdf->download('Cutting_Slip_'. $res->id .'-'. $master_name .'.pdf');
    // }
}