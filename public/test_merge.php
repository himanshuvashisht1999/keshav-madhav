<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$c1 = \App\Models\OrderStageTransaction::whereIn('id', [1021, 2232])->get();
$c2 = \App\Models\OrderStageTransaction::where('id', 24)->get();
$c3 = \App\Models\OrderPrintingStageTransaction::limit(10)->get();
echo "c1 count: " . $c1->count() . "\n";
echo "c3 count: " . $c3->count() . "\n";
echo "Merged c1+c3 count: " . $c1->merge($c3)->count() . "\n";

$allTransactions = collect();
$allTransactions = $allTransactions->concat($c1)->concat($c3);
echo "Concat c1+c3 count: " . $allTransactions->count() . "\n";
