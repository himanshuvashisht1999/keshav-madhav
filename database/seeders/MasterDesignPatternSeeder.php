<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDesignPattern;
use Illuminate\Support\Facades\DB;

class MasterDesignPatternSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterDesignPattern::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $patterns = [
            ['name' => 'Solid', 'sku' => 'PAT-SLD'],
            ['name' => 'Acid Wash', 'sku' => 'PAT-ACD-WSH'],
            ['name' => 'Stone Wash', 'sku' => 'PAT-STN-WSH'],
            ['name' => 'Bleach Wash', 'sku' => 'PAT-BLC-WSH'],
            ['name' => 'Whisker', 'sku' => 'PAT-WHK'],
            ['name' => 'Distressed', 'sku' => 'PAT-DST'],
            ['name' => 'Faded', 'sku' => 'PAT-FAD'],
            ['name' => 'Crinkle', 'sku' => 'PAT-CRN'],
            ['name' => 'Raw Denim', 'sku' => 'PAT-RAW'],
            ['name' => 'Double Dyed', 'sku' => 'PAT-DBL-DYD'],
        ];

        foreach ($patterns as $index => $pattern) {
            MasterDesignPattern::create([
                'sno' => $index + 1,
                'name' => $pattern['name'],
                'sku' => $pattern['sku'],
                'status' => 1,
            ]);
        }
    }
}
