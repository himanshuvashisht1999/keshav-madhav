<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterSizeMeasurement;
use Illuminate\Support\Facades\DB;

class MasterSizeMeasurementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterSizeMeasurement::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $groups = [
            ['name' => '14-22', 'sizes' => ['14', '16', '18', '20', '22']],
            ['name' => '24-32', 'sizes' => ['24', '26', '28', '30', '32']],
            ['name' => '34-42', 'sizes' => ['34', '36', '38', '40', '42']],
            ['name' => '44-48', 'sizes' => ['44', '46', '48']],
            ['name' => '16-24', 'sizes' => ['16', '18', '20', '22', '24']],
            ['name' => '14-18 (Rep)', 'sizes' => ['14', '14', '16', '16', '18']],
            ['name' => '26-30 (Rep)', 'sizes' => ['26', '26', '28', '28', '30']],
            ['name' => '32-36 (Rep)', 'sizes' => ['32', '32', '34', '34', '36']],
            ['name' => '38-42 (Rep)', 'sizes' => ['38', '38', '40', '40', '42']],
            ['name' => '28-36', 'sizes' => ['28', '30', '32', '34', '36']],
        ];

        foreach ($groups as $index => $group) {
            MasterSizeMeasurement::create([
                'sno' => $index + 1,
                'name' => $group['name'],
                'set_size' => $group['name'],
                'no_of_pcs' => count($group['sizes']),
                'size_group' => implode(',', $group['sizes']),
                'sku' => strtoupper($group['name']),
                'status' => 1,
            ]);
        }
    }
}
