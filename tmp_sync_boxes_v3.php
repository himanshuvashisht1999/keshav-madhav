<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\PackingBox;
use App\Models\DomesticInventory;

$inventories = DomesticInventory::all();

foreach($inventories as $inv) {
    echo "Processing Inventory #{$inv->id} (Barcode: {$inv->barcode}, First Box: {$inv->box_no})\n";
    
    // Find the first box
    $firstBox = PackingBox::where('box_no', $inv->box_no)->first();
    if($firstBox) {
        // Find all boxes in the same PackingMain that have the same type of items
        // Since Domestic boxes normally only have one type of set per box, we can match by the design in the items
        $firstItem = $firstBox->items->first();
        if($firstItem && $firstItem->detail && $firstItem->detail->orderProductSet) {
            $designId = $firstItem->detail->orderProductSet->production_goods_id;
            
            $relatedBoxes = PackingBox::where('packing_main_id', $firstBox->packing_main_id)
                ->where('box_type', 'domestic')
                ->whereHas('items.detail.orderProductSet', function($q) use ($designId) {
                    $q->where('production_goods_id', $designId);
                })
                ->get();
                
            echo "Found " . $relatedBoxes->count() . " related boxes to update with barcode {$inv->barcode}\n";
            foreach($relatedBoxes as $rb) {
                $rb->barcode = $inv->barcode;
                $rb->save();
            }
        } else {
            // If no items, just update the first box at least
            $firstBox->barcode = $inv->barcode;
            $firstBox->save();
        }
    }
}
echo "Done.\n";
