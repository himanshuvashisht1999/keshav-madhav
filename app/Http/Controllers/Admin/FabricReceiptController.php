<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\FabricReceiptService as Service;
use App\Requests\Admin\FabricReceiptStoreRequest;
use App\Requests\Admin\FabricReceiptUpdateRequest;
use App\Requests\Admin\FabricReceiptDetailStoreRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class FabricReceiptController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['vendors'] = $this->service->vendors();
        return view('admin.fabric_receipt.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['vendors'] = $this->service->vendors();
        return view('admin.fabric_receipt.create',$response);
    }
    public function store(FabricReceiptStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.fabric_receipt.detail',['id' => $data])->withSuccess('The fabric receipt has been successfully created.');
    }
    public function detail(Request $request){
        $response['data'] = $this->service->view($request);
        $response['vendors'] = $this->service->vendors();
        $request->merge(['vendor_id' => $response['data']->vendor_id]);
        $response['purchase_orders'] = $this->service->purchase_orders($request);
        if($response['purchase_orders']){
            if($response['purchase_orders'][0]){
                $purchase_order_id = $response['purchase_orders'][0]->id;
            }
        }else{
            $purchase_order_id = 0;
        }
        $response['purchase_order_items'] = $this->service->purchase_order_items($purchase_order_id);
        return view('admin.fabric_receipt.detail',$response);
    }
    public function getPurchaseOrderItems($id)
    {
        $items = $this->service->purchase_order_items($id);
        return response()->json($items);
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['vendors'] = $this->service->vendors();

        return view('admin.fabric_receipt.edit',$response);
    }
    public function update(FabricReceiptUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.fabric_receipt.index')->withSuccess('The fabric receipt has been successfully updated.');
    }
    public function storeDetail(FabricReceiptDetailStoreRequest $request){
        $data = $this->service->storeDetail($request);
        return redirect()->route('admin.fabric_receipt.detail',['id' => $request->id])->withSuccess('The fabric receipt detail has been successfully created.');
        
    }
    public function view(Request $request){
        $response['data'] = $this->service->view($request);
        return view('admin.fabric_receipt.view',$response);
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.fabric_receipt.index')->withSuccess('The fabric receipt has been successfully deleted.'); 
    }

}