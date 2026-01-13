<?php

use App\Models\Storeroom;

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

echo "Count: " . Storeroom::count() . "\n";
$all = Storeroom::all();
foreach($all as $s) {
    echo "ID: " . $s->id . " - " . $s->name . "\n";
}
