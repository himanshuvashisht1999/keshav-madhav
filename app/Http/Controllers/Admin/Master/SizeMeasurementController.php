<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\SizeMeasurementService as Service;
use App\Requests\Admin\Master\SizeMeasurementStoreRequest;
use App\Requests\Admin\Master\SizeMeasurementUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class SizeMeasurementController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.size-measurement.index');
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.size-measurement.create');
    }
    public function store(SizeMeasurementStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.size-measurement.index')->withSuccess('The size measurement has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.size-measurement.index')->withSuccess('The size measurement has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['size_selections'] = $this->service->size_selections();
        return view('admin.master.size-measurement.edit',$response);
    }
    public function update(SizeMeasurementUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.size-measurement.index')->withSuccess('The size measurement has been successfully updated.');
    }

}