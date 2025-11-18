<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\ProductionGoodsItemService as Service;
use App\Services\Admin\Master\ProductionGoodsService;
use App\Requests\Admin\Master\ProductionGoodsItemStoreRequest;
use App\Requests\Admin\Master\ProductionGoodsItemUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class ProductionGoodsItemController extends Controller { 
    protected $service;
    public function __construct(Service $service, ProductionGoodsService $productionGoodsService) {
        $this->service = $service;
        $this->productionGoodsService = $productionGoodsService;
    }
    public function index(){
        return view('admin.master.production-goods-item.index');
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(Request $request){
        $response['itemCatogeriesValue'] = $this->service->getItemCatogeriesValue();
        $response['productItems'] = $this->service->getProductItems($request);
        $response['fabrics'] = $this->productionGoodsService->fabrics();
        $response['selectecFabrics'] = $this->service->getProductFebrics($request);

        return view('admin.master.production-goods-item.create', $response);
    }
    public function store(ProductionGoodsItemStoreRequest $request){
        try {
            
            $result = $this->service->store($request);
            if($result = true){
                return redirect()->route('admin.master.production-goods.index')->withSuccess('The production goods has been successfully created.');
            }else{
                return redirect()->back()->with('error', $result);
            }

            
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.production-goods.index')->withSuccess('The production goods has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['sizes'] = $this->service->sizes();
        $response['fabrics'] = $this->service->fabrics();
        $response['garment_types'] = $this->service->garment_types();
        $response['garment_patterns'] = $this->service->garment_patterns();
        $response['colors'] = $this->service->colors();
        $response['product_stages'] = $this->service->product_stages();
        return view('admin.master.production-goods-item.edit',$response);
    }
    public function update(ProductionGoodsUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.production-goods-item.index')->withSuccess('The production goods has been successfully updated.');
    }

}