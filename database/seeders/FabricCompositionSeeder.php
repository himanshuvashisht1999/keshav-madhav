<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FabricComposition;
use Illuminate\Support\Facades\DB;

class FabricCompositionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FabricComposition::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $compositions = [
            ['name' => '100% Cotton', 'sku' => 'COMP-CTN-100'],
            ['name' => '98% Cotton 2% Spandex', 'sku' => 'COMP-CTN-SPX-982'],
            ['name' => '90% Cotton 8% Polyester 2% Elastane', 'sku' => 'COMP-CTN-PLY-ELST'],
            ['name' => '80% Cotton 20% Polyester', 'sku' => 'COMP-CTN-PLY-8020'],
            ['name' => '70% Cotton 28% Polyester 2% Spandex', 'sku' => 'COMP-CTN-PLY-SPX'],
            ['name' => '65% Cotton 35% Polyester', 'sku' => 'COMP-CTN-PLY-6535'],
            ['name' => '100% Organic Cotton', 'sku' => 'COMP-ORG-CTN'],
            ['name' => 'Denim Stretch (Lycra)', 'sku' => 'COMP-DEN-STR'],
        ];

        foreach ($compositions as $index => $c) {
            FabricComposition::create([
                'sno' => $index + 1,
                'name' => $c['name'],
                'sku' => $c['sku'],
                'status' => 1,
            ]);
        }
    }
}
