<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Fabric;
use Illuminate\Support\Facades\DB;

class FabricSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Fabric::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $fabrics = [
            ['name' => 'Ultra Indigo Stretch', 'vendor_id' => 1, 'composition_id' => 2],
            ['name' => 'Raw Selvedge Denim', 'vendor_id' => 1, 'composition_id' => 1],
            ['name' => 'Comfort Slub Denim', 'vendor_id' => 2, 'composition_id' => 3],
            ['name' => 'Light Wash Summer Denim', 'vendor_id' => 2, 'composition_id' => 8],
            ['name' => 'Midnight Black Premium', 'vendor_id' => 3, 'composition_id' => 5],
            ['name' => 'Grey Melange Soft', 'vendor_id' => 3, 'composition_id' => 4],
            ['name' => 'Heavy Duty 14oz Denim', 'vendor_id' => 6, 'composition_id' => 1],
            ['name' => 'Eco-Friendly Organic Denim', 'vendor_id' => 7, 'composition_id' => 7],
            ['name' => 'Workwear Tough Canvas', 'vendor_id' => 8, 'composition_id' => 6],
            ['name' => 'Standard Blue Denim', 'vendor_id' => 4, 'composition_id' => 1],
        ];

        foreach ($fabrics as $index => $f) {
            // Logic from FabricService: strtoupper($request->name) . "-" .  $request->vendor_id;
            $sku = strtoupper($f['name']) . "-" . $f['vendor_id'];

            Fabric::create([
                'sno' => $index + 1,
                'name' => $f['name'],
                'vendor_id' => $f['vendor_id'],
                'composition_id' => $f['composition_id'],
                'sku' => $sku,
                'status' => 1,
            ]);
        }
    }
}
