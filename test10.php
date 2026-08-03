<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

print_r(\Illuminate\Support\Facades\Schema::getColumnListing('domestic_inventory_histories'));
