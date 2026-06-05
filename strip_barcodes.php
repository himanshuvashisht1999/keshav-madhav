<?php

$inventories = DB::table('domestic_inventories')->get();
foreach ($inventories as $inv) {
    $stripped = preg_replace('/P\d+F\d+$/', '', $inv->barcode);
    if ($stripped !== $inv->barcode) {
        DB::table('domestic_inventories')->where('id', $inv->id)->update(['barcode' => $stripped]);
    }
}

$items = DB::table('agent_order_items')->get();
foreach ($items as $item) {
    $stripped = preg_replace('/P\d+F\d+$/', '', $item->barcode);
    if ($stripped !== $item->barcode) {
        DB::table('agent_order_items')->where('id', $item->id)->update(['barcode' => $stripped]);
    }
}

$boxes = DB::table('packing_boxes')->get();
foreach ($boxes as $box) {
    $stripped = preg_replace('/P\d+F\d+$/', '', $box->barcode);
    if ($stripped !== $box->barcode) {
        DB::table('packing_boxes')->where('id', $box->id)->update(['barcode' => $stripped]);
    }
}
echo "Done strip barcodes.\n";
