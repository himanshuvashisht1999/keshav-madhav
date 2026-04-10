<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\FabricUnitService as Service;
use App\Requests\Admin\Master\FabricUnitStoreRequest;
use App\Requests\Admin\Master\FabricUnitUpdateRequest;
use Auth;

class FabricUnitController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fabric_unit.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.fabric_unit.create');
    }
    public function store(FabricUnitStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric_unit.index')->withSuccess('The fabric unit has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric_unit.index')->withSuccess('The fabric unit has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fabric_unit.edit',$response);
    }
    public function update(FabricUnitUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric_unit.index')->withSuccess('The fabric unit has been successfully updated.');
    }

}
