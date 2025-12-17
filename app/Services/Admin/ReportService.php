<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Models\OrderMain;
use App\Models\ProductionSlipDigitizationParts;
use App\Models\OrderProductSet;

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
                $result[] = [
                    'order_date'      => $order->created_at,
                    'customer'        => $order->customer->name ?? '',
                    'order_no'        => $order->sku,
                    'lot_no'          => $lot_no,
                    'total_pcs_in_order'    => $total_pcs_in_order, // calculate if needed
                    'pieces_in_lot'   => '', // calculate if needed
                    'status'          => '',
                ];
            }
        }

        //dd($result);
        return $result;

   }


}