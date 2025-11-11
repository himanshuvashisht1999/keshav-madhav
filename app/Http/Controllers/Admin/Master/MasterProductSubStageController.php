<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterProductStageService as Service;
use App\Services\Admin\OrderStagesService as OrderStagesService;
use App\Requests\Admin\Master\MasterProductStageStoreRequest;
use App\Requests\Admin\Master\MasterProductStageUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterProductStageController extends Controller { 
    protected $service;
    public function __construct(Service $service, OrderStagesService $orderStagesService){
        $this->orderStagesService = $orderStagesService;
        $this->service = $service;
    }
    
    public function index(Request $request){
        $response['stage_data'] = $this->orderStagesService->stage_data($request);
        return view('admin.master.product-sub-stage.index', $response);
    } 

    public function subStageList(Request $request){
        return $this->service->subStageList($request);
    }

    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.product_stage.create');
    }
    public function store(MasterProductStageStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.product_stage.index')->withSuccess('The production stage has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.product_stage.index')->withSuccess('The production stage has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.product_stage.edit',$response);
    }
    public function update(MasterProductStageUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.product_stage.index')->withSuccess('The production stage has been successfully updated.');
    }

}