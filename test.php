<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$count = \App\Models\DomesticInventoryHistory::where('old_product_id', 706)->count();
echo "Count for old_product_id=706: " . $count . PHP_EOL;

$histories = \App\Models\DomesticInventoryHistory::where('old_product_id', 706)->orWhere('new_product_id', 706)->get();
foreach ($histories as $history) {
    echo "ID: {$history->id}, Type: {$history->type}, Old: {$history->old_product_id}, New: {$history->new_product_id}\n";
}