<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterDesignsService as Service;
use App\Requests\Admin\Master\MasterDesignsStoreRequest;
use App\Requests\Admin\Master\MasterDesignsUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterDesignController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.designs.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.designs.create');
    }
    public function store(MasterDesignsStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.designs.index')->withSuccess('The design has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.designs.index')->withSuccess('The design has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.designs.edit',$response);
    }
    public function update(MasterDesignsUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.designs.index')->withSuccess('The design has been successfully updated.');
    }

}