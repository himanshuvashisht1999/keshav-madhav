<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OrderCuttingStage;

class BackfillCuttingWarehouse extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fabric:backfill-warehouse';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill warehouse_id on order_cutting_stage table for historical records';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $stages = OrderCuttingStage::whereNull('warehouse_id')
            ->where('to_assign_id', '!=', 0)
            ->with('cutting_master')
            ->get();
            
        $count = 0;
        foreach ($stages as $stage) {
            if ($stage->cutting_master && $stage->cutting_master->master_fabric_warehouse_id) {
                $stage->warehouse_id = $stage->cutting_master->master_fabric_warehouse_id;
                $stage->save();
                $count++;
            }
        }
        
        $this->info("Successfully backfilled warehouse_id on {$count} order_cutting_stage records.");
        return Command::SUCCESS;
    }
}
