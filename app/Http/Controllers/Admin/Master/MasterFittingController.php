<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterFittingService as Service;
use App\Requests\Admin\Master\MasterFittingStoreRequest;
use App\Requests\Admin\Master\MasterFittingUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterFittingController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fitting.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function allFittings(){
        $data = \App\Models\MasterProductFitting::select('id', 'name')->where('status', 1)->get();
        return response()->json($data);
    }
    public function create(){
        return view('admin.master.fitting.create');
    }
    public function store(MasterFittingStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fitting.index')->withSuccess('The fitting has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fitting.index')->withSuccess('The fitting has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fitting.edit',$response);
    }
    public function update(MasterFittingUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fitting.index')->withSuccess('The fitting has been successfully updated.');
    }

}