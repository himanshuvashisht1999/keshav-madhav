<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ProductionGoods;
use App\Models\ProductionGoodImage;
use App\Models\ProductStage;
use App\Models\BillOfMaterial;
use Illuminate\Support\Facades\DB;

class ProductionGoodsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        ProductionGoods::truncate();
        ProductionGoodImage::truncate();
        ProductStage::truncate();
        BillOfMaterial::truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $products = [
            [
                'name' => 'Classic Blue Slim Fit',
                'sku' => 'PRD-CB-SLIM',
                'type_id' => 1, // Men's
                'size_id' => 2, // 24-32
                'pattern_id' => 2, // Slim Fit
                'color_id' => 1, // Indigo Blue
                'design_no' => 'DSGN-001',
                'fabric_sku' => 'ULTRA INDIGO STRETCH-1',
                'meter' => 1.2,
                'stages' => [3, 4, 6, 11] // Cutting, Stitching, Washing, Packing
            ],
            [
                'name' => 'Midnight Black Skinny',
                'sku' => 'PRD-MB-SKINNY',
                'type_id' => 2, // Women's
                'size_id' => 2, // 24-32
                'pattern_id' => 1, // Skinny Fit
                'color_id' => 12, // Midnight Black
                'design_no' => 'DSGN-002',
                'fabric_sku' => 'MIDNIGHT BLACK PREMIUM-3',
                'meter' => 1.1,
                'stages' => [3, 1, 4, 6, 11] // Cutting, Printing, Stitching, Washing, Packing
            ],
            [
                'name' => 'Rugged Straight Cut',
                'sku' => 'PRD-RSC-STRAIGHT',
                'type_id' => 1, // Men's
                'size_id' => 3, // 34-42
                'pattern_id' => 4, // Straight Fit
                'color_id' => 3, // Dark Blue
                'design_no' => 'DSGN-003',
                'fabric_sku' => 'HEAVY DUTY 14OZ DENIM-6',
                'meter' => 1.4,
                'stages' => [3, 4, 6, 7, 8, 11] // Cutting, Stitching, Washing, Thread Cutting, Button Revit, Packing
            ],
            [
                'name' => 'Kid\'s Comfort Jogger',
                'sku' => 'PRD-KC-JOGGER',
                'type_id' => 3, // Kid's
                'size_id' => 1, // 14-22
                'pattern_id' => 8, // Jogger
                'color_id' => 2, // Light Blue
                'design_no' => 'DSGN-004',
                'fabric_sku' => 'LIGHT WASH SUMMER DENIM-2',
                'meter' => 0.8,
                'stages' => [3, 4, 6, 11]
            ],
            [
                'name' => 'Vintage Relaxed Fit',
                'sku' => 'PRD-VRF-RELAXED',
                'type_id' => 4, // Unisex
                'size_id' => 10, // 28-36
                'pattern_id' => 5, // Relaxed Fit
                'color_id' => 10, // Stone Wash
                'design_no' => 'DSGN-005',
                'fabric_sku' => 'COMFORT SLUB DENIM-2',
                'meter' => 1.3,
                'stages' => [3, 2, 4, 6, 9, 10, 11] // Incl Embroidery, Pressing, Checking
            ],
        ];

        foreach ($products as $index => $p) {
            $product = ProductionGoods::create([
                'sno' => $index + 1,
                'company_id' => 1,
                'sku' => $p['sku'],
                'name_of_garment' => $p['name'],
                'type_of_garment' => $p['type_id'],
                'master_size_id' => $p['size_id'],
                'garment_pattern' => $p['pattern_id'],
                'master_color_id' => $p['color_id'],
                'design_number' => $p['design_no'],
                'is_printing' => in_array(1, $p['stages']) ? 1 : 0,
                'is_embroidery' => in_array(2, $p['stages']) ? 1 : 0,
                'status' => 1,
            ]);

            // Seed placeholder images
            ProductionGoodImage::create([
                'product_id' => $product->id,
                'is_main' => 1,
                'image' => 'placeholder.png',
                'status' => 1,
            ]);

            // Seed Stages
            foreach ($p['stages'] as $stageId) {
                ProductStage::create([
                    'master_product_id' => $product->id,
                    'master_stage_id' => $stageId,
                    'status' => 1,
                ]);
            }

            // Seed BOM
            BillOfMaterial::create([
                'product_id' => $product->id,
                'fabric_sku' => $p['fabric_sku'],
                'meter' => $p['meter'],
                'status' => 1,
            ]);
        }
    }
}
