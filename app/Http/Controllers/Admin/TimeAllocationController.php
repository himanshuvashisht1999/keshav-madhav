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

    public function index(){
        return view('admin.time_allocation.index');
    }

    public function indexList(Request $request){
        $datatable = new \App\Http\DataTable\Admin\TimeAllocationDataTable();
        return $datatable->indexList($request);
    }

    public function edit($id){
        $response['production_stages'] = $this->service->getProductionStages();
        $response['allocation'] = \App\Models\MasterStageWiseTimeAllocation::findOrFail($id);
        
        return view('admin.time_allocation.edit', $response);
    } 

    public function getLotDetails(Request $request)
    {
        $details = $this->service->getLotDetailsForDisplay($request->lot_no);
        return response()->json($details);
    }

    public function update(Request $request, $id){
        $data = $this->service->updateTimeAllocation($request, $id);
        if($data['status_code'] == 1){
            return redirect()->route('admin.time_allocation.index')->withSuccess($data['message']);
        }else{
            return redirect()->back()->withError($data['message']);
        }
    }

    public function backfill(\App\Services\Admin\OrderDigitalizationService $digitalizationService)
    {
        try {
            $lots = \App\Models\OrderLot::whereNotIn('lot_no', function($q) {
                $q->select('lot_no')->from('master_stage_wise_time_allocation');
            })->get();

            $count = 0;
            foreach ($lots as $lot) {
                $prodDate = $lot->production_datetime ?: $lot->created_at;
                $digitalizationService->autoAllocateTime($lot->lot_no, $prodDate, $lot->production_slip_digitization_id);
                $count++;
            }

            return redirect()->route('admin.time_allocation.index')->withSuccess("Successfully backfilled time allocations for {$count} lots.");
        } catch (\Exception $e) {
            return redirect()->route('admin.time_allocation.index')->withError("Error backfilling lots: " . $e->getMessage());
        }
    }
}
