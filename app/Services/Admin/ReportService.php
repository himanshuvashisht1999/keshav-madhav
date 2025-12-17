<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Models\OrderMain;
use App\Models\ProductionSlipDigitizationParts;
use App\Models\OrderProductSet;
use App\Models\MasterSizeMeasurement;
use Carbon\Carbon;

class ReportService {

//    public function salesOrder(Request $request){
//         $orders = OrderMain::with('customer')->all();
//         foreach($orders as $order){
//             $all_data = ProductionSlipDigitizationParts::where('from_stage_id',3)->where('to_stage_id',4)->where('order_no',$order->sku)->get();
//             $lot_no = '';
//             $total_pieces = 0;
//             foreach($all_data as $single_data){
//                 $part_data = ProductionSlipDigitizationParts::where('id',$single_data->id)->first();
//                 if($part_data){
//                     $master_size = MasterSize::where('id',$part_data->set_size)->first();
//                     $lot_no = $part_data->lot_no;
//                     $total_pieces+= $part_data->set_quantity;
//                 }
//             }
//             if($data){
//                 $result['order_date'] = $order->created_at;
//                 $result['customer'] = $order->customer->name;
//                 $result['order_no'] = $order->created_at;
//                 $result['lot_no'] = $lot_no;
//                 $result['total_pieces'] = $total_pieces;
//                 $result['pieces_in_lot'] = $order->created_at;
//                 $result['status'] = $order->created_at;
//             }
//         }
        

//         dd($order_ids);
//    }

    public function salesOrder(Request $request){
            

            $result = [];

            $orders = OrderMain::with('customer')->get();

            foreach ($orders as $order) {

                $lotNos = ProductionSlipDigitizationParts::where('from_stage_id', 3)
                    ->where('to_stage_id', 4)
                    ->where('order_no', $order->sku)
                    ->distinct()
                    ->pluck('lot_no');
                $total_pcs_in_order = OrderProductSet::where('order_main_id',$order->id)->sum('total_quantity');
                // dd($total_pcs_in_order);
                foreach ($lotNos as $lot_no) {

                    $parts_data = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->get();
                    $stage_name = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->orderBy('id','desc')->value('to_stage_name');
                    $allowed_till_datetime = ProductionSlipDigitizationParts::where('lot_no', $lot_no)->orderBy('id','desc')->value('allowed_till_datetime');

                    $isDelayed = 'No';
                    if ($allowed_till_datetime) {
                        $allowedTime = Carbon::parse($allowed_till_datetime);
                        $currentTime = Carbon::now();

                        if ($currentTime->greaterThan($allowedTime)) {
                            $isDelayed = 'Yes';
                        }
                    }

                    $pieces_in_lot = 0;
                    foreach($parts_data as $single_part){
                        $set_quantity = $single_part->set_quantity;
                        $set_size_id = $single_part->set_size;
                        $no_of_piece_in_size = MasterSizeMeasurement::where('id',$set_size_id)->value('no_of_pcs');
                        $pieces_in_lot += $no_of_piece_in_size * $set_quantity;

                    }
                    $result[] = [
                        'order_date'      => $order->created_at,
                        'customer'        => $order->customer->name ?? '',
                        'order_no'        => $order->sku,
                        'lot_no'          => $lot_no,
                        'total_pcs_in_order'    => $total_pcs_in_order, // calculate if needed
                        'pieces_in_lot'   => $pieces_in_lot, // calculate if needed
                        'stage_name'          => $stage_name ?? '',
                        'isDelayed'          => $isDelayed ?? 'No',
                        'allowed_till_datetime' => $allowed_till_datetime,
                        'current_datetime'      => $currentTime->toDateTimeString(),

                    ];
                }
            }

            // dd($result);
            return collect($result)->groupBy('order_no');

        }


}