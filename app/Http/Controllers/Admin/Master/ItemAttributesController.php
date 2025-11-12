<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\ItemAttributesService as Service;
use App\Requests\Admin\Master\ItemAttributesStoreRequest;
use App\Requests\Admin\Master\ItemAttributesUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class ItemAttributesController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.item-attributes.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.item-attributes.create');
    }
    public function store(ItemStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.item-attributes.index')->withSuccess('The sub item has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.item-attributes.index')->withSuccess('The sub item has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.item-attributes.edit',$response);
    }
    public function update(ItemAttributesUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.item-attributes.index')->withSuccess('The sub item has been successfully updated.');
    }

}