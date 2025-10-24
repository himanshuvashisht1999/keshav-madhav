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
    public function create(){
        $response['products'] = $this->service->products();
        $response['product_stage'] = $this->service->product_stage();
        return view('admin.order_stages.create',$response);
    }
    public function store(OrderStagesStoreRequest $request){
        $data = $this->service->store($request);
        if($data['status_code'] == 1){
            return redirect()->route('admin.order_stages.index')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
        
    }
    
    public function edit(Request $request){
        $response['data'] = $this->service->edit($request);
        $response['products'] = $this->service->products();
        return view('admin.order_stages.edit',$response);
    }
    public function update(OrderStagesUpdateRequest $request){
        $data = $this->service->update($request);
        return redirect()->route('admin.order_stages.index')->withSuccess('The product order has been successfully updated.');
    }
    public function view(Request $request){
        $response['data'] = $this->service->view($request);
        return view('admin.order_stages.view',$response);
    }
    public function delete(Request $request){
        $data = $this->service->delete($request);
        return redirect()->route('admin.order_stages.index')->withSuccess('The product order has been successfully deleted.'); 
    }

    public function transfer(Request $request)
    {
        try {
            $this->service->transfer($request);

            return redirect()->back()->with('success', 'The product order has been successfully transferred.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
    public function produce(Request $request){
        $response['data'] = $this->service->produce($request);
        return view('admin.order_stages.produce',$response);
    }
    public function issueFabric(Request $request){
        $response['data'] = $this->service->issueFabric($request);
        return view('admin.order_stages.issue_fabric',$response);
    }
    public function issueFabricPost(Request $request){
        $response= $this->service->issueFabricPost($request);
        if($response['status'] == 0){
           return redirect()->back()->with('error',$response['message']);
        }
        return redirect()->route('admin.order_stages.index')->withSuccess($response['message']); 
    }

}