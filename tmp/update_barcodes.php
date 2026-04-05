<?php
use Illuminate\Support\Facades\DB;

$count = DB::table('domestic_inventories')
    ->where('barcode', 'like', '%-%')
    ->update([
        'barcode' => DB::raw("REPLACE(barcode, '-', '')"),
        'qrcode' => DB::raw("REPLACE(qrcode, '-', '')")
    ]);

echo "Updated $count inventory records.";
