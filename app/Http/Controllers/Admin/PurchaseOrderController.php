<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\PurchaseOrderService as Service;
use App\Requests\Admin\PurchaseOrderStoreRequest;
use App\Requests\Admin\PurchaseOrderUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class PurchaseOrderController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['vendors'] = $this->service->vendors();
        $response['fabrics'] = $this->service->fabrics();
        return view('admin.purchase_order.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function adjustment(Request $request){
        $response['vendors'] = $this->service->vendors();
        $vendor_id = $request->vendor_id ?? 0;
        $response['fabrics'] = $this->service->fabrics();
        $response['data'] = $this->service->adjustment($request);
        
        return view('admin.purchase_order.adjustment',$response);
    } 

    public function adjustmentShipment(Request $request){
        $data = $this->service->adjustmentShipment($request);
        return $data;
    } 

    public function estimation(){
        $response['products'] = $this->service->products();
        return view('admin.purchase_order.estimation',$response);
    }
    // public function estimation_store(){
    //     return redirect()->route('admin.purchase_order.create');
    // }

    public function create(Request $request){
        $vendor_id = $request->vendor_id ?? 1;
        $response['vendors'] = $this->service->vendors();
        // $response['fabrics'] = $this->service->fabrics();
        $response['selected_vendor_id'] = $vendor_id;
        
        $response['selected_fabric_id'] = $request->fabric_id ?? '';
        $response['selected_total_meter'] = $request->total_meter ?? 1;
        // $response['vendors'] = $this->service->vendor($vendor_id);
        $response['fabrics'] = $this->service->fabrics_per_vendor($vendor_id);
        $response['fabric_warehouses'] = $this->service->fabric_warehouses();
        return view('admin.purchase_order.create',$response);
    }

    public function vendorFabrics($vendor_id)
    {
        $fabrics = $this->service->fabrics_per_vendor($vendor_id);

        // return minimal JSON: id, name, sku (you used sku in JS)
        $payload = $fabrics->map(function($f){
            return [
                'id' => $f->id,
                'name' => $f->name,
                'sku' => $f->sku ?? '',
            ];
        });

        return response()->json($payload);
    }

    public function store(PurchaseOrderStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.purchase_order.index')->withSuccess('The purchase order has been successfully created.');
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['vendors'] = $this->service->vendors();
        $response['fabrics'] = $this->service->fabrics();
        return view('admin.purchase_order.edit',$response);
    }
    public function update(PurhcaseOrderUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.purchase_order.index')->withSuccess('The purchase order has been successfully updated.');
    }
    public function view(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['vendors'] = $this->service->vendors();
        $response['fabrics'] = $this->service->fabrics();
        $response['general_setting'] = $this->service->general_setting();
        return view('admin.purchase_order.view',$response);
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.purchase_order.index')->withSuccess('The purchase order has been successfully deleted.'); 
    }

    public function resend(Request $request){
        $data = $this->service->resend($request);
        return response()->json(['success' => true, 'message' => 'Purchase order resent successfully.']);

    }

}