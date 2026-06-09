<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $fabricsQuery = DB::table('fabric_receipts')
        ->select(DB::raw("CAST('Fabric' AS CHAR) as item_type"));

    $itemsQuery = DB::table('items_receipts') // or dual if possible
        ->select(DB::raw("CAST('Product/Accessory' AS CHAR) as item_type"));

    // Let's make itemsQuery return a row by changing it to query from a table with rows
    $itemsQuery2 = DB::table('users')->limit(1)->select(DB::raw("CAST('Product/Accessory' AS CHAR) as item_type"));

    $combinedQuery = $fabricsQuery->union($itemsQuery2);

    $results = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_purchases"))
        ->mergeBindings($combinedQuery)->get();
        
    print_r($results->toArray());
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
