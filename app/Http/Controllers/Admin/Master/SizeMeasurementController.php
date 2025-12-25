<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\SizeMeasurementService as Service;
use App\Services\Admin\Master\SizeService;
use App\Services\Admin\ProductOrderService;

use App\Requests\Admin\Master\SizeMeasurementStoreRequest;
use App\Requests\Admin\Master\SizeMeasurementUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class SizeMeasurementController extends Controller { 
    protected $service;
    public function __construct(Service $service, SizeService $SizeService, ProductOrderService $ProductOrderService) {
        $this->service = $service;
        $this->SizeService = $SizeService;
        $this->ProductOrderService = $ProductOrderService;
    }
    public function index(){
        $response['customers'] = $this->ProductOrderService->customers();
        return view('admin.master.size-measurement.index', $response);
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['sizes'] = $this->SizeService->getSizes();
        $response['customers'] = $this->ProductOrderService->customers();
        $response['products'] = $this->ProductOrderService->products();
        return view('admin.master.size-measurement.create', $response);
    }
    public function store(SizeMeasurementStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.size-measurement.index')->withSuccess('The size measurement has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.size-measurement.index')->withSuccess('The size measurement has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['sizes'] = $this->SizeService->getSizes();
        $response['data'] = $this->service->edit($request);
        $response['selectedSizes'] = explode(",",$response['data']->size_group);
        $response['customers'] = $this->ProductOrderService->customers();
        // dd($response['data'], $response['customers']);
        $response['products'] = $this->ProductOrderService->products();
        return view('admin.master.size-measurement.edit',$response);
    }
    public function update(SizeMeasurementUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.size-measurement.index')->withSuccess('The size measurement has been successfully updated.');
    }

}