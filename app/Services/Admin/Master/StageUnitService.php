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

class StageUnitService
{


    public function master_warehouse_fabrics()
    {
        $data = MasterFabricWarehouse::where('status', 1)->get();
        return $data;
    }
    public function master_stages()
    {
        $data = MasterProductStage::whereIn('status', [1, 2])->get();
        return $data;
    }

    public function stageUnit($master_fabric_warehouse_id)
    {
        $stages = MasterProductStage::whereIn('status', [1, 2])
            ->orderBy('sequence', 'asc')
            ->get();

        $units = StageMasterUnit::where('master_fabric_warehouse_id', $master_fabric_warehouse_id)
            ->get()
            ->groupBy('master_stage_id');

        $response = [];

        foreach ($stages as $stage) {

            if ($units->has($stage->id)) {

                foreach ($units[$stage->id] as $unit) {
                    $response[] = [
                        'id' => $unit->id,
                        'encrypted_id' => Crypt::encryptString($unit->id),
                        'master_stage_id' => $stage->id,
                        'stage_name' => $stage->name,
                        'name' => $unit->name,
                        'phone' => $unit->phone,
                        'employee_id' => $unit->employee_id,
                        'password' => $unit->password,
                    ];
                }

            } else {

                // ⛔ placeholder row (DO NOT SAVE THIS)
                $response[] = [
                    'id' => '',
                    'encrypted_id' => '',
                    'master_stage_id' => $stage->id,
                    'stage_name' => $stage->name,
                    'name' => '',
                    'phone' => '',
                    'employee_id' => '',
                    'password' => '',
                ];
            }
        }

        return $response;
    }





    public function update(Request $request)
    {
        $warehouseId = $request->warehouse_id;

        foreach ($request->rows as $row) {

            // skip blank/invalid rows
            if (empty($row['master_stage_id'])) {
                continue;
            }

            StageMasterUnit::updateOrCreate(
                [
                    'id' => $row['id'] ?? null,
                ],
                [
                    'master_fabric_warehouse_id' => $warehouseId,
                    'master_stage_id' => $row['master_stage_id'],
                    'name' => $row['name'] ?? '',
                    'phone' => $row['phone'] ?? '',
                    'employee_id' => $row['employee_id'] ?? null,
                    'password' => $row['password'] ?? null,
                    'status' => 1,
                ]
            );
        }

        return redirect()->back()->with('success', 'Stage units saved successfully');
    }





}