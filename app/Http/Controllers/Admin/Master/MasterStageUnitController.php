<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\CustomerService as Service;
use App\Requests\Admin\Master\StageUnitStoreRequest;
use App\Requests\Admin\Master\StageUnitUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class MasterStageUnitController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['stages'] = $this->service->stages();
        return view('admin.master.stage_unit.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['items'] = $this->service->items();
        return view('admin.master.stage_unit.create',$response);
    }
    public function store(StageUnitStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.stage_unit.index')->withSuccess('The stage unit has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.stage_unit.index')->withSuccess('The stage unit has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['items'] = $this->service->items();
        return view('admin.master.stage_unit.edit',$response);
    }
    public function update(StageUnitUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.stage_unit.index')->withSuccess('The stage unit has been successfully updated.');
    }
    

}