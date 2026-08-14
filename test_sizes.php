<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$ctrl = new \App\Http\Controllers\Admin\PackingController(new \App\Services\Admin\PackingService());
$req = new \Illuminate\Http\Request(['design_number' => 'MM-JO-CO-255']);
echo json_encode($ctrl->apiGetSizeSets($req, 1638)->getData());
