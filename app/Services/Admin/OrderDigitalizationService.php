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
use App\Models\FabricRollAssigningsDetail;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\OrderPrintingToStichingTransactionDetail;
use App\Models\OrderStageTransactionDetail;
use App\Models\OrderGodamStageTransaction;
use App\Models\OrderGodamStageTransactionDetail;
use App\Models\MasterProductStage;



use PDF;


use App\Http\DataTable\Admin\OrderDigitalizationDataTable as DataTable;
use Illuminate\Support\Facades\DB;

class OrderDigitalizationService
{
    public function __construct(
        DataTable $datatable
    ) {
        $this->datatable = $datatable;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function storeRollsAssign(Request $request)
    {
        DB::beginTransaction();
        try {
            // Get the production slip to retrieve stage_master_unit_id
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
            $order_product_set = OrderProductSet::with('orderMain')->where('id', $request->design_id)->first();

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
                $save_lot->production_datetime = $request->production_datetime;
                $save_lot->save();

                // ✅ NEW: Update Cutting Stage with actual Lot No for future timing updates
                \App\Models\OrderCuttingStage::where('set_product_id', $order_product_set->id)
                    ->where(function($q) {
                        $q->whereNull('lot_no')->orWhere('lot_no', '');
                    })
                    ->update(['lot_no' => $lotNo]);

                // Auto-allocate time for the newly created lot
                $this->autoAllocateTime($lotNo, $request->production_datetime, $request->production_slip_digitization_id);
            }

            // Update timing for Cutting (CMPO)
            if ($order_product_set && $slip->stage_master_unit_id) {
                $unit = StageMasterUnit::find($slip->stage_master_unit_id);
                $days = $unit->lot_time_in_days ?? 0;
                $pDate = $request->production_datetime ?: now()->toDateTimeString();
                
                $order_product_set->update([
                    'start_date' => $pDate,
                    'end_date' => $this->calculateExpectedCompletion($pDate, $days),
                ]);

                // Also update the Cutting Stage records
                \App\Models\OrderCuttingStage::where('set_product_id', $order_product_set->id)
                    ->update([
                        'start_date' => $pDate,
                        'end_date' => $this->calculateExpectedCompletion($pDate, $days),
                    ]);
            }


            ////// corporate order photo upload
            if ($request->lot_no_list) {
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
                    $save_data_main->fabric_receipt_detail_id = $request->roll_no_list[$key];
                    $save_data_main->roll_no = null;
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
                        throw new \Exception('Roll not found: ' . $request->roll_no_list[$key]);
                    }

                    if ($fabricReceiptDetail->remaining_quantity < $request->meter_list[$key]) {
                        throw new \Exception('Insufficient meter for roll: ' . $request->roll_no_list[$key]);
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
                            $totalQtyCut = 0;
                            foreach ($sizeArray as $sizeItem) {
                                $totalQtyCut += $sizeItem['qty'];

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

                            // Update OrderCuttingStage Remaining Quantity (Handle Split Assignments)
                            $remainingToDecrement = $totalQtyCut;
                            $cuttingStages = OrderCuttingStage::where('set_product_id', $request->design_id)
                                ->where('to_assign_id', $slip->stage_master_unit_id)
                                ->where('remaining_quantity', '>', 0)
                                ->orderBy('created_at', 'asc')
                                ->get();

                            foreach ($cuttingStages as $cs) {
                                if ($remainingToDecrement <= 0)
                                    break;

                                $decrement = min($cs->remaining_quantity, $remainingToDecrement);
                                $cs->remaining_quantity -= $decrement;
                                if ($cs->remaining_quantity <= 0) {
                                    $cs->status = 2; // Completed
                                }
                                $cs->save();
                                $remainingToDecrement -= $decrement;
                            }
                        }
                    }
                }
            }

            // ✅ NEW: Auto-assign to Printing if pre-selected in OrderProductSet
            if ($order_product_set->is_printing == 1 && $order_product_set->printing_unit_id) {
                foreach ($lotNos as $lotNo) {
                    // Update OrderLot
                    OrderLot::where('lot_no', $lotNo)->where('production_slip_digitization_id', $slip->id)->update([
                        'is_printing' => 1
                    ]);

                    // Get first roll assign for transaction source
                    $firstRoll = FabricRollAssigning::where('lot_no', $lotNo)->where('production_slip_digitization_id', $slip->id)->first();
                    
                    if ($firstRoll) {
                        // Mark as sent to printing
                        FabricRollAssigning::where('lot_no', $lotNo)->where('production_slip_digitization_id', $slip->id)->update([
                            'status' => 3, // Sent to printing
                            'to_stage_id' => 1 // Printing stage
                        ]);

                        // Get sizes for the transaction
                        $sizes = \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $firstRoll->id)->pluck('quantity', 'size')->toArray();

                        $this->createTransactionWithDetails(
                            $lotNo,
                            3, // From Cutting
                            1, // To Printing
                            $slip->id,
                            $order_product_set->id,
                            $slip->stage_master_unit_id,
                            $order_product_set->printing_unit_id,
                            $firstRoll->id,
                            $request->production_datetime,
                            $sizes
                        );
                        
                        // Send WhatsApp notification to Printing Unit
                        $this->sendAssignmentWhatsApp($order_product_set->printing_unit_id, $lotNo);
                    }
                }
            }

            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slipUpdate = [
                'save_type' => 1,
                'lot_no' => $lotNos[0] ?? null
            ];

            // Only set status to 1 if user explicitly marks it as final
            if ($request->is_final == 1) {
                $slipUpdate['status'] = 1;
            } else {
                // If it's already 1, keep it 1. If not, keep it 0.
                if ($slip->status != 1) {
                    $slipUpdate['status'] = 0;
                }
            }

            $slip->update($slipUpdate);

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
            if ($request->lot_no_list) {
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

                    if ($insertedId) {
                        if ($request->set_qty[$key] != NULL) {

                            $set_size_id = $request->set_size[$key];
                            $size_group = MasterSizeMeasurement::where('id', $set_size_id)->where('status', 1)->value('size_group');
                            if ($size_group) {
                                $size_group_explode = explode(",", $size_group);
                                foreach ($size_group_explode as $size) {
                                    $save_sets_details = new ProductionDigitizationSetsDetails;
                                    $save_sets_details->production_slip_digitization_parts_id = $insertedId;
                                    $save_sets_details->set_size_id = $set_size_id;
                                    $save_sets_details->set_qty = $request->set_qty[$key];
                                    $save_sets_details->size = $size;
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
                            $save_sets_details->set_qty = NULL;
                            $save_sets_details->size = $request->individual_size[$key];
                            $save_sets_details->qauntity = $request->individual_qty[$key];
                            $save_sets_details->save();
                        }

                    }

                }
            }

            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'lot_no' => $request->lot_no,
                'status' => 1
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
            $save_data_main->start_date_time = $datetime;
            foreach ($request->stages as $stage_id => $days) {
                $expected = $this->calculateExpectedCompletion($datetime, $days);
                $save_data_main->{'stage_id_' . $stage_id} = $expected;
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
            $save_data_master->start_date_time = $request->start_date_time;
            foreach ($request->stages as $stage_id => $days) {
                $save_data_master->{'stage_id_' . $stage_id} = $days;
            }
            $save_data_master->status = 1;
            $save_data_master->save();

            // Only update slip status if production_slip_digitization_id is provided
            if ($request->production_slip_digitization_id) {
                $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
                if ($slip) {
                    $slip->update([
                        'lot_no' => $request->lot_no,
                        'status' => 1
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

    public function autoAllocateTime($lotNo, $production_datetime, $slip_id)
    {
        $exists = \App\Models\MasterStageWiseTimeAllocation::where('lot_no', $lotNo)->exists();
        if ($exists) return;

        // 1. Create the Master Allocation record (skeleton)
        $master = new \App\Models\MasterStageWiseTimeAllocation();
        $master->lot_no = $lotNo;
        
        // Pre-fill with unit-specific times if available
        $stages = \App\Models\MasterProductStage::where('status', 1)->get();
        foreach ($stages as $stage) {
            $col = 'stage_id_' . $stage->id;
            if (\Illuminate\Support\Facades\Schema::hasColumn('master_stage_wise_time_allocation', $col)) {
                // Try to find a unit assigned to this stage for this lot to get their default time
                $unitTime = 0;
                $tx = \App\Models\OrderStageTransaction::where('lot_no', $lotNo)->where('to_stage_id', $stage->id)->first()
                    ?? \App\Models\OrderPrintingStageTransaction::where('lot_no', $lotNo)->where('to_stage_id', $stage->id)->first();
                
                if ($tx && $tx->getToUnitMaster) {
                    $unitTime = $tx->getToUnitMaster->lot_time_in_days ?? 0;
                }
                
                $master->$col = $unitTime;

                // ✅ Removed: Populate Unified Timing table during rolls allotment
                // if ($unitTime > 0 || $stage->id == 3) {
                //      \App\Models\OrderLotStageTiming::updateOrCreate(
                //         ['lot_no' => $lotNo, 'master_stage_id' => $stage->id],
                //         [
                //             'days_allocated' => $unitTime,
                //             'status' => ($stage->id == 3) ? 1 : 0,
                //             'start_date' => $production_datetime,
                //             'end_date' => $this->calculateExpectedCompletion($production_datetime, $unitTime)
                //         ]
                //     );
                // }
            }
        }
        $master->save();

        // 2. Create the Tracking record
        $tracking = new \App\Models\OrderStageWiseTimeTracking();
        $tracking->lot_no = $lotNo;
        $tracking->start_date_time = $production_datetime;
        $tracking->save();
    }

    public function view(Request $request)
    {
        $data = Order::with('products.product_details.product_detail_stocks', 'products.order_stages.stage', 'products.order_stage_trnsactions')->where('id', $request->id)->first();
        return $data;
    }

    public function edit(Request $request)
    {
        $data = Order::where('id', $request->id)->first();
        return $data;
    }
    public function update(Request $request)
    {
        $update_data = Order::find($request->id);
        $update_data->order_type = $request->order_type;
        $update_data->status = 1;
        $update_data->save();

        return true;
    }

    function orderMainForRollAssign()
    {
        $results = OrderMain::where('status', 1)->get();
        $data = [];
        if ($results) {
            foreach ($results as $res) {
                $data[$res->id] = $res->sku;
            }
        }
        return $data;
    }

    public function getFabricsData()
    {
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
            $data[$res->fabric_id] = $res->sku . " - (" . $res->total_fabric . ")";
        }
        return $data;
    }

    public function getRollsData(Request $request)
    {
        $results = Stock::select(
            'id',
            'roll_no',
            'meter'
        )
            ->where('fabric_id', $request->fabric_id)
            ->get();
        $data = [];
        foreach ($results as $res) {
            $data[$res->id] = $res->roll_no . " - (" . $res->meter . " Meters)";
        }
        return $data;
    }

    public function getSlipDigitalization(Request $request)
    {
        // defaults status 0 data get for Digitization
        if ($request->slip_id) {
            $results = ProductionSlipDigitization::with([
                'getUnitMaster.masterFabricWarehouse'
            ])->where('id', $request->slip_id)->first();
        } else {
            $results = ProductionSlipDigitization::with([
                'getUnitMaster.masterFabricWarehouse'
            ])
                ->where('status', 0)
                ->whereNot('from_stage_id', 3)
                ->orderBy('id', 'asc')
                ->first();
        }
        $from_stage_id = $results->from_stage_id;

        // Default to packing (11) if it's a rework slip
        if ($results->type === 'rework') {
            $to_stage_id = 11;
        } else {
            if ($from_stage_id == 1) {
                $to_stage_id = 4;
            }
            if ($from_stage_id == 3) {
                $to_stage_id = 4;
            }
            if ($from_stage_id == 4) {
                $to_stage_id = 5;
            }
            if ($from_stage_id == 5) {
                $to_stage_id = 6;
            }
            if ($from_stage_id == 6) {
                $to_stage_id = 7;
            }
            if ($from_stage_id == 7) {
                $to_stage_id = 8;
            }
            if ($from_stage_id == 8) {
                $to_stage_id = 9;
            }
            if ($from_stage_id == 9) {
                $to_stage_id = 10;
            }
            if ($from_stage_id == 10) {
                $to_stage_id = 11;
            }
            if ($from_stage_id == 11) {
                $to_stage_id = 12;
            }
        }

        $data = [];
        if ($results) {

            $results_units = StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
                ->join('master_product_stages as master_stages', 'master_stages.id', '=', 'stage_master_units.master_stage_id')
                ->where('stage_master_units.status', 1)
                ->orderBy('master_stages.sequence', 'asc')
                ->select('stage_master_units.*', 'master_stages.sequence')
                ->get()
                ->toArray();

            foreach ($results_units as &$unit_data) {
                $whName = $unit_data['master_fabric_warehouse']['name'] ?? $unit_data['master_fabric_warehouse']['cutting_master_name'] ?? 'No Warehouse';
                $unit_data['name'] = $unit_data['name'] . ' (' . $whName . ')';
            }
            unset($unit_data);
            //dd($results_units);
            $unit_master_data = [];
            $from_stage = [];
            if ($results_units) {
                foreach ($results_units as $unit_data) {

                    // if($results->from_stage_id != $unit_data['master_stage_id']){
                    if ($unit_data['master_stage_id'] == $to_stage_id) {
                        $unit_master_data[] = [
                            'id' => $unit_data['id'],
                            'master_stage_id' => $unit_data['master_stage_id'],
                            'name' => $unit_data['name'],
                            'master_stage_name' => $unit_data['master_stage']['name'],

                        ];
                    }
                    if ($results['stage_master_unit_id'] == $unit_data['id']) {
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
            $last_production_datetime = null;
            $maxId = 0;

            // 1. OrderStageTransaction
            $tx1 = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $results->id)->orderBy('id', 'desc')->first();
            if ($tx1 && $tx1->id > $maxId) {
                $maxId = $tx1->id;
                $last_production_datetime = $tx1->production_datetime;
            }

            // 2. OrderPrintingStageTransaction
            $tx2 = \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $results->id)->orderBy('id', 'desc')->first();
            // Since IDs across tables aren't perfectly comparable, we can just take the most recent created_at or production_datetime
            // Let's use created_at to find the truly most recent transaction
            $latestTx = collect([
                \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $results->id)->orderBy('id', 'desc')->first(),
                \App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $results->id)->orderBy('id', 'desc')->first(),
                \App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $results->id)->orderBy('id', 'desc')->first(),
                \App\Models\OrderGodamStageTransaction::where('production_slip_digitization_id', $results->id)->orderBy('id', 'desc')->first(),
            ])->filter()->sortByDesc('created_at')->first();

            if ($latestTx) {
                $last_production_datetime = $latestTx->production_datetime;
            }

            $data = [
                'id' => $results->id,
                'slip_file' => $results->slip_file,
                'from_stage' => $from_stage,
                'unit_master_data' => $unit_master_data,
                'date_time' => $results->created_at,
                'status' => $results->status,
                'last_production_datetime' => $last_production_datetime
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
                'status' => 2
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
            $slip = ProductionSlipDigitization::lockForUpdate()->find($request->production_slip_digitization_id);
            if (!$slip) throw new \Exception('Slip not found');

            // --- 1. REVERSION LOGIC BY SLIP TYPE ---
            
            // A. If it's a Cutting/Rolls Allotment Slip (Initial Stage 3 Allotment)
            if ($slip->from_stage_id == 3) { 
                $fras = \App\Models\FabricRollAssigning::where('production_slip_digitization_id', $slip->id)->get();
                foreach ($fras as $fra) {
                    // Restore Roll Meter in Fabric Receipt
                    $fabricIds = explode(',', $fra->order_product_set?->fabric_id ?? '');
                    $roll = \App\Models\FabricReceiptDetail::find($fra->fabric_receipt_detail_id);
                    if ($roll) {
                        $new_qty = min($roll->meter, $roll->remaining_quantity + $fra->meter);
                        $roll->update(['remaining_quantity' => $new_qty]);
                    }

                    // Restore Sizes and Cutting Stage Remaining Quantities
                    $details = \App\Models\FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $fra->id)->get();
                    foreach ($details as $det) {
                        // Restore OrderProductSetDetail remaining_lot_allocated
                        if ($det->order_product_set_detail_id) {
                            \App\Models\OrderProductSetDetail::where('id', $det->order_product_set_detail_id)
                                ->increment('remaining_lot_allocated', $det->quantity);
                        }

                        // Restore OrderCuttingStage remaining_quantity
                        $cs = \App\Models\OrderCuttingStage::where('set_product_id', $fra->order_products_set_id)
                            ->where('to_assign_id', $fra->stage_master_unit_id)
                            ->where('status', '!=', 0)
                            ->orderBy('updated_at', 'desc')
                            ->first();
                        if ($cs) {
                            $cs->increment('remaining_quantity', $det->quantity);
                            $cs->update(['status' => 1]); // Back to partial/assigned
                        }
                        $det->delete();
                    }
                    $fra->delete();
                }

                // Delete associated Lots created for this slip
                \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)->delete();
            } else {
                // B. If it's a Movement Slip (e.g., Printing -> Stitching, Stitching -> Washing, etc.)
                
                // 1. Restore Source Transactions (those closed using this slip's image/file)
                $updateRevert = [
                    'image' => null,
                    'is_closed_for_unit' => 0,
                    'complete_date' => null,
                    'remaining_quantity' => DB::raw('quantity') // Reset to full available quantity
                ];

                \App\Models\OrderStageTransaction::where('image', $slip->slip_file)->update($updateRevert);
                \App\Models\OrderPrintingStageTransaction::where('image', $slip->slip_file)->update($updateRevert);
                \App\Models\OrderPrintingToStichingTransaction::where('image', $slip->slip_file)->update($updateRevert);

                // 2. Revert Source Timing Table (Mark previous stage as In Progress again)
                $involvedLots = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $slip->id)->pluck('lot_no')
                    ->merge(\App\Models\OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)->pluck('lot_no'))
                    ->merge(\App\Models\OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $slip->id)->pluck('lot_no'))
                    ->unique();

                foreach ($involvedLots as $lot) {
                    \App\Models\OrderLotStageTiming::where('lot_no', $lot)
                        ->where('master_stage_id', $slip->from_stage_id)
                        ->update([
                            'complete_date' => null,
                            'status' => 1 // Back to In Progress
                        ]);
                }

                // 3. Delete Target Transactions and their details
                $targetModels = [
                    \App\Models\OrderStageTransaction::class => \App\Models\OrderStageTransactionDetail::class,
                    \App\Models\OrderPrintingStageTransaction::class => \App\Models\OrderPrintingStageTransactionDetail::class,
                    \App\Models\OrderGodamStageTransaction::class => \App\Models\OrderGodamStageTransactionDetail::class,
                    \App\Models\OrderPrintingToStichingTransaction::class => \App\Models\OrderPrintingToStichingTransactionDetail::class,
                ];

                foreach ($targetModels as $txModel => $detModel) {
                    $txs = $txModel::where('production_slip_digitization_id', $slip->id)->get();
                    foreach ($txs as $tx) {
                        $fk = (new $detModel)->getForeignKey();
                        $detModel::where($fk, $tx->id)->delete();
                        $tx->delete();
                    }
                }
            }

            // --- 2. PACKING SPECIFIC CLEANUP ---
            if ($slip->to_stage_id == 11 || $slip->type == 'rework') {
                $packingMain = \App\Models\PackingMain::where('slip_id', $slip->id)->first();
                if ($packingMain) {
                    \App\Models\PackingItem::where('packing_main_id', $packingMain->id)->delete();
                    \App\Models\PackingBox::where('packing_main_id', $packingMain->id)->delete();
                    \App\Models\PackingCarton::where('packing_main_id', $packingMain->id)->delete();
                    $packingMain->delete();
                }
            }

            // --- 3. UNIFIED TIMING & ALLOCATION CLEANUP ---
            $lotNos = \App\Models\OrderLot::where('production_slip_digitization_id', $slip->id)->pluck('lot_no');
            if ($lotNos->isEmpty() && $slip->lot_no) {
                $lotNos = collect(explode(',', $slip->lot_no))->map(fn($l) => trim($l))->filter();
            }

            if ($lotNos->isNotEmpty()) {
                // Delete timing for the target stage (since the move is being undone)
                \App\Models\OrderLotStageTiming::whereIn('lot_no', $lotNos)
                    ->where('master_stage_id', '!=', $slip->from_stage_id)
                    ->delete();

                // If it was the initial digitization (Stage 3), clean up tracking/allocation
                if ($slip->from_stage_id == 3) {
                    \App\Models\MasterStageWiseTimeAllocation::whereIn('lot_no', $lotNos)->delete();
                    \App\Models\OrderStageWiseTimeTracking::whereIn('lot_no', $lotNos)->delete();
                }
            }

            // --- 4. SLIP PARTS CLEANUP ---
            $parts = \App\Models\ProductionSlipDigitizationParts::where('production_slip_digitization_id', $slip->id)->get();
            foreach ($parts as $part) {
                \App\Models\ProductionDigitizationSetsDetails::where('production_slip_digitization_parts_id', $part->id)->delete();
                $part->delete();
            }

            // Finally, mark slip as deleted (status 3)
            $slip->update(['status' => 3]);

            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Slip Digitization and all associated production data have been properly reverted.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            $return_data['message'] = 'Deletion failed: ' . $e->getMessage();
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
        $WORK_END = 17;   // 5 PM (8-hour workday)
        $HOURS_PER_DAY = 8; // 8 hours per day
        $HALF_DAY_HOURS = 4; // 4 hours for half day

        $current = new \DateTime($startDateTime);
        $hour = (int) $current->format('H');

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
        if ($request->slip_id) {
            $results = ProductionSlipDigitization::with([
                'getUnitMaster.masterFabricWarehouse'
            ])
                ->where('id', $request->slip_id)->first();
        } else {

            if ($request->is_skip == 1) {
                $results = ProductionSlipDigitization::with([
                    'getUnitMaster.masterFabricWarehouse'
                ])
                    ->where('status', 2)
                    ->where('from_stage_id', 3)
                    ->orderBy('id', 'asc')
                    ->first();
            } else {
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
    public function stages($stage_master_unit_id)
    {
        $data = StageMasterUnit::with('masterStage')->where('status', 1)->where('master_fabric_warehouse_id', $stage_master_unit_id)->orderBy('id', 'asc')->get();
        return $data;
    }
    public function master_fabric_warehouse($master_fabric_warehouse_id)
    {
        $data = masterFabricWarehouse::where('id', $master_fabric_warehouse_id)->first();
        return $data;
    }
    public function designs()
    {
        $data = ProductionGoods::where('status', 1)->get();
        return $data;
    }

    public function stage_unit_data($master_fabric_warehouse_id, $master_stage_id)
    {
        $data = StageMasterUnit::where('master_fabric_warehouse_id', $master_fabric_warehouse_id)->where('master_stage_id', $master_stage_id)->first();
        return $data;
    }

    public function roll_numbers()
    {
        $data = FabricReceiptDetail::whereNot('status', 0)->pluck('roll_number');
        return $data;
    }
    public function order_numbers()
    {
        $data = OrderMain::whereNot('status', 0)->pluck('sku');
        return $data;
    }
    // public function cutting_master_orders($cutting_unit){
    //     $order_main_ids = OrderCuttingStage::where('to_assign_id',$cutting_unit)->pluck('order_main_id');
    //     $main_orders = OrderMain::whereIn('id',$order_main_ids)->get();

    //     return $main_orders;
    // }
    function product_sizes()
    {
        $data = MasterSizeMeasurement::whereIn('status', [1, 2])->orderBy('id', 'asc')->get();
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
        $data = OrderProductSet::where('order_main_id', $main_order_id)->get();
        return $data;
    }

    // Step 2: Design → Rolls + Size Groups
    public function getDesignDetails(Request $request)
    {
        $orderNo = $request->order_no;
        $design = $request->design_no;

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
    // public function orders($stage_master_unit_id)
    // {
    //     $main_orders = OrderMain::orderby('id','desc')->with([
    //             'OrderProductSets' => function ($q) use ($stage_master_unit_id) {
    //                 $q->where('stage_master_unit_id', $stage_master_unit_id)
    //                 ->with([
    //                     'fabric.receiptDetails',
    //                     'colors',
    //                     'master_design_pattern',
    //                     'master_product_fitting',
    //                     'size_measurement',
    //                     'stage_master_unit',
    //                     'product_set_details'
    //                 ]);
    //             }
    //         ])
    //         ->whereHas('OrderProductSets', function ($q) use ($stage_master_unit_id) {
    //             $q->where('stage_master_unit_id', $stage_master_unit_id);
    //         })
    //         ->get();
    //         // dd($main_orders);
    //         foreach($main_orders as $order){
    //             $order_id = $order->id;
    //             $data[$order_id] = $this->getOrderPackingData($order_id);
    //         }

    //         // $this->getOrderPackingData(1);
    //     // FALLBACK: If product_set_details is empty, check if they exist under order_main_id (due to previous bug)
    //     foreach ($main_orders as $order) {
    //         foreach ($order->OrderProductSets as $set) {
    //             if ($set->product_set_details->isEmpty()) {
    //                 $fallbackDetails = \App\Models\OrderProductSetDetail::where('order_products_set_id', $order->id)->get();
    //                 if (isset($data[$order->id]) && !($data[$order->id]['total'] <= $data[$order->id]['packed']) ){
    //                     if ($fallbackDetails->isNotEmpty()) {
    //                         $set->setRelation('product_set_details', $fallbackDetails);
    //                     }
    //                 }

    //             }
    //         }
    //     }

    //     return $main_orders;
    // }

    public function orders($stage_master_unit_id)
    {
        $main_orders = OrderMain::query()
            ->orderByDesc('id')
            ->where(function ($query) use ($stage_master_unit_id) {
                $query->whereHas('OrderProductSets', function ($q) use ($stage_master_unit_id) {
                    $q->where('stage_master_unit_id', $stage_master_unit_id);
                })
                    ->orWhereHas('orderCuttingStages', function ($q) use ($stage_master_unit_id) {
                        $q->where('to_assign_id', $stage_master_unit_id);
                    });
            })
            ->with([
                'OrderProductSets' => function ($q) use ($stage_master_unit_id) {
                    $q->where(function ($subQ) use ($stage_master_unit_id) {
                        $subQ->where('stage_master_unit_id', $stage_master_unit_id)
                            ->orWhereHas('orderCuttingStages', function ($oc) use ($stage_master_unit_id) {
                                $oc->where('to_assign_id', $stage_master_unit_id);
                            });
                    })
                        ->with([
                            'fabric.receiptDetails',
                            'colors',
                            'master_design_pattern',
                            'master_product_fitting',
                            'size_measurement',
                            'stage_master_unit',
                            'product_set_details',
                            'orderCuttingStages' => function ($oc) use ($stage_master_unit_id) {
                                $oc->where('to_assign_id', $stage_master_unit_id)
                                    ->with(['fabric.receiptDetails', 'pattern', 'master_fitting', 'cutting_master']);
                            }
                        ]);
                }
            ])
            ->get();

        /** Load packing data */
        $packingData = [];
        foreach ($main_orders as $order) {
            $packingData[$order->id] = getOrderDispatchData($order->id);
        }

        /**  Remove fully packed orders */
        $main_orders = $main_orders->filter(function ($order) use ($packingData) {
            return isset($packingData[$order->id])
                && $packingData[$order->id]['remaining'] > 0;
        })->values();
        // dd($main_orders, $packingData);
        /** Load fallback details (KEY FIX HERE) */
        $fallbackDetails = \App\Models\OrderProductSetDetail::whereIn(
            'order_products_set_id',
            $main_orders->pluck('OrderProductSets')
                ->flatten()
                ->pluck('id')
        )
            ->get()
            ->groupBy('order_products_set_id');

        /** Attach fallback data correctly */
        foreach ($main_orders as $order) {
            foreach ($order->OrderProductSets as $set) {
                // FALLBACK: Populate design info from assignments if primary fields are null (happens in partial assignment)
                if ($set->orderCuttingStages->isNotEmpty()) {
                    $firstOsc = $set->orderCuttingStages->first();
                    if (!$set->fabric_id && $firstOsc->fabric) {
                        $set->setRelation('fabric', $firstOsc->fabric);
                    }
                    if (!$set->master_design_pattern_id && $firstOsc->pattern) {
                        $set->setRelation('master_design_pattern', $firstOsc->pattern);
                    }
                    if (!$set->master_product_fitting_id && $firstOsc->master_fitting) {
                        $set->setRelation('master_product_fitting', $firstOsc->master_fitting);
                    }
                    if (!$set->stage_master_unit_id && $firstOsc->cutting_master) {
                        $set->setRelation('stage_master_unit', $firstOsc->cutting_master);
                    }
                }

                if ($set->product_set_details->isEmpty()) {
                    if (isset($fallbackDetails[$set->id])) {
                        $set->setRelation(
                            'product_set_details',
                            $fallbackDetails[$set->id]
                        );
                    }
                }
            }
        }

        return $main_orders;
    }

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

                // Collect fabric, order, design, color, fitting, and pattern info
                if ($detail->orderProductSetDetail) {
                    $productSet = $detail->orderProductSetDetail->orderProductSet;
                    if ($productSet) {
                        // Get fabric names
                        if ($productSet->fabric_names) {
                            $names = explode(',', $productSet->fabric_names);
                            foreach ($names as $n) {
                                $fabricNames[trim($n)] = true;
                            }
                        }

                        // Get order SKU
                        if ($productSet->orderMain) {
                            $orderNumbers[$productSet->orderMain->sku] = true;
                        }

                        // NEW: Get design number, color, fitting, and pattern
                        if ($productSet->design_number) {
                            $designNumbers[$productSet->design_number] = true;
                        }
                        if ($productSet->colors) {
                            $colorNames[$productSet->colors->name] = true;
                        }
                        if ($productSet->master_product_fitting) {
                            $fittingNames[$productSet->master_product_fitting->name] = true;
                        }
                        if ($productSet->master_design_pattern) {
                            $patternNames[$productSet->master_design_pattern->name] = true;
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
            'design_numbers' => array_keys($designNumbers ?? []),
            'color_names' => array_keys($colorNames ?? []),
            'fitting_names' => array_keys($fittingNames ?? []),
            'pattern_names' => array_keys($patternNames ?? []),
            'total_meters' => $totalMeters,
            'total_rolls' => $rollAssignments->count(),
            'size_wise_quantities' => $sizeWiseQuantities,
            'rolls' => $rollsData
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
            ->get()
            ->pluck('fabric_names')
            ->flatMap(function($names) {
                return array_map('trim', explode(',', $names));
            })
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

            // Update slip status based on is_final flag
            $slipUpdate = [
                'lot_no' => $request->lot_no,
                'to_stage_id' => $request->to_stage_id ?? 4
            ];

            if ($request->is_final == 1) {
                $slipUpdate['status'] = 1;
            } else {
                if ($slip->status != 1) {
                    $slipUpdate['status'] = 0;
                }
            }

            $slip->update($slipUpdate);

            $order_lot_update = OrderLot::where('lot_no', $request->lot_no)->update([
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

            // Check if it is in Godam
            $godamTx = OrderGodamStageTransaction::where('lot_no', $request->lot_no)->where('remaining_quantity', '>', 0)->orderBy('id', 'desc')->first();

            $from_stage_id = 3;
            $sub_stage_id_from = $slip->stage_master_unit_id;

            if ($godamTx) {
                $from_stage_id = 13; // Godam
                $sub_stage_id_from = $godamTx->sub_stage_id_to;

                $godamIds = OrderGodamStageTransaction::where('lot_no', $request->lot_no)->where('remaining_quantity', '>', 0)->pluck('id');
                
                OrderGodamStageTransaction::whereIn('id', $godamIds)->update([
                    'remaining_quantity' => 0,
                    'status' => 2,
                    'complete_date' => $request->production_datetime
                ]);

                OrderGodamStageTransactionDetail::whereIn('order_godam_stage_transaction_id', $godamIds)->update([
                    'remaining_quantity' => 0
                ]);
            }

            $this->createTransactionWithDetails($request->lot_no, $from_stage_id, 4, $slip->id, $fab_roll_assigning->order_products_set_id, $sub_stage_id_from, $request->to_stage_unit_id, $fab_roll_assigning->id, $request->production_datetime);

            if ($request->is_final == 1) {
                // Close ANY incoming assignments for this lot and this unit to hide from Unit assignments list
                $this->closeIncomingAssignments($request->lot_no, $slip->stage_master_unit_id, $slip->slip_file, $request->production_datetime);
            }

            // Send WhatsApp notification
            $this->sendAssignmentWhatsApp($request->to_stage_unit_id, $request->lot_no);

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

            // Update slip status based on is_final flag
            $slipUpdate = [
                'lot_no' => $request->lot_no,
                'save_type' => 2,
                'to_stage_id' => $request->to_stage_id ?? 1
            ];

            if ($request->is_final == 1) {
                $slipUpdate['status'] = 1;
            } else {
                if ($slip->status != 1) {
                    $slipUpdate['status'] = 0;
                }
            }

            $slip->update($slipUpdate);

            $order_lot_update = OrderLot::where('lot_no', $request->lot_no)->update([
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

            // Save Digitized Parts (Size-wise) from the request
            if ($request->sizes) {
                foreach ($request->sizes as $size => $qty) {
                    if ($qty > 0) {
                        \App\Models\ProductionSlipDigitizationParts::create([
                            'production_slip_digitization_id' => $slip->id,
                            'lot_no' => $request->lot_no,
                            'to_unit_id' => $request->to_stage_unit_id,
                            'single_size' => $size,
                            'single_quantity' => $qty,
                            'status' => 1
                        ]);
                    }
                }
            }

            // Create Transaction with Details (Cutting -> Printing)
            // Stage 3 is Cutting. Stage 1 is Printing.
            $this->createTransactionWithDetails($request->lot_no, 3, 1, $slip->id, $fab_roll_assigning->order_products_set_id, $slip->stage_master_unit_id, $request->to_stage_unit_id, $fab_roll_assigning->id, $request->production_datetime, $request->sizes);

            if ($request->is_final == 1) {
                // Close ANY incoming assignments for this lot and this unit
                $this->closeIncomingAssignments($request->lot_no, $slip->stage_master_unit_id, $slip->slip_file, $request->production_datetime);
            }

            // Send WhatsApp notification
            $this->sendAssignmentWhatsApp($request->to_stage_unit_id, $request->lot_no);

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

    protected function createTransactionWithDetails($lot_no, $from_stage_id, $to_stage_id, $slip_id = null, $order_products_set_id, $sub_stage_id, $sub_stage_id_to, $production_fabric_roll_assigning_id, $production_datetime, $newSizes = null)
    {
        $production_fabric_roll_assigning = FabricRollAssigning::where('id', $production_fabric_roll_assigning_id)->first();

        // Determine pieces: Use provided newSizes if available, else copy from original cutting slip
        if ($newSizes) {
            $totalQuantity = array_sum($newSizes);
            $detailsSource = [];
            foreach ($newSizes as $s => $q) {
                if ($q > 0)
                    $detailsSource[] = (object) ['size' => $s, 'quantity' => $q];
            }
        } else {
            $totalQuantity = FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $production_fabric_roll_assigning_id)->sum('quantity');
            $detailsSource = FabricRollAssigningsDetail::where('production_fabric_roll_assigning_id', $production_fabric_roll_assigning_id)->get();
        }

        $unitTo = StageMasterUnit::find($sub_stage_id_to);
        $days = $unitTo->lot_time_in_days ?? 0;
        $startTime = $production_datetime ?: now()->toDateTimeString();
        $expectedAt = $this->calculateExpectedCompletion($startTime, $days);

        $commonData = [
            'from_stage_id' => $from_stage_id,
            'to_stage_id' => $to_stage_id,
            'lot_no' => $lot_no,
            'quantity' => $totalQuantity,
            'remaining_quantity' => $totalQuantity,
            'sub_stage_id' => $sub_stage_id,
            'sub_stage_id_to' => $sub_stage_id_to,
            'processed_by' => auth()->id(),
            'production_datetime' => $production_datetime,
            'production_slip_digitization_id' => $slip_id,
            'status' => 1,
            'start_date' => $startTime,
            'end_date' => $expectedAt,
        ];

        // ✅ NEW: Update Unified Timing Table
        $timingData = [
            'unit_id' => $sub_stage_id_to,
            'days_allocated' => $days,
            'status' => 1
        ];

        // Only set start/end date if it's not stitching (4) OR if stitching timing isn't set yet
        // As per user: "When slip digitilised of printing then stiching time of start and end date will not change"
        $existingTiming = \App\Models\OrderLotStageTiming::where('lot_no', $lot_no)->where('master_stage_id', $to_stage_id)->first();
        if ($to_stage_id != 4 || !$existingTiming || (!$existingTiming->start_date && !$existingTiming->end_date)) {
            $timingData['start_date'] = $startTime;
            $timingData['end_date'] = $expectedAt;
        }

        if ($to_stage_id != 3) {
            \App\Models\OrderLotStageTiming::updateOrCreate(
                ['lot_no' => $lot_no, 'master_stage_id' => $to_stage_id],
                $timingData
            );
        }

        if ($to_stage_id == 1) {
            $transaction = OrderPrintingStageTransaction::create($commonData);

            foreach ($detailsSource as $item) {
                OrderPrintingStageTransactionDetail::create([
                    'order_printing_stage_transaction_id' => $transaction->id,
                    'size' => $item->size,
                    'quantity' => $item->quantity
                ]);
            }
        } else {
            $transaction = OrderStageTransaction::create($commonData);

            foreach ($detailsSource as $item) {
                OrderStageTransactionDetail::create([
                    'order_stage_transaction_id' => $transaction->id,
                    'size' => $item->size,
                    'quantity' => $item->quantity
                ]);
            }
        }

        return $transaction;
    }

    public function getStageUnits($warehouse_id = null, $master_stage_id)
    {
        $query = \App\Models\StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
            ->join('master_product_stages as master_stages', 'master_stages.id', '=', 'stage_master_units.master_stage_id')
            ->where('stage_master_units.status', 1)
            ->where('stage_master_units.master_stage_id', $master_stage_id);

        if ($warehouse_id) {
            $query->where('stage_master_units.master_fabric_warehouse_id', $warehouse_id);
        }

        return $query->orderBy('stage_master_units.name', 'asc')
            ->select('stage_master_units.*')
            ->get();
    }

    public function getAvailableLotsForStage($stage_id)
    {
        // 1. Get all lots that have entered this stage
        $model_name = ($stage_id == 1)
            ? OrderPrintingStageTransaction::class
            : OrderStageTransaction::class;

        $out_model_name = ($stage_id == 1)
            ? OrderPrintingToStichingTransaction::class
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
            $outflow = $out_model_name::where('from_stage_id', $stage_id)
                ->where('lot_no', $lot_no)
                ->sum('quantity');

            // If there is stock remaining, add to list
            if (($inflow - $outflow) > 0) {
                // Determine Order No (Optional, for display)
                // $transaction = $model_name::where('lot_no', $lot_no)->first();
                $availableLots[] = (object) [
                    'lot_no' => $lot_no,
                    // 'remaining_qty' => $inflow - $outflow
                ];
            }
        }

        return $availableLots;
    }

    public function getLotDetailsForHandSlip($lot_no, $current_stage_id, $movement_type = 1)
    {
        // 1. Calculate Inflow (Total Items entered this stage) per size
        $inflowData = collect();

        // Standard stage transactions (To current stage)
        $inflowData = $inflowData->concat(
            OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transactions.id', '=', 'order_stage_transaction_details.order_stage_transaction_id')
                ->where('order_stage_transactions.to_stage_id', $current_stage_id)
                ->where('order_stage_transactions.lot_no', $lot_no)
                ->select('order_stage_transaction_details.size', 'order_stage_transaction_details.quantity')
                ->get()
        );

        // Printing specific transactions (If stage is Printing)
        if ($current_stage_id == 1) {
            $inflowData = $inflowData->concat(
                OrderPrintingStageTransactionDetail::join('order_printing_stage_transactions', 'order_printing_stage_transactions.id', '=', 'order_printing_stage_transaction_details.order_printing_stage_transaction_id')
                    ->where('order_printing_stage_transactions.to_stage_id', $current_stage_id)
                    ->where('order_printing_stage_transactions.lot_no', $lot_no)
                    ->select('order_printing_stage_transaction_details.size', 'order_printing_stage_transaction_details.quantity')
                    ->get()
            );
        }

        // 2. Calculate Outflow (What already LEFT this stage) per size
        $outflowData = collect();

        // Standard stage transactions (From current stage)
        $outflowData = $outflowData->concat(
            OrderStageTransactionDetail::join('order_stage_transactions', 'order_stage_transactions.id', '=', 'order_stage_transaction_details.order_stage_transaction_id')
                ->where('order_stage_transactions.from_stage_id', $current_stage_id)
                ->where('order_stage_transactions.lot_no', $lot_no)
                ->select('order_stage_transaction_details.size', 'order_stage_transaction_details.quantity')
                ->get()
        );

        // Movement to Printing (Stage 3 outflow)
        if ($current_stage_id == 3) {
            $outflowData = $outflowData->concat(
                OrderPrintingStageTransactionDetail::join('order_printing_stage_transactions', 'order_printing_stage_transactions.id', '=', 'order_printing_stage_transaction_details.order_printing_stage_transaction_id')
                    ->where('order_printing_stage_transactions.from_stage_id', $current_stage_id)
                    ->where('order_printing_stage_transactions.lot_no', $lot_no)
                    ->select('order_printing_stage_transaction_details.size', 'order_printing_stage_transaction_details.quantity')
                    ->get()
            );
        }

        // Movement from Printing to Stitching (Stage 1 outflow)
        if ($current_stage_id == 1) {
            $outflowData = $outflowData->concat(
                OrderPrintingToStichingTransactionDetail::join('order_printing_to_stiching_transactions', 'order_printing_to_stiching_transactions.id', '=', 'order_printing_to_stiching_transaction_details.order_printing_to_stiching_transaction_id')
                    ->where('order_printing_to_stiching_transactions.from_stage_id', $current_stage_id)
                    ->where('order_printing_to_stiching_transactions.lot_no', $lot_no)
                    ->select('order_printing_to_stiching_transaction_details.size', 'order_printing_to_stiching_transaction_details.quantity')
                    ->get()
            );
        }

        // Movement to Godam (any stage to 13)
        $outflowData = $outflowData->concat(
            OrderGodamStageTransactionDetail::join('order_godam_stage_transactions', 'order_godam_stage_transactions.id', '=', 'order_godam_stage_transaction_details.order_godam_stage_transaction_id')
                ->where('order_godam_stage_transactions.from_stage_id', $current_stage_id)
                ->where('order_godam_stage_transactions.lot_no', $lot_no)
                ->select('order_godam_stage_transaction_details.size', 'order_godam_stage_transaction_details.quantity')
                ->get()
        );

        $inventory = [];
        foreach ($inflowData as $item) {
            $inventory[$item->size] = ($inventory[$item->size] ?? 0) + $item->quantity;
        }

        foreach ($outflowData as $item) {
            if (isset($inventory[$item->size])) {
                $inventory[$item->size] -= $item->quantity;
            }
        }

        // 3. Filter out zero quantities (optional, but cleaner)
        // $inventory = array_filter($inventory, function($qty) { return $qty > 0; });

        // 4. Get Basic Lot Info (Cutting Master Info)
        $basicInfo = $this->getLotDetailsForDisplay($lot_no);
        if ($basicInfo) {
            $basicInfo['total_inflow'] = $inflowData->sum('quantity');
            $basicInfo['total_remaining'] = array_sum($inventory);
        }

        // Determine next/previous stage units
        $available_units = [];
        $slip_id = request()->production_slip_digitization_id;
        $slip = ProductionSlipDigitization::find($slip_id);
        $warehouse_id = $slip->getUnitMaster->master_fabric_warehouse_id ?? 0;

        if ($movement_type == 2) {
            // Damage Movement (Backward) - Allow any stage
            $available_units = StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
                ->where('status', 1)
                ->get()
                ->map(function ($u) {
                    $whName = $u->masterFabricWarehouse->name ?? $u->masterFabricWarehouse->cutting_master_name ?? 'No Warehouse';
                    return [
                        'id' => $u->id,
                        'name' => $u->name . ' (' . $whName . ')',
                        'master_stage_name' => $u->masterStage->name
                    ];
                });
        } else {
            // Regular Movement (Forward)
            // Existing logic to determine to_stage_id
            $to_stage_id = 0;
            if ($current_stage_id == 1) {
                $is_stitching = \App\Models\OrderLot::where('lot_no', $lot_no)->value('is_stitching');
                if (!$is_stitching) {
                    $to_stage_id = 13; // Godam
                } else {
                    $to_stage_id = 4;
                }
            } elseif ($current_stage_id == 3) {
                $to_stage_id = 4;
            } elseif ($current_stage_id == 4) {
                $to_stage_id = 5;
            } elseif ($current_stage_id == 5) {
                $to_stage_id = 6;
            } elseif ($current_stage_id == 6) {
                $to_stage_id = 7;
            } elseif ($current_stage_id == 7) {
                $to_stage_id = 8;
            } elseif ($current_stage_id == 8) {
                $to_stage_id = 9;
            } elseif ($current_stage_id == 9) {
                $to_stage_id = 10;
            } elseif ($current_stage_id == 10) {
                $to_stage_id = 11;
            } elseif ($current_stage_id == 11) {
                $to_stage_id = 12;
            }

            if ($to_stage_id > 0) {
                $filtered = StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
                    ->where('master_stage_id', $to_stage_id)
                    ->where('status', 1)
                    ->get();

                // Second: without that stage (exclude already fetched)
                if ($to_stage_id == 11) {
                    $others = StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
                        ->where('master_stage_id', '!=', $to_stage_id)
                        ->where('status', 1)
                        ->get();
                } else {
                    $others = StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
                        ->where('master_stage_id', '!=', $to_stage_id)
                        ->where('status', 1)
                        ->whereNotIn('master_stage_id', [11, 12, 13])
                        ->get();
                }

                // Merge both (filtered first)
                $available_units = $filtered->concat($others)->map(function ($u) {
                    $whName = $u->masterFabricWarehouse->name ?? $u->masterFabricWarehouse->cutting_master_name ?? 'No Warehouse';
                    return [
                        'id' => $u->id,
                        'name' => $u->name . ' (' . $whName . ')',
                        'master_stage_name' => $u->masterStage->name ?? null
                    ];
                });
            }

            // Also check for Godam if applicable
            $stage_check = OrderStageTransaction::where('lot_no', $lot_no)->where(function ($q) {
                $q->where('from_stage_id', 3)->orWhere('to_stage_id', 4);
            })->first();

            if (!$stage_check) {
                $stage_check = OrderPrintingToStichingTransaction::where('lot_no', $lot_no)->first();
            }

            if (!$stage_check) {
                $godam_units = StageMasterUnit::with(['masterStage', 'masterFabricWarehouse'])
                    ->where('master_stage_id', 13)
                    ->get()
                    ->map(function ($u) {
                        $whName = $u->masterFabricWarehouse->name ?? $u->masterFabricWarehouse->cutting_master_name ?? 'No Warehouse';
                        return [
                            'id' => $u->id,
                            'name' => $u->name . ' (' . $whName . ')',
                            'master_stage_name' => $u->masterStage->name
                        ];
                    });
                
                $availArray = is_array($available_units) ? $available_units : $available_units->toArray();
                $available_units = array_merge($availArray, $godam_units->toArray());
            }
        }

        // Remove any duplicates (like Godam being added twice)
        $available_units = collect($available_units)->unique('id')->values()->toArray();

        return [
            'lot_no' => $lot_no,
            'inventory' => $inventory,
            'basic_info' => $basicInfo,
            'available_units' => $available_units
        ];
    }

    public function storeHandSlip(Request $request)
    {
        DB::beginTransaction();
        try {
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
            if (!$slip)
                throw new \Exception('Slip not found');

            $stage_master_unit_from = StageMasterUnit::where('id', $request->from_stage_id)->first();
            $stage_master_unit_to = StageMasterUnit::where('id', $request->to_stage_id)->first();

            $from_stage_id = $stage_master_unit_from->master_stage_id;
            $to_stage_id = $stage_master_unit_to->master_stage_id;
            $lot_no = $request->lot_no;
            $sizes = $request->sizes;

            $movement_type = $request->movement_type ?? 1;

            // 1. Validate Inventory (using new core logic)
            $inventoryData = $this->getLotDetailsForHandSlip($lot_no, $from_stage_id, $movement_type);
            $currentInventory = $inventoryData['inventory'];
            $totalMoved = 0;

            foreach ($sizes as $size => $qty) {
                if ($qty > 0) {
                    if (!isset($currentInventory[$size]) || $currentInventory[$size] < $qty) {
                        throw new \Exception("Insufficient inventory for Size $size. Available: " . ($currentInventory[$size] ?? 0));
                    }
                    $totalMoved += $qty;
                }
            }

            if ($totalMoved == 0)
                throw new \Exception('No quantity selected to move.');

            // 2. Identify and Update Source Transactions (Loop for multiple split assignments)
            $remainingToDecrement = $totalMoved;

            // Try Standard Transactions
            $standardTx = OrderStageTransaction::where('to_stage_id', $from_stage_id)
                ->where('sub_stage_id_to', $stage_master_unit_from->id)
                ->where('lot_no', $lot_no)
                ->where('remaining_quantity', '>', 0)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($standardTx as $tx) {
                if ($remainingToDecrement <= 0)
                    break;
                $decrement = min($tx->remaining_quantity, $remainingToDecrement);
                $tx->remaining_quantity -= $decrement;
                if ($tx->remaining_quantity <= 0)
                    $tx->status = 2; // Completed
                $tx->save();
                $remainingToDecrement -= $decrement;
            }

            // Check Printing specific (If still more to decrement)
            if ($remainingToDecrement > 0 && $from_stage_id == 1) {
                $printingTx = OrderPrintingStageTransaction::where('to_stage_id', $from_stage_id)
                    ->where('sub_stage_id_to', $stage_master_unit_from->id)
                    ->where('lot_no', $lot_no)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($printingTx as $tx) {
                    if ($remainingToDecrement <= 0)
                        break;
                    $decrement = min($tx->remaining_quantity, $remainingToDecrement);
                    $tx->remaining_quantity -= $decrement;
                    if ($tx->remaining_quantity <= 0)
                        $tx->status = 2;
                    $tx->save();
                    $remainingToDecrement -= $decrement;
                }
            }

            // Check Transition specific (If still more to decrement)
            if ($remainingToDecrement > 0 && $from_stage_id == 4) {
                $transitionTx = OrderPrintingToStichingTransaction::where('to_stage_id', $from_stage_id)
                    ->where('sub_stage_id_to', $stage_master_unit_from->id)
                    ->where('lot_no', $lot_no)
                    ->where('remaining_quantity', '>', 0)
                    ->orderBy('id', 'asc')
                    ->get();

                foreach ($transitionTx as $tx) {
                    if ($remainingToDecrement <= 0)
                        break;
                    $decrement = min($tx->remaining_quantity, $remainingToDecrement);
                    $tx->remaining_quantity -= $decrement;
                    if ($tx->remaining_quantity <= 0)
                        $tx->status = 2;
                    $tx->save();
                    $remainingToDecrement -= $decrement;
                }
            }

            // 3. Create Target Transaction
            $transaction = null;
            if ($from_stage_id == 1 && $to_stage_id == 4) {
                // Printing -> Stitching
                $unitTo = StageMasterUnit::find($stage_master_unit_to->id);
                $days = $unitTo->lot_time_in_days ?? 0;
                $pTime = $request->production_datetime ?: now()->toDateTimeString();
                $expectedAt = $this->calculateExpectedCompletion($pTime, $days);

                $transaction = OrderPrintingToStichingTransaction::create([
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => $to_stage_id,
                    'sub_stage_id' => $stage_master_unit_from->id,
                    'sub_stage_id_to' => $stage_master_unit_to->id,
                    'lot_no' => $lot_no,
                    'quantity' => $totalMoved,
                    'remaining_quantity' => $totalMoved,
                    'production_datetime' => $request->production_datetime,
                    'production_slip_digitization_id' => $slip->id,
                    'status' => 1,
                    'type' => $movement_type,
                    'start_date' => $pTime,
                    'end_date' => $expectedAt,
                ]);



                foreach ($sizes as $size => $qty) {
                    if ($qty > 0) {
                        OrderPrintingToStichingTransactionDetail::create([
                            'order_printing_to_stiching_transaction_id' => $transaction->id,
                            'size' => $size,
                            'quantity' => $qty
                        ]);
                    }
                }
            } elseif ($to_stage_id == 1) {
                // To Printing
                $unitTo = StageMasterUnit::find($stage_master_unit_to->id);
                $days = $unitTo->lot_time_in_days ?? 0;
                $pTime = $request->production_datetime ?: now()->toDateTimeString();
                $expectedAt = $this->calculateExpectedCompletion($pTime, $days);

                $transaction = OrderPrintingStageTransaction::create([
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => $to_stage_id,
                    'sub_stage_id' => $stage_master_unit_from->id,
                    'sub_stage_id_to' => $stage_master_unit_to->id,
                    'lot_no' => $lot_no,
                    'quantity' => $totalMoved,
                    'remaining_quantity' => $totalMoved,
                    'production_datetime' => $request->production_datetime,
                    'production_slip_digitization_id' => $slip->id,
                    'status' => 1,
                    'type' => $movement_type,
                    'start_date' => $pTime,
                    'end_date' => $expectedAt,
                ]);

                foreach ($sizes as $size => $qty) {
                    if ($qty > 0) {
                        OrderPrintingStageTransactionDetail::create([
                            'order_printing_stage_transaction_id' => $transaction->id,
                            'size' => $size,
                            'quantity' => $qty
                        ]);
                    }
                }
            } elseif ($to_stage_id == 13) {
                // To Godam
                $unitTo = StageMasterUnit::find($stage_master_unit_to->id);
                $days = $unitTo->lot_time_in_days ?? 0;
                $pTime = $request->production_datetime ?: now()->toDateTimeString();
                $expectedAt = $this->calculateExpectedCompletion($pTime, $days);

                $transaction = OrderGodamStageTransaction::create([
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => $to_stage_id,
                    'sub_stage_id' => $stage_master_unit_from->id,
                    'sub_stage_id_to' => $stage_master_unit_to->id,
                    'lot_no' => $lot_no,
                    'quantity' => $totalMoved,
                    'remaining_quantity' => $totalMoved,
                    'production_datetime' => $request->production_datetime,
                    'production_slip_digitization_id' => $slip->id,
                    'status' => 1,
                    'type' => $movement_type,
                    'start_date' => $pTime,
                    'end_date' => $expectedAt,
                ]);

                foreach ($sizes as $size => $qty) {
                    if ($qty > 0) {
                        OrderGodamStageTransactionDetail::create([
                            'order_godam_stage_transaction_id' => $transaction->id,
                            'size' => $size,
                            'quantity' => $qty,
                            'remaining_quantity' => $qty,
                        ]);
                    }
                }
            }

            if (!$transaction) {
                // Default Standard Movement
                $unitTo = StageMasterUnit::find($stage_master_unit_to->id);
                $days = $unitTo->lot_time_in_days ?? 0;
                $pTime = $request->production_datetime ?: now()->toDateTimeString();
                $expectedAt = $this->calculateExpectedCompletion($pTime, $days);

                $transaction = OrderStageTransaction::create([
                    'from_stage_id' => $from_stage_id,
                    'to_stage_id' => $to_stage_id,
                    'sub_stage_id' => $stage_master_unit_from->id,
                    'sub_stage_id_to' => $stage_master_unit_to->id,
                    'lot_no' => $lot_no,
                    'quantity' => $totalMoved,
                    'remaining_quantity' => $totalMoved,
                    'production_datetime' => $request->production_datetime,
                    'production_slip_digitization_id' => $slip->id,
                    'status' => 1,
                    'type' => $movement_type,
                    'start_date' => $pTime,
                    'end_date' => $expectedAt,
                ]);

                foreach ($sizes as $size => $qty) {
                    if ($qty > 0) {
                        OrderStageTransactionDetail::create([
                            'order_stage_transaction_id' => $transaction->id,
                            'size' => $size,
                            'quantity' => $qty
                        ]);
                    }
                }
            }

            $slipUpdate = [
                'lot_no' => $lot_no,
                'type' => $movement_type,
                'to_stage_id' => $to_stage_id
            ];

            if ($request->is_final == 1) {
                $slipUpdate['status'] = 1;
            } else {
                if ($slip->status != 1) {
                    $slipUpdate['status'] = 0;
                }
            }

            $slip->update($slipUpdate);
            if ($request->is_final == 1) {
                $this->closeIncomingAssignments($lot_no, $slip->stage_master_unit_id, $slip->slip_file, $request->production_datetime);
            }

            // Send WhatsApp notification
            $this->sendAssignmentWhatsApp($request->to_stage_id, $lot_no);

            // ✅ NEW: Sync with Unified Timing Table for the TARGET stage (with Protection)
            if ($transaction && $to_stage_id != 3) {
                // Fetch unit info again to be sure (days, etc)
                $uTo = \App\Models\StageMasterUnit::find($stage_master_unit_to->id);
                $dAllocated = $uTo->lot_time_in_days ?? 0;
                $startTime = $request->production_datetime ?: now()->toDateTimeString();
                $finishEta = $this->calculateExpectedCompletion($startTime, $dAllocated);

                $timingData = [
                    'unit_id' => $stage_master_unit_to->id,
                    'days_allocated' => $dAllocated,
                    'status' => 1
                ];

                // Protection: Do not overwrite start/end dates for Stitching (4) if they are already set
                // As per user request: "no data should be update of the stiching start date , end date"
                $existingTiming = \App\Models\OrderLotStageTiming::where('lot_no', $lot_no)->where('master_stage_id', $to_stage_id)->first();
                if ($to_stage_id != 4 || !$existingTiming || (!$existingTiming->start_date && !$existingTiming->end_date)) {
                    $timingData['start_date'] = $startTime;
                    $timingData['end_date'] = $finishEta;
                }

                \App\Models\OrderLotStageTiming::updateOrCreate(
                    ['lot_no' => $lot_no, 'master_stage_id' => $to_stage_id],
                    $timingData
                );
            }

            DB::commit();
            return ['status_code' => 1, 'message' => 'Hand Slip Processed Successfully'];


        } catch (\Exception $e) {
            DB::rollBack();
            return ['status_code' => 0, 'message' => $e->getMessage()];
        }
    }

    public function used_lots()
    {
        $lots = FabricRollAssigning::distinct()->pluck('lot_no');
        return $lots;
    }

    // public function getOrderPackingData($orderMainId)
    // {
    //     $total = DB::table('order_products_sets')
    //         ->where('order_main_id', $orderMainId)
    //         ->sum('total_quantity');

    //     $packed = DB::table('packing_items as pi')
    //         ->join('packing_mains as pm', 'pm.id', '=', 'pi.packing_main_id')
    //         ->where('pm.order_main_id', $orderMainId)
    //         ->where('pm.status', 1)
    //         ->whereIn('pm.id', function ($q) use ($orderMainId) {
    //             $q->select('pc.packing_main_id')
    //                 ->from('order_dispatch as od')
    //                 ->join('order_dispatch_details as odd', 'odd.order_dispatch_id', '=', 'od.id')
    //                 ->join('packing_cartons as pc', 'pc.id', '=', 'odd.carton_packing_id')
    //                 ->where('od.main_order_id', $orderMainId);
    //         })
    //         ->sum('pi.quantity');

    //     return [
    //         'total'     => (int) $total,
    //         'packed'    => (int) $packed,
    //         'remaining' => max(0, $total - $packed),
    //     ];
    // }

    /**
     * Core logic to mark incoming assignments as consumed after admin digitization.
     */
    private function closeIncomingAssignments($lot_no, $unit_id, $slip_file, $completeDate = null)
    {
        $finishTime = $completeDate ?: now();
        $update = [
            'image' => $slip_file,
            'remaining_quantity' => 0,
            'complete_date' => $finishTime,
            'is_closed_for_unit' => 1
        ];

        // 1. Regular/Legacy stage transfers
        \App\Models\OrderStageTransaction::where('lot_no', $lot_no)
            ->where('sub_stage_id_to', $unit_id)
            ->whereNull('image')
            ->update($update);

        // 2. Transferred from Printing Stage
        \App\Models\OrderPrintingStageTransaction::where('lot_no', $lot_no)
            ->where('sub_stage_id_to', $unit_id)
            ->whereNull('image')
            ->update($update);

        // 3. Printing unit sending directly to Stitching
        \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $lot_no)
            ->where('sub_stage_id_to', $unit_id)
            ->whereNull('image')
            ->update($update);

        // ✅ Update Unified Timing
        $unit = \App\Models\StageMasterUnit::find($unit_id);
        if ($unit) {
            \App\Models\OrderLotStageTiming::where('lot_no', $lot_no)
                ->where('master_stage_id', $unit->master_stage_id)
                ->update([
                    'complete_date' => $finishTime,
                    'status' => 2
                ]);
        }
    }

    public function getAssignments($unitId)
    {
        return OrderCuttingStage::where('to_assign_id', $unitId)
            ->where('status', '!=', 2) // Not completed
            ->with(['orderMain', 'productSet'])
            ->get();
    }

    public function getAssignmentDetails(Request $request)
    {
        $assignment = OrderCuttingStage::with(['orderMain', 'productSet'])->find($request->assignment_id);
        if ($assignment) {
            return [
                'status' => 1,
                'order_main_id' => $assignment->order_main_id,
                'order_product_set_id' => $assignment->set_product_id,
                'design_number' => $assignment->productSet->design_number ?? '-',
                'sku' => $assignment->orderMain->sku ?? '-',
            ];
        }
        return ['status' => 0, 'message' => 'Assignment not found'];
    }

    private function sendAssignmentWhatsApp($unit_id, $lot_no)
    {
        $unit = \App\Models\StageMasterUnit::find($unit_id);
        if ($unit && $unit->phone) {
            $message = "Namaste {$unit->name},\n\nAapko ek naya lot assign hua hai (Lot No: *{$lot_no}*).\nKripya apne mobile app me check karein.\n\n- SNAPKIDS";
            send_whatsapp_message($unit->phone, $message);
        }
    }
}
