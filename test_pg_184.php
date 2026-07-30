<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$pg = \DB::table('production_goods')->where('id', 184)->first();
$series = \DB::table('master_series')->where('id', $pg->master_series_id)->first();
echo "Series: {$series->name}, Garment: {$pg->name_of_garment}\n";
