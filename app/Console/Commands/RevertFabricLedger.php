<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\FabricReceiptDetail;

class RevertFabricLedger extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fabric:revert-ledger';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Revert fabric receipt detail warehouse id to parent';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $details = FabricReceiptDetail::with('fabric_receipt')->get();
        $count = 0;
        foreach ($details as $d) {
            if ($d->fabric_receipt && $d->master_fabric_warehouse_id != $d->fabric_receipt->master_fabric_warehouse_id) {
                $d->master_fabric_warehouse_id = $d->fabric_receipt->master_fabric_warehouse_id;
                $d->save();
                $count++;
            }
        }
        $this->info("Reverted {$count} records.");
        return Command::SUCCESS;
    }
}
