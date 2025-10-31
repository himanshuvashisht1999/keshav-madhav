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
class ProductOrderController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['products'] = $this->service->products();
        $response['customers'] = $this->service->customers();
        return view('admin.product_order.create',$response);
    }
    public function store(ProductOrderStoreRequest $request){
        $data = $this->service->store($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.product_order.index')->withSuccess($data['message']);
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
        return view('admin.product_order.issue_fabric',$response);
    }
    public function issueFabricPost(Request $request){
        $response= $this->service->issueFabricPost($request);
        if($response['status'] == 0){
           return redirect()->back()->with('error',$response['message']);
        }
        return redirect()->route('admin.product_order.index')->withSuccess($response['message']); 
    }

    public function issueSlip(Request $request)
    {
       
        $filters = $request->all();

        
         $data = [
            'order_no' => 'ORD-2025-0012',
            'customer_name' => 'ABC Textiles',
            'order_date' => now()->format('d M Y'),
            'order_time' => now()->format('H:i'),
            'product_sku' => 'PRD-1001',
            'product_qty' => '120 pcs',
            'fabric_sku' => 'FAB-A23',
            'fabric_qty' => '30.50 m',
            'items' => [
                ['name' => 'Shirt - Blue', 'sku' => 'PRD-1001', 'qty' => 60],
            ],
        ];

        $pdf = PDF::loadView('admin.pdf.order_slip', $data)
            ->setPaper('a4', 'portrait');

        return $pdf->stream('order_slip.pdf');
        // return $pdf->download('order_slip.pdf');
    }

    public function productStatusHoverData(Request $request){
        $response['data'] = $this->service->productStatusHoverData($request);
        return response()->json($response);
    }

}