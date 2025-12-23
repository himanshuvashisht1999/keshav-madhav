<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\StageMasterUnit;
use App\Models\MasterFabricWarehouse;
use App\Models\MasterProductStage;
use App\Http\DataTable\Admin\Master\CustomerDataTable as DataTable;
use Illuminate\Support\Facades\Crypt;

class StageUnitService {


    public function master_warehouse_fabrics(){
        $data = MasterFabricWarehouse::where('status',1)->get();
        return $data;
    }
    public function master_stages(){
        $data = MasterProductStage::whereIn('status',[1,2])->get();
        return $data;
    }

    public function stageUnit($master_fabric_warehouse_id){

        $stages = MasterProductStage::whereIn('status',[1,2])->orderBy('sequence','asc')->get();

        $units = StageMasterUnit::where('master_fabric_warehouse_id', $master_fabric_warehouse_id)
                    ->get()
                    ->keyBy('master_stage_id');

        $response = [];

        foreach ($stages as $stage) {
            $unit = $units->get($stage->id);
            if($unit){
                $response[] = [
                    'id'            => $unit->id,
                    'encrypted_id'  => Crypt::encryptString($unit->id),
                    'master_stage_id' => $stage->id,
                    'stage_name'      => $stage->name,
                    'name'            => $unit->name ?? '',
                    'phone'           => $unit->phone ?? '',
                ];
            }else{
                $response[] = [
                    'id'            => '',
                    'encrypted_id'  => '',
                    'master_stage_id' => $stage->id,
                    'stage_name'      => $stage->name,
                    'name'            => '',
                    'phone'           =>  '',
                ];
            }
            
        }

        return $response;
    }

    public function update(Request $request){
        foreach ($request->rows as $row) {
            StageMasterUnit::updateOrCreate(
                [
                    'master_fabric_warehouse_id' => $row['master_fabric_warehouse_id'],
                    'master_stage_id'            => $row['master_stage_id'],
                ],
                [
                    'name'   => $row['name'],
                    'phone'  => $row['phone'],
                    'status' => 1,
                ]
            );
        }


        return true;

    }

    

}