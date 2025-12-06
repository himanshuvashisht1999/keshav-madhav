<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterWarehouseService as Service;
use App\Requests\Admin\Master\MasterWarehouseStoreRequest;
use App\Requests\Admin\Master\MasterWarehouseUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterWarehouseController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.warehouse.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.warehouse.create');
    }
    public function store(MasterWarehouseStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.warehouse.index')->withSuccess('The color has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.warehouse.index')->withSuccess('The color has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.warehouse.edit',$response);
    }
    public function update(MasterWarehouseUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.warehouse.index')->withSuccess('The color has been successfully updated.');
    }

}