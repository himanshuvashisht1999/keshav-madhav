<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\CompanyService as Service;
use Auth;

class CompanyController extends Controller { 
    protected $service;

    public function __construct(Service $service) {
        $this->service = $service;
    }

    public function index(){
        return view('admin.master.company.index');
    }

    public function indexList(Request $request){
        return $this->service->indexList($request);
    }

    public function create(){
        return view('admin.master.company.create');
    }

    public function store(Request $request){
        $request->validate([
            'name' => 'required',
            'gst_number' => 'required|unique:companies,gst_number',
            'phone' => 'nullable',
            'email' => 'nullable|email',
        ]);
        $this->service->store($request);
        return redirect()->route('admin.master.company.index')->withSuccess('Company created successfully.');
    }

    public function edit(Request $request){
        $data = $this->service->edit($request);
        return view('admin.master.company.edit', compact('data'));
    }

    public function update(Request $request){
        $request->validate([
            'name' => 'required',
            'gst_number' => 'required|unique:companies,gst_number,' . $request->id,
            'phone' => 'nullable',
            'email' => 'nullable|email',
            'status' => 'required|in:1,0',
        ]);
        $this->service->update($request);
        return redirect()->route('admin.master.company.index')->withSuccess('Company updated successfully.');
    }

    public function delete(Request $request){
        $this->service->delete($request);
        return response()->json(['status' => 'success', 'message' => 'Company deleted successfully.']);
    }
}
