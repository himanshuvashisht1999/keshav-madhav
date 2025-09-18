<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\VendorService as Service;
use App\Requests\Admin\Master\VendorStoreRequest;
use App\Requests\Admin\Master\VendorUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class VendorController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.vendor.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.vendor.create');
    }
    public function store(VendorStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.vendor.index')->withSuccess('The vendor has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.vendor.index')->withSuccess('The vendor has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.vendor.edit',$response);
    }
    public function update(VendorUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.vendor.index')->withSuccess('The vendor has been successfully updated.');
    }

}