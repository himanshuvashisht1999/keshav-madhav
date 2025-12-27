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
        // dd($request->all());
        try {
           
            ////// corporate order photo upload
            if ($request->lot_no_list){
                foreach ($request->lot_no_list as $key => $lot_no) {
                    $save_data_main = new FabricRollAssigning;
                    $save_data_main->sku = '';
                    $save_data_main->lot_no = $lot_no;
                    $save_data_main->production_slip_digitization_id = $request->production_slip_digitization_id;
                    $save_data_main->order_no = '';
                    $save_data_main->stage_master_unit_id = $request->cutting_unit_list[$key];
                    $save_data_main->roll_no = $request->roll_no_list[$key];
                    $save_data_main->meter = $request->meter_list[$key];
                    $save_data_main->slip_create_date_time = $request->slip_create_date_time ?? NULL;
                    $save_data_main->status = 1;
                    $save_data_main->save();

                    // update rolls
                    // 2️⃣ Update fabric receipt (SAFE)
                    $fabricReceiptDetail = FabricReceiptDetail::where(
                        'roll_number',
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
                }
            }

            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'status'  => 4
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
            $save_data_main->production_slip_digitization_id = $request->production_slip_digitization_id;
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
            $save_data_master->production_slip_digitization_id = $request->production_slip_digitization_id;
            $save_data_master->start_date_time  = $request->start_date_time;
            foreach ($request->stages as $stage_id => $days) {
                $save_data_master->{'stage_id_'.$stage_id} = $days;
            }
            $save_data_master->status = 1;
            $save_data_master->save();

            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'lot_no'  => $request->lot_no,
                'status'  => 5
            ]);
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

    public function getSlipDigitalization()
    {
        // defaults status 0 data get for Digitization

        $results = ProductionSlipDigitization::with([
            'getUnitMaster.masterFabricWarehouse'
            ])
            ->where('status', 0)
            ->whereNot('from_stage_id', 3)
            ->orderBy('id', 'asc')
            ->first();
            
        $data = [];
        if ($results){
            
            // $results_units = StageMasterUnit::with('masterStage')->where('status', 1)->where('master_fabric_warehouse_id', $results->getUnitMaster->master_fabric_warehouse_id)
            //     ->orderBy('sequence', 'asc')
            //     ->get()->toArray();
            // dd($results);
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
            $unit_master_data = [];
            $from_stage = [];
            if ($results_units){
                foreach ($results_units as $unit_data) {

                    if($results->from_stage_id != $unit_data['master_stage_id']){
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
                'date_time' => $results->created_at
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
        $WORK_END   = 19;   // 7 PM
        $EVENING    = 17;   // 5 PM
        $HOURS_PER_DAY = 10;
        $HALF_DAY_HOURS = 5;

        $current = new \DateTime($startDateTime);
        $hour = (int)$current->format('H');

        // 🔹 Align start time
        if ($hour < $WORK_START) {
            $current->setTime($WORK_START, 0);
        } elseif ($hour >= $WORK_END) {
            $current->modify('+1 day')->setTime($WORK_START, 0);
        }

        /* -------------------------
        HALF DAY (0.5) LOGIC
        --------------------------*/
        if ($days == 0.5) {

            $endOfDay = clone $current;
            $endOfDay->setTime($WORK_END, 0);

            // aaj kitne hours available hain
            $availableToday =
                ($endOfDay->getTimestamp() - $current->getTimestamp()) / 3600;

            // ✅ Agar aaj 5 hours available hain
            if ($availableToday >= $HALF_DAY_HOURS) {
                $current->modify("+{$HALF_DAY_HOURS} hours");
            }
            // ❌ warna next day se count
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



}
