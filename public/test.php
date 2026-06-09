<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $fabricsQuery = DB::table('fabric_receipts')
        ->join('vendors', 'fabric_receipts.vendor_id', '=', 'vendors.id')
        ->select(
            'fabric_receipts.id as ref_id',
            'fabric_receipts.bill_no as invoice_no',
            'vendors.name as vendor_name',
            DB::raw("CAST('Fabric' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
            'fabric_receipts.time as date',
            DB::raw('COALESCE(fabric_receipts.total_amount, 0) as grand_total')
        );

    $itemsQuery = DB::table('items_receipts')
        ->join('vendors', 'items_receipts.vendor_id', '=', 'vendors.id')
        ->select(
            'items_receipts.id as ref_id',
            DB::raw('NULL as invoice_no'),
            'vendors.name as vendor_name',
            DB::raw("CAST('Product/Accessory' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
            'items_receipts.time as date',
            DB::raw('0 as grand_total') // No amount columns in items_receipts
        );

    // Apply Product/Accessory filter
    $fabricsQuery->whereRaw('1 = 0');

    $combinedQuery = $fabricsQuery->union($itemsQuery);

    $query = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_purchases"))
        ->mergeBindings($combinedQuery);

    $count = $query->count();
    echo "Count: " . $count . "\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
