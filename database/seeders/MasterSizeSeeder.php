<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterSize;
use Illuminate\Support\Facades\DB;

class MasterSizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        MasterSize::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $sno = 1;
        for ($s = 14; $s <= 48; $s += 2) {
            MasterSize::create([
                'sno' => $sno++,
                'size' => (string) $s,
                'sku' => 'SZ-' . $s,
                'status' => 1,
            ]);
        }
    }
}
