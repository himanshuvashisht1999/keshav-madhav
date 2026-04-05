<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterProductFitting;
use Illuminate\Support\Facades\DB;

class MasterProductFittingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterProductFitting::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $fittings = [
            ['name' => 'Slim Fit', 'sku' => 'FIT-SLM'],
            ['name' => 'Regular Fit', 'sku' => 'FIT-REG'],
            ['name' => 'Bootcut', 'sku' => 'FIT-BTC'],
            ['name' => 'Skinny Fit', 'sku' => 'FIT-SKN'],
            ['name' => 'Loose Fit', 'sku' => 'FIT-LSE'],
            ['name' => 'Straight Fit', 'sku' => 'FIT-STR'],
        ];

        foreach ($fittings as $index => $fitting) {
            MasterProductFitting::create([
                'sno' => $index + 1,
                'name' => $fitting['name'],
                'sku' => $fitting['sku'],
                'status' => 1,
            ]);
        }
    }
}
