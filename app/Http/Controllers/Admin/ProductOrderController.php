<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\ProductOrderService as Service;
use App\Requests\Admin\ProductOrderStoreRequest;
use App\Requests\Admin\ProductOrderUpdateRequest;
use App\Models\ProductionPO;
use Illuminate\Support\Facades\DB;
use App\Models\OrderProduct;
use App\Models\OrderProductDetailStock;
use App\Models\OrderCuttingStage;
use App\Models\OrderMain;
use App\Models\MasterColor;
use App\Models\OrderProductSet;
use Illuminate\Support\Facades\Crypt;
use Auth;
use PDF;



class ProductOrderController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function index(Request $request)
    {
        $response['customers'] = $this->service->customers();
        $response['order_main_id'] = $request->id ?? 0;
        $response['order_main'] = $this->service->orderMainDetails($request);
        return view('admin.product_order.index', $response);
    }
    public function indexList(Request $request)
    {
        return $this->service->indexList($request);
    }
    public function indexOrder()
    {
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.index-order', $response);
    }

    public function indexListOrder(Request $request)
    {
        return $this->service->indexListOrder($request);
    }
    public function indexOrderSet(Request $request)
    {
        $response['order_main_id'] = $request->id ?? 0;
        $response['order_main'] = $this->service->orderMainDetails($request);
        $response['check_assign'] = $this->service->checkAssign($request);
        $response['cutting_units'] = $this->service->cutting_units();
        $response['printing_units'] = $this->service->printing_units();
        $response['patterns'] = $this->service->getPatterns();
        // dd($response['patterns']);
        $response['fabrics'] = $this->service->fabrics();
        // dd( $response['fabrics']);
        $response['fittings'] = $this->service->fittings();
        $response['vendors'] = \App\Models\Vendor::where('status', 1)->get();
        $response['customers'] = \App\Models\MasterCustomer::where('status', 1)->get();
        return view('admin.product_order.index-order-set', $response);
    }
    public function indexListOrderSet(Request $request)
    {
        $response['order_main_id'] = $request->id ?? 0;
        return $this->service->indexListOrderSet($request);
    }
    public function create()
    {
        // $response['products'] = $this->service->products();
        // $response['product_size'] = $this->service->product_sizes();
        // $response['colours'] = $this->service->getColours();
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.create', $response);
    }
    public function master_data()
    {
        $response['products'] = $this->service->products();
        $response['sizes'] = $this->service->product_sizes();
        $response['colours'] = $this->service->getColours();
        $response['cutting_units'] = $this->service->cutting_units();
        $response['fabrics'] = $this->service->fabrics();
        $response['fittings'] = $this->service->fittings();
        $response['patterns'] = $this->service->getPatterns();
        return response()->json($response);
    }
    public function store(ProductOrderStoreRequest $request)
    {

        $data = $this->service->store($request);
        if ($data['status_code'] == 1) {
            return redirect()->route('admin.product_order.indexOrder')->withSuccess($data['message']);
        } else {
            return redirect()->back()->withError($data['message']);
        }

    }

    public function createDomestic()
    {
        $response['cutting_units'] = $this->service->cutting_units();
        $response['fabrics'] = $this->service->fabrics();
        $response['fittings'] = $this->service->fittings();
        $response['patterns'] = $this->service->getPatterns();
        $response['sizes'] = $this->service->product_sizes();
        $response['colours'] = $this->service->getColours();
        $response['products'] = $this->service->products();
        return view('admin.product_order.create_domestic', $response);
    }

    public function storeDomestic(Request $request)
    {
        $data = $this->service->storeDomestic($request);
        if ($data['status_code'] == 1) {
            return redirect()->route('admin.product_order.indexOrder')->withSuccess($data['message']);
        } else {
            return redirect()->back()->withError($data['message']);
        }
    }

    public function edit(Request $request)
    {
        $response['data'] = $this->service->edit($request);
        $response['products'] = $this->service->products();
        return view('admin.product_order.edit', $response);
    }
    public function update(Request $request)
    {
        $response = $this->service->update($request);
        return redirect()->route('admin.product_order.index');
    }

    public function editOrderMain($id)
    {
        $response['data'] = $this->service->editOrderMain($id);
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.edit-order-main', $response);
    }

    public function updateOrderMain(Request $request, $id)
    {
        $response = $this->service->updateOrderMain($request, $id);
        if ($response['status_code'] == 1) {
            return redirect()->route('admin.product_order.indexOrder')->with('success', $response['message']);
        } else {
            return back()->with('error', $response['message']);
        }
    }
    public function view(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.product_order.view', $response);
    }
    public function delete(Request $request)
    {
        $data = $this->service->delete($request);
        return redirect()->route('admin.product_order.index')->withSuccess('The product order has been successfully deleted.');
    }

    public function deleteOrderMain(Request $request)
    {
        $data = $this->service->deleteOrderMain($request);
        if ($data['status_code'] == 1) {
            return redirect()->route('admin.product_order.indexOrder')->withSuccess($data['message']);
        } else {
            return redirect()->route('admin.product_order.indexOrder')->withError($data['message']);
        }
    }

    public function transfer(Request $request)
    {
        try {

            $result = $this->service->transfer($request);
            if ($result = true) {
                return redirect()->back()->with('success', 'The product order has been successfully transferred.');
            } else {
                return redirect()->back()->with('error', $result);
            }


        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function produce(Request $request)
    {
        $response['data'] = $this->service->produce($request);
        return view('admin.product_order.produce', $response);
    }
    public function issueFabric(Request $request)
    {
        $response['data'] = $this->service->issueFabric($request);
        $response['sub_stages_cutting'] = $this->service->sub_stages_cutting();
        return view('admin.product_order.issue_fabric', $response);
    }
    public function issueFabricPost(Request $request)
    {
        $response = $this->service->issueFabricPost($request);
        if ($response['status'] == 0) {
            return redirect()->back()->with('error', $response['message']);
        }
        return redirect()->route('admin.product_order.produce', ['id' => $response['order_id']])->withSuccess($response['message']);
    }

    public function issueSlip(Request $request)
    {
        $orderProduct = OrderProduct::with('order')->where('id', $request->id)->first();
        $order = $orderProduct->order;
        $issuedRecords = OrderProductDetailStock::where('order_product_id', $orderProduct->id)
            ->with('stock')
            ->get();

        $issuedData = $issuedRecords->map(function ($item) {
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

    public function productStatusHoverData(Request $request)
    {
        $response['data'] = $this->service->productStatusHoverData($request);
        return response()->json($response);
    }

    public function getCustomerSizes(Request $request)
    {
        $response = $this->service->getCustomerSizes($request);
        return response()->json($response);
    }

    public function getCustomerDesign(Request $request)
    {
        $response = $this->service->getCustomerDesign($request);
        return response()->json($response);
    }

    public function assign_to(Request $request)
    {
        $response = $this->service->assign_to($request);

        if ($request->ajax()) {
            return response()->json($response);
        }

        if ($response['status'] == true) {
            return redirect()->back()->with('success', $response['message']);
        } else {
            return redirect()->back()->withError($response['message']);
        }
    }

    public function deleteAssignment(Request $request)
    {
        $response = $this->service->deleteAssignment($request);
        return response()->json($response);
    }
    public function createPO(Request $request)
    {
        $response = $this->service->createPO($request);
        return response()->json($response);
    }
    public function downloadPO(Request $request)
    {
        $id = $request->id;
        $po = OrderCuttingStage::with(['vendor', 'customer', 'productSet.colors'])->findOrFail($id);

        $pdf = \PDF::loadView('admin.product_order.download-po', compact('po'));
        return $pdf->download('PO_' . $po->sku . '.pdf');
    }

    public function bulkPO()
    {
        $response['vendors'] = \App\Models\Vendor::where('status', 1)->get();
        $response['customers'] = \App\Models\MasterCustomer::where('status', 1)->get();
        $response['fabrics'] = $this->service->fabrics();
        $response['fittings'] = $this->service->fittings();
        $response['patterns'] = $this->service->getPatterns();
        return view('admin.product_order.bulk-po', $response);
    }

    public function getUnassignedSets(Request $request)
    {
        $query = OrderProductSet::with(['orderMain', 'colors'])
            ->where('remain_total_quantity', '>', 0)
            ->where('status', '!=', 2); // Not fully assigned/PO

        if ($request->ids && is_array($request->ids)) {
            $query->whereIn('id', $request->ids);
        }

        if ($request->order_id) {
            $query->where('order_main_id', $request->order_id);
        }

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('design_number', 'LIKE', "%{$request->search}%")
                    ->orWhere('sku', 'LIKE', "%{$request->search}%");
            });
        }

        $sets = $query->limit(50)->get();

        return response()->json($sets);
    }

    public function getUnassignedOrders(Request $request)
    {
        $query = OrderMain::whereHas('productSets', function ($q) {
            $q->where('remain_total_quantity', '>', 0)
                ->where('status', '!=', 2);
        });

        if ($request->search) {
            $query->where('sku', 'LIKE', "%{$request->search}%");
        }

        $orders = $query->limit(20)->get();
        return response()->json($orders);
    }

    public function storeBulkPO(Request $request)
    {
        $response = $this->service->storeBulkPO($request);
        return response()->json($response);
    }

    public function poList(Request $request)
    {
        $query = ProductionPO::with(['vendor', 'customer', 'orderMain', 'items']);

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                  ->orWhereHas('orderMain', function($o) use ($search) {
                      $o->where('sku', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('vendor_id')) {
            $query->where('vendor_id', $request->get('vendor_id'));
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->get('customer_id'));
        }

        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->get('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->get('end_date'));
        }

        $total_quantity = (clone $query)->get()->sum(function($po) {
            return $po->items->sum('quantity');
        });

        $pos = $query->orderBy('created_at', 'desc')->paginate(20);
        
        $vendors = \App\Models\Vendor::where('status', 1)->orderBy('name')->get();
        $customers = \App\Models\MasterCustomer::where('status', 1)->orderBy('name')->get();

        return view('admin.product_order.po-list', compact('pos', 'vendors', 'customers', 'total_quantity'));
    }

    public function viewBulkPO($id)
    {
        $po = ProductionPO::with(['vendor', 'customer', 'orderMain', 'items.productSet', 'items.pattern', 'items.master_fitting'])->findOrFail($id);
        $general_setting = \App\Models\GeneralSettings::first();
        return view('admin.product_order.po-view', compact('po', 'general_setting'));
    }

    public function deletePO($id)
    {
        DB::beginTransaction();
        try {
            $po = ProductionPO::findOrFail($id);
            // Restore quantities to OrderProductSet before deleting items
            foreach ($po->items as $item) {
                $set = OrderProductSet::find($item->set_product_id);
                if ($set) {
                    $set->remain_total_quantity += $item->quantity;
                    $set->status = 1; // Back to active/partial
                    $set->save();
                }
                $item->delete();
            }
            $po->delete();
            DB::commit();
            return response()->json(['status' => true, 'message' => 'PO deleted successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['status' => false, 'message' => $e->getMessage()]);
        }
    }

    public function downloadBulkPO($id)
    {
        $po = ProductionPO::with(['vendor', 'customer', 'orderMain', 'items.productSet', 'items.pattern', 'items.master_fitting'])->findOrFail($id);
        $general_setting = \App\Models\GeneralSettings::first();

        $pdf = \PDF::loadView('admin.product_order.download-bulk-po', compact('po', 'general_setting'));
        return $pdf->download($po->po_number . '.pdf');
    }

    public function editBulkPO($id)
    {
        $po = ProductionPO::with(['vendor', 'customer', 'orderMain', 'items.productSet', 'items.pattern', 'items.master_fitting'])->findOrFail($id);
        $response['po'] = $po;
        $response['vendors'] = \App\Models\Vendor::where('status', 1)->get();
        $response['customers'] = \App\Models\MasterCustomer::where('status', 1)->get();
        $response['fabrics'] = $this->service->fabrics();
        $response['fittings'] = $this->service->fittings();
        $response['patterns'] = $this->service->getPatterns();
        return view('admin.product_order.bulk-po-edit', $response);
    }

    public function updateBulkPO(Request $request, $id)
    {
        $response = $this->service->updateBulkPO($request, $id);
        return response()->json($response);
    }

    public function indexOrderSetDownload(Request $request)
    {
        // $data = OrderProductSet::with([
        //     'stage_master_unit',
        //     'fabric',
        //     'master_design_pattern',
        //     'orderMain.customer',
        //     'colors',
        //     'size_measurement',
        //     'master_product_fitting',
        // ])->where('id', $request->id)->firstOrFail();

        // $cmpoHeader = [
        //     'cmpo_id'     => $data->id,
        //     'date'        => $data->created_at->format('d-m-Y'),
        //     'order_no'    => $data->orderMain->sku ?? '-',
        //     'customer'    => $data->orderMain->customer->name ?? '-',
        //     'design_no'   => $data->design_number ?? '-',
        //     'color'       => $data->colors->name ?? '-',
        //     'fabric'      => $data->fabric->name ?? '-',
        //     'pattern'     => $data->master_design_pattern->name ?? '-',
        //     'warehouse_name' => $data->stage_master_unit->masterFabricWarehouse->cutting_master_name ?? '-',
        //     'cuttingMaster' => $data->stage_master_unit->name ?? '-',
        //     'cuttingMasterAddress' => $data->stage_master_unit->masterFabricWarehouse->address ?? '-',
        //     'fitting' => $data->master_product_fitting?->name ?? '-',
        //     'remark' => $data->remark ?? '-',
        //     'total_pcs' => $data->total_quantity ?? '0',
        // ];

        // // ==============================
        // // SIZE-WISE DATA (LIKE CUTTING SLIP)
        // // ==============================
        // $sizeData = [];

        // $sizes = [$data->set_size]; // fallback

        // if (!empty($data->size_measurement->size_group)) {
        //     $sizes = explode(',', $data->size_measurement->size_group);
        // }

        // foreach ($sizes as $size) {
        //     $size = trim($size);

        //     if (!isset($sizeData[$size])) {
        //         $sizeData[$size] = [
        //             'design_no' => $data->design_number,
        //             'color'     => $data->colors->name,
        //             'size'      => $size,
        //             'pcs'       => 0,
        //         ];
        //     }

        //     // distribute quantity per size
        //     $sizeData[$size]['pcs'] += $data->set_quantity;
        // }
        $slip_data = $this->buildCmpoData($request->id);
        // ==============================
        // PDF
        // ==============================
        $pdf = Pdf::loadView(
            'admin.product_order.cmpo_slip',
            [
                'header' => $slip_data['cmpoHeader'],
                'sizeData' => $slip_data['sizeData'],
                'assignments' => $slip_data['assignments'],
            ]
        )->setPaper('a4', 'portrait');

        return $pdf->download('CMPO-' . $request->id . '.pdf');
    }

    public function viewCuttingSlip(Request $request)
    {
        $slip_data = $this->buildCmpoData($request->id);
        $response = [
            'header' => $slip_data['cmpoHeader'],
            'sizeData' => $slip_data['sizeData'],
            'assignments' => $slip_data['assignments'],
        ];
        return view('admin.product_order.view_cmpo_slip', $response);
    }

    public function downloadCuttingSlip(Request $request)
    {
        // dd($request->all());
        $res = OrderMain::with('customer')
            ->where('id', $request->id)
            ->first();
        $mainOrder = [
            'id' => $res->id,
            'name' => $res->sku,
            'expected_delivery_date' => $res->expected_delivery_date,
            'company_name' => $res->customer->name,
            'corporate_order_file' => $res->corporate_order_file,
            'created_at' => $res->created_at->format('d-m-Y'),
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
        foreach ($results as $res1) {
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
                            'remarks' => $remarks,
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

        return $pdf->download('Cutting_Slip_' . $res->id . '-' . $master_name . '.pdf');
    }

    public function saveCustomSetSize(Request $request)
    {
        $response = $this->service->saveCustomSetSize($request);
        return response()->json($response);
    }

    // public function getCuttingUnit(Request $request){
    //     $response = $this->service->getCuttingUnit($request);
    //     return response()->json($response);
    // }

    private function buildCmpoData(int $id): array
    {
        $data = OrderProductSet::with([
            'stage_master_unit.masterFabricWarehouse',
            'fabric',
            'master_design_pattern',
            'orderMain.customer',
            'colors',
            'size_measurement',
            'master_product_fitting',
            'orderCuttingStages.cutting_master',
            'printing_unit',
        ])->findOrFail($id);

        $assignments = $data->orderCuttingStages;

        $firstAssignment = $assignments->first();

        // ================= HEADER =================
        $cmpoHeader = [
            'cmpo_id' => $data->id,
            'date' => $data->created_at->format('d-m-Y'),
            'order_no' => $data->orderMain->sku ?? '-',
            'customer' => $data->orderMain->customer->name ?? '-',
            'design_no' => $data->design_number ?? '-',
            'color' => $data->colors->name ?? '-',
            // Fallback for fields blank until 100% assignment
            'fabric' => $data->fabric_names ?? ($firstAssignment->fabric_names ?? '-'),
            'pattern' => $data->master_design_pattern->name ?? ($firstAssignment->pattern->name ?? '-'),
            'warehouse_name' => $data->stage_master_unit->masterFabricWarehouse->cutting_master_name ?? ($firstAssignment->cutting_master->masterFabricWarehouse->cutting_master_name ?? '-'),
            'cuttingMaster' => $data->stage_master_unit->name ?? ($firstAssignment->cutting_master->name ?? '-'),
            'cuttingMasterAddress' => $data->stage_master_unit->masterFabricWarehouse->address ?? ($firstAssignment->cutting_master->masterFabricWarehouse->address ?? '-'),
            'fitting' => $data->master_product_fitting?->name ?? ($firstAssignment->master_fitting?->name ?? '-'),
            'remark' => $data->remark ?? ($firstAssignment->remarks ?? '-'),
            'belt' => $firstAssignment->belt ?? '-',
            'size_set' => $data->size_measurement?->name ?? '-',
            'pcs_in_set' => $data->no_of_pcs ?? 0,
            'total_pcs' => $data->total_quantity ?? 0,
            'printing_unit_name' => $data->printing_unit ? $data->printing_unit->name : '-',
        ];

        // ================= SIZE DATA =================
        $sizeData = [];

        $sizes = [$data->set_size];

        if (!empty($data->size_measurement?->size_group)) {
            $sizes = array_map('trim', explode(',', $data->size_measurement->size_group));
        }

        /* size count */
        $totalInRatio = count($sizes);
        $sizeCounts = array_count_values($sizes);

        foreach ($sizeCounts as $size => $count) {
            $sizeData[$size] = [
                'design_no' => $data->design_number,
                'color' => $data->colors->name,
                'size' => $size,
                'pcs' => $totalInRatio > 0 ? ($count * $data->total_quantity) / $totalInRatio : 0,
            ];
        }

        return [
            'cmpoHeader' => $cmpoHeader,
            'sizeData' => $sizeData,
            'assignments' => $assignments
        ];
    }

}