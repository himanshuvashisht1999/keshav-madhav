<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\OrderStageWiseTimeTracking;
use App\Models\MasterStageWiseTimeAllocation;
use App\Models\ProductionSlipDigitization;
use App\Models\OrderMain;
use App\Models\Stock;
use App\Models\StageMasterUnit;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class TimeAllocationService {

    public function orderMainForRollAssign(){
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

    public function getProductionStages()
    {
        $stages = \App\Models\MasterProductStage::where('status', 1)
            ->where('id', '!=', 3) // Exclude Cutting as it's lot-based and handled separately
            ->orderBy('sequence', 'asc')
            ->select('id', 'name', 'sequence')
            ->get();
            
        return $stages;
    }

    // Deprecating getSlipDigitalization for Time Allocation as it's no longer used
    public function getSlipDigitalization() { return null; }

    public function getActiveLots()
    {
        $allocatedLots = \App\Models\OrderStageWiseTimeTracking::pluck('lot_no')->toArray();

        $lots = \App\Models\FabricRollAssigning::whereNotIn('lot_no', $allocatedLots)
            ->distinct()
            ->pluck('lot_no');
            
        return $lots;
    }

    public function getLotStageTransactions($lotNo)
    {
        $stages = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12];
        $data = [];

        $timings = \App\Models\OrderLotStageTiming::where('lot_no', $lotNo)->get()->keyBy('master_stage_id');

        foreach ($stages as $stageId) {
            $record = $timings->get($stageId);

            $startDate = $record->start_date ?? null;
            $endDate = $record->end_date ?? null;
            $completeDate = $record->complete_date ?? null;
            $days = $record->days_allocated ?? 0;

            // Fallback for Cutting (Stage 3)
            if ($stageId == 3 && (!$startDate || !$endDate)) {
                $cutting = \App\Models\OrderCuttingStage::where('lot_no', $lotNo)->first();
                if ($cutting) {
                    $startDate = $startDate ?: $cutting->start_date;
                    $endDate = $endDate ?: $cutting->end_date;
                    $completeDate = $completeDate ?: $cutting->complete_date;
                    
                    // If record exists but days are 0, try to get them from the unit assigned to the cutting record
                    if ($days == 0 && $cutting->to_assign_id) {
                        $unit = StageMasterUnit::find($cutting->to_assign_id);
                        $days = $unit->lot_time_in_days ?? 0;
                    }
                }
            }

            $data[$stageId] = [
                'start_date' => $startDate ? date('Y-m-d\TH:i', strtotime($startDate)) : null,
                'end_date' => $endDate ? date('Y-m-d\TH:i', strtotime($endDate)) : null,
                'complete_date' => $completeDate ? date('Y-m-d\TH:i', strtotime($completeDate)) : null,
                'days_allocated' => $days
            ];
        }
        return $data;
    }

    public function getLotDetailsForDisplay($lot_no)
    {
        $rolls = \App\Models\FabricRollAssigning::where('lot_no', $lot_no)
            ->with(['order_product_set.fabric', 'order_product_set.colors', 'stageMasterUnit', 'fabricRollAssigningsDetail']) // Eager load relationships
            ->get();

        if ($rolls->isEmpty()) {
            return null;
        }

        $fabric_names = $rolls->pluck('order_product_set.fabric_names')
            ->flatMap(function($names) {
                return array_map('trim', explode(',', $names));
            })
            ->unique()
            ->filter()
            ->implode(', ');
        $color_names = $rolls->pluck('order_product_set.colors.name')->unique()->filter()->implode(', ');
        $order_nos = $rolls->pluck('order_no')->unique()->filter()->implode(', ');
        $total_meter = $rolls->sum('meter');
        $cutting_master = $rolls->first()->stageMasterUnit->name ?? 'N/A';
        $total_quantity = $rolls->sum(function ($roll) {
            return $roll->fabricRollAssigningsDetail->sum('quantity');
        });
        // Get roll-wise details
        $roll_details = $rolls->map(function($roll) {
            return [
                'roll_no' => $roll->roll_no,
                'meter' => $roll->meter,
                'fabric' => $roll->order_product_set->fabric_names ?? '',
                'color' => $roll->order_product_set->colors->name ?? '',
                'quantity'=> $roll->fabricRollAssigningsDetail->sum('quantity')
            ];
        });

        return [
            'lot_no' => $lot_no,
            'fabric_names' => $fabric_names,
            'color_names' => $color_names,
            'order_numbers' => $order_nos,
            'total_meter' => $total_meter,
            'cutting_master' => $cutting_master,
            'roll_count' => $rolls->count(),
            'total_quantity' => $total_quantity,
            'roll_details' => $roll_details
        ];
    }

    public function getSkipSlips()
    {
        $count = ProductionSlipDigitization::where('status', 2)->whereNot('from_stage_id', 3)->count();
        return $count;
    }

    public function storeTimeAllocation(Request $request)
    {
        DB::beginTransaction();

        try {

            $save_data_main = new OrderStageWiseTimeTracking;

            // ✅ Parse ONCE
            $startDatetime = Carbon::parse($request->start_date_time);
            $currentDatetime = $startDatetime->copy();

            $save_data_main->sku = '';
            $save_data_main->lot_no = $request->lot_no;
            $save_data_main->production_slip_digitization_id =
                $request->production_slip_digitization_id ?? null;
            $save_data_main->start_date_time =
                $startDatetime->toDateTimeString();

            if ($request->stages && is_array($request->stages)) {
                foreach ($request->stages as $stage_id => $days) {

                    $currentDatetime =
                        $this->calculateExpectedCompletion($currentDatetime, $days);

                    $save_data_main->{'stage_id_'.$stage_id} =
                        $currentDatetime->toDateTimeString();
                }
            }

            $save_data_main->status = 1;
            $save_data_main->save();

            // -------- MASTER TABLE --------
            $save_data_master = new MasterStageWiseTimeAllocation;

            $save_data_master->sku = '';
            $save_data_master->lot_no = $request->lot_no;
            $save_data_master->production_slip_digitization_id =
                $request->production_slip_digitization_id ?? null;
            $save_data_master->start_date_time =
                $startDatetime->toDateTimeString();

            if ($request->stages && is_array($request->stages)) {
                foreach ($request->stages as $stage_id => $days) {
                    $save_data_master->{'stage_id_'.$stage_id} = $days;
                }
            }

            $save_data_master->status = 1;
            $save_data_master->save();

            if ($request->production_slip_digitization_id) {
                $slip = ProductionSlipDigitization::find(
                    $request->production_slip_digitization_id
                );

                if ($slip) {
                    $slip->update([
                        'lot_no' => $request->lot_no,
                        'status' => 1
                    ]);
                }
            }

            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Stage wise time allocation successfully completed.'
            ];

        } catch (\Exception $e) {
            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function updateTimeAllocation(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $save_data_master = MasterStageWiseTimeAllocation::findOrFail($id);
            $startDatetime = Carbon::parse($save_data_master->start_date_time);
            $currentDatetime = $startDatetime->copy();

            $save_data_main = OrderStageWiseTimeTracking::where('lot_no', $save_data_master->lot_no)
                ->where('production_slip_digitization_id', $save_data_master->production_slip_digitization_id)
                ->first();

            if (!$save_data_main) {
                // Should not happen but fallback
                $save_data_main = new OrderStageWiseTimeTracking;
                $save_data_main->sku = $save_data_master->sku;
                $save_data_main->lot_no = $save_data_master->lot_no;
                $save_data_main->production_slip_digitization_id = $save_data_master->production_slip_digitization_id;
                $save_data_main->start_date_time = $startDatetime->toDateTimeString();
                $save_data_main->status = 1;
            }

            if ($request->stages && is_array($request->stages)) {
                $tracking_columns = \Illuminate\Support\Facades\Schema::getColumnListing('order_stage_wise_time_tracking');
                $master_columns = \Illuminate\Support\Facades\Schema::getColumnListing('master_stage_wise_time_allocation');

                foreach ($request->stages as $stage_id => $days) {
                    if (in_array('stage_id_' . $stage_id, $master_columns)) {
                        $save_data_master->{'stage_id_' . $stage_id} = $days;
                    }

                    if (in_array('stage_id_' . $stage_id, $tracking_columns)) {
                        $currentDatetime = $this->calculateExpectedCompletion($currentDatetime, $days);
                        $save_data_main->{'stage_id_' . $stage_id} = $currentDatetime->toDateTimeString();
                    }

                    // ✅ Propagate timing to actual transactions
                    $start = $request->start_dates[$stage_id] ?? null;
                    $end = $request->end_dates[$stage_id] ?? null;
                    $complete = $request->complete_dates[$stage_id] ?? null;

                    $this->updateTransactionTiming($save_data_master->lot_no, $stage_id, $start, $end, $complete, $days);
                }
            }

            $save_data_main->save();
            $save_data_master->save();

            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Time allocation successfully updated.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    protected function updateTransactionTiming($lotNo, $stageId, $start, $end, $complete, $days = null)
    {
        $updateData = [
            'start_date' => $start ?: null,
            'end_date' => $end ?: null,
            'complete_date' => $complete ?: null,
            'status' => $complete ? 2 : 1
        ];

        if ($days !== null) {
            $updateData['days_allocated'] = $days;
        }

        \App\Models\OrderLotStageTiming::updateOrCreate(
            ['lot_no' => $lotNo, 'master_stage_id' => $stageId],
            $updateData
        );

        // ✅ Remove days_allocated before propagating to legacy tables, as they do not have this column
        unset($updateData['days_allocated']);

        // ✅ Also propagate back to original tables for legacy compatibility (optional but safe for now)
        if ($stageId == 3) {
            $orderLot = \App\Models\OrderLot::where('lot_no', $lotNo)->first();
            if ($orderLot) {
                \App\Models\OrderCuttingStage::where('set_product_id', $orderLot->order_products_set_id)->update($updateData);
            }
        } elseif ($stageId == 1) {
            \App\Models\OrderPrintingStageTransaction::where('lot_no', $lotNo)->where('to_stage_id', $stageId)->update($updateData);
        } elseif ($stageId == 4) {
            \App\Models\OrderPrintingToStichingTransaction::where('lot_no', $lotNo)->where('to_stage_id', $stageId)->update($updateData);
            \App\Models\OrderStageTransaction::where('lot_no', $lotNo)->where('to_stage_id', 4)->update($updateData);
        } elseif ($stageId == 12) {
            \App\Models\OrderGodamStageTransaction::where('lot_no', $lotNo)->where('to_stage_id', $stageId)->update($updateData);
        } else {
            \App\Models\OrderStageTransaction::where('lot_no', $lotNo)->where('to_stage_id', $stageId)->update($updateData);
        }
    }

    public function calculateExpectedCompletion(Carbon $current, $days)
    {
        $WORK_START = 9;
        $WORK_END = 17;
        $HOURS_PER_DAY = 8;
        $HALF_DAY_HOURS = 4;

        $current = $current->copy();

        $hour = (int) $current->format('H');

        if ($hour < $WORK_START) {
            $current->setTime($WORK_START, 0);
        } elseif ($hour >= $WORK_END) {
            $current->addDay()->setTime($WORK_START, 0);
        }

        // -------- HALF DAY --------
        if ($days == 0.5) {

            $endOfDay = $current->copy()->setTime($WORK_END, 0);
            $availableToday = $endOfDay->diffInHours($current, false) * -1;

            if ($availableToday >= $HALF_DAY_HOURS) {
                $current->addHours($HALF_DAY_HOURS);
            } else {
                $current->addDay()->setTime($WORK_START, 0)
                        ->addHours($HALF_DAY_HOURS);
            }

            return $current;
        }

        // -------- FULL DAYS --------
        $remainingHours = $days * $HOURS_PER_DAY;

        while ($remainingHours > 0) {

            $endOfDay = $current->copy()->setTime($WORK_END, 0);
            $availableToday = $endOfDay->diffInHours($current, false) * -1;

            $hoursToUse = min($availableToday, $remainingHours);

            if ($hoursToUse > 0) {
                $current->addHours($hoursToUse);
                $remainingHours -= $hoursToUse;
            }

            if ($remainingHours > 0) {
                $current->addDay()->setTime($WORK_START, 0);
            }
        }

        return $current;
    }


    public function skip(Request $request)
    {
        DB::beginTransaction();
        try {
            
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'status'  => 2
            ]);

            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Slip Digitization skip successfully.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function deleteSlip(Request $request)
    {
        DB::beginTransaction();
        try {
            
            $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);

            $slip->update([
                'status'  => 3
            ]);

            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Slip Digitization Delete successfully.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

    public function addSkipSlips(Request $request)
    {
        DB::beginTransaction();
        try {
            
            ProductionSlipDigitization::where('status', 2)
            ->update([
                'status' => 0
            ]);

            DB::commit();

            return [
                'status_code' => 1,
                'message' => 'Skip Slip successfully add for Digitization.'
            ];

        } catch (\Exception $e) {
            DB::rollBack();

            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
    }

}
