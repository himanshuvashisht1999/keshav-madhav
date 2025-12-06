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
    // public function packaging(Request $request){
    //     $check_status = package_box_show($request->order_id);
    //     if($check_status == 0){
    //         return redirect()->back()->with('error', 'Request Failed');
    //     }
    //     $response['order_data'] = $this->service->order_data($request->order_id);
    //     if($response['order_data']){
    //         $response['product_types'] = $this->service->product_types($request->order_id);
    //     }else{
    //         return redirect()->back()->with('error', 'Order not found');
    //     }
    //     $response['package_data'] = $this->service->package_data($request->order_id);
    //     return view('admin.warehouse.packaging',$response);
    // }
    public function packaging(Request $request){
        $check_status = package_box_show($request->order_id);
        if($check_status == 0){
            return redirect()->back()->with('error', 'Request Failed');
        }
        $response['order_data'] = $this->service->order_data($request->order_id);
        if($response['order_data']){
            $response['product_types'] = $this->service->product_types($request->order_id);
        }else{
            return redirect()->back()->with('error', 'Order not found');
        }
        $response['package_data'] = $this->service->package_data($request->order_id);
        $response['warehouses'] = $this->service->warehouse_data();
      
        return view('admin.warehouse.packaging',$response);
    }

    public function getBlocks($warehouseId){
        $warehouse = $this->service->getBlocks($warehouseId);
        return response()->json($warehouse->blocks->map(function ($block) {
            return [
                'id'   => $block->id,
                'name' => $block->name,
            ];
        }));
    }

    public function packagingStore(Request $request){
        $save_data = $this->service->packagingStore($request);
        if($save_data['status'] == 0){
            return redirect()->back()->with('error', $save_data['message']);
        }else{
            return redirect()->route('admin.warehouse.indexOrder')->withSuccess($save_data['message']);
        }
    }

    public function packagingShow(Request $request){
        
        $response['package'] = $this->service->packagingShow($request->package_id);
        return view('admin.warehouse.packaging_show',$response);
    }
    public function barcodeDownload(Request $request){
        return $this->service->barcodeDownload($request->box_id);
     
    }

}