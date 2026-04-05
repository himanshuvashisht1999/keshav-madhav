<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$listing = Illuminate\Support\Facades\DB::getSchemaBuilder()->getColumnListing('domestic_inventories');
file_put_contents('c:\xampp\htdocs\keshav-madhav\tmp\listing.txt', implode("\n", $listing));
