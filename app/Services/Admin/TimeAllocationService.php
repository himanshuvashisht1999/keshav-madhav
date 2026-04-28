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

        $lots = \App\Models\FabricRollAssigning::whereNotIn('lot_no', $allocatedLots)
            ->distinct()
            ->pluck('lot_no');
            
        return $lots;
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
            // dd($e->getMessage());
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
