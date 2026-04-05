<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterPattern;
use Illuminate\Support\Facades\DB;

class MasterPatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterPattern::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $patterns = [
            ['name' => 'Skinny Fit', 'sku' => 'PAT-SKINNY'],
            ['name' => 'Slim Fit', 'sku' => 'PAT-SLIM'],
            ['name' => 'Regular Fit', 'sku' => 'PAT-REGULAR'],
            ['name' => 'Straight Fit', 'sku' => 'PAT-STRAIGHT'],
            ['name' => 'Relaxed Fit', 'sku' => 'PAT-RELAXED'],
            ['name' => 'Bootcut', 'sku' => 'PAT-BOOTCUT'],
            ['name' => 'Flare', 'sku' => 'PAT-FLARE'],
            ['name' => 'Jogger', 'sku' => 'PAT-JOGGER'],
        ];

        foreach ($patterns as $index => $p) {
            MasterPattern::create([
                'sno' => $index + 1,
                'name' => $p['name'],
                'sku' => $p['sku'],
                'status' => 1,
            ]);
        }
    }
}
