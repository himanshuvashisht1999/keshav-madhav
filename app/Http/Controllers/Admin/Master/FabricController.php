<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\FabricService as Service;
use App\Services\Admin\FabricReceiptService;
use App\Requests\Admin\Master\FabricStoreRequest;
use App\Requests\Admin\Master\FabricUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class FabricController extends Controller { 
    protected $service;
    public function __construct(Service $service, FabricReceiptService $fabricReceiptService){ 
        $this->service = $service;
        $this->fabricReceiptService = $fabricReceiptService;
    }
    public function index(){
        $response['vendor_data'] = $this->fabricReceiptService->vendors();
        // $response['fab_width_data'] = $this->service->fab_width_data();
        // $response['fab_weave_data'] = $this->service->fab_weave_data();
        // $response['fab_gsm_data'] = $this->service->fab_gsm_data();
        $response['fab_composition_data'] = $this->service->fab_composition_data();
      
        return view('admin.master.fabric.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        // $response['fab_dye_data'] = $this->service->fab_dye_data();
        // $response['fab_width_data'] = $this->service->fab_width_data();
        // $response['fab_weave_data'] = $this->service->fab_weave_data();
        // $response['fab_gsm_data'] = $this->service->fab_gsm_data();
        $response['vender_data'] = $this->fabricReceiptService->vendors();
        $response['fab_composition_data'] = $this->service->fab_composition_data();
        return view('admin.master.fabric.create',$response);
    }
    public function store(FabricStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.fabric.index')->withSuccess('The fabric has been successfully created.');
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['vender_data'] = $this->fabricReceiptService->vendors();
        // $response['fab_dye_data'] = $this->service->fab_dye_data();
        // $response['fab_width_data'] = $this->service->fab_width_data();
        // $response['fab_weave_data'] = $this->service->fab_weave_data();
        // $response['fab_gsm_data'] = $this->service->fab_gsm_data();
        $response['fab_composition_data'] = $this->service->fab_composition_data();
        return view('admin.master.fabric.edit',$response);
    }
    public function update(FabricUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.fabric.index')->withSuccess('The fabric has been successfully updated.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.fabric.index')->withSuccess('The fabric has been successfully deleted.'); 
    }
    public function deleteImage(Request $request){
        $data = $this->service->deleteImage($request);
        return redirect()->route('admin.master.fabric.edit',['id' => $request->fabric_id])->withSuccess('Image deleted successfully.');
    }

}