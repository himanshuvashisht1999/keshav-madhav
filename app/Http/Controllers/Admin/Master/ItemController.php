<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\ItemService as Service;
use App\Requests\Admin\Master\ItemStoreRequest;
use App\Requests\Admin\Master\ItemUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class ItemController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.item.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.item.create');
    }
    public function store(ItemStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.item.index')->withSuccess('The item has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.item.index')->withSuccess('The item has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.item.edit',$response);
    }
    public function update(ItemUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.item.index')->withSuccess('The item has been successfully updated.');
    }

}