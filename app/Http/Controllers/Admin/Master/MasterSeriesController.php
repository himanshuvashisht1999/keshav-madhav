<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\Master\MasterSeriesService as Service;
use App\Requests\Admin\Master\SeriesStoreRequest;
use App\Requests\Admin\Master\SeriesUpdateRequest;
use Auth;

class MasterSeriesController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(){
        return view('admin.master.series.index');
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function create(){
        return view('admin.master.series.create');
    }
    public function store(SeriesStoreRequest $request){
        $data = $this->service->store($request);
        return redirect()->route('admin.master.series.index')->withSuccess('The series has been successfully created.');
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.master.series.index')->withSuccess('The series has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        return view('admin.master.series.edit', $response);
    }
    public function update(SeriesUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.master.series.index')->withSuccess('The series has been successfully updated.');
    }
}