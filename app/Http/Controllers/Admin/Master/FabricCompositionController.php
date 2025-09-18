<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\FabricCompositionService as Service;
use App\Requests\Admin\Master\FabricCompositionStoreRequest;
use App\Requests\Admin\Master\FabricCompositionUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class FabricCompositionController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fabric_composition.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.fabric_composition.create');
    }
    public function store(FabricCompositionStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric_composition.index')->withSuccess('The fabric composition has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric_composition.index')->withSuccess('The fabric composition has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fabric_composition.edit',$response);
    }
    public function update(FabricCompositionUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric_composition.index')->withSuccess('The fabric composition has been successfully updated.');
    }

}