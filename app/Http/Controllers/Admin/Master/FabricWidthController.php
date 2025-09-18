<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\FabricWidthService as Service;
use App\Requests\Admin\Master\FabricWidthStoreRequest;
use App\Requests\Admin\Master\FabricWidthUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class FabricWidthController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fabric_width.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.fabric_width.create');
    }
    public function store(FabricWidthStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric_width.index')->withSuccess('The fabric width has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric_width.index')->withSuccess('The fabric width has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fabric_width.edit',$response);
    }
    public function update(FabricWidthUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric_width.index')->withSuccess('The fabric width has been successfully updated.');
    }

}