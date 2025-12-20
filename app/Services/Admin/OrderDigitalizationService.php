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
                    $save_data_main->order_no = $request->order_no_list[$key];
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
            ->orderBy('id', 'asc')
            ->first();
            
        $data = [];
        if ($results){
            // $results = ProductionSlipDigitization::with('getUnitMaster')
            //     ->where('status', 0)
            //     ->orderBy('id', 'asc')
            //     ->first();
            // dd($results);
            $results_units = StageMasterUnit::with('masterStage')->where('status', 1)->where('master_fabric_warehouse_id', $results->getUnitMaster->master_fabric_warehouse_id)
                ->orderBy('id', 'asc')
                ->get()->toArray();
            $unit_master_data = [];
            $from_stage = [];
            if ($results_units){
                foreach ($results_units as $unit_data) {
                    $unit_master_data[] = [
                        'id' => $unit_data['id'],
                        'master_stage_id' => $unit_data['master_stage_id'],
                        'name' => $unit_data['name'],
                        'master_stage_name' => $unit_data['master_stage']['name'],

                    ];
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
        $count = ProductionSlipDigitization::where('status', 2)->count();
        return $count;
    }

}
