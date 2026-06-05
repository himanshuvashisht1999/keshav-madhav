<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class MergeDuplicateInventory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:merge-duplicates {--run : Actually perform the merge}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Merges duplicate rows in domestic_inventories caused by pattern/fitting removal.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("Scanning for duplicates in domestic_inventories...");

        // Find groups of duplicates
        $duplicates = DB::select('
            SELECT product_id, color_id, size_set_id, quantity, rack_id, COUNT(*) as cnt 
            FROM domestic_inventories 
            WHERE status = 1
            GROUP BY product_id, color_id, size_set_id, quantity, rack_id 
            HAVING cnt > 1
        ');

        if (empty($duplicates)) {
            $this->info("No duplicate groups found!");
            return 0;
        }

        $this->info("Found " . count($duplicates) . " duplicate groups.");
        
        $totalMerged = 0;
        $totalDeleted = 0;

        $runMode = $this->option('run');

        DB::beginTransaction();
        try {
            foreach ($duplicates as $group) {
                // Get all rows in this group, ordered by total_boxes DESC so the primary is the largest
                $rows = DB::table('domestic_inventories')
                    ->where('product_id', $group->product_id)
                    ->where('color_id', $group->color_id)
                    ->where('size_set_id', $group->size_set_id)
                    ->where('quantity', $group->quantity)
                    ->where('rack_id', $group->rack_id)
                    ->where('status', 1)
                    ->orderBy('total_boxes', 'desc')
                    ->get();

                if ($rows->count() <= 1) continue;

                $primary = $rows->first();
                $others = $rows->slice(1);

                $additionalBoxes = $others->sum('total_boxes');
                
                $this->info("Group [Product: {$group->product_id}, Color: {$group->color_id}, Size: {$group->size_set_id}]");
                $this->info(" -> Primary Row ID: {$primary->id} (Boxes: {$primary->total_boxes})");
                $this->info(" -> Will absorb " . $others->count() . " rows, totaling $additionalBoxes boxes.");

                if ($runMode) {
                    // 1. Update Primary Row
                    DB::table('domestic_inventories')
                        ->where('id', $primary->id)
                        ->update([
                            'total_boxes' => $primary->total_boxes + $additionalBoxes,
                            'updated_at' => now()
                        ]);

                    // 2. Update Foreign Keys if applicable (WarehouseTransferItem)
                    $otherIds = $others->pluck('id')->toArray();
                    DB::table('warehouse_transfer_items')
                        ->whereIn('domestic_inventory_id', $otherIds)
                        ->update(['domestic_inventory_id' => $primary->id]);

                    // 3. Delete duplicates
                    DB::table('domestic_inventories')->whereIn('id', $otherIds)->delete();
                    
                    $totalMerged++;
                    $totalDeleted += count($otherIds);
                }
            }

            if ($runMode) {
                DB::commit();
                $this->info("SUCCESS! Merged $totalMerged groups, deleted $totalDeleted duplicate rows.");
            } else {
                DB::rollBack();
                $this->info("DRY RUN COMPLETE. Run with --run to apply changes.");
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("Error during merge: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
