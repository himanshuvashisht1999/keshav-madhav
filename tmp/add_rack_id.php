<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try {
    Illuminate\Support\Facades\Schema::table('domestic_inventories', function ($table) {
        $table->unsignedBigInteger('rack_id')->nullable()->after('packing_carton_id');
    });
    echo "SUCCESS: rack_id added to domestic_inventories\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
