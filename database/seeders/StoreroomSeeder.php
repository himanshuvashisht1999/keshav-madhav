<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Storeroom;
use App\Models\Rack;
use Illuminate\Support\Facades\DB;

class StoreroomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Rack::truncate();
        Storeroom::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $storerooms = [
            [
                'name' => 'Finished Goods A',
                'description' => 'Main storage for ready-to-dispatch jeans.',
                'racks' => ['A-1', 'A-2', 'A-3', 'A-4', 'A-5']
            ],
            [
                'name' => 'Raw Material Hub',
                'description' => 'Storage for fabric rolls and trims.',
                'racks' => ['R-1', 'R-2', 'R-3', 'R-4']
            ],
            [
                'name' => 'Quality Control Area',
                'description' => 'Temporary storage for items under inspection.',
                'racks' => ['QC-1', 'QC-2']
            ],
        ];

        foreach ($storerooms as $storeData) {
            $storeroom = Storeroom::create([
                'name' => $storeData['name'],
                'description' => $storeData['description'],
                'status' => 1
            ]);

            foreach ($storeData['racks'] as $rackName) {
                Rack::create([
                    'storeroom_id' => $storeroom->id,
                    'name' => $rackName,
                    'capacity' => rand(50, 200),
                    'status' => 1
                ]);
            }
        }
    }
}
