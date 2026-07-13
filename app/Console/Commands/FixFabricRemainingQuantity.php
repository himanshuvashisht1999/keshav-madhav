<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FixFabricRemainingQuantity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:fabric-remaining-qty';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculates and fixes the remaining_quantity for all fabric receipt details based on actual usages.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("Starting data repair for Fabric Receipt Details...");
        $details = \App\Models\FabricReceiptDetail::all();
        $fixed = 0;
        
        $bar = $this->output->createProgressBar(count($details));
        
        foreach($details as $detail) {
            $internal = \App\Models\FabricRollAssigning::where('fabric_receipt_detail_id', $detail->id)->sum('meter');
            $agent = \App\Models\AgentOrderFabricItem::where('fabric_receipt_detail_id', $detail->id)->sum('meter');
            
            // Check returns
            $agent_returns = 0;
            // AgentOrderReturnItem uses item_id for the agent_order_fabric_items id when item_type='fabric'
            $agent_items = \App\Models\AgentOrderFabricItem::where('fabric_receipt_detail_id', $detail->id)->pluck('id');
            if ($agent_items->count() > 0) {
                $agent_returns = \DB::table('agent_order_return_items')
                    ->where('item_type', 'fabric')
                    ->whereIn('item_id', $agent_items)
                    ->sum('quantity');
            }

            $total_used = $internal + $agent - $agent_returns;
            $expected_remaining = $detail->meter - $total_used;
            
            if ($expected_remaining < 0) { 
                $expected_remaining = 0; 
            }
            
            if (abs($detail->remaining_quantity - $expected_remaining) > 0.01) {
                $detail->remaining_quantity = $expected_remaining;
                $detail->save();
                $fixed++;
            }
            $bar->advance();
        }
        
        $bar->finish();
        $this->info("\nRepair completed! Fixed {$fixed} records.");
        
        return Command::SUCCESS;
    }
}
