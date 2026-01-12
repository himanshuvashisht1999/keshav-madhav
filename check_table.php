<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$columns = \Illuminate\Support\Facades\Schema::getColumnListing('order_products_set_details');
echo "Checking columns in table 'order_products_set_details':\n";
$hasPlural = in_array('order_products_set_id', $columns);
$hasSingular = in_array('order_product_set_id', $columns);

echo "Has 'order_products_set_id': " . ($hasPlural ? "YES" : "NO") . "\n";
echo "Has 'order_product_set_id': " . ($hasSingular ? "YES" : "NO") . "\n";
echo "All columns: " . implode(', ', $columns) . "\n";
