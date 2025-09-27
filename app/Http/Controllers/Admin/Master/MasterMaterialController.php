<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterMaterialsService as Service;
use App\Requests\Admin\Master\MasterMaterialsStoreRequest;
use App\Requests\Admin\Master\MasterMaterialsUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterMaterialController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.materials.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.materials.create');
    }
    public function store(MasterMaterialsStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.materials.index')->withSuccess('The material has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.materials.index')->withSuccess('The material has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.materials.edit',$response);
    }
    public function update(MasterMaterialsUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.materials.index')->withSuccess('The material has been successfully updated.');
    }

}