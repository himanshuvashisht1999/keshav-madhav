<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$q = \App\Models\AgentOrderDispatch::whereHas('orders', function($q) {
    $q->where('sale_type', 'fabric');
});

echo $q->toSql();
echo "\n";
print_r($q->getBindings());

$q2 = \App\Models\AgentOrderDispatch::whereHas('orders', function($q) {
    $q->where('sale_type', 'item');
});

echo "\nItem Query:\n";
echo $q2->toSql();
echo "\n";
print_r($q2->getBindings());
