<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\BrandService as Service;
use Illuminate\Support\Facades\Crypt;
use Auth;

class BrandController extends Controller { 
    protected $service;

    public function __construct(Service $service) {
        $this->service = $service;
    }

    public function index(){
        return view('admin.master.brand.index');
    }

    public function indexList(Request $request){
        return $this->service->indexList($request);
    }

    public function allBrands(){
        $data = \App\Models\Brand::select('id', 'name')->where('status', 'active')->get();
        return response()->json($data);
    }

    public function create(){
        return view('admin.master.brand.create');
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'nullable|unique:brands,name',
        ]);
        $this->service->store($request);
        return redirect()->route('admin.master.brand.index')->withSuccess('Brand created successfully.');
    }

    public function edit(Request $request){
        $data = $this->service->edit($request);
        return view('admin.master.brand.edit', compact('data'));
    }

    public function update(Request $request){
        $request->validate([
            'name' => 'nullable|unique:brands,name,' . $request->id,
            'status' => 'required|in:active,inactive',
        ]);
        $this->service->update($request);
        return redirect()->route('admin.master.brand.index')->withSuccess('Brand updated successfully.');
    }

    public function delete(Request $request){
        $this->service->delete($request);
        return response()->json(['status' => 'success', 'message' => 'Brand deleted successfully.']);
    }
}
