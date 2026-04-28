<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    DB::statement('ALTER TABLE order_products_sets MODIFY fabric_id VARCHAR(500) NULL');
    DB::statement('ALTER TABLE order_cutting_stage MODIFY fabric_id VARCHAR(500) NULL');
    echo "Columns altered successfully.\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
