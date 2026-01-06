<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderDigitalizationService as Service;
use App\Services\Admin\ProductOrderService;
use App\Services\Admin\FabricReceiptService;

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
        $response['product_size'] = $this->service->product_sizes();
        // dd($response['product_size']);
        $response['colours'] = $this->productOrderService->getColours();
        $response['customers'] = $this->productOrderService->customers();
        $response['slip_data'] = $this->service->getSlipDigitalization();
        $response['skip_slip_data'] = $this->service->getSkipSlips();
        // dd($response['slip_data']);
        /// roll assign 
        return view('admin.order_digitalization.create-slips-production',$response);
    }

    public function createRollsAssign(){
        $response['order_no_data'] = $this->service->orderMainForRollAssign();
        $response['cutting_units'] = $this->fabricReceiptService->cutting_units();
        // dd($response['cutting_units']);
        $response['fabrics'] = $this->service->getFabricsData();
        $response['slip_data'] = $this->service->getSlipDigitalization();
        $response['skip_slip_data'] = $this->service->getSkipSlips();
        // dd($response['slip_data']);
        return view('admin.order_digitalization.create-rolls-assign', $response);
    }

    public function createTimeAllocation(){
        $response['order_no_data'] = $this->service->orderMainForRollAssign();
        $response['cutting_units'] = $this->fabricReceiptService->cutting_units();
        // dd($response['cutting_units']);
        $response['fabrics'] = $this->service->getFabricsData();
        $response['slip_data'] = $this->service->getSlipDigitalization();
        $response['skip_slip_data'] = $this->service->getSkipSlips();
        // dd($response['slip_data']);
        return view('admin.order_digitalization.create-time-allocation', $response);
    }
    public function storeProductionSlipDigitization(Request $request){
        $data = $this->service->storeProductionSlipDigitization($request);
        if($data['status_code'] == 1){
            return redirect()->back()->withSuccess($data['message']);
          
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }
    public function storeTimeAllocation(Request $request){
        $data = $this->service->storeTimeAllocation($request);
        if($data['status_code'] == 1){
            return redirect()->back()->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }
    public function storeRollsAssign(Request $request){
        $data = $this->service->storeRollsAssign($request);
        if($data['status_code'] == 1){
            return redirect()->back()->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }

    public function skip(Request $request){
        $data = $this->service->skip($request);
        if($data['status_code'] == 1){
            return redirect()->back()->withSuccess($data['message']);
            
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }

    public function deleteSlip(Request $request){
        $data = $this->service->deleteSlip($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.order_digitalization.create-slips-production')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }

    public function addSkipSlips(Request $request){
        $data = $this->service->addSkipSlips($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.order_digitalization.create-slips-production')->withSuccess($data['message']);
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

    public function cuttingMaster(Request $request){
        $response['cutting_slip'] = $this->service->cutting_slip($request);
        if($response['cutting_slip']){
            //// time allot
            $response['stages'] = $this->service->stages($response['cutting_slip']->getUnitMaster?->master_fabric_warehouse_id);

            //// rolls assign
            $response['master_fabric_warehouse'] = $this->service->master_fabric_warehouse($response['cutting_slip']->getUnitMaster?->master_fabric_warehouse_id);

           
            $response['product_size'] = $this->service->product_sizes();
            $response['colours'] = $this->productOrderService->getColours();
            $response['designs'] = $this->service->designs();

            /// stiching
            $master_fabric_warehouse_id = $response['cutting_slip']->getUnitMaster?->master_fabric_warehouse_id;
            $response['stiching_to_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,4);
            $response['printing_to_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,1);
            $response['embroidery_to_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,2);
            $response['cutting_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,3);
            $response['roll_numbers'] = $this->service->roll_numbers();
            $response['order_numbers'] = $this->service->order_numbers();

        }

        return view('admin.order_digitalization.cutting_master',$response);
    }
}