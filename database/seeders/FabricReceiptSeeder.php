<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\FabricReceipt;
use App\Models\FabricReceiptDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Fabric;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class FabricReceiptSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        FabricReceipt::truncate();
        FabricReceiptDetail::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $pos = PurchaseOrder::with('items')->get();

        foreach ($pos as $index => $po) {
            // Create a receipt for each PO
            $date = Carbon::parse($po->date)->addDays(5);

            $receipt = FabricReceipt::create([
                'vendor_id' => $po->vendor_id,
                'truck_number' => 'MH-04-' . rand(1000, 9999),
                'time' => $date->format('Y-m-d H:i:s'),
                'roll' => $po->items->count() * 2, // 2 rolls per item
                'received_by' => 'Store Manager',
                'master_fabric_warehouse_id' => $po->fabric_warehouse_id,
                'amount' => $po->items->sum('total_price'),
                'gst_percentage' => 12,
                'gst_amount' => $po->items->sum('total_price') * 0.12,
                'total_amount' => $po->items->sum('total_price') * 1.12,
                'status' => 1,
            ]);

            // SKU matching logic: FR/dmY/id
            $sku = "FR/" . $date->format('dmY') . "/" . $receipt->id;
            $shipment_id = "SHP/" . $date->format('dmY') . "/" . $receipt->id;
            $receipt->update([
                'sku' => $sku,
                'shipment_id' => $shipment_id
            ]);

            foreach ($po->items as $item) {
                // Create 2 rolls for each PO item
                for ($i = 1; $i <= 2; $i++) {
                    $meter = $item->meter / 2;
                    $qrcode_number = $this->generateUniqueQrNumber();

                    FabricReceiptDetail::create([
                        'fabric_receipt_id' => $receipt->id,
                        'purchase_order_id' => $po->id,
                        'purchase_order_item_id' => $item->id,
                        'fabric_id' => $item->fabric_id,
                        'fabric_sku' => $item->fabric_sku,
                        'roll' => 1,
                        'roll_number' => rand(100, 9999),
                        'meter' => $meter,
                        'remaining_quantity' => $meter,
                        'price_per_meter' => $item->price,
                        'master_fabric_warehouse_id' => $po->fabric_warehouse_id,
                        'qrcode_number' => $qrcode_number,
                        'shipment_number' => $shipment_id,
                        'status' => 1,
                    ]);
                }

                // Mark PO item as closed (status 2) and remaining_quantity as 0
                $item->update([
                    'remaining_quantity' => 0,
                    'status' => 2,
                ]);
            }
        }
    }

    private function generateUniqueQrNumber()
    {
        return mt_rand(10000000, 99999999) . mt_rand(10000000, 99999999);
    }
}
