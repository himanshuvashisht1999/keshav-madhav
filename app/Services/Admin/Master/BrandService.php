<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Brand;
use App\Http\DataTable\Admin\Master\BrandDataTable as DataTable;

class BrandService {
    protected $datatable;
    protected $brand;

    public function __construct(DataTable $datatable, Brand $brand) {
        $this->datatable = $datatable;
        $this->brand = $brand;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $brand = new Brand;
        $brand->name = $request->name;
        $brand->status = $request->status ?? 'active';
        $brand->created_by = Auth::guard('admin')->id();
        $brand->save();
        return true;
    }

    public function edit(Request $request){
        return Brand::findOrFail($request->id);
    }

    public function update(Request $request){
        $brand = Brand::findOrFail($request->id);
        $brand->name = $request->name;
        $brand->status = $request->status;
        $brand->save();
        return true;
    }

    public function delete(Request $request){
        $brand = Brand::findOrFail($request->id);
        // Soft delete pattern usually used in this project is status = inactive
        $brand->status = 'inactive';
        $brand->save();
        return true;
    }
}
