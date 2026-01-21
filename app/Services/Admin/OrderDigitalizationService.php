<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderMain;
use App\Models\StageMasterUnit;
use App\Models\Stock;
use App\Models\FabricRollAssigning;
use App\Models\ProductionSlipDigitization;
use App\Models\ProductionSlipDigitizationParts;
use App\Models\ProductionDigitizationSetsDetails;
use App\Models\MasterSizeMeasurement;
use App\Models\FabricReceiptDetail;
use App\Models\OrderStageWiseTimeTracking;
use App\Models\MasterStageWiseTimeAllocation;
use App\Models\masterFabricWarehouse;
use App\Models\ProductionGoods;
use App\Models\OrderCuttingStage;
use App\Models\OrderProductSet;
use App\Models\OrderStageTransaction;
use App\Models\OrderLot;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingStageTransactionDetail;



use PDF;


use App\Http\DataTable\Admin\OrderDigitalizationDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderDigitalizationService {
    public function __construct(
        DataTable $datatable
    ) {
        $this->datatable= $datatable;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }
   
    public function storeRollsAssign(Request $request)
    {
        DB::beginTransaction();
        try {
            // Get the production slip to retrieve stage_master_unit_id
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
            $order_product_set = OrderProductSet::with('orderMain')->where('id',$request->design_id)->first();
            
            if (!$slip) {
                throw new \Exception('Production slip not found');
            }
            $lotNos = array_unique($request->lot_no_list ?? []);

            foreach ($lotNos as $lotNo) {
                $exists = OrderLot::where('lot_no', $lotNo)
                    ->where('production_slip_digitization_id', '!=', $request->production_slip_digitization_id)
                    ->exists();

                if ($exists) {
                    throw new \Exception("Lot No {$lotNo} already exists. Please use a unique Lot No.");
                }

                $save_lot = new OrderLot;
                $save_lot->order_main_id = $order_product_set->orderMain?->id;
                $save_lot->order_products_set_id = $order_product_set->id;
                $save_lot->production_slip_digitization_id = $request->production_slip_digitization_id;
                $save_lot->lot_no = $lotNo;
                $save_lot->save();
            }


            ////// corporate order photo upload
            if ($request->lot_no_list){
                foreach ($request->lot_no_list as $key => $lot_no) {
                    
                    // VALIDATION CHECK: Check if lot number already exists
                    

                    $save_data_main = new FabricRollAssigning;
                    $save_data_main->sku = '';
                    $save_data_main->lot_no = $lot_no;
                    $save_data_main->order_lot_id = $save_lot->id;
                    $save_data_main->production_slip_digitization_id = $request->production_slip_digitization_id;
                    $save_data_main->order_products_set_id = $request->design_id;
                    $save_data_main->order_no = $order_product_set->orderMain?->sku;
                    $save_data_main->stage_master_unit_id = $slip->stage_master_unit_id; // Get from slip
                    $save_data_main->roll_no = $request->roll_no_list[$key];
                    $save_data_main->meter = $request->meter_list[$key];
                    $save_data_main->slip_create_date_time = $request->slip_create_date_time ?? NULL;
                    $save_data_main->status = 1;
                    $save_data_main->save();

                    // update rolls
                    // 2️⃣ Update fabric receipt (SAFE)
                    $fabricReceiptDetail = FabricReceiptDetail::where(
                        'id',
                        $request->roll_no_list[$key]
                    )->lockForUpdate()->first();

                    if (!$fabricReceiptDetail) {
                        throw new \Exception('Roll not found: '.$request->roll_no_list[$key]);
                    }

                    if ($fabricReceiptDetail->remaining_quantity < $request->meter_list[$key]) {
                        throw new \Exception('Insufficient meter for roll: '.$request->roll_no_list[$key]);
                    }

                    $fabricReceiptDetail->remaining_quantity -= $request->meter_list[$key];
                    $fabricReceiptDetail->save();

                    // Update saved record with human-readable roll number
                    $save_data_main->update(['roll_no' => $fabricReceiptDetail->roll_number]);

                    // 3️⃣ Save Size Details (NEW)
                    // if (isset($request->size_details[$key])) {
                    if ($key === 0 && isset($request->size_details[$key])) {
                        $sizeArray = json_decode($request->size_details[$key], true);
                        if (is_array($sizeArray)) {
                            foreach ($sizeArray as $sizeItem) {
                                // Create Detail Record
                                $detail = new \App\Models\FabricRollAssigningsDetail();
                                $detail->production_fabric_roll_assigning_id = $save_data_main->id;
                                $detail->order_product_set_detail_id = $sizeItem['detail_id'];
                                $detail->size = $sizeItem['size'];
                                $detail->quantity = $sizeItem['qty'];
                                $detail->status = 1;
                                $detail->save();

                                // Update OrderProductSetDetail
                                if ($sizeItem['detail_id']) {
                                    $setDetail = \App\Models\OrderProductSetDetail::find($sizeItem['detail_id']);
                                    if ($setDetail) {
                                        $setDetail->remaining_lot_allocated -= $sizeItem['qty'];
                                        $setDetail->save();
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'status'  => 1
            ]);

            // Commit everything if all successful
            DB::commit();
            return [
                'status_code' => 1,
                'message' => 'Fabric rolls assigned successfully'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function storeProductionSlipDigitization(Request $request)
    {

        DB::beginTransaction();
        try {
        //    dd($request->all());
            ////// corporate order photo upload
            if ($request->lot_no_list){
                foreach ($request->lot_no_list as $key => $lot_no) {
                    $save_data_main = new ProductionSlipDigitizationParts;
                    $save_data_main->production_slip_digitization_id = $request->production_slip_digitization_id;
                    $save_data_main->sku = '';
                    $save_data_main->lot_no = $lot_no;
                    $save_data_main->slip_date_time = $request->slip_create_date_time;
                    $save_data_main->order_no = $request->order_no;

                    $save_data_main->from_stage_id = $request->from_stage_id[$key];
                    $save_data_main->from_stage_name = $request->from_stage_name[$key];
                    $save_data_main->from_unit_id = $request->from_unit_id[$key];
                    $save_data_main->from_unit_name = $request->from_unit_name[$key]; 

                    $save_data_main->to_stage_id = $request->to_stage_id[$key];
                    $save_data_main->to_stage_name = $request->to_stage_name[$key]; 
                    $save_data_main->to_unit_id = $request->to_unit_id[$key];
                    $save_data_main->to_unit_name = $request->to_unit_name[$key]; 

                    $save_data_main->design_number = $request->design[$key];
                    $save_data_main->color_id = $request->colour_id[$key];

                    $save_data_main->set_size = $request->set_size[$key] ?? NULL;
                    $save_data_main->set_quantity = $request->set_qty[$key] ?? NULL; 
                    $save_data_main->single_size = $request->individual_size[$key] ?? NULL;
                    $save_data_main->single_quantity = $request->individual_qty[$key] ?? NULL;
                    $save_data_main->allowed_time = $request->allowed_time ?? '';
                    $save_data_main->time_type = $request->time_type ?? '';
                    $save_data_main->allowed_till_datetime = $request->allowed_till_datetime ?? NULL;
                    $save_data_main->remarks = $request->remark ?? '';
                    $save_data_main->status = 1;
                    $save_data_main->save();

                    $insertedId = $save_data_main->id;

                    if ($insertedId){
                        if ($request->set_qty[$key] != NULL){
                            
                            $set_size_id = $request->set_size[$key];
                            $size_group = MasterSizeMeasurement::where('id', $set_size_id)->where('status', 1)->value('size_group');
                            if($size_group){
                                $size_group_explode = explode(",", $size_group);
                                foreach ($size_group_explode as $size) {
                                    $save_sets_details = new ProductionDigitizationSetsDetails;
                                    $save_sets_details->production_slip_digitization_parts_id = $insertedId;
                                    $save_sets_details->set_size_id = $set_size_id;
                                    $save_sets_details->set_qty =  $request->set_qty[$key];
                                    $save_sets_details->size =  $size;
                                    $save_sets_details->qauntity = $request->set_qty[$key];
                                    $save_sets_details->status = 1;
                                    $save_sets_details->save();
                                }
                            } 
                        } else {
                            /// individual size
                            $save_sets_details = new ProductionDigitizationSetsDetails;
                            $save_sets_details->production_slip_digitization_parts_id = $insertedId;
                            $save_sets_details->set_size_id = NULL;
                            $save_sets_details->set_qty =  NULL;
                            $save_sets_details->size =  $request->individual_size[$key];
                            $save_sets_details->qauntity = $request->individual_qty[$key];
                            $save_sets_details->save();
                        }
                       
                    }

                }
            }

            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'lot_no'  => $request->lot_no,
                'status'  => 1
            ]);

            // Commit everything if all successful
            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Production Slip Digitization successfully completed.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function storeTimeAllocation(Request $request)
    {

        DB::beginTransaction();
        try {
        //    dd($request->all());
            
            $save_data_main = new OrderStageWiseTimeTracking;
            $datetime = date(
                        'Y-m-d H:i:s',
                        strtotime($request->start_date_time)
                    );
            // $datetime ="2025-12-20 17:40:00";
            // $expected_completed_at = $this->calculateExpectedCompletion($datetime, $days);
            // dd($expected_completed_at);
            $save_data_main->sku = '';
            $save_data_main->lot_no = $request->lot_no;
            $save_data_main->production_slip_digitization_id = $request->production_slip_digitization_id ?? null;
            $save_data_main->start_date_time  = $datetime;
            foreach ($request->stages as $stage_id => $days) {
                $expected = $this->calculateExpectedCompletion($datetime, $days);
                $save_data_main->{'stage_id_'.$stage_id} = $expected;
                $datetime = $expected;
            }
            $save_data_main->status = 1;
            $save_data_main->save();

            ///// master stage wise time allocation  ///// 

            $save_data_master = new MasterStageWiseTimeAllocation;
           
            // $datetime ="2025-12-20 17:40:00";
            // $expected_completed_at = $this->calculateExpectedCompletion($datetime, $days);
            // dd($expected_completed_at);
            $save_data_master->sku = '';
            $save_data_master->lot_no = $request->lot_no;
            $save_data_master->production_slip_digitization_id = $request->production_slip_digitization_id ?? null;
            $save_data_master->start_date_time  = $request->start_date_time;
            foreach ($request->stages as $stage_id => $days) {
                $save_data_master->{'stage_id_'.$stage_id} = $days;
            }
            $save_data_master->status = 1;
            $save_data_master->save();

            // Only update slip status if production_slip_digitization_id is provided
            if ($request->production_slip_digitization_id) {
                $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
                if ($slip) {
                    $slip->update([
                        'lot_no'  => $request->lot_no,
                        'status'  => 1
                    ]);
                }
            }
            
            $msg = 'Stage wise time allocation successfully completed.';
            
            // Commit everything if all successful
            DB::commit();

            return [
                'status_code' => 1,
                'message' => $msg
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }
    public function view(Request $request){
        $data = Order::with('products.product_details.product_detail_stocks','products.order_stages.stage','products.order_stage_trnsactions')->where('id',$request->id)->first();
        return $data;
    }
    
    public function edit(Request $request){
        $data = Order::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = Order::find($request->id);
        $update_data->order_type = $request->order_type;
        $update_data->status = 1;
        $update_data->save();

        return true;
    }

    function orderMainForRollAssign(){
        $results = OrderMain::where('status',1)->get();
        $data = [];
        if ($results){
            foreach($results as $res){
                $data[$res->id] = $res->sku;
            }
        }
        return $data;
    }

    public function getFabricsData(){
        $results = Stock::select(
            'fabric_id',
            'sku',
            DB::raw('SUM(meter) as total_fabric')
        )
        ->groupBy('fabric_id', 'sku')
        ->havingRaw('SUM(meter) != 0')
        ->get();
        $data = [];
        foreach ($results as $res) {
            $data[$res->fabric_id] = $res->sku." - (".$res->total_fabric.")";
        }
        return $data;
    }

    public function getRollsData(Request $request){
        $results = Stock::select(
            'id',
            'roll_no',
            'meter'
        )
        ->where('fabric_id', $request->fabric_id)
        ->get();
        $data = [];
        foreach ($results as $res) {
            $data[$res->id] = $res->roll_no ." - (".$res->meter." Meters)";
        }
        return $data;
    }

    public function getSlipDigitalization(Request $request)
    {
        // defaults status 0 data get for Digitization
        if($request->slip_id){
            $results = ProductionSlipDigitization::with([
            'getUnitMaster.masterFabricWarehouse'
            ])->where('id', $request->slip_id)->first();
        }else{
            $results = ProductionSlipDigitization::with([
            'getUnitMaster.masterFabricWarehouse'
            ])
            ->where('status', 0)
            ->whereNot('from_stage_id', 3)
            ->orderBy('id', 'asc')
            ->first();
        }
        $from_stage_id = $results->from_stage_id;
        if($from_stage_id == 1){$to_stage_id = 4;}
        if($from_stage_id == 3){$to_stage_id = 4;}
        if($from_stage_id == 4){$to_stage_id = 5;}
        if($from_stage_id == 5){$to_stage_id = 6;}
        if($from_stage_id == 6){$to_stage_id = 7;}
        if($from_stage_id == 7){$to_stage_id = 8;}
        if($from_stage_id == 8){$to_stage_id = 9;}
        if($from_stage_id == 9){$to_stage_id = 10;}
        if($from_stage_id == 10){$to_stage_id = 11;}
        if($from_stage_id == 11){$to_stage_id = 12;}
            
        $data = [];
        if ($results){
            
            $results_units = StageMasterUnit::with('masterStage')
                ->join('master_product_stages as master_stages', 'master_stages.id', '=', 'stage_master_units.master_stage_id')
                ->where('stage_master_units.status', 1)
                ->where(
                    'stage_master_units.master_fabric_warehouse_id',
                    $results->getUnitMaster->master_fabric_warehouse_id
                )
                ->orderBy('master_stages.sequence', 'asc')
                ->select('stage_master_units.*', 'master_stages.sequence')
                ->get()
                ->toArray();
                //dd($results_units);
            $unit_master_data = [];
            $from_stage = [];
            if ($results_units){
                foreach ($results_units as $unit_data) {

                    // if($results->from_stage_id != $unit_data['master_stage_id']){
                    if( $unit_data['master_stage_id'] == $to_stage_id){
                        $unit_master_data[] = [
                            'id' => $unit_data['id'],
                            'master_stage_id' => $unit_data['master_stage_id'],
                            'name' => $unit_data['name'],
                            'master_stage_name' => $unit_data['master_stage']['name'],

                        ];
                    }
                    if ($results['stage_master_unit_id'] == $unit_data['id']){
                        $from_stage = [
                            'id' => $unit_data['id'],
                            'master_stage_id' => $unit_data['master_stage_id'],
                            'name' => $unit_data['name'],
                            'master_stage_name' => $unit_data['master_stage']['name'],
                            'warehouse_id' => $results->getUnitMaster->masterFabricWarehouse->id,
                            'warehouse' => $results->getUnitMaster->masterFabricWarehouse->cutting_master_name,
                            'address' => $results->getUnitMaster->masterFabricWarehouse->address,
                        ];
                    }
                }
            }
            // dd($results_units);
            $data = [
                'id' => $results->id,
                'slip_file' => $results->slip_file,
                'from_stage' => $from_stage,
                'unit_master_data' => $unit_master_data,
                'date_time' => $results->created_at,
                'status' => $results->status
            ];
        }

        return $data; 
    }
    
    public function skip(Request $request)
    {
        DB::beginTransaction();
        try {
        //    dd($request->all());
            
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'status'  => 2
            ]);

            // Commit everything if all successful
            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Slip Digitization skip successfully.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function deleteSlip(Request $request)
    {
        DB::beginTransaction();
        try {
        //    dd($request->all());
            
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'status'  => 3
            ]);

            // Commit everything if all successful
            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Slip Digitization Delete successfully.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function addSkipSlips(Request $request)
    {
        DB::beginTransaction();
        try {
        //    dd($request->all());
            
            ProductionSlipDigitization::where('status', 2)
            ->update([
                'status' => 0
            ]);
            // Commit everything if all successful
            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Skip Slip successfully add for Digitization.'
            ];

        } catch (\Exception $e) {
            //  Rollback everything on any error
            DB::rollBack();

            $return_data['message'] = $e->getMessage();
            $return_data['status_code'] = 0;
            return $return_data;
        }
    }

    public function getSkipSlips()
    {
        $count = ProductionSlipDigitization::where('status', 2)->whereNot('from_stage_id', 3)->count();
        return $count;
    }


    function calculateExpectedCompletion($startDateTime, $days)
    {
        $WORK_START = 9;    // 9 AM
        $WORK_END   = 17;   // 5 PM (8-hour workday)
        $HOURS_PER_DAY = 8; // 8 hours per day
        $HALF_DAY_HOURS = 4; // 4 hours for half day

        $current = new \DateTime($startDateTime);
        $hour = (int)$current->format('H');

        // 🔹 Align start time to working hours
        if ($hour < $WORK_START) {
            // If before 9 AM, start at 9 AM
            $current->setTime($WORK_START, 0);
        } elseif ($hour >= $WORK_END) {
            // If after 5 PM, move to next day 9 AM
            $current->modify('+1 day')->setTime($WORK_START, 0);
        }

        /* -------------------------
        HALF DAY (0.5) LOGIC
        --------------------------*/
        if ($days == 0.5) {

            $endOfDay = clone $current;
            $endOfDay->setTime($WORK_END, 0);

            // Calculate how many hours are available today
            $availableToday =
                ($endOfDay->getTimestamp() - $current->getTimestamp()) / 3600;

            // ✅ If 4 hours are available today
            if ($availableToday >= $HALF_DAY_HOURS) {
                $current->modify("+{$HALF_DAY_HOURS} hours");
            }
            // ❌ Otherwise start from next day
            else {
                $current->modify('+1 day')->setTime($WORK_START, 0);
                $current->modify("+{$HALF_DAY_HOURS} hours");
            }

            return $current->format('Y-m-d H:i:s');
        }

        /* -------------------------
        FULL / MULTI DAY LOGIC
        --------------------------*/
        $remainingHours = $days * $HOURS_PER_DAY;

        while ($remainingHours > 0) {

            $endOfDay = clone $current;
            $endOfDay->setTime($WORK_END, 0);

            $availableToday =
                ($endOfDay->getTimestamp() - $current->getTimestamp()) / 3600;

            if ($availableToday <= 0) {
                $current->modify('+1 day')->setTime($WORK_START, 0);
                continue;
            }

            if ($remainingHours <= $availableToday) {
                $minutes = (int) round($remainingHours * 60);
                $current->modify("+{$minutes} minutes");
                break;
            }

            $remainingHours -= $availableToday;
            $current->modify('+1 day')->setTime($WORK_START, 0);
        }

        return $current->format('Y-m-d H:i:s');
    }

    /////////////////  cutting master 

    public function cutting_slip(Request $request)
    {
        if($request->slip_id){
            $results = ProductionSlipDigitization::with([
                'getUnitMaster.masterFabricWarehouse'
                ])
                ->where('id', $request->slip_id)->first();
        }else{

            if($request->is_skip == 1){
                $results = ProductionSlipDigitization::with([
                    'getUnitMaster.masterFabricWarehouse'
                    ])
                    ->where('status', 2)
                    ->where('from_stage_id', 3)
                    ->orderBy('id', 'asc')
                    ->first();
            }else{
                $results = ProductionSlipDigitization::with([
                    'getUnitMaster.masterFabricWarehouse'
                    ])
                    ->where('status', 0)
                    ->where('from_stage_id', 3)
                    ->orderBy('id', 'asc')
                    ->first();
            }
        }

        
        
            
        
        return $results;
    }
    public function stages($stage_master_unit_id){
        $data = StageMasterUnit::with('masterStage')->where('status', 1)->where('master_fabric_warehouse_id', $stage_master_unit_id)->orderBy('id', 'asc')->get();
        return $data;
    }
    public function master_fabric_warehouse($master_fabric_warehouse_id){
        $data = masterFabricWarehouse::where('id',$master_fabric_warehouse_id)->first();
        return $data;
    }
    public function designs(){
        $data = ProductionGoods::where('status',1)->get();
        return $data;
    }

    public function stage_unit_data($master_fabric_warehouse_id,$master_stage_id){
        $data = StageMasterUnit::where('master_fabric_warehouse_id',$master_fabric_warehouse_id)->where('master_stage_id',$master_stage_id)->first();
        return $data;
    }

    public function roll_numbers(){
        $data = FabricReceiptDetail::whereNot('status',0)->pluck('roll_number');
        return $data;
    }
    public function order_numbers(){
        $data = OrderMain::whereNot('status',0)->pluck('sku');
        return $data;
    }
    // public function cutting_master_orders($cutting_unit){
    //     $order_main_ids = OrderCuttingStage::where('to_assign_id',$cutting_unit)->pluck('order_main_id');
    //     $main_orders = OrderMain::whereIn('id',$order_main_ids)->get();

    //     return $main_orders;
    // }
    function product_sizes(){
        $data = MasterSizeMeasurement::whereIn('status',[1,2])->orderBy('id','asc')->get();
        return $data;
    }

    /**
     * Get all available lots for time allocation
     * Returns distinct lot numbers from fabric roll assignments
     * Excludes lots that already have time allocation
     */
    public function getLotsForTimeAllocation()
    {
        // Get lots that already have time allocation
        $allocatedLots = MasterStageWiseTimeAllocation::whereNotNull('lot_no')
            ->where('lot_no', '!=', '')
            ->pluck('lot_no')
            ->toArray();

        // Get all lots from fabric roll assignments, excluding already allocated ones
        return FabricRollAssigning::distinct()
            ->whereNotNull('lot_no')
            ->where('lot_no', '!=', '')
            ->whereNotIn('lot_no', $allocatedLots)
            ->pluck('lot_no')
            ->sort()
            ->values();
    }

    /**
     * Get all production stages for time allocation
     * Returns stages with status 1 or 2, ordered by sequence
     */
    public function getProductionStages()
    {
        return \App\Models\MasterProductStage::whereIn('status', [1, 2])
            ->orderBy('sequence', 'asc')
            ->get();
    }


    public function getDesigns(Request $request)
    {
        $main_order_id = $request->main_order_id;
        $data = OrderProductSet::where('order_main_id',$main_order_id)->get();
        return $data;
    }

    // Step 2: Design → Rolls + Size Groups
    public function getDesignDetails(Request $request)
    {
        $orderNo = $request->order_no;
        $design  = $request->design_no;

        $rolls = FabricRoll::where('order_no', $orderNo)
            ->where('design_number', $design)
            ->select('roll_no', 'meter')
            ->get();

        $sizes = ProductSize::where('design_number', $design)->get();

        return response()->json([
            'rolls' => $rolls,
            'sizes' => $sizes
        ]);
    }


    // new code 
    public function orders($stage_master_unit_id)
    {
        $main_orders = OrderMain::orderby('id','desc')->with([
                'OrderProductSets' => function ($q) use ($stage_master_unit_id) {
                    $q->where('stage_master_unit_id', $stage_master_unit_id)
                    ->with([
                        'fabric.receiptDetails',
                        'colors',
                        'master_design_pattern',
                        'master_product_fitting',
                        'size_measurement',
                        'stage_master_unit',
                        'product_set_details'
                    ]);
                }
            ])
            ->whereHas('OrderProductSets', function ($q) use ($stage_master_unit_id) {
                $q->where('stage_master_unit_id', $stage_master_unit_id);
            })
            ->get();
            // dd($main_orders);

        // FALLBACK: If product_set_details is empty, check if they exist under order_main_id (due to previous bug)
        foreach ($main_orders as $order) {
            foreach ($order->OrderProductSets as $set) {
                if ($set->product_set_details->isEmpty()) {
                    $fallbackDetails = \App\Models\OrderProductSetDetail::where('order_products_set_id', $order->id)->get();
                    if ($fallbackDetails->isNotEmpty()) {
                        $set->setRelation('product_set_details', $fallbackDetails);
                    }
                }
            }
        }

        return $main_orders;
    }

    // public function getLotsBySlip($stage_id,$slip_id)
    // {
    //     if($stage_id == 1){
    //         $lot_nums = OrderLot::where('is_printing',0)->pluck('lot_no');
    //     }else{
    //         $lot_nums = OrderLot::where('is_stitching',0)->pluck('lot_no');
    //     }
        
    //     return $lot_nums;
    // }
    public function getLotsBySlip(int $stage_id, int $slip_id)
    {
        $slip = ProductionSlipDigitization::find($slip_id);
        if (!$slip) {
            return collect([]);
        }
        $query = OrderLot::query();

        if ($stage_id == 1) {
            $query->where('is_printing', 0);
        } else {
            $query->where('is_stitching', 0);
        }

        $query->whereIn('lot_no', function ($q) use ($slip) {
            $q->select('lot_no')
            ->from('production_fabric_roll_assigning')
            ->where('stage_master_unit_id', $slip->stage_master_unit_id);
        });

        return $query->pluck('lot_no')->unique()->values();
    }

    public function getLotDetails($lot_no, $slip_id)
    {
        $rolls = FabricRollAssigning::where('lot_no', $lot_no)
            ->where('production_slip_digitization_id', $slip_id)
            ->get();

        $details = [];
        foreach ($rolls as $roll) {
            $sizeDetails = \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $roll->id)
                ->with('orderProductSetDetail')
                ->get();
            
            $details[] = [
                'roll' => $roll,
                'sizes' => $sizeDetails
            ];
        }

        return $details;
    }

    /**
     * Get comprehensive lot details for display
     * Returns cutting master, fabric, order details, and size quantities
     */
    public function getLotDetailsForDisplay($lot_no)
    {
        // Get fabric roll assignments for this lot
        $rollAssignments = FabricRollAssigning::where('lot_no', $lot_no)
            ->with([
                'stageMasterUnit.masterFabricWarehouse',
                'stageMasterUnit.masterStage'
            ])
            ->get();

        if ($rollAssignments->isEmpty()) {
            return null;
        }

        $firstAssignment = $rollAssignments->first();
        
        // Get cutting master details
        $cuttingMaster = $firstAssignment->stageMasterUnit;
        $warehouse = $cuttingMaster ? $cuttingMaster->masterFabricWarehouse : null;

        // Get all rolls with their details
        $rollsData = [];
        $totalMeters = 0;
        $sizeWiseQuantities = [];
        $fabricNames = [];
        $orderNumbers = [];

        foreach ($rollAssignments as $assignment) {
            $totalMeters += $assignment->meter;

            // Get size details for this roll with eager loading
            $sizeDetails = \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $assignment->id)
                ->with(['orderProductSetDetail.orderProductSet.fabric', 'orderProductSetDetail.orderProductSet.orderMain'])
                ->get();

            foreach ($sizeDetails as $detail) {
                $size = $detail->size;
                if (!isset($sizeWiseQuantities[$size])) {
                    $sizeWiseQuantities[$size] = 0;
                }
                $sizeWiseQuantities[$size] += $detail->quantity;

                // Collect fabric and order info with proper null checks
                if ($detail->orderProductSetDetail) {
                    $productSet = $detail->orderProductSetDetail->orderProductSet;
                    //dd($detail->orderProductSetDetail->orderProductSet);
                    if ($productSet) {
                        // Get fabric name - try both relationship methods
                        if ($productSet->fabric) {
                            $fabricName = $productSet->fabric->name ?? null;
                            if ($fabricName) {
                                $fabricNames[$fabricName] = true;
                            }
                        }
                        
                        // Get order SKU - try both relationship methods
                        if ($productSet->orderMain) {
                            $orderSku = $productSet->orderMain->sku ?? null;
                            if ($orderSku) {
                                $orderNumbers[$orderSku] = true;
                            }
                        }
                    }
                }
            }

            $rollsData[] = [
                'roll_number' => $assignment->roll_no,
                'meters' => $assignment->meter,
                'sizes' => $sizeDetails
            ];
        }

        return [
            'lot_no' => $lot_no,
            'cutting_master' => [
                'name' => $cuttingMaster ? $cuttingMaster->name : 'N/A',
                'warehouse' => $warehouse ? $warehouse->cutting_master_name : 'N/A',
                'address' => $warehouse ? $warehouse->address : 'N/A',
            ],
            'fabric_names' => array_keys($fabricNames),
            'order_numbers' => array_keys($orderNumbers),
            'total_meters' => $totalMeters,
            'total_rolls' => $rollAssignments->count(),
            'size_wise_quantities' => $sizeWiseQuantities,
            'rolls' => $rollsData,
            'debug' => [
                'fabric_count' => count($fabricNames),
                'order_count' => count($orderNumbers),
                'size_details_count' => $rollAssignments->sum(function($r) {
                    return \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $r->id)->count();
                })
            ]
        ];
    }

    /**
     * Alternative method to get lot details with direct queries
     */
    public function getLotDetailsAlternative($lot_no)
    {
        // Get all fabric roll assignments for this lot
        $rollIds = FabricRollAssigning::where('lot_no', $lot_no)
            ->pluck('id')
            ->toArray();

        if (empty($rollIds)) {
            return null;
        }

        // Get all order product set detail IDs from fabric roll assigning details
        $orderProductSetDetailIds = \App\Models\FabricRollAssigningsDetail::whereIn('production_fabric_roll_assigning_id', $rollIds)
            ->pluck('order_product_set_detail_id')
            ->unique()
            ->toArray();

        // Get all order product set IDs
        $orderProductSetIds = \App\Models\OrderProductSetDetail::whereIn('id', $orderProductSetDetailIds)
            ->pluck('order_products_set_id')
            ->unique()
            ->toArray();

        // Get fabric names
        $fabricNames = \App\Models\OrderProductSet::whereIn('id', $orderProductSetIds)
            ->with('fabric')
            ->get()
            ->pluck('fabric.name')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Get order SKUs
        $orderSkus = \App\Models\OrderProductSet::whereIn('id', $orderProductSetIds)
            ->with('orderMain')
            ->get()
            ->pluck('orderMain.sku')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        return [
            'fabric_names' => $fabricNames,
            'order_numbers' => $orderSkus
        ];
    }

    public function getNextStages($warehouse_id)
    {
        return StageMasterUnit::with('masterStage')
            ->where('master_fabric_warehouse_id', $warehouse_id)
            ->where('status', 1)
            ->get();
    }

    public function storeStitching(Request $request)
    {
        DB::beginTransaction();
        try {
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
            
            if (!$slip) {
                throw new \Exception('Production slip not found');
            }

            // Update slip status to mark as processed for stitching
            $slip->update([
                'status' => 1, // Processed for stitching
                // 'lot_no' => $request->lot_no
            ]);

            $order_lot_update = OrderLot::where('lot_no',$request->lot_no)->update([
                'is_stitching' => 1
            ]);

            // Update all fabric roll assignments for this lot to mark them as sent to stitching
            FabricRollAssigning::where('lot_no', $request->lot_no)
                ->where('stage_master_unit_id', $slip->stage_master_unit_id)
                ->update([
                    'status' => 2, // Sent to next stage
                    'to_stage_id' => $request->to_stage_id ?? 4 // Stitching stage
            ]);
            $fab_roll_assigning = FabricRollAssigning::where('lot_no', $request->lot_no)
                ->where('stage_master_unit_id', $slip->stage_master_unit_id)
                ->first();

        $this->createTransactionWithDetails($request->lot_no, 3, 4, $slip->id,$fab_roll_assigning->order_products_set_id,$slip->stage_master_unit_id,$request->to_stage_unit_id);
            
            DB::commit();
            return [
                'status_code' => 1,
                'message' => 'Lot successfully sent to Stitching stage.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function storePrinting(Request $request)
    {
        DB::beginTransaction();
        try {
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
            
            if (!$slip) {
                throw new \Exception('Production slip not found');
            }

            // Update slip status to mark as processed for printing
            $slip->update([
                'status' => 1, // Processed for printing
                'lot_no' => $request->lot_no
            ]);

            $order_lot_update = OrderLot::where('lot_no',$request->lot_no)->update([
                'is_printing' => 1
            ]);

            // Update all fabric roll assignments for this lot to mark them as sent to printing
            FabricRollAssigning::where('lot_no', $request->lot_no)
                ->where('stage_master_unit_id', $slip->stage_master_unit_id)
                ->update([
                    'status' => 3, // Sent to printing
                    'to_stage_id' => $request->to_stage_id ?? 1 // Printing stage
            ]);
            $fab_roll_assigning = FabricRollAssigning::where('lot_no', $request->lot_no)
                ->where('stage_master_unit_id', $slip->stage_master_unit_id)
                ->first();

            // Create Transaction with Details (Cutting -> Printing)
            // Stage 3 is Cutting. Stage 1 is Printing.
            $this->createTransactionWithDetails($request->lot_no, 3, 1, $slip->id,$fab_roll_assigning->order_products_set_id,$slip->stage_master_unit_id,$request->to_stage_unit_id);
            
            DB::commit();
            return [
                'status_code' => 1,
                'message' => 'Lot successfully sent to Printing stage.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    private function createTransactionWithDetails($lot_no, $from_stage_id, $to_stage_id, $slip_id = null,$order_products_set_id,$sub_stage_id,$sub_stage_id_to)
    {
        // 1. Calculate Total Quantities per Size for the Lot
        // We get this from the OrderProductSet details which represent the Cutting Master output
        $sets = \App\Models\OrderProductSet::where('id', $order_products_set_id)
            ->with('product_set_details')
            ->get();

        $sizeQuantities = [];
        $totalQuantity = 0;
        $orderProductIds = [];

        foreach ($sets as $set) {
            foreach ($set->product_set_details as $detail) {
                // Determine size key (could be ID or Name, preserving what is in DB)
                $sizeKey = $detail->size; 
                
                if (!isset($sizeQuantities[$sizeKey])) {
                    $sizeQuantities[$sizeKey] = 0;
                }
                $sizeQuantities[$sizeKey] += $detail->total_quantity;
                $totalQuantity += $detail->total_quantity;
            }
             // Keep track of order product IDs if needed, though usually mixed in a lot
             if($set->order_product_id) {
                 $orderProductIds[] = $set->order_product_id;
             }
        }
        
        $orderProductId = count($orderProductIds) > 0 ? $orderProductIds[0] : null;

        // 2. Create Transaction Header
        if($to_stage_id == 1){
            $transaction = OrderPrintingStageTransaction::create([
                'from_stage_id' => $from_stage_id,
                'to_stage_id' => $to_stage_id, 
                'lot_no' => $lot_no,
                'quantity' => $totalQuantity,
                'remaining_quantity' => $totalQuantity, // Initially full
                'order_product_id' => $orderProductId, // Best guess or first
                'status' => 1,
                'sub_stage_id' => $sub_stage_id,
                'sub_stage_id_to' => $sub_stage_id_to,
            ]);

            // 3. Create Transaction Details (Size-wise)
            foreach ($sizeQuantities as $size => $qty) {
                OrderPrintingStageTransactionDetail::create([
                    'order_printing_stage_transaction_id' => $transaction->id,
                    'size' => $size,
                    'quantity' => $qty
                ]);
            }
        }else{
            $transaction = OrderStageTransaction::create([
                'from_stage_id' => $from_stage_id,
                'to_stage_id' => $to_stage_id, 
                'lot_no' => $lot_no,
                'quantity' => $totalQuantity,
                'remaining_quantity' => $totalQuantity, // Initially full
                'order_product_id' => $orderProductId, // Best guess or first
                'status' => 1,
                'sub_stage_id' => $sub_stage_id,
                'sub_stage_id_to' => $sub_stage_id_to,
            ]);

            // 3. Create Transaction Details (Size-wise)
            foreach ($sizeQuantities as $size => $qty) {
                \App\Models\OrderStageTransactionDetail::create([
                    'order_stage_transaction_id' => $transaction->id,
                    'size' => $size,
                    'quantity' => $qty
                ]);
            }

        }
        
        
        return $transaction;
    }

    public function getStageUnits($warehouse_id, $master_stage_id)
    {
        return \App\Models\StageMasterUnit::with('masterStage')
            ->join('master_product_stages as master_stages', 'master_stages.id', '=', 'stage_master_units.master_stage_id')
            ->where('stage_master_units.status', 1)
            ->where('stage_master_units.master_fabric_warehouse_id', $warehouse_id)
            ->where('stage_master_units.master_stage_id', $master_stage_id)
            ->orderBy('stage_master_units.name', 'asc')
            ->select('stage_master_units.*')
            ->get();
    }

    public function getAvailableLotsForStage($stage_id)
    {
        // 1. Get all lots that have entered this stage
        $model_name = ($stage_id == 1)
        ? OrderPrintingStageTransaction::class
        : OrderStageTransaction::class;
        $candidateLots = $model_name::where('to_stage_id', $stage_id)
            ->distinct()
            ->pluck('lot_no');

        $availableLots = [];

        foreach ($candidateLots as $lot_no) {
            // Optimization: We could do this in a single aggregate query if needed, 
            // but loop is safer for complex size logic logic transparency.
            
            // Calculate Inflow (Total Items entered this stage)
            $inflow = $model_name::where('to_stage_id', $stage_id)
                ->where('lot_no', $lot_no)
                ->sum('quantity');

            // Calculate Outflow (Total Items left this stage)
            $outflow = $model_name::where('from_stage_id', $stage_id)
                ->where('lot_no', $lot_no)
                ->sum('quantity');
            
            // If there is stock remaining, add to list
            if (($inflow - $outflow) > 0) {
                // Determine Order No (Optional, for display)
                // $transaction = $model_name::where('lot_no', $lot_no)->first();
                $availableLots[] = (object)[
                    'lot_no' => $lot_no,
                    // 'remaining_qty' => $inflow - $outflow
                ];
            }
        }

        return $availableLots;
    }

    public function getLotDetailsForHandSlip($lot_no, $current_stage_id)
    {
        // 1. Calculate Inflow (What came INTO this stage) per size
        if($current_stage_id == 1){
            $inflow = OrderPrintingStageTransactionDetail::join('order_printing_stage_transactions', 'order_printing_stage_transactions.id', '=', 'order_printing_stage_transaction_details.order_printing_stage_transaction_id')
            ->where('order_printing_stage_transactions.to_stage_id', $current_stage_id)
            ->where('order_printing_stage_transactions.lot_no', $lot_no)
            ->select('order_printing_stage_transaction_details.size', 'order_printing_stage_transaction_details.quantity')
            ->get();

            // 2. Calculate Outflow (What already LEFT this stage) per size
            $outflow = OrderPrintingStageTransactionDetail::join('order_printing_stage_transactions', 'order_printing_stage_transactions.id', '=', 'order_printing_stage_transaction_details.order_printing_stage_transaction_id')
                ->where('order_printing_stage_transactions.from_stage_id', $current_stage_id)
                ->where('order_printing_stage_transactions.lot_no', $lot_no)
                ->select('order_printing_stage_transaction_details.size', 'order_printing_stage_transaction_details.quantity')
                ->get();
        }else{
            $inflow = \App\Models\OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transactions.id', '=', 'order_stage_transaction_details.order_stage_transaction_id')
            ->where('order_stage_transactions.to_stage_id', $current_stage_id)
            ->where('order_stage_transactions.lot_no', $lot_no)
            ->select('order_stage_transaction_details.size', 'order_stage_transaction_details.quantity')
            ->get();

            // 2. Calculate Outflow (What already LEFT this stage) per size
            $outflow = \App\Models\OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transactions.id', '=', 'order_stage_transaction_details.order_stage_transaction_id')
                ->where('order_stage_transactions.from_stage_id', $current_stage_id)
                ->where('order_stage_transactions.lot_no', $lot_no)
                ->select('order_stage_transaction_details.size', 'order_stage_transaction_details.quantity')
                ->get();

        }
        

        $inventory = [];

        foreach ($inflow as $item) {
            if (!isset($inventory[$item->size])) {
                $inventory[$item->size] = 0;
            }
            $inventory[$item->size] += $item->quantity;
        }

        foreach ($outflow as $item) {
            if (isset($inventory[$item->size])) {
                $inventory[$item->size] -= $item->quantity;
            }
        }

        // 3. Filter out zero quantities (optional, but cleaner)
        // $inventory = array_filter($inventory, function($qty) { return $qty > 0; });
        
        // 4. Get Basic Lot Info (Cutting Master Info)
        // We can reuse getLotDetailsForUser logic or just fetch basics
        $basicInfo = $this->getLotDetailsForDisplay(['lot_no' => $lot_no], true); // true for basic only? Adjust later if needed 
        // Or simply:
         $cuttingMaster = \App\Models\FabricRollAssigning::where('lot_no', $lot_no)->first();
         
         return [
             'lot_no' => $lot_no,
             'inventory' => $inventory,
             'basic_info' => $basicInfo
         ];
    }

    public function storeHandSlip(Request $request)
    {
        DB::beginTransaction();
        try {
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
            if (!$slip) throw new \Exception('Slip not found');

            $stage_master_unit_from = StageMasterUnit::where('id',$request->from_stage_id)->first();
            $stage_master_unit_to = StageMasterUnit::where('id',$request->to_stage_id)->first();

            $from_stage_id = $stage_master_unit_from->master_stage_id; // Current Stage
            $to_stage_id = $stage_master_unit_to->master_stage_id;     // Next Stage
            $lot_no = $request->lot_no;
            $sizes = $request->sizes; // Array of [size => qty]

            // Validate Inventory
            $currentInventory = $this->getLotDetailsForHandSlip($lot_no, $from_stage_id)['inventory'];
            $totalMoved = 0;

            foreach ($sizes as $size => $qty) {
                if ($qty > 0) {
                    if (!isset($currentInventory[$size]) || $currentInventory[$size] < $qty) {
                         throw new \Exception("Insufficient inventory for Size $size. Available: " . ($currentInventory[$size]??0));
                    }
                    $totalMoved += $qty;
                }
            }

            if ($totalMoved == 0) throw new \Exception('No quantity selected to move.');

            // Create Transaction
            if($from_stage_id == 1){
                $update_order_stage_trans = OrderPrintingStageTransaction::where('to_stage_id',$from_stage_id)->where('sub_stage_id_to',$stage_master_unit_from?->id)->where('lot_no',$lot_no)->first();
            }else{
                $update_order_stage_trans = OrderStageTransaction::where('to_stage_id',$from_stage_id)->where('sub_stage_id_to',$stage_master_unit_from?->id)->where('lot_no',$lot_no)->first();
            }
            
            if($update_order_stage_trans){
                $update_order_stage_trans->remaining_quantity = $update_order_stage_trans->remaining_quantity - $totalMoved;
                $update_order_stage_trans->update();
            }
            
            $transaction = OrderStageTransaction::create([
                'from_stage_id' => $from_stage_id,
                'to_stage_id' => $to_stage_id,
                'sub_stage_id' => $stage_master_unit_from?->id,
                'sub_stage_id_to' => $stage_master_unit_to?->id,
                'lot_no' => $lot_no,
                'quantity' => $totalMoved,
                'remaining_quantity' => $totalMoved, 
                'status' => 1,
            ]);

            // Create Details
            foreach ($sizes as $size => $qty) {
                if ($qty > 0) {
                    \App\Models\OrderStageTransactionDetail::create([
                        'order_stage_transaction_id' => $transaction->id,
                        'size' => $size,
                        'quantity' => $qty
                    ]);
                }
            }
            
            
            $slip->update(['status' => 1, 'lot_no' => $lot_no]); // Mark as processed

            DB::commit();
            return ['status_code' => 1, 'message' => 'Hand Slip Processed Successfully'];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status_code' => 0, 'message' => $e->getMessage()];
        }
    }

    public function used_lots(){
        $lots= FabricRollAssigning::distinct()->pluck('lot_no');
        return $lots;
    }
}
