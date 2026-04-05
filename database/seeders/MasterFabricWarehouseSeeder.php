<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterFabricWarehouse;
use Illuminate\Support\Facades\DB;

class MasterFabricWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterFabricWarehouse::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $warehouses = [
            [
                'cutting_master_name' => 'Rajesh Kumar',
                'sku' => 'FW-MUM01',
                'address' => 'Gala No. 12, Dharavi Main Road, Mumbai, Maharashtra 400017',
            ],
            [
                'cutting_master_name' => 'Amit Shah',
                'sku' => 'FW-DLH01',
                'address' => 'Plot 88, Gandhinagar Textile Market, New Delhi 110031',
            ],
        ];

        foreach ($warehouses as $index => $w) {
            MasterFabricWarehouse::create([
                'sno' => $index + 1,
                'cutting_master_name' => $w['cutting_master_name'],
                'sku' => $w['sku'],
                'address' => $w['address'],
                'status' => 1,
            ]);
        }
    }
}
