<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterProductTypesService as Service;
use App\Requests\Admin\Master\MasterProductTypesStoreRequest;
use App\Requests\Admin\Master\MasterProductTypesUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterProductTypeController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.product_types.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.product_types.create');
    }
    public function store(MasterProductTypesStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.product-types.index')->withSuccess('The color has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.product-types.index')->withSuccess('The color has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.product_types.edit',$response);
    }
    public function update(MasterProductTypesUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.product-types.index')->withSuccess('The color has been successfully updated.');
    }

}