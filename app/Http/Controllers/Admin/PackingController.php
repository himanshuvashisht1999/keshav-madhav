<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\PackingService;

class PackingController extends Controller { 
    protected $service;
    
    public function __construct(PackingService $service) {
        $this->service = $service;
    }

    public function index(Request $request){
        $slips = $this->service->getPendingSlips();
        return view('admin.packing.index', compact('slips'));
    }

    public function process($slip_id){
        $slip = $this->service->getSlipDetails($slip_id); 
        // Need to load order details to show items to pack
        // Slip has 'sku'.
        $order = $this->service->getOrderDetails($slip->sku);
        
        // We also need to fetch any existing PackingMain if we are returning to a draft
        $packing = $this->service->getPackingMainWithStructure($slip_id);
        
        return view('admin.packing.process', compact('slip', 'order', 'packing'));
    }
    // API/AJAX Methods
    public function saveCarton(Request $request) {
        $data = $request->all();
        // Validation logic here
        $result = $this->service->saveCarton($data);
        return response()->json($result);
    }

    public function saveBox(Request $request) {
        $data = $request->all();
        $result = $this->service->saveBox($data);
        return response()->json($result);
    }

    public function finalize(Request $request) {
        $result = $this->service->finalizePacking($request->packing_main_id);
        return response()->json($result);
    }
}