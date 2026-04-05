<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\StageMasterUnit;
use App\Models\MasterFabricWarehouse;
use App\Models\MasterProductStage;
use Illuminate\Support\Facades\DB;

class StageMasterUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        StageMasterUnit::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $warehouses = MasterFabricWarehouse::all();
        $stages = MasterProductStage::all();

        if ($warehouses->isEmpty() || $stages->isEmpty()) {
            return;
        }

        $units = [
            ['name' => 'Unit - Printing & Embroidery', 'phone' => '9888877001', 'master_stage_id' => 1, 'employee_id' => 'EMP-PRNT'],
            ['name' => 'Unit - Embroidery', 'phone' => '9888877002', 'master_stage_id' => 2, 'employee_id' => 'EMP-EMB'],
            ['name' => 'Unit - Cutting', 'phone' => '9888877003', 'master_stage_id' => 3, 'employee_id' => 'EMP-CUT'],
            ['name' => 'Unit - Stitching', 'phone' => '9888877004', 'master_stage_id' => 4, 'employee_id' => 'EMP-STCH'],
            ['name' => 'Unit - Kaj', 'phone' => '9888877005', 'master_stage_id' => 5, 'employee_id' => 'EMP-KAJ'],
            ['name' => 'Unit - Washing', 'phone' => '9888877006', 'master_stage_id' => 6, 'employee_id' => 'EMP-WSH'],
            ['name' => 'Unit - Thread Cutting', 'phone' => '9888877007', 'master_stage_id' => 7, 'employee_id' => 'EMP-THRD'],
            ['name' => 'Unit - Button Revit', 'phone' => '9888877008', 'master_stage_id' => 8, 'employee_id' => 'EMP-BTN'],
            ['name' => 'Unit - Pressing', 'phone' => '9888877009', 'master_stage_id' => 9, 'employee_id' => 'EMP-PRS'],
            ['name' => 'Unit - Checking', 'phone' => '9888877010', 'master_stage_id' => 10, 'employee_id' => 'EMP-CHK'],
            ['name' => 'Unit - Packing', 'phone' => '9888877011', 'master_stage_id' => 11, 'employee_id' => 'EMP-PCK'],
            ['name' => 'Unit - Dispatch', 'phone' => '9888877012', 'master_stage_id' => 12, 'employee_id' => 'EMP-DSP'],
            ['name' => 'Unit - Godam', 'phone' => '9888877013', 'master_stage_id' => 13, 'employee_id' => 'EMP-GDM'],
        ];

        foreach ($warehouses as $w) {
            foreach ($units as $index => $u) {
                StageMasterUnit::create([
                    'sno' => $index + 1,
                    'master_fabric_warehouse_id' => $w->id,
                    'master_stage_id' => $u['master_stage_id'],
                    'name' => $u['name'],
                    'phone' => $u['phone'],
                    'employee_id' => $u['employee_id'],
                    'password' => 'password123',
                    'status' => 1,
                ]);
            }
        }
    }
}
