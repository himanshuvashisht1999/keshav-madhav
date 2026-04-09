<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PackingBox;
use App\Models\DomesticInventory;

$inventories = DomesticInventory::all();
echo "Found " . $inventories->count() . " domestic inventory records.\n";

foreach($inventories as $inv) {
    if($inv->barcode) {
        // Find boxes that match this inventory's first box_no
        $boxes = PackingBox::where('box_no', $inv->box_no)->where('box_type', 'domestic')->get();
        foreach($boxes as $box) {
            $box->barcode = $inv->barcode;
            $box->save();
            echo "Updated Box #{$box->box_no} with barcode {$inv->barcode} from Inventory #{$inv->id}\n";
        }
    }
}

// Second pass: for boxes that are still unlinked, try to find them by items (as a fallback)
$unlinked = PackingBox::whereNull('barcode')->where('box_type', 'domestic')->with('items.detail.orderProductSet')->get();
foreach($unlinked as $box) {
    if($box->items->count() > 0) {
        $set = $box->items[0]->detail->orderProductSet;
        if($set) {
            $barcode = 'D' . $set->production_goods_id . 'S' . $set->set_size . 'C' . $set->color_id . 'P' . ($set->master_design_pattern_id ?? 0) . 'F' . ($set->master_product_fitting_id ?? 0);
            $box->barcode = $barcode;
            $box->save();
            echo "Fallback Sync: Box #{$box->box_no} with barcode $barcode\n";
        }
    }
}

echo "Done.\n";
