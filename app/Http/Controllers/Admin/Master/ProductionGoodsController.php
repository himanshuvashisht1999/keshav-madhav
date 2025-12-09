<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\ProductionGoodsService as Service;
use App\Requests\Admin\Master\ProductionGoodsStoreRequest;
use App\Requests\Admin\Master\ProductionGoodsUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class ProductionGoodsController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['colors'] = $this->service->colors();
        $response['sizes'] = $this->service->sizes();
        $response['designs'] = $this->service->designs();
        $response['materials'] = $this->service->materials();
        $response['fabrics'] = $this->service->fabrics();
 
        return view('admin.master.production-goods.index',$response);
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['sizes'] = $this->service->sizes();
        $response['fabrics'] = $this->service->fabrics();
        $response['product_types'] = $this->service->product_types();
        // dd($response['garment_types']);
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        return view('admin.master.production-goods.create',$response);
    }
    public function store(ProductionGoodsStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.production-goods.index')->withSuccess('The production goods has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.production-goods.index')->withSuccess('The production goods has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['sizes'] = $this->service->sizes();
        $response['fabrics'] = $this->service->fabrics();
        $response['product_types'] = $this->service->product_types();
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        return view('admin.master.production-goods.edit',$response);
    }
    public function view(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['sizes'] = $this->service->sizes();
        $response['fabrics'] = $this->service->fabrics();
        $response['product_types'] = $this->service->product_types();
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        return view('admin.master.production-goods.view',$response);
    }
    public function update(ProductionGoodsUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.production-goods.index')->withSuccess('The production goods has been successfully updated.');
    }

}