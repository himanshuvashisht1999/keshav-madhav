<?php

namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\PurchaseOrderMaterialService as Service;
use App\Requests\Admin\PurchaseOrderMaterialStoreRequest;
use App\Requests\Admin\PurchaseOrderMaterialUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class PurchaseOrderMaterialController extends Controller{
        protected $service; 

        public function __construct(Service $service){
          $this->service = $service;
        }
        public function index(){
          $response['vendors'] = $this->service->vendors();
          $response['items'] = $this->service->items();
          return view('admin.purchase_order_material.index', $response);
        }
        public function indexList(Request $request){
          return $this->service->indexList($request);
        }
        public function create(){
          $response['vendors'] = $this->service->vendors();
          $response['items'] = $this->service->items();
          return view('admin.purchase_order_material.create', $response);
        }
        public function store(PurchaseOrderMaterialStoreRequest $request){
          $data = $this->service->store($request);
          return redirect()->route('admin.purchase_order_material.index')->withSuccess('The purchase order material has been successfully created.');
        }
        public function edit(Request $request){
          $response['data'] = $this->service->edit($request);
          $response['vendors'] = $this->service->vendors();
          $response['items'] = $this->service->items();
          return view('admin.purchase_order_material.edit',$response);
        }
        public function update(PurchaseOrderMaterialUpdateRequest $request){
          $data = $this->service->update($request);
          return redirect()->route('admin.purchase_order_material.index')->withSuccess('The purchase order material has been successfully updated.');
        }
        public function view(Request $request){
          $response['data'] = $this->service->view($request);
          $response['vendors'] = $this->service->vendors();
          $response['items'] = $this->service->items();
          $response['general_setting'] = $this->service->general_setting();
          return view('admin.purchase_order_material.view',$response);
        }
        public function delete(Request $request){
          $data = $this->service->delete($request);
          return redirect()->route('admin.purchase_order_material.index')->withSuccess('The purchase order material has been successfully deleted.');
        }

}