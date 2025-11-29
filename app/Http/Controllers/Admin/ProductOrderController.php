<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\ProductOrderService as Service;
use App\Requests\Admin\ProductOrderStoreRequest;
use App\Requests\Admin\ProductOrderUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;
use PDF;
use App\Models\OrderProduct;
use App\Models\OrderProductDetailStock;

class ProductOrderController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(Request $request){
        $response['customers'] = $this->service->customers();
        $response['order_main_id'] = $request->id ?? 0;
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
    public function create(){
        $response['products'] = $this->service->products();
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

}