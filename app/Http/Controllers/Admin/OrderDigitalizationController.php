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
        if(!empty($response['slip_data']['from_stage']['master_stage_id'])){
             $response['available_lots'] = $this->service->getAvailableLotsForStage($response['slip_data']['from_stage']['master_stage_id']);
        } else {
             $response['available_lots'] = [];
        }
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

    // public function cuttingMaster(Request $request){
    //     $response['cutting_slip'] = $this->service->cutting_slip($request);
    //     // dd($response['cutting_slip']);
    //     if($response['cutting_slip']){
    //         //// time allot
    //         $response['stages'] = $this->service->stages($response['cutting_slip']->getUnitMaster?->master_fabric_warehouse_id);

    //         //// rolls assign
    //         $response['master_fabric_warehouse'] = $this->service->master_fabric_warehouse($response['cutting_slip']->getUnitMaster?->master_fabric_warehouse_id);

           
    //         $response['product_size'] = $this->service->product_sizes();
    //         $response['colours'] = $this->productOrderService->getColours();
    //         $response['designs'] = $this->service->designs();

    //         /// stiching
    //         $master_fabric_warehouse_id = $response['cutting_slip']->getUnitMaster?->master_fabric_warehouse_id;
    //         $response['stiching_to_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,4);
    //         $response['printing_to_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,1);
    //         $response['embroidery_to_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,2);
    //         $response['cutting_data'] = $this->service->stage_unit_data($master_fabric_warehouse_id,3);
    //         $response['roll_numbers'] = $this->service->roll_numbers();
    //         $response['order_numbers'] = $this->service->order_numbers();

    //         $cutting_unit = $response['cutting_slip']->getUnitMaster?->id;
    //         $response['cutting_master_orders'] = $this->service->cutting_master_orders($cutting_unit);
    //     }

    //     return view('admin.order_digitalization.cutting_master',$response);
    // }

    public function cuttingMaster(Request $request){
        $production_slip_digitization = $this->service->cutting_slip($request);
        if($production_slip_digitization){
            $response['orders'] = $this->service->orders($production_slip_digitization->stage_master_unit_id);
            $response['lots_stitching'] = $this->service->getLotsBySlip($production_slip_digitization->id, 'stitching');
            $response['lots_printing'] = $this->service->getLotsBySlip($production_slip_digitization->id, 'printing');
            
            // NEW: Fetch Units
            $warehouse_id = $production_slip_digitization->getUnitMaster->master_fabric_warehouse_id ?? 0;
            $response['stitching_units'] = $this->service->getStageUnits($warehouse_id, 4); // 4 = Stitching
            $response['printing_units'] = $this->service->getStageUnits($warehouse_id, 1);  // 1 = Printing
            
            $cutting_unit = $production_slip_digitization->stage_master_unit_id;
            $response['cutting_master_orders'] = $this->service->cutting_master_orders($cutting_unit);
            
            // $response['next_stages'] = $this->service->getNextStages($production_slip_digitization->getUnitMaster->master_fabric_warehouse_id);
        }else{
            $response['orders'] = [];
            $response['lots_stitching'] = [];
            $response['lots_printing'] = [];
            // $response['next_stages'] = [];
            // $response['stitching_units'] = [];
            // $response['printing_units'] = [];
        }
        $response['cutting_slip'] = $production_slip_digitization;
        
        // Data for Time Allocation (independent of slip)
        $response['available_lots'] = $this->service->getLotsForTimeAllocation();
        $response['production_stages'] = $this->service->getProductionStages();

        return view('admin.order_digitalization.cutting_master',$response);
    }

    public function getLotDetails(Request $request)
    {
        $details = $this->service->getLotDetails($request->lot_no, $request->production_slip_digitization_id);
        return response()->json($details);
    }

    public function storeStitching(Request $request)
    {
        $result = $this->service->storeStitching($request);
        
        if ($result['status_code'] == 1) {
            return redirect()->route('admin.order_digitalization.cutting-master')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    public function storePrinting(Request $request)
    {
        $result = $this->service->storePrinting($request);
        
        if ($result['status_code'] == 1) {
            return redirect()->route('admin.order_digitalization.cutting-master')
                ->with('success', $result['message']);
        } else {
            return redirect()->back()
                ->with('error', $result['message']);
        }
    }

    public function getDesigns(Request $request){
        $response = $this->service->getDesigns($request);
        return response()->json($response);
    }

    public function getDesignDetails(Request $request){
        $response = $this->service->getDesignDetails($request);
        return response()->json($response);
    }

    public function getLotDetailsForDisplay(Request $request)
    {
        $details = $this->service->getLotDetailsForDisplay($request->lot_no);
        
        // Try alternative method if fabric/orders are empty
        if ($details && (empty($details['fabric_names']) || empty($details['order_numbers']))) {
            $alternative = $this->service->getLotDetailsAlternative($request->lot_no);
            if ($alternative) {
                if (empty($details['fabric_names']) && !empty($alternative['fabric_names'])) {
                    $details['fabric_names'] = $alternative['fabric_names'];
                }
                if (empty($details['order_numbers']) && !empty($alternative['order_numbers'])) {
                    $details['order_numbers'] = $alternative['order_numbers'];
                }
            }
        }
        
        return response()->json($details);
    }


    public function getLotDetailsForHandSlip(Request $request)
    {
        $details = $this->service->getLotDetailsForHandSlip($request->lot_no, $request->from_stage_id);
        return response()->json($details);
    }

    public function storeHandSlip(Request $request)
    {
        $result = $this->service->storeHandSlip($request);
        if ($result['status_code'] == 1) {
            return redirect()->route('admin.order_digitalization.create-slips-production')->withSuccess($result['message']);
        } else {
            return redirect()->back()->withError($result['message']);
        }
    }
}