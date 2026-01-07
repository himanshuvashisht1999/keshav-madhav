<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Requests\Admin\Master\DesignPatternUpdateRequest;
use App\Services\Admin\Master\DesignPatternService as Service;
use App\Requests\Admin\Master\DesignPatternStoreRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class DesignPatternController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.design_pattern.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.design_pattern.create');
    }
    public function store(DesignPatternStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.design-pattern.index')->withSuccess('The design pattern has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.design-pattern.index')->withSuccess('The design pattern has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        // dd($response['parts_data']);
        return view('admin.master.design_pattern.edit',$response);
    }
    public function update(DesignPatternUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.design-pattern.index')->withSuccess('The design pattern has been successfully updated.');
    }

}