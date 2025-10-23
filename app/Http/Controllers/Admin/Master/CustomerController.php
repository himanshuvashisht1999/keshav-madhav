<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\CustomerService as Service;
use App\Requests\Admin\Master\CustomerStoreRequest;
use App\Requests\Admin\Master\CustomerUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class CustomerController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        $response['items'] = $this->service->items();
        return view('admin.master.customer.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        $response['items'] = $this->service->items();
        return view('admin.master.customer.create',$response);
    }
    public function store(CustomerStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.customer.index')->withSuccess('The customer has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.customer.index')->withSuccess('The customer has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['items'] = $this->service->items();
        return view('admin.master.customer.edit',$response);
    }
    public function update(CustomerUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.customer.index')->withSuccess('The customer has been successfully updated.');
    }
    

}