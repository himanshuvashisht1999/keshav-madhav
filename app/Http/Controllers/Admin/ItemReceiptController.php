<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\ItemReceiptService as Service;
use App\Requests\Admin\ItemReceiptStoreRequest;
use App\Requests\Admin\ItemReceiptUpdateRequest;
use App\Requests\Admin\ItemReceiptDetailStoreRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class ItemReceiptController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['vendors'] = $this->service->vendors();
        return view('admin.item_receipt.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['vendors'] = $this->service->vendors();
        return view('admin.item_receipt.create',$response);
    }
    public function store(ItemReceiptStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.item_receipt.detail',['id' => $data])->withSuccess('The item receipt has been successfully created.');
    }
    public function detail(Request $request){
        $response['data'] = $this->service->view($request);
        $response['vendors'] = $this->service->vendors();
        $response['items'] = $this->service->items();

        $response['new_batch_no'] = $this->service->new_batch_no();
 
        $request->merge(['vendor_id' => $response['data']->vendor_id]);
        $response['purchase_orders'] = $this->service->purchase_orders($request);
        return view('admin.item_receipt.detail',$response);
    }
    public function getPurchaseOrderItems($id)
    {
        $items = $this->service->purchase_order_items($id);
        return response()->json($items);
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['vendors'] = $this->service->vendors();

        return view('admin.item_receipt.edit',$response);
    }
    public function update(ItemReceiptUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.item_receipt.index')->withSuccess('The item receipt has been successfully updated.');
    }
    public function storeDetail(ItemReceiptDetailStoreRequest $request){
        $data = $this->service->storeDetail($request);
        return redirect()->route('admin.item_receipt.index')->withSuccess('The item receipt detail has been successfully created.');
        
    }
    public function view(Request $request){
        $response['data'] = $this->service->view($request);
        return view('admin.item_receipt.view',$response);
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.item_receipt.index')->withSuccess('The item receipt has been successfully deleted.'); 
    }

}
