<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterFabricWarehouseService as Service;
use App\Requests\Admin\Master\MasterFabricWarehouseStoreRequest;
use App\Requests\Admin\Master\MasterFabricWarehouseUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterFabricWarehouseController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fabric_warehouse.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.fabric_warehouse.create');
    }
    public function store(MasterFabricWarehouseStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric_warehouse.index')->withSuccess('The fabric warehouse has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric_warehouse.index')->withSuccess('The fabric warehouse has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fabric_warehouse.edit',$response);
    }
    public function update(MasterFabricWarehouseUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric_warehouse.index')->withSuccess('The fabric warehouse has been successfully updated.');
    }

}