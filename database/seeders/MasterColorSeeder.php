<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterColor;
use Illuminate\Support\Facades\DB;

class MasterColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterColor::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $colors = [
            ['name' => 'Indigo Blue', 'sku' => 'CLR-IND-BLU'],
            ['name' => 'Light Blue', 'sku' => 'CLR-LGT-BLU'],
            ['name' => 'Dark Blue', 'sku' => 'CLR-DRK-BLU'],
            ['name' => 'Black Denim', 'sku' => 'CLR-BLK-DNM'],
            ['name' => 'Charcoal Grey', 'sku' => 'CLR-CHR-GRY'],
            ['name' => 'Light Grey', 'sku' => 'CLR-LGT-GRY'],
            ['name' => 'Olive Green', 'sku' => 'CLR-OLV-GRN'],
            ['name' => 'Khaki', 'sku' => 'CLR-KHK'],
            ['name' => 'Tan', 'sku' => 'CLR-TAN'],
            ['name' => 'Stone Wash', 'sku' => 'CLR-STN-WSH'],
            ['name' => 'Ice Blue', 'sku' => 'CLR-ICE-BLU'],
            ['name' => 'Midnight Black', 'sku' => 'CLR-MID-BLK'],
        ];

        foreach ($colors as $index => $color) {
            MasterColor::create([
                'sno' => $index + 1,
                'name' => $color['name'],
                'sku' => $color['sku'],
                'status' => 1,
            ]);
        }
    }
}
