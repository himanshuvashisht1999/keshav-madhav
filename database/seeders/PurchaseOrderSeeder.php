<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Fabric;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PurchaseOrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        PurchaseOrder::truncate();
        PurchaseOrderItem::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pos = [
            [
                'vendor_id' => 1, // Arvind Mills
                'warehouse_id' => 1, // Rajesh Kumar
                'items' => [
                    ['fabric_id' => 1, 'meter' => 500, 'price' => 320],
                    ['fabric_id' => 2, 'meter' => 300, 'price' => 450],
                ]
            ],
            [
                'vendor_id' => 2, // Vardhman
                'warehouse_id' => 2, // Amit Shah
                'items' => [
                    ['fabric_id' => 3, 'meter' => 800, 'price' => 280],
                    ['fabric_id' => 4, 'meter' => 200, 'price' => 310],
                ]
            ],
            [
                'vendor_id' => 3, // Raymond
                'warehouse_id' => 1,
                'items' => [
                    ['fabric_id' => 5, 'meter' => 1000, 'price' => 420],
                    ['fabric_id' => 6, 'meter' => 400, 'price' => 350],
                ]
            ],
            [
                'vendor_id' => 6, // Nandan Denim
                'warehouse_id' => 2,
                'items' => [
                    ['fabric_id' => 7, 'meter' => 1500, 'price' => 290],
                ]
            ],
        ];

        foreach ($pos as $poData) {
            $date = Carbon::now()->subDays(rand(1, 10));
            $deliveryDate = (clone $date)->addDays(15);

            $po = PurchaseOrder::create([
                'date' => $date->format('Y-m-d'),
                'vendor_id' => $poData['vendor_id'],
                'delivery_date' => $deliveryDate->format('Y-m-d'),
                'fabric_warehouse_id' => $poData['warehouse_id'],
                'is_notify' => 1,
                'status' => 1,
                'sku' => '', // placeholders
            ]);

            // Generate SKU consistent with service logic: PO/dmY/id
            $sku = "PO/" . $date->format('dmY') . "/" . $po->id;
            $po->update(['sku' => $sku]);

            foreach ($poData['items'] as $itemData) {
                $fabric = Fabric::find($itemData['fabric_id']);
                if ($fabric) {
                    PurchaseOrderItem::create([
                        'purchase_order_id' => $po->id,
                        'fabric_id' => $fabric->id,
                        'fabric_sku' => $fabric->sku,
                        'meter' => $itemData['meter'],
                        'remaining_quantity' => $itemData['meter'],
                        'price' => $itemData['price'],
                        'total_price' => $itemData['meter'] * $itemData['price'],
                        'status' => 1,
                    ]);
                }
            }
        }
    }
}
