<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterWarehouse;
use Illuminate\Support\Facades\DB;

class MasterWarehouseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterWarehouse::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $warehouses = [
            [
                'name' => 'Mumbai Main Hub',
                'address' => 'Gala No. 4, Bhiwandi Road, Thane West, Mumbai, Maharashtra 421302',
                'sku' => 'WH-MUM-MAIN'
            ],
            [
                'name' => 'Delhi Distribution Center',
                'address' => 'Plot No. 45, Okhla Industrial Estate Phase III, New Delhi 110020',
                'sku' => 'WH-DEL-DIST'
            ],
            [
                'name' => 'Bangalore Logistics Park',
                'address' => 'Survey No. 23/1, Hosur Road, Bommasandra, Bangalore, Karnataka 560099',
                'sku' => 'WH-BLR-LOG'
            ],
            [
                'name' => 'Ahmedabad Textile Depot',
                'address' => 'Shed No. 12, GIDC Naroda, Ahmedabad, Gujarat 382330',
                'sku' => 'WH-AHD-TEX'
            ],
        ];

        foreach ($warehouses as $index => $warehouse) {
            MasterWarehouse::create([
                'sno' => $index + 1,
                'name' => $warehouse['name'],
                'address' => $warehouse['address'],
                'sku' => $warehouse['sku'],
                'status' => 1,
            ]);
        }
    }
}
