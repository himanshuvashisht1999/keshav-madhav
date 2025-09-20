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
    public function create(){
        $response['vendors'] = $this->service->vendors();
        $response['fabrics'] = $this->service->fabrics();
        return view('admin.purchase_order.create',$response);
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

}