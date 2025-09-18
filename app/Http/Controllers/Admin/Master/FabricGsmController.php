<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\FabricGsmService as Service;
use App\Requests\Admin\Master\FabricGsmStoreRequest;
use App\Requests\Admin\Master\FabricGsmUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class FabricGsmController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.fabric_gsm.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.fabric_gsm.create');
    }
    public function store(FabricGsmStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric_gsm.index')->withSuccess('The fabric gsm has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric_gsm.index')->withSuccess('The fabric gsm has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.fabric_gsm.edit',$response);
    }
    public function update(FabricGsmUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric_gsm.index')->withSuccess('The fabric gsm has been successfully updated.');
    }

}