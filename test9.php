<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$id = 706;
$histories = \App\Models\DomesticInventoryHistory::where('new_product_id', $id)
    ->orWhere('old_product_id', $id)
    ->get();

foreach($histories as $h) {
    echo "ID: {$h->id}, Type: {$h->type}, Old: {$h->old_product_id}, New: {$h->new_product_id}, Qty: {$h->box_quantity}\n";
}
