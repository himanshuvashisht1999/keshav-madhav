<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PackingMain;
use App\Models\DomesticInventoryHistory;
use Illuminate\Support\Facades\DB;

class BackfillInboundSessionHistories extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'inventory:backfill-inbound-sessions 
                            {--session= : Specific session ID to backfill}
                            {--dry-run : Simulate the backfill without writing to the database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Backfill missing DomesticInventoryHistory records for inbound sessions (especially stock consume sessions)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $sessionId = $this->option('session');
        $isDryRun = $this->option('dry-run');

        if ($isDryRun) {
            $this->warn("Running in DRY-RUN mode. No database changes will be committed.");
        }

        $query = PackingMain::where('order_main_id', 0)
            ->where('slip_id', 0)
            ->orderBy('id', 'asc');

        if ($sessionId) {
            $query->where('id', $sessionId);
        }

        $sessions = $query->get();
        $totalSessions = $sessions->count();

        $this->info("Found {$totalSessions} inbound sessions to analyze.");

        $updatedSessions = 0;
        $totalInsertedRecords = 0;
        $totalBoxesAdded = 0;

        $bar = $this->output->createProgressBar($totalSessions);
        $bar->start();

        foreach ($sessions as $session) {
            $cartonsCount = $session->cartons()->count();

            // Check existing creation/sample history
            $existingCreation = DomesticInventoryHistory::where('created_at', $session->created_at)
                ->whereIn('type', ['creation', 'sample'])
                ->get();

            $existingBoxes = $existingCreation->sum('box_quantity');

            // If creation histories already match cartons, nothing to do
            if ($existingCreation->isNotEmpty() && ($cartonsCount == 0 || $existingBoxes >= $cartonsCount)) {
                $bar->advance();
                continue;
            }

            // Get generated items using model helper
            $items = $session->getInboundItems();

            if ($items->isEmpty()) {
                $bar->advance();
                continue;
            }

            // If existing creation records already match the generated items count, skip
            if ($existingCreation->count() >= $items->count() && $existingBoxes >= $items->sum('box_quantity')) {
                $bar->advance();
                continue;
            }

            $recordsToInsert = [];

            foreach ($items as $item) {
                // Check if this specific variant is already recorded at this timestamp
                $alreadyExists = DomesticInventoryHistory::where('created_at', $session->created_at)
                    ->where('new_product_id', $item->new_product_id)
                    ->where('new_size_set_id', $item->new_size_set_id)
                    ->where('new_color_id', $item->new_color_id)
                    ->whereIn('type', ['creation', 'sample'])
                    ->first();

                if ($alreadyExists) {
                    continue;
                }

                $boxQuantity = $item->box_quantity > 0 ? $item->box_quantity : 1;

                $recordsToInsert[] = [
                    'user_id' => $session->created_by ?? 1,
                    'purchase_id' => null,
                    'vendor_id' => null,
                    'customer_id' => null,
                    'old_product_id' => null,
                    'old_size_set_id' => null,
                    'old_color_id' => null,
                    'old_fitting_id' => null,
                    'old_pattern_id' => null,
                    'old_rack_id' => null,
                    'old_warehouse_id' => null,
                    'new_product_id' => $item->new_product_id,
                    'new_size_set_id' => $item->new_size_set_id,
                    'new_color_id' => $item->new_color_id,
                    'new_fitting_id' => null,
                    'new_pattern_id' => null,
                    'new_rack_id' => $item->new_rack_id,
                    'new_warehouse_id' => null,
                    'box_quantity' => $boxQuantity,
                    'mrp' => $item->mrp ?? 0,
                    'type' => 'creation',
                    'pieces_per_box' => $item->pieces_per_box ?? 0,
                    'purchase_rate' => 0,
                    'remarks' => 'Backfilled inbound session generated stock',
                    'created_at' => $session->created_at,
                    'updated_at' => $session->created_at,
                ];
            }

            if (!empty($recordsToInsert)) {
                $updatedSessions++;
                $sessionBoxes = 0;

                foreach ($recordsToInsert as $record) {
                    $totalInsertedRecords++;
                    $totalBoxesAdded += $record['box_quantity'];
                    $sessionBoxes += $record['box_quantity'];

                    if (!$isDryRun) {
                        DomesticInventoryHistory::create($record);
                    }
                }
            }

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Inbound Sessions Checked', $totalSessions],
                ['Sessions Needing Backfill', $updatedSessions],
                ['Total History Records Inserted', $totalInsertedRecords],
                ['Total Boxes Accounted For', $totalBoxesAdded],
                ['Mode', $isDryRun ? 'DRY-RUN (Simulated)' : 'LIVE (Changes Saved)'],
            ]
        );

        if ($isDryRun) {
            $this->info("Dry run complete. Run without --dry-run to commit changes.");
        } else {
            $this->info("Successfully backfilled all historical inbound sessions.");
        }

        return Command::SUCCESS;
    }
}
