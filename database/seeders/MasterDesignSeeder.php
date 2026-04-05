<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterDesign;
use Illuminate\Support\Facades\DB;

class MasterDesignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterDesign::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $designs = [
            ['name' => 'Classic 5-Pocket', 'sku' => 'DSN-CLS-5PKT'],
            ['name' => 'Distressed Vintage', 'sku' => 'DSN-DST-VNT'],
            ['name' => 'High-Waist Comfort', 'sku' => 'DSN-HIW-CMF'],
            ['name' => 'Narrow Tapered', 'sku' => 'DSN-NAR-TPR'],
            ['name' => 'Washed Cargo', 'sku' => 'DSN-WSH-CRG'],
            ['name' => 'Raw Edge Ankle', 'sku' => 'DSN-RAW-ANK'],
            ['name' => 'Double Stitched Rugged', 'sku' => 'DSN-DBL-RGD'],
            ['name' => 'Biker Moto Panel', 'sku' => 'DSN-BKR-MTO'],
            ['name' => 'Side Stripe Athleisure', 'sku' => 'DSN-SID-ATH'],
            ['name' => 'Minimalist Clean Cut', 'sku' => 'DSN-MIN-CLN'],
        ];

        foreach ($designs as $index => $design) {
            MasterDesign::create([
                'sno' => $index + 1,
                'name' => $design['name'],
                'sku' => $design['sku'],
                'status' => 1,
            ]);
        }
    }
}
