<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FabricRollAssigning;
use App\Models\FabricReceiptDetail;

class FixRollAssignments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:roll-assignments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill fabric_receipt_detail_id for existing FabricRollAssigning records.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $records = FabricRollAssigning::whereNull('fabric_receipt_detail_id')
            ->whereNotNull('roll_no')
            ->where('roll_no', '!=', '')
            ->get();

        $this->info("Found {$records->count()} records to process.");

        $updatedCount = 0;
        $notFoundCount = 0;

        foreach ($records as $fra) {
            $fabricIds = array_filter(explode(',', $fra->orderProductSet?->fabric_id ?? ''));

            $query = FabricReceiptDetail::where('roll_number', $fra->roll_no);
            if (!empty($fabricIds)) {
                $query->whereIn('fabric_id', $fabricIds);
            }

            $roll = $query->first();

            if ($roll) {
                $fra->fabric_receipt_detail_id = $roll->id;
                $fra->save();
                $updatedCount++;
            } else {
                $this->warn("No matching FabricReceiptDetail found for roll_no '{$fra->roll_no}' (FabricRollAssigning ID: {$fra->id})");
                $notFoundCount++;
            }
        }

        $this->info("Successfully updated {$updatedCount} records.");
        if ($notFoundCount > 0) {
            $this->warn("Failed to find matches for {$notFoundCount} records.");
        }

        return 0;
    }
}
