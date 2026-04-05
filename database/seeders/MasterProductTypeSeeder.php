<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterProductType;
use Illuminate\Support\Facades\DB;

class MasterProductTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterProductType::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $types = [
            ['name' => "Men's Jeans", 'sku' => 'TYP-MEN'],
            ['name' => "Women's Jeans", 'sku' => 'TYP-WMN'],
            ['name' => "Kid's Jeans", 'sku' => 'TYP-KID'],
            ['name' => "Unisex Jeans", 'sku' => 'TYP-UNI'],
        ];

        foreach ($types as $index => $type) {
            MasterProductType::create([
                'sno' => $index + 1,
                'name' => $type['name'],
                'sku' => $type['sku'],
                'status' => 1,
            ]);
        }
    }
}
