<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderStagesService as Service;
use App\Requests\Admin\OrderStagesStoreRequest;
use App\Requests\Admin\OrderStagesUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use Auth;

class OrderStagesController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(Request $request){
        $response['product_stage'] = $this->service->product_stage();
        $response['stage_data'] = $this->service->stage_data($request);
        return view('admin.order_stages.index',$response);
    } 
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    

}