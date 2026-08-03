<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sql = \App\Models\DomesticInventoryHistory::where('old_product_id', 706)
            ->orWhere('new_product_id', 706)
            ->when('2026-07-01', fn($q) => $q->whereDate('created_at', '>=', '2026-07-01'))
            ->toSql();
echo $sql . "\n";
