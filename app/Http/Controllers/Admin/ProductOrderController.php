<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\ProductOrderService as Service;
use App\Requests\Admin\ProductOrderStoreRequest;
use App\Requests\Admin\ProductOrderUpdateRequest;
use App\Models\OrderProduct;
use App\Models\OrderProductDetailStock;
use App\Models\OrderCuttingStage;
use App\Models\OrderMain;
use App\Models\MasterColor;
use App\Models\OrderProductSet;
use Illuminate\Support\Facades\Crypt;
use Auth;
use PDF;



class ProductOrderController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(Request $request){
        $response['customers'] = $this->service->customers();
        $response['order_main_id'] = $request->id ?? 0;
        $response['order_main'] = $this->service->orderMainDetails($request);
        return view('admin.product_order.index',$response);
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
        $response['cutting_units'] = $this->service->cutting_units();
        $response['patterns'] = $this->service->getPatterns();
        // dd($response['patterns']);
        $response['fabrics'] = $this->service->fabrics();
        // dd( $response['fabrics']);
        $response['fittings'] = $this->service->fittings();
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
    public function store(ProductOrderStoreRequest $request){
        
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
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.product_order.index')->withSuccess('The product order has been successfully deleted.'); 
    }

    public function transfer(Request $request)
    {
        try {
            
            $result = $this->service->transfer($request);
            if($result = true){
                return redirect()->back()->with('success', 'The product order has been successfully transferred.');
            }else{
                return redirect()->back()->with('error', $result);
            }

            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function produce(Request $request){
        $response['data'] = $this->service->produce($request);
        return view('admin.product_order.produce',$response);
    }
    public function issueFabric(Request $request){
        $response['data'] = $this->service->issueFabric($request);
        $response['sub_stages_cutting'] = $this->service->sub_stages_cutting();
        return view('admin.product_order.issue_fabric',$response);
    }
    public function issueFabricPost(Request $request){
        $response= $this->service->issueFabricPost($request);
        if($response['status'] == 0){
           return redirect()->back()->with('error',$response['message']);
        }
        return redirect()->route('admin.product_order.produce',['id' => $response['order_id']])->withSuccess($response['message']); 
    }

    public function issueSlip(Request $request)
    {
        $orderProduct = OrderProduct::with('order')->where('id',$request->id)->first();
        $order = $orderProduct->order;
        $issuedRecords = OrderProductDetailStock::where('order_product_id', $orderProduct->id)
            ->with('stock')
            ->get();

        $issuedData = $issuedRecords->map(function($item) {
            return [
                'fabric_name' => $item->stock->sku ?? 'N/A',
                'roll_no' => $item->stock->unique_number ?? 'N/A',
                'meter' => $item->meter
            ];
        })->toArray();

        $pdfData = [
            'order' => $order,
            'orderProduct' => $orderProduct,
            'issuedData' => $issuedData,
            'issuer' => 'Stock Manager',
            'receiver' => $orderProduct->first_stage->stage->name ?? 'Next Stage',
        ];

        $pdf = \PDF::loadView('admin.product_order.fabric_combined_receipt', $pdfData)
            ->setPaper('A4', 'portrait');

        return $pdf->download('Fabric_Issue_Receive_Slip.pdf');
    }

    public function productStatusHoverData(Request $request){
        $response['data'] = $this->service->productStatusHoverData($request);
        return response()->json($response);
    }

    public function getCustomerSizes(Request $request){
        $response = $this->service->getCustomerSizes($request);
        return response()->json($response);
    }

    public function getCustomerDesign(Request $request){
        $response = $this->service->getCustomerDesign($request);
        return response()->json($response);
    }
    
    public function assign_to(Request $request){
        $response = $this->service->assign_to($request);
        if($response['status'] == true){
            return redirect()->back()->with('success', $response['message']);
        }else{
            return redirect()->back()->withError($response['message']);
        }
        
        
    }

    public function indexOrderSetDownload(Request $request)
    {
        $data = OrderProductSet::with([
            'order_cutting_stage.cutting_master.masterFabricWarehouse',
            'order_cutting_stage.fabric',
            'order_cutting_stage.pattern',
            'orderMain.customer',
            'colors',
            'sizeMeasurement',
            'order_cutting_stage.master_fitting',
        ])->where('id', $request->id)->firstOrFail();
        // dd($data);
        // ==============================
        // HEADER DATA
        // ==============================
        // dd($data);
        $cmpoHeader = [
            'cmpo_id'     => $data->id,
            'date'        => $data->created_at->format('d-m-Y'),
            'order_no'    => $data->orderMain->sku ?? '-',
            'customer'    => $data->orderMain->customer->name ?? '-',
            'design_no'   => $data->design_number ?? '-',
            'color'       => $data->colors->name ?? '-',
            'fabric'      => $data->order_cutting_stage->fabric->name ?? '-',
            'pattern'     => $data->order_cutting_stage->pattern->name ?? '-',
            'warehouse_name' => $data->order_cutting_stage->cutting_master->masterFabricWarehouse->cutting_master_name ?? '-',
            'cuttingMaster' => $data->order_cutting_stage->cutting_master->name ?? '-',
            'cuttingMasterAddress' => $data->order_cutting_stage->cutting_master->masterFabricWarehouse->address ?? '-',
            'fitting' => $data->order_cutting_stage?->master_fitting->name ?? '-',
            'remark' => $data->order_cutting_stage?->remarks ?? '-',
            'total_pcs' => $data->total_quantity ?? '0',
        ];

        // ==============================
        // SIZE-WISE DATA (LIKE CUTTING SLIP)
        // ==============================
        $sizeData = [];

        $sizes = [$data->set_size]; // fallback
        
        if (!empty($data->sizeMeasurement->size_group)) {
            $sizes = explode(',', $data->sizeMeasurement->size_group);
        }
        // dd($data);

        foreach ($sizes as $size) {
            $size = trim($size);

            if (!isset($sizeData[$size])) {
                $sizeData[$size] = [
                    'design_no' => $data->design_number,
                    'color'     => $data->colors->name,
                    'size'      => $size,
                    'pcs'       => 0,
                ];
            }

            // distribute quantity per size
            $sizeData[$size]['pcs'] += $data->set_quantity;
        }

        // ==============================
        // PDF
        // ==============================
        $pdf = Pdf::loadView(
            'admin.product_order.cmpo_slip',
            [
                'header'   => $cmpoHeader,
                'sizeData' => $sizeData,
            ]
        )->setPaper('a4', 'portrait');

        return $pdf->download('CMPO-' . $data->id . '.pdf');
    }

    public function downloadCuttingSlip(Request $request){
        // dd($request->all());
        $res = OrderMain::with('customer')
        ->where('id', $request->id)
        ->first();
        $mainOrder = [
            'id'    => $res->id,
            'name'  => $res->sku,
            'expected_delivery_date'  => $res->expected_delivery_date,
            'company_name' => $res->customer->name,
            'corporate_order_file' => $res->corporate_order_file,
            'created_at'  =>  $res->created_at->format('d-m-Y'),
            'slip_no' => $res->id 
        ];
        // $results = OrderCuttingStage::with([
        //     'productSet',
        //     'cuttingMaster'
        // ])->where('order_main_id', $request->id)
        // ->get();

        $results = OrderCuttingStage::with([
            'cuttingMaster',
            'productSet.sizeMeasurement'
        ])->where('order_main_id', $request->id)
        ->get();
            // dd($results);
        $master_name = $address = $remarks = '';
        $cuttingData = [];
        foreach($results as $res1){
            $color_data = MasterColor::where('id', $res1->productSet->color_id)
                ->first();
            $master_name = $res1->cuttingMaster->cutting_master_name;
            $address = $res1->cuttingMaster->address ?? '';
            $remarks = $res1->remarks ?? '';
            $sizes = [$res1->productSet->set_size];
            if (!empty($res1->productSet->sizeMeasurement->size_group)) {
                $sizes = explode(',', $res1->productSet->sizeMeasurement->size_group);

                foreach ($sizes as $size) {

                    $key = $res1->productSet->design_number . "-" . $color_data->name . "-" . trim($size);

                    // initialize if not exists
                    if (!isset($cuttingData[$key])) {
                        $cuttingData[$key] = [
                            'product_name' => $res1->sku,
                            'remarks'  => $remarks,
                            'design_number' => $res1->productSet->design_number,
                            'cuttingMaster' => $master_name,
                            'cutting_master_address' => $address,
                            'colour' => $color_data->name,
                            'set_size' => trim($size),
                            'no_of_pcs' => $res1->productSet->no_of_pcs,
                            'set_quantity' => 0,
                            'total_quantity' => 0,
                        ];
                    }

                    // accumulate values
                    $cuttingData[$key]['set_quantity'] += $res1->productSet->set_quantity;
                    $cuttingData[$key]['total_quantity'] += $res1->productSet->total_quantity;
                }
            }
            $till_allowed_time = date('Y-m-d h:i A', strtotime($res1['till_allowed_time']));
        }
        
        $data = [
            'mainOrder' => $mainOrder,
            'cuttingData' => $cuttingData,
            'till_allowed_time' => $till_allowed_time ?? '',
            'cuttingMaster' => [
                'cuttingMaster' => $master_name,
                'cutting_master_address' => $address,
                'remarks' => $remarks
            ],
        ];
        
        $pdf = PDF::loadView('admin.product_order.download-cutting-slip', $data);

        return $pdf->download('Cutting_Slip_'. $res->id .'-'. $master_name .'.pdf');
    }

    public function saveCustomSetSize(Request $request){
        $response = $this->service->saveCustomSetSize($request);
        return response()->json($response);
    }

    // public function getCuttingUnit(Request $request){
    //     $response = $this->service->getCuttingUnit($request);
    //     return response()->json($response);
    // }
}