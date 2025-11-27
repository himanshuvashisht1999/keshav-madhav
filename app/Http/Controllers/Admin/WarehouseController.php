<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\WarehouseService as Service;

class WarehouseController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(Request $request){
        $response['customers'] = $this->service->customers();
        $response['order_main_id'] = $request->id ?? 0;
        return view('admin.warehouse.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function indexOrder(){
        $response['customers'] = $this->service->customers();
        return view('admin.warehouse.index-order',$response);
    } 
    public function indexListOrder(Request $request){
        return $this->service->indexListOrder($request);
    }

    public function view(Request $request){
        $response['data'] = $this->service->view($request);
        return view('admin.warehouse.view',$response);
    }

    public function produce(Request $request){
        $response['data'] = $this->service->produce($request);
        return view('admin.warehouse.produce',$response);
    }
    public function productStatusHoverData(Request $request){
        $response['data'] = $this->service->productStatusHoverData($request);
        return response()->json($response);
    }

    public function listing(){ 
        $response['product_stage'] = $this->service->product_stage();
        $response['master_blocks'] = $this->service->master_blocks();
        return view('admin.warehouse.listing',$response);
    }

    public function indexListListing(Request $request){
        return $this->service->indexListListing($request);
    }

}