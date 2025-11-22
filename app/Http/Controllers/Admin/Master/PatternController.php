<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\PatternService as Service;
use App\Requests\Admin\Master\PatternStoreRequest;
use App\Requests\Admin\Master\PatternUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class PatternController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.pattern.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.pattern.create');
    }
    public function store(PatternStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.pattern.index')->withSuccess('The pattern has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.pattern.index')->withSuccess('The pattern has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['parts_data'] = $this->service->editParts($request);
        // dd($response['parts_data']);
        return view('admin.master.pattern.edit',$response);
    }
    public function update(PatternUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.pattern.index')->withSuccess('The pattern has been successfully updated.');
    }

}