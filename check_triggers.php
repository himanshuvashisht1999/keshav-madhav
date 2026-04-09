<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$triggers = DB::select('SHOW TRIGGERS');
foreach($triggers as $t) {
    if ($t->Table === 'packing_boxes') {
        echo "Trigger: " . $t->Trigger . " Event: " . $t->Event . "\n";
        echo "Statement: " . $t->Statement . "\n";
    }
}
echo "Done.\n";
