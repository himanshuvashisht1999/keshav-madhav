<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$cols = Schema::getColumnListing('packing_boxes');
foreach($cols as $c) {
    echo "Column: [" . $c . "] Length: " . strlen($c) . "\n";
}
