<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\MasterDesign;
use App\Models\MasterMaterial;
use App\Models\MasterSizeMeasurement;
use App\Models\Fabric;
use App\Models\BillOfMaterial;
use App\Models\MasterProductType;
use App\Models\MasterProductStage;
use App\Models\ProductStage;
use App\Models\ProductionGoodImage;
use App\Models\ItemAttributeValue;
use App\Http\DataTable\Admin\Master\ProductionGoodsDataTable as DataTable;
use App\Models\MasterPattern;
use App\Models\MasterDesignPattern;
use App\Models\MasterSeries;
use App\Models\Brand;
use App\Models\ProductNature;
use App\Models\FabricType;

class ProductionGoodsService
{
    public function __construct(
        DataTable $datatable,
        ProductionGoods $production_goods
    ) {
        $this->datatable = $datatable;
        $this->production_goods = $production_goods;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function exportData(Request $request)
    {
        $query = ProductionGoods::with('series', 'brand', 'fitting', 'pattern', 'productNature', 'fabricType')->where('status', '!=', 3)->orderBy('id','desc');
                
        if ($request->has('name_of_garment') && !empty($request->name_of_garment)) {
            $searchTerm = $request->get('name_of_garment');
            $query->where(function($q) use ($searchTerm) {
                $q->where('name_of_garment', 'like', "%{$searchTerm}%")
                  ->orWhereHas('series', function($sq) use ($searchTerm) {
                      $sq->where('name', 'like', "%{$searchTerm}%");
                  });
            });
        }

        if ($request->has('brand_name') && !empty($request->brand_name)) {
            $brandTerm = $request->get('brand_name');
            $query->whereHas('brand', function($q) use ($brandTerm) {
                $q->where('name', 'like', "%{$brandTerm}%");
            });
        }

        if ($request->has('design_number') && !empty($request->design_number)) {
            $query->where('design_number', 'like', "%{$request->get('design_number')}%");
        }

        if ($request->has('fitting_name') && !empty($request->fitting_name)) {
            $term = $request->get('fitting_name');
            $query->whereHas('fitting', function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }

        if ($request->has('pattern_name') && !empty($request->pattern_name)) {
            $term = $request->get('pattern_name');
            $query->whereHas('pattern', function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }

        if ($request->has('nature_name') && !empty($request->nature_name)) {
            $term = $request->get('nature_name');
            $query->whereHas('productNature', function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }

        if ($request->has('fabric_type_name') && !empty($request->fabric_type_name)) {
            $term = $request->get('fabric_type_name');
            $query->whereHas('fabricType', function($q) use ($term) {
                $q->where('name', 'like', "%{$term}%");
            });
        }
        
        return $query->get();
    }

    public function store(Request $request)
    {
        $printing_stage_after = $request->printing_stage_after ?? 0;
        $embroidery_stage_after = $request->embroidery_stage_after ?? 0;

        $save_data = new ProductionGoods;

        $save_data->company_id = $request->company_id;
        $save_data->sku = $request->sku;
        $save_data->name_of_garment = $request->name_of_garment;
        $save_data->master_series_id = $request->master_series_id;
        $save_data->design_number = $request->design_number;
        $save_data->brand_id = $request->brand_id;

        // For General company these fields are required, for Royal they might be null
        $save_data->type_of_garment = $request->type_of_garment; // null for Royal is okay if column is nullable
        $save_data->master_size_id = $request->master_size_id;
        $save_data->garment_pattern = $request->garment_pattern;
        $save_data->master_color_id = $request->master_color_id;

        $save_data->master_product_fitting_id = $request->master_product_fitting_id;
        $save_data->master_pattern_id = $request->master_pattern_id;
        $save_data->product_nature_id = $request->product_nature_id;
        $save_data->fabric_type_id = $request->fabric_type_id;

        $save_data->is_printing = $request->is_printing ?? 1;
        $save_data->is_embroidery = $request->is_embroidery ?? 1;
        $save_data->printing_stage_after = $printing_stage_after;
        $save_data->embroidery_stage_after = $embroidery_stage_after;

        // If you don't really use this, you can drop this column later
        $save_data->fabric_sku = '';

        $save_data->status = 1;
        $save_data->save();

        /////////  save images
        $imgName = NULL;
        if ($request->file('main_image')) {
            $image = $request->file('main_image');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "product-" . rand() . "_" . time() . "." . $extImage;
            $destinationPath = public_path() . '/assets/products';
            $image->move($destinationPath, $imgName);
        }

        $save_main_image = new ProductionGoodImage;
        $save_main_image->product_id = $save_data->id;
        $save_main_image->is_main = 1;
        $save_main_image->image = $imgName;
        $save_main_image->status = 1;
        $save_main_image->save();

        if ($request->hasFile('other_images')) {
            foreach ($request->file('other_images') as $key => $imageFile) {
                if (!$imageFile) {
                    continue;
                }
                // Generate file name
                $extImage = $imageFile->getClientOriginalExtension();
                $imgName = "product-" . rand() . "_" . time() . "_$key." . $extImage;
                // Move to folder
                $destinationPath = public_path() . '/assets/products';
                $imageFile->move($destinationPath, $imgName);

                // Save record in DB
                $save_other_image = new ProductionGoodImage;
                $save_other_image->product_id = $save_data->id;
                $save_other_image->is_main = 0; // <-- Not main image
                $save_other_image->image = $imgName;
                $save_other_image->status = 1;
                $save_other_image->save();
            }
        }



        ////////// end images

        /**
         * PRODUCT STAGES
         * - For General: user selected stages manually
         * - For Royal: we auto-filled all stages via JS (even though UI is hidden)
         */
        if ($request->has('product_stage_id') && is_array($request->product_stage_id)) {
            foreach ($request->product_stage_id as $single) {
                if (!$single) {
                    continue;
                }

                // Save the main stage
                $save_stage = new ProductStage;
                $save_stage->master_product_id = $save_data->id;
                $save_stage->master_stage_id = $single;
                $save_stage->save();

                // Insert Printing stage (id = 1) after selected stage
                if ($printing_stage_after && $printing_stage_after == $single) {
                    $printingStage = new ProductStage;
                    $printingStage->master_product_id = $save_data->id;
                    $printingStage->master_stage_id = 1; // printing master id
                    $printingStage->save();
                }

                // Insert Embroidery stage (id = 2) after selected stage
                if ($embroidery_stage_after && $embroidery_stage_after == $single) {
                    $embroideryStage = new ProductStage;
                    $embroideryStage->master_product_id = $save_data->id;
                    $embroideryStage->master_stage_id = 2; // embroidery master id
                    $embroideryStage->save();
                }
            }
        }

        /**
         * SAVE PRODUCT VARIANTS (Dynamic Size Sets + Colors + MRP)
         */
        if ($request->has('size_sets') && is_array($request->size_sets)) {
            foreach ($request->size_sets as $index => $sizeSetId) {
                if ($sizeSetId) {
                    $mrp = $request->mrps[$index] ?? 0;
                    $sizeSetImage = null;
                    $allSetImages = $request->file('size_set_images');
                    if (is_array($allSetImages) && isset($allSetImages[$index])) {
                        $image = $allSetImages[$index];
                        $ext = $image->getClientOriginalExtension();
                        $sizeSetImage = "set-" . rand() . "_" . time() . "." . $ext;
                        $image->move(public_path('assets/products'), $sizeSetImage);
                    }

                    $variant = \App\Models\ProductionGoodVariant::create([
                        'production_goods_id' => $save_data->id,
                        'master_size_measurement_id' => $sizeSetId,
                        'image' => $sizeSetImage,
                        'mrp' => $mrp
                    ]);

                    // Nested Colors & Images for this Size Set
                    if (isset($request->variant_colors[$index]) && is_array($request->variant_colors[$index])) {
                        foreach ($request->variant_colors[$index] as $cIdx => $colorId) {
                            if (!$colorId)
                                continue;

                            $imagePath = null;
                            if ($request->hasFile("variant_images.$index.$cIdx")) {
                                $image = $request->file("variant_images.$index.$cIdx");
                                $ext = $image->getClientOriginalExtension();
                                $imagePath = "variant-" . rand() . "_" . time() . "." . $ext;
                                $image->move(public_path('assets/products'), $imagePath);
                            }

                            $barcode = 'D' . $save_data->id . 'S' . $sizeSetId . 'C' . $colorId;
                            \App\Models\ProductionGoodVariantItem::create([
                                'variant_id' => $variant->id,
                                'master_color_id' => $colorId,
                                'barcode' => $barcode,
                                'image' => $imagePath,
                            ]);
                        }
                    }
                }
            }
        }

        /**
         * BILL OF MATERIAL (FABRIC DETAILS)
         * Fix: use index from foreach instead of $i and set product_id correctly.
         */
        if (!empty($request->fabric_sku) && is_array($request->fabric_sku)) {
            foreach ($request->fabric_sku as $index => $fabricSku) {
                if (!$fabricSku) {
                    continue;
                }

                $meter = $request->fabric_meter[$index] ?? null;
                if ($meter === null || $meter === '') {
                    continue;
                }

                $save_data_product = new BillOfMaterial;
                $save_data_product->product_id = $save_data->id; // <-- FIXED
                $save_data_product->fabric_sku = $fabricSku; // <-- FIXED
                $save_data_product->meter = $meter; // <-- FIXED
                $save_data_product->status = 1;
                $save_data_product->save();
            }
        }

        // Sync to inventory prices/images
        // $this->syncToInventory($save_data->id);

        return true;
    }


    public function edit(Request $request)
    {
        $data = ProductionGoods::with('bill_of_materials', 'product_stages', 'variants.items.color')->where('id', $request->id)->first();

        if ($data) {
            // Check if ANY variant has stock in inventory to lock product level fields (fitting, pattern)
            $data->is_locked_in_inventory = \App\Models\DomesticInventory::where('product_id', $data->id)->exists();

            foreach ($data->variants as $variant) {
                // If any item (color) under this variant is in inventory, the variant (size set) itself is locked
                $has_item_in_inv = false;
                foreach ($variant->items as $item) {
                    $item->is_locked_in_inventory = \App\Models\DomesticInventory::where('barcode', $item->barcode)->exists();
                    if ($item->is_locked_in_inventory) {
                        $has_item_in_inv = true;
                    }
                }
                $variant->is_locked_in_inventory = $has_item_in_inv;
            }
        }

        return $data;
    }
    public function update(Request $request)
    {

        $printing_stage_after = $request->printing_stage_after ?? null;
        $embroidery_stage_after = $request->embroidery_stage_after ?? null;

        $update_data = ProductionGoods::findOrFail($request->id);

        $isProductInInv = \App\Models\DomesticInventory::where('product_id', $update_data->id)->exists();

        // Basic fields (common)
        $update_data->company_id = $request->company_id; // from edit form

        $update_data->master_product_fitting_id = $request->master_product_fitting_id;
        $update_data->master_pattern_id = $request->master_pattern_id;
        $update_data->design_number = $request->design_number ?? $update_data->design_number;

        $update_data->brand_id = $request->brand_id;
        $update_data->name_of_garment = $request->name_of_garment;
        $update_data->master_series_id = $request->master_series_id;
        // SKU we keep from DB or from request (readonly in form, but safe to assign)
        $update_data->sku = $request->sku ?? $update_data->sku;

        // For General, these are required; for Royal they may be null (columns should be nullable)
        $update_data->type_of_garment = $request->type_of_garment;
        $update_data->master_size_id = $request->master_size_id;
        $update_data->garment_pattern = $request->garment_pattern;
        $update_data->master_color_id = $request->master_color_id;

        $update_data->master_product_fitting_id = $request->master_product_fitting_id;
        $update_data->master_pattern_id = $request->master_pattern_id;
        $update_data->product_nature_id = $request->product_nature_id;
        $update_data->fabric_type_id = $request->fabric_type_id;

        $update_data->is_printing = $request->is_printing ?? 0;
        $update_data->is_embroidery = $request->is_embroidery ?? 0;
        $update_data->printing_stage_after = $printing_stage_after;
        $update_data->embroidery_stage_after = $embroidery_stage_after;

        // You're not really using fabric_sku on product itself; keep it empty as in store()
        $update_data->fabric_sku = '';
        $update_data->save();

        $productId = $update_data->id;

        $productId = $update_data->id;

        /**
         * UPDATE PRODUCT VARIANTS (Granular)
         */
        $keepVariantIds = [];
        $keepItemIds = [];

        if ($request->has('size_sets') && is_array($request->size_sets)) {
            foreach ($request->size_sets as $index => $sizeSetId) {
                if (!$sizeSetId)
                    continue;

                $variantId = $request->variant_ids[$index] ?? null;
                $mrp = $request->mrps[$index] ?? 0;

                // Handle Size Set Image
                $sizeSetImage = null;
                $allSetImages = $request->file('size_set_images');
                if (is_array($allSetImages) && isset($allSetImages[$index])) {
                    $image = $allSetImages[$index];
                    $ext = $image->getClientOriginalExtension();
                    $sizeSetImageName = "set-" . rand() . "_" . time() . "." . $ext;
                    $image->move(public_path('assets/products'), $sizeSetImageName);
                    $sizeSetImage = $sizeSetImageName;
                }
                $variantData = [
                    'production_goods_id' => $productId,
                    'master_size_measurement_id' => $sizeSetId,
                    'mrp' => $mrp
                ];
                if ($sizeSetImage) {
                    $variantData['image'] = $sizeSetImage;
                }

                if ($variantId) {
                    $variant = \App\Models\ProductionGoodVariant::find($variantId);
                    if ($variant) {
                        $variant->update($variantData);
                    } else {
                        $variant = \App\Models\ProductionGoodVariant::create($variantData);
                    }
                } else {
                    $variant = \App\Models\ProductionGoodVariant::create($variantData);
                }
                $keepVariantIds[] = $variant->id;

                // Nested Colors & Images
                if (isset($request->variant_colors[$index]) && is_array($request->variant_colors[$index])) {
                    foreach ($request->variant_colors[$index] as $cIdx => $colorId) {
                        if (!$colorId)
                            continue;

                        $itemId = $request->variant_item_ids[$index][$cIdx] ?? null;

                        $imagePath = null;
                        if ($request->hasFile("variant_images.$index.$cIdx")) {
                            $image = $request->file("variant_images.$index.$cIdx");
                            $ext = $image->getClientOriginalExtension();
                            $imageName = "variant-" . rand() . "_" . time() . "." . $ext;
                            $image->move(public_path('assets/products'), $imageName);
                            $imagePath = $imageName;
                        }

                        $barcode = 'D' . $productId . 'S' . $sizeSetId . 'C' . $colorId;
                        $itemData = [
                            'variant_id' => $variant->id,
                            'master_color_id' => $colorId,
                            'barcode' => $barcode,
                        ];
                        if ($imagePath) {
                            $itemData['image'] = $imagePath;
                        }

                        if ($itemId) {
                            $item = \App\Models\ProductionGoodVariantItem::find($itemId);
                            if ($item) {
                                $item->update($itemData);
                            } else {
                                $item = \App\Models\ProductionGoodVariantItem::create($itemData);
                            }
                        } else {
                            $item = \App\Models\ProductionGoodVariantItem::create($itemData);
                        }
                        $keepItemIds[] = $item->id;
                    }
                }
            }
        }

        // Delete variants/items that are no longer present, BUT ONLY if they are not in inventory
        // (Or if they are in inventory but we are keeping at least one other item with the same barcode)
        $itemsToDelete = \App\Models\ProductionGoodVariantItem::whereIn('variant_id', $keepVariantIds)
            ->whereNotIn('id', $keepItemIds)->get();

        foreach ($itemsToDelete as $it) {
            if (!\App\Models\DomesticInventory::where('barcode', $it->barcode)->exists()) {
                $it->delete();
            } else {
                // If it has inventory, we can still delete it if there is at least one other item with the same barcode being kept
                $isBarcodeKept = \App\Models\ProductionGoodVariantItem::whereIn('id', $keepItemIds)
                    ->where('barcode', $it->barcode)
                    ->exists();
                if ($isBarcodeKept) {
                    $it->delete();
                }
            }
        }

        $variantsToDelete = \App\Models\ProductionGoodVariant::where('production_goods_id', $productId)
            ->whereNotIn('id', $keepVariantIds)->get();

        foreach ($variantsToDelete as $vt) {
            // Check if any item of this variant is in inventory
            reset($vt->items);
            $hasInInv = false;
            foreach ($vt->items as $vItem) {
                if (\App\Models\DomesticInventory::where('barcode', $vItem->barcode)->exists()) {
                    $hasInInv = true;
                    break;
                }
            }
            if (!$hasInInv) {
                $vt->delete();
            }
        }

        if ($request->file('main_image')) {
            $image = $request->file('main_image');

            // New file name
            $extImage = $image->getClientOriginalExtension();
            $imgName = "product-" . rand() . "_" . time() . "." . $extImage;

            // Move to /public/assets/products
            $destinationPath = public_path() . '/assets/products';
            $image->move($destinationPath, $imgName);

            // Find existing main image row (if any)
            $oldMain = ProductionGoodImage::where('product_id', $productId)
                ->where('is_main', 1)
                ->first();

            if ($oldMain) {
                // OPTIONAL: delete old file from disk
                // We need the raw value from DB, not the accessor URL
                $oldFilename = $oldMain->getRawOriginal('image');
                if ($oldFilename) {
                    $oldPath = public_path('assets/products/' . $oldFilename);
                    if (file_exists($oldPath)) {
                        @unlink($oldPath);
                    }
                }

                // Update existing row
                $oldMain->image = $imgName;
                $oldMain->status = 1;
                $oldMain->save();
            } else {
                // No main image yet → create new row
                $save_main_image = new ProductionGoodImage;
                $save_main_image->product_id = $productId;
                $save_main_image->is_main = 1;
                $save_main_image->image = $imgName;
                $save_main_image->status = 1;
                $save_main_image->save();
            }
        }

        if ($request->hasFile('other_images')) {
            foreach ($request->file('other_images') as $key => $imageFile) {
                if (!$imageFile) {
                    continue;
                }

                $extImage = $imageFile->getClientOriginalExtension();
                $imgName = "product-" . rand() . "_" . time() . "_$key." . $extImage;

                $destinationPath = public_path() . '/assets/products';
                $imageFile->move($destinationPath, $imgName);

                $save_other_image = new ProductionGoodImage;
                $save_other_image->product_id = $productId;
                $save_other_image->is_main = 0; // NOT main
                $save_other_image->image = $imgName;
                $save_other_image->status = 1;
                $save_other_image->save();
            }
        }

        if ($request->has('delete_image_ids') && is_array($request->delete_image_ids)) {
            $idsToDelete = $request->delete_image_ids;

            $images = ProductionGoodImage::where('product_id', $productId)
                ->whereIn('id', $idsToDelete)
                ->get();

            foreach ($images as $img) {
                // Get raw filename from DB (not the accessor URL)
                $filename = $img->getRawOriginal('image');
                if ($filename) {
                    $path = public_path('assets/products/' . $filename);
                    if (file_exists($path)) {
                        @unlink($path);
                    }
                }

                // Hard delete row (or set status=0 if you prefer soft delete)
                $img->delete();
                // or: $img->status = 0; $img->save();
            }
        }


        if ($request->has('product_stage_id') && is_array($request->product_stage_id)) {

            $newStages = $request->product_stage_id;

            // Delete all old stage rows for this product and rebuild cleanly
            ProductStage::where('master_product_id', $productId)->delete();

            foreach ($newStages as $stageId) {
                if (!$stageId) {
                    continue;
                }

                // Base stage
                ProductStage::create([
                    'master_product_id' => $productId,
                    'master_stage_id' => $stageId,
                    'status' => 1,
                ]);

                // Insert Printing stage (assuming master_stage_id = 1) after selected
                if ($printing_stage_after && $printing_stage_after == $stageId) {
                    ProductStage::create([
                        'master_product_id' => $productId,
                        'master_stage_id' => 1, // printing master ID
                        'status' => 1,
                    ]);
                }

                // Insert Embroidery stage (assuming master_stage_id = 2) after selected
                if ($embroidery_stage_after && $embroidery_stage_after == $stageId) {
                    ProductStage::create([
                        'master_product_id' => $productId,
                        'master_stage_id' => 2, // embroidery master ID
                        'status' => 1,
                    ]);
                }
            }
        }
        // else: For Royal with hidden stages, keep existing ProductStage rows as-is.

        /**
         * === FABRIC / BILL OF MATERIAL (OPTIONAL) ===
         * If you want edit page changes to save BOM as well:
         */

        if (!empty($request->fabric_sku) && is_array($request->fabric_sku)) {

            // Get existing BOM rows keyed by ID
            $existing = BillOfMaterial::where('product_id', $productId)
                ->get()
                ->keyBy('id'); // [id => BillOfMaterial]

            foreach ($request->fabric_sku as $index => $fabricSku) {
                $fabricSku = $fabricSku ?? '';

                // Skip completely empty row
                if ($fabricSku === '') {
                    continue;
                }

                $meter = $request->fabric_meter[$index] ?? null;
                if ($meter === null || $meter === '') {
                    continue;
                }

                $bomId = $request->bom_id[$index] ?? null;

                if ($bomId && isset($existing[$bomId])) {
                    // ✅ UPDATE existing row
                    $bom = $existing[$bomId];

                    $bom->fabric_sku = $fabricSku;
                    $bom->meter = $meter;
                    $bom->status = 1;
                    $bom->save();

                    // Remove from $existing so we know it's been handled
                    unset($existing[$bomId]);

                } else {
                    // ✅ CREATE new row
                    BillOfMaterial::create([
                        'product_id' => $productId,
                        'fabric_sku' => $fabricSku,
                        'meter' => $meter,
                        'status' => 1,
                    ]);
                }
            }

            // ✅ Any BOM still in $existing were removed in the form → delete or deactivate
            foreach ($existing as $leftover) {
                // Hard delete:
                // $leftover->delete();

                // OR soft delete / deactivate:
                $leftover->status = 0;
                $leftover->save();
            }

        } else {
            // No fabric rows submitted at all → consider clearing BOM
            // BillOfMaterial::where('product_id', $productId)->delete();
            // or:
            // BillOfMaterial::where('product_id', $productId)->update(['status' => 0]);
        }

        // You can redirect or return JSON; returning true works if it’s used via AJAX or internal.
        return true;
    }

    public function delete(Request $request)
    {
        $isInInventory = \App\Models\DomesticInventory::where('product_id', $request->id)->exists();
        if ($isInInventory) {
            return "Cannot delete product as it has records in domestic inventory.";
        }

        $data = ProductionGoods::where('id', $request->id)->update([
            'status' => 3,
        ]);
        return true;
    }

    public function colors()
    {
        $data = MasterColor::where('status', 1)->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function sizes()
    {
        $data = MasterSizeMeasurement::whereIn('status', [1, 2])->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function designs()
    {
        $data = MasterDesign::where('status', 1)->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function materials()
    {
        $data = MasterMaterial::where('status', 1)->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function fabrics()
    {
        $data = Fabric::where('status', 1)->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function items()
    {
        $data = ItemAttributeValue::where('status', 1)->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function product_types()
    {
        $data = MasterProductType::where('status', 1)->orderBy('sku', 'asc')->get();
        return $data;
    }
    public function garment_patterns()
    {
        $data = MasterDesignPattern::where('status', 1)->orderBy('name', 'asc')->get();
        return $data;
    }
    public function fittings()
    {
        $data = \App\Models\MasterProductFitting::where('status', 1)->orderBy('name', 'asc')->get();
        return $data;
    }
    public function product_stages()
    {
        $data = MasterProductStage::where('status', 1)->get();
        return $data;
    }
    public function series()
    {
        $data = MasterSeries::where('status', 1)->orderBy('name', 'asc')->get();
        return $data;
    }
    public function brands()
    {
        $data = Brand::where('status', 'active')->orderBy('name', 'asc')->get();
        return $data;
    }
    public function productNatures()
    {
        $data = ProductNature::where('status', 1)->orderBy('name', 'asc')->get();
        return $data;
    }
    public function fabricTypes()
    {
        $data = FabricType::where('status', 1)->orderBy('name', 'asc')->get();
        return $data;
    }
    public function getNextProductName($master_series_id)
    {
        if (empty($master_series_id))
            return '';
        // Find the maximum numeric product name for the selected series
        $lastProduct = ProductionGoods::where('master_series_id', $master_series_id)
            ->whereRaw('name_of_garment REGEXP "^[0-9]+$"')
            ->orderByRaw('CAST(name_of_garment AS UNSIGNED) DESC')
            ->first();

        if ($lastProduct) {
            return (int) $lastProduct->name_of_garment + 1;
        }

        // If no purely numeric names found, auto increment based on count
        $count = ProductionGoods::where('master_series_id', $master_series_id)->count();
        return $count + 1;
    }

}