<?php

namespace Database\Seeders;

use App\Models\AdjustmentMaster;
use Illuminate\Database\Seeder;

class AdjustmentMasterSeeder extends Seeder
{
    public function run()
    {
        $masters = [
            ['name' => 'Committee', 'model_name' => 'App\Models\Committee'],
            ['name' => 'Commission', 'model_name' => 'App\Models\Commission'],
            ['name' => 'General Expense', 'model_name' => 'App\Models\GeneralExpense'],
            ['name' => 'Electricity Expense', 'model_name' => 'App\Models\ElectricityExpense'],
            ['name' => 'Rent', 'model_name' => 'App\Models\Rent'],
            ['name' => 'Telephone Expense', 'model_name' => 'App\Models\TelephoneExpense'],
            ['name' => 'Tax', 'model_name' => 'App\Models\Tax'],
            ['name' => 'Interest', 'model_name' => 'App\Models\Interest'],
            ['name' => 'Tour Expense', 'model_name' => 'App\Models\TourExpense'],
            ['name' => 'Contractor', 'model_name' => 'App\Models\Contractor'],
            ['name' => 'Consumable Good', 'model_name' => 'App\Models\ConsumableGood'],
        ];

        foreach ($masters as $master) {
            AdjustmentMaster::updateOrCreate(['name' => $master['name']], $master);
        }
    }
}
