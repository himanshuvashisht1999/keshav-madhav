<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterWarehouseBlocksService as Service;
use App\Requests\Admin\Master\MasterWarehouseBlocksStoreRequest;
use App\Requests\Admin\Master\MasterWarehouseBlocksUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterWarehouseBlocksController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['master_warehouses'] = $this->service->getMasterWarehouse();
        return view('admin.master.warehouse_blocks.index', $response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.warehouse_blocks.create');
    }
    public function store(MasterWarehouseBlocksStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.warehouse-blocks.index')->withSuccess('The color has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.warehouse-blocks.index')->withSuccess('The color has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.warehouse_blocks.edit',$response);
    }
    public function update(MasterWarehouseBlocksUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.warehouse-blocks.index')->withSuccess('The color has been successfully updated.');
    }

}