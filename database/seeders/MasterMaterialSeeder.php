<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterMaterial;
use Illuminate\Support\Facades\DB;

class MasterMaterialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterMaterial::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $materials = [
            ['name' => '100% Cotton Denim', 'sku' => 'MAT-CTN-100'],
            ['name' => 'Stretch Denim (Cotton/Spandex)', 'sku' => 'MAT-STR-CTN'],
            ['name' => 'Poly-Cotton Blend', 'sku' => 'MAT-PLY-CTN'],
            ['name' => 'Selvedge Denim', 'sku' => 'MAT-SLV-DNM'],
            ['name' => 'Tencel Denim', 'sku' => 'MAT-TNC-DNM'],
            ['name' => 'Lycra Jeans Fabric', 'sku' => 'MAT-LYC-DNM'],
            ['name' => 'Recycled Polyester Denim', 'sku' => 'MAT-REC-PLY'],
            ['name' => 'Heavy Weight Denim (14oz+)', 'sku' => 'MAT-HVY-DNM'],
            ['name' => 'Light Weight Denim (8oz-)', 'sku' => 'MAT-LGT-DNM'],
            ['name' => 'Bull Denim', 'sku' => 'MAT-BUL-DNM'],
        ];

        foreach ($materials as $index => $material) {
            MasterMaterial::create([
                'sno' => $index + 1,
                'name' => $material['name'],
                'sku' => $material['sku'],
                'status' => 1,
            ]);
        }
    }
}
