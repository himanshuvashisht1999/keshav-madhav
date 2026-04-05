<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Company;
use App\Http\DataTable\Admin\Master\CompanyDataTable as DataTable;

class CompanyService {
    protected $datatable;

    public function __construct(DataTable $datatable) {
        $this->datatable = $datatable;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $company = new Company;
        $company->name = $request->name;
        $company->address = $request->address;
        $company->gst_number = $request->gst_number;
        $company->phone = $request->phone;
        $company->email = $request->email;
        $company->status = 0; // Default to inactive
        $company->created_by = Auth::guard('admin')->id();
        $company->save();
        return true;
    }

    public function edit(Request $request){
        return Company::findOrFail($request->id);
    }

    public function update(Request $request){
        $company = Company::findOrFail($request->id);
        $company->name = $request->name;
        $company->address = $request->address;
        $company->gst_number = $request->gst_number;
        $company->phone = $request->phone;
        $company->email = $request->email;
        $company->status = $request->status;
        $company->save();
        return true;
    }

    public function delete(Request $request){
        $company = Company::findOrFail($request->id);
        $company->status = 3; // soft delete status
        $company->save();
        return true;
    }
}
