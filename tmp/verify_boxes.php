<?php
include 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\Admin\PackingService;
use App\Models\PackingCarton;
use App\Models\PackingBox;
use App\Models\PackingItem;
use App\Models\OrderProductSet;
use Illuminate\Support\Facades\DB;

$service = new PackingService();

$slip_id = 47;
$order_id = 12;

$set = OrderProductSet::where('order_main_id', $order_id)->first();
if (!$set) {
    die("No sets found for order $order_id\n");
}

$plan = [
    [
        'carton_no' => '9998',
        'rack_id' => 1,
        'type' => 'set',
        'content_id' => $set->id,
        'quantity' => 1
    ]
];

DB::beginTransaction();
try {
    $res = $service->saveMultiCartonPlan([
        'slip_id' => $slip_id,
        'order_id' => $order_id,
        'plan' => $plan
    ]);
    
    echo "Service Response: " . json_encode($res) . "\n";
    
    $carton = PackingCarton::where('carton_no', '9998')->first();
    echo "CARTON CREATED: " . ($carton ? 'YES' : 'NO') . "\n";
    
    if ($carton) {
        $boxes = PackingBox::where('packing_carton_id', $carton->id)->get();
        echo "BOXES IN CARTON: " . $boxes->count() . "\n";
        foreach ($boxes as $box) {
            echo "BOX NO: " . $box->box_no . "\n";
            $items = PackingItem::where('packing_box_id', $box->id)->get();
            echo "ITEMS IN BOX: " . $items->count() . "\n";
        }
    }
    
    DB::rollBack();
    echo "VERIFICATION DONE (Rollback successful)\n";
} catch (\Exception $e) {
    DB::rollBack();
    echo "ERROR: " . $e->getMessage() . "\n";
}
