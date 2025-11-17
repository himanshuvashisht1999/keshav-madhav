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
    public function index(Request $request){
        $response['id'] = $request->id;
        return view('admin.master.item-attributes.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(Request $request){
        $response['attributes'] = $this->service->attributes($request);
        return view('admin.master.item-attributes.create',$response);
    }
    public function store(ItemAttributesStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.item-attributes.index',['id' => $request->id])->withSuccess('The sub item has been successfully created.');
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
        return redirect()->route('admin.master.item-attributes.index',['id' => $request->item_id])->withSuccess('The sub item has been successfully updated.');
    }

}