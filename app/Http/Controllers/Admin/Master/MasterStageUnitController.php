<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\StageUnitService as Service;
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
        $response['master_warehouse_fabrics'] = $this->service->master_warehouse_fabrics();
        $response['master_stages'] = $this->service->master_stages();
        return view('admin.master.stage_unit.index',$response);
    }

    public function stageUnit($master_fabric_warehouse_id){
        $data = $this->service->stageUnit($master_fabric_warehouse_id);
        return response()->json($data);

    }
    public function update(Request $request){
        $data = $this->service->update($request);
        return redirect()->back()->with('success', 'Stage units saved successfully');
    }
    

}