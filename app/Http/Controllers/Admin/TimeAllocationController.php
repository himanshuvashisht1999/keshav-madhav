<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\TimeAllocationService as Service;
use App\Services\Admin\FabricReceiptService;

class TimeAllocationController extends Controller { 
    protected $service;
    protected $fabricReceiptService;

    public function __construct(Service $service, FabricReceiptService $fabricReceiptService) {
        $this->service = $service;
        $this->fabricReceiptService = $fabricReceiptService;
    }

    public function create(){
        // Fetch global stages for Time Allocation
        $response['production_stages'] = $this->service->getProductionStages();
        
        // Remove slip-related data fetching
        $response['slip_data'] = null; 
        $response['skip_slip_data'] = 0; 
        
        $response['available_lots'] = $this->service->getActiveLots();
        return view('admin.time_allocation.create', $response);
    } 

    public function getLotDetails(Request $request)
    {
        $details = $this->service->getLotDetailsForDisplay($request->lot_no);
        return response()->json($details);
    }

    public function store(Request $request){
        $data = $this->service->storeTimeAllocation($request);
        if($data['status_code'] == 1){
            return redirect()->back()->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }


}
