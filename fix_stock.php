<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\FabricReceiptDetail;
use App\Models\FabricRollAssigning;
use App\Models\AgentOrderFabricItem;
use App\Models\FabricReturnDetail;
use Illuminate\Support\Facades\DB;

echo "Starting stock correction script...\n\n";

$rolls = FabricReceiptDetail::all();
$fixed_count = 0;

foreach ($rolls as $roll) {
    $original_meter = (float) $roll->meter;

    // 1. Production Usages
    $production_used = (float) FabricRollAssigning::where('fabric_receipt_detail_id', $roll->id)->sum('meter');

    // 2. Direct Sales (Agent Orders)
    // Only count agent orders that have not been cancelled
    $direct_sales_used = (float) AgentOrderFabricItem::where('fabric_receipt_detail_id', $roll->id)->sum('meter');

    // 3. Returns (if any exist)
    $returns = 0;
    if (\Illuminate\Support\Facades\Schema::hasTable('fabric_return_details')) {
        $returns = (float) DB::table('fabric_return_details')
                        ->where('fabric_receipt_detail_id', $roll->id)
                        ->sum('return_meter');
    }

    // Expected remaining quantity
    $total_used = $production_used + $direct_sales_used + $returns;
    $expected_remaining = $original_meter - $total_used;

    // Sanity check: prevent negative remaining quantity
    if ($expected_remaining < 0) {
        $expected_remaining = 0;
    }

    // Update if there is a mismatch
    if (round((float)$roll->remaining_quantity, 2) !== round($expected_remaining, 2)) {
        echo "Fixing Roll ID {$roll->id} (Roll No: {$roll->roll_number}) | DB: {$roll->remaining_quantity} -> Corrected: {$expected_remaining}\n";
        $roll->update(['remaining_quantity' => $expected_remaining]);
        $fixed_count++;
    }
}

echo "\nCompleted! Fixed {$fixed_count} rolls.\n";
