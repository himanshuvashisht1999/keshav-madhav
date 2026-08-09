<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$req = new \Illuminate\Http\Request();
$req->merge(['limit' => 5]);
$ctrl = app(\App\Http\Controllers\Admin\WarehouseInventoryController::class);
$q = $ctrl->buildIndexQuery($req);
$results = $q->take(5)->get();
foreach($results as $r) {
    echo "Variant ID: " . $r->variant_id . " - Image: " . $r->product_image . "\n";
}
?>
