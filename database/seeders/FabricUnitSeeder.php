<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\FabricUnit;
use Illuminate\Support\Facades\DB;

class FabricUnitSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FabricUnit::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $units = [
            ['name' => 'meter', 'symbol' => 'm'],
            ['name' => 'kg', 'symbol' => 'kg'],
        ];

        foreach ($units as $index => $u) {
            FabricUnit::create([
                'sno' => $index + 1,
                'name' => $u['name'],
                'symbol' => $u['symbol'],
                'status' => 1,
            ]);
        }
    }
}
