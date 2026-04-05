<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            VendorSeeder::class,
            SalesAgentSeeder::class,
            MasterCustomerSeeder::class,
            EmployeeSeeder::class,
            MasterColorSeeder::class,
            MasterSizeSeeder::class,
            MasterProductTypeSeeder::class,
            MasterProductFittingSeeder::class,
            MasterDesignSeeder::class,
            MasterPatternSeeder::class,
            MasterDesignPatternSeeder::class,
            MasterSizeMeasurementSeeder::class,
            MasterMaterialSeeder::class,
            MasterWarehouseSeeder::class,
            StoreroomSeeder::class,
            MasterFabricWarehouseSeeder::class,
            StageMasterUnitSeeder::class,
            FabricCompositionSeeder::class,
            FabricSeeder::class,
            ProductionGoodsSeeder::class,
            PurchaseOrderSeeder::class,
            FabricReceiptSeeder::class,
        ]);
        // \App\Models\User::factory(10)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
    }
}
