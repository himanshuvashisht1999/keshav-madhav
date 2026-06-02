<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterColorsService as Service;
use App\Requests\Admin\Master\MasterColorsStoreRequest;
use App\Requests\Admin\Master\MasterColorsUpdateRequest;
use IllumFabricWeaveControllerinate\Support\Facades\Crypt;
use Auth;

class MasterColorController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.colors.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function allColors(){
        $data = \App\Models\MasterColor::select('id', 'name')->where('status', 1)->get();
        return response()->json($data);
    }
    public function create(){
        return view('admin.master.colors.create');
    }
    public function store(MasterColorsStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.colors.index')->withSuccess('The color has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.colors.index')->withSuccess('The color has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.colors.edit',$response);
    }
    public function update(MasterColorsUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.colors.index')->withSuccess('The color has been successfully updated.');
    }

}