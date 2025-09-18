<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\FabricDyeService as Service;
use App\Requests\Admin\Master\FabricDyeStoreRequest;
use App\Requests\Admin\Master\FabricDyeUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class FabricDyeController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fabric_dye.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.fabric_dye.create');
    }
    public function store(FabricDyeStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric_dye.index')->withSuccess('The fabric dye has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric_dye.index')->withSuccess('The fabric dye has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fabric_dye.edit',$response);
    }
    public function update(FabricDyeUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric_dye.index')->withSuccess('The fabric dye has been successfully updated.');
    }

}