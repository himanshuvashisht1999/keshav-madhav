<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Storeroom;
use App\Models\Rack;

class StoreroomSeeder extends Seeder
{
    public function run()
    {
        $store = Storeroom::create(['name' => 'Main Warehouse', 'description' => 'Primary storage']);
        Rack::create(['storeroom_id' => $store->id, 'name' => 'Rack A1', 'capacity' => 100]);
        Rack::create(['storeroom_id' => $store->id, 'name' => 'Rack A2', 'capacity' => 100]);
        
        $store2 = Storeroom::create(['name' => 'Dispatch Area', 'description' => 'Ready for shipping']);
        Rack::create(['storeroom_id' => $store2->id, 'name' => 'Zone D1', 'capacity' => 50]);
    }
}
