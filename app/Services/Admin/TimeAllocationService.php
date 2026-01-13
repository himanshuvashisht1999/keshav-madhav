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
            ->orderBy('sequence', 'asc')
            ->select('id', 'name', 'sequence') // Fetch necessary columns
            ->get();
            
        return $stages;
    }

    // Deprecating getSlipDigitalization for Time Allocation as it's no longer used
    public function getSlipDigitalization() { return null; }

    public function getActiveLots()
    {
        $allocatedLots = \App\Models\OrderStageWiseTimeTracking::pluck('lot_no')->toArray();

        $lots = \App\Models\FabricRollAssigning::where('status', 1)
            ->whereNotIn('lot_no', $allocatedLots)
            ->distinct()
            ->pluck('lot_no');
            
        return $lots;
    }

    public function getLotDetailsForDisplay($lot_no)
    {
        $rolls = \App\Models\FabricRollAssigning::where('lot_no', $lot_no)
            ->with(['order_product_set.fabric', 'order_product_set.colors', 'stageMasterUnit']) // Eager load relationships
            ->get();

        if ($rolls->isEmpty()) {
            return null;
        }

        $fabric_names = $rolls->pluck('order_product_set.fabric.name')->unique()->filter()->implode(', ');
        $color_names = $rolls->pluck('order_product_set.colors.name')->unique()->filter()->implode(', ');
        $order_nos = $rolls->pluck('order_no')->unique()->filter()->implode(', ');
        $total_meter = $rolls->sum('meter');
        $cutting_master = $rolls->first()->stageMasterUnit->name ?? 'N/A';

        // Get roll-wise details
        $roll_details = $rolls->map(function($roll) {
            return [
                'roll_no' => $roll->roll_no,
                'meter' => $roll->meter,
                'fabric' => $roll->order_product_set->fabric->name ?? '',
                'color' => $roll->order_product_set->colors->name ?? ''
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
            $datetime = date(
                        'Y-m-d H:i:s',
                        strtotime($request->start_date_time)
                    );
            
            $save_data_main->sku = '';
            $save_data_main->lot_no = $request->lot_no;
            $save_data_main->production_slip_digitization_id = $request->production_slip_digitization_id ?? null;
            $save_data_main->start_date_time  = $datetime;
            
            if ($request->stages && is_array($request->stages)) {
                foreach ($request->stages as $stage_id => $days) {
                    $expected = $this->calculateExpectedCompletion($datetime, $days);
                    $save_data_main->{'stage_id_'.$stage_id} = $expected;
                    $datetime = $expected;
                }
            }

            $save_data_main->status = 1;
            $save_data_main->save();

            ///// master stage wise time allocation  ///// 

            $save_data_master = new MasterStageWiseTimeAllocation;
           
            $save_data_master->sku = '';
            $save_data_master->lot_no = $request->lot_no;
            $save_data_master->production_slip_digitization_id = $request->production_slip_digitization_id ?? null;
            $save_data_master->start_date_time  = $request->start_date_time;
            
            if ($request->stages && is_array($request->stages)) {
                foreach ($request->stages as $stage_id => $days) {
                    $save_data_master->{'stage_id_'.$stage_id} = $days;
                }
            }
            
            $save_data_master->status = 1;
            $save_data_master->save();

            // Only update slip status if production_slip_digitization_id is provided
            if ($request->production_slip_digitization_id) {
                $slip = ProductionSlipDigitization::find($request->production_slip_digitization_id);
                if ($slip) {
                    $slip->update([
                        'lot_no'  => $request->lot_no,
                        'status'  => 5
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

            return [
                'status_code' => 0,
                'message' => $e->getMessage()
            ];
        }
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
            
            // If we have time today, use it, but max out at remainingHours
            $hoursToUse = min($availableToday, $remainingHours);

            if ($hoursToUse > 0) {
                $current->modify("+{$hoursToUse} hours");
                $remainingHours -= $hoursToUse;
            }

            // If still need more hours, move to next day 9 AM
            if ($remainingHours > 0) {
                $current->modify('+1 day');
                $current->setTime($WORK_START, 0);
            }
        }

        return $current->format('Y-m-d H:i:s');
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
