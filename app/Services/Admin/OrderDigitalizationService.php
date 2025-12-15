<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Order;
use App\Models\OrderProduct;
use App\Models\ProductionGoods;
use App\Models\MasterCustomer;
use App\Models\OrderMain;
use App\Models\OrderProductSet;
use App\Models\Stock;
use App\Models\FabricRollAssigning;

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
   
    public function store(Request $request)
    {
        DB::beginTransaction();
        
        try {
        //    dd($request->all());
            ////// corporate order photo upload
            if ($request->lot_no_list){
                foreach ($request->lot_no_list as $key => $lot_no) {
                    $save_data_main = new FabricRollAssigning;
                    $save_data_main->sku = '';
                    $save_data_main->lot_no = $lot_no;
                    $save_data_main->order_no = $request->order_no_list[$key];
                    $save_data_main->cutting_master_id = $request->cutting_unit_list[$key];
                    $save_data_main->roll_no = $request->roll_no_list[$key];
                    $save_data_main->meter = $request->meter_list[$key];
                    $save_data_main->slip_file = $request->slip_file ?? '';
                    $save_data_main->status = 1;
                    $save_data_main->save();
                }
            }

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
}
