<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\SizeService as Service;
use App\Requests\Admin\Master\SizeStoreRequest;
use App\Requests\Admin\Master\SizeUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class SizeController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.master-size.index');
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.master-size.create');
    }
    public function store(SizeStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.size.index')->withSuccess('The size has been successfully created.');
    }
   
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['sizes'] = $this->service->getSizes();
        return view('admin.master.master-size.edit',$response);
    }
    public function update(SizeUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.size.index')->withSuccess('The size has been successfully updated.');
    }

}