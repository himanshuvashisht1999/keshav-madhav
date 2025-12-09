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

class ProductionGoodsService {
    public function __construct(
        DataTable $datatable,
        ProductionGoods $production_goods
    ) {
        $this->datatable= $datatable;
        $this->production_goods= $production_goods;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $printing_stage_after   = $request->printing_stage_after ?? 0;
        $embroidery_stage_after = $request->embroidery_stage_after ?? 0;

        $save_data = new ProductionGoods;

        $save_data->company_id       = $request->company_id;
        $save_data->sku              = $request->sku;
        $save_data->name_of_garment  = $request->name_of_garment;
        $save_data->design_number    = $request->design_number;

        // For General company these fields are required, for Royal they might be null
        $save_data->type_of_garment  = $request->type_of_garment;    // null for Royal is okay if column is nullable
        $save_data->master_size_id   = $request->master_size_id;
        $save_data->garment_pattern  = $request->garment_pattern;
        $save_data->master_color_id  = $request->master_color_id;

        $save_data->is_printing          = $request->is_printing ?? 1;
        $save_data->is_embroidery        = $request->is_embroidery ?? 1;
        $save_data->printing_stage_after = $printing_stage_after;
        $save_data->embroidery_stage_after = $embroidery_stage_after;

        // If you don't really use this, you can drop this column later
        $save_data->fabric_sku = '';

        $save_data->status = 1;
        $save_data->save();

        /////////  save images
        $imgName = NULL;
        if($request->file('main_image')){
            $image = $request->file('main_image');
            $extImage = $image->getClientOriginalExtension();
            $imgName = "product-".rand()."_".time().".".$extImage;
            $destinationPath = public_path().'/assets/products';
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
                $imgName  = "product-" . rand() . "_" . time() . "_$key." . $extImage;
                // Move to folder
                $destinationPath = public_path() . '/assets/products';
                $imageFile->move($destinationPath, $imgName);

                // Save record in DB
                $save_other_image = new ProductionGoodImage;
                $save_other_image->product_id = $save_data->id;
                $save_other_image->is_main = 0;        // <-- Not main image
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
                $save_stage->master_stage_id   = $single;
                $save_stage->save();

                // Insert Printing stage (id = 1) after selected stage
                if ($printing_stage_after && $printing_stage_after == $single) {
                    $printingStage = new ProductStage;
                    $printingStage->master_product_id = $save_data->id;
                    $printingStage->master_stage_id   = 1; // printing master id
                    $printingStage->save();
                }

                // Insert Embroidery stage (id = 2) after selected stage
                if ($embroidery_stage_after && $embroidery_stage_after == $single) {
                    $embroideryStage = new ProductStage;
                    $embroideryStage->master_product_id = $save_data->id;
                    $embroideryStage->master_stage_id   = 2; // embroidery master id
                    $embroideryStage->save();
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
                $save_data_product->product_id = $save_data->id;   // <-- FIXED
                $save_data_product->fabric_sku = $fabricSku;       // <-- FIXED
                $save_data_product->meter      = $meter;           // <-- FIXED
                $save_data_product->status     = 1;
                $save_data_product->save();
            }
        }

        // You can return redirect/JSON as per your flow
        // return redirect()->route('admin.master.production-goods.index')
        //                  ->with('success', 'Production Goods created successfully.');

        return true;
    }

    public function edit(Request $request){
        $data = ProductionGoods::with('bill_of_materials','product_stages')->where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $printing_stage_after   = $request->printing_stage_after ?? null;
        $embroidery_stage_after = $request->embroidery_stage_after ?? null;

        $update_data = ProductionGoods::findOrFail($request->id);

        // Basic fields (common)
        $update_data->company_id      = $request->company_id;     // from edit form
        $update_data->design_number   = $request->design_number ?? $update_data->design_number;
        $update_data->name_of_garment = $request->name_of_garment;
        // SKU we keep from DB or from request (readonly in form, but safe to assign)
        $update_data->sku             = $request->sku ?? $update_data->sku;

        // For General, these are required; for Royal they may be null (columns should be nullable)
        $update_data->type_of_garment = $request->type_of_garment;
        $update_data->master_size_id  = $request->master_size_id;
        $update_data->garment_pattern = $request->garment_pattern;
        $update_data->master_color_id = $request->master_color_id;

        $update_data->is_printing          = $request->is_printing ?? 0;
        $update_data->is_embroidery        = $request->is_embroidery ?? 0;
        $update_data->printing_stage_after = $printing_stage_after;
        $update_data->embroidery_stage_after = $embroidery_stage_after;

        // You’re not really using fabric_sku on product itself; keep it empty as in store()
        $update_data->fabric_sku = '';
        $update_data->save();

        $productId = $update_data->id;

        if ($request->file('main_image')) {
            $image = $request->file('main_image');

            // New file name
            $extImage = $image->getClientOriginalExtension();
            $imgName  = "product-" . rand() . "_" . time() . "." . $extImage;

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
                $oldMain->image  = $imgName;
                $oldMain->status = 1;
                $oldMain->save();
            } else {
                // No main image yet → create new row
                $save_main_image = new ProductionGoodImage;
                $save_main_image->product_id = $productId;
                $save_main_image->is_main    = 1;
                $save_main_image->image      = $imgName;
                $save_main_image->status     = 1;
                $save_main_image->save();
            }
        }

        if ($request->hasFile('other_images')) {
            foreach ($request->file('other_images') as $key => $imageFile) {
                if (!$imageFile) {
                    continue;
                }

                $extImage = $imageFile->getClientOriginalExtension();
                $imgName  = "product-" . rand() . "_" . time() . "_$key." . $extImage;

                $destinationPath = public_path() . '/assets/products';
                $imageFile->move($destinationPath, $imgName);

                $save_other_image = new ProductionGoodImage;
                $save_other_image->product_id = $productId;
                $save_other_image->is_main    = 0;      // NOT main
                $save_other_image->image      = $imgName;
                $save_other_image->status     = 1;
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
                    'master_stage_id'   => $stageId,
                    'status'            => 1,
                ]);

                // Insert Printing stage (assuming master_stage_id = 1) after selected
                if ($printing_stage_after && $printing_stage_after == $stageId) {
                    ProductStage::create([
                        'master_product_id' => $productId,
                        'master_stage_id'   => 1, // printing master ID
                        'status'            => 1,
                    ]);
                }

                // Insert Embroidery stage (assuming master_stage_id = 2) after selected
                if ($embroidery_stage_after && $embroidery_stage_after == $stageId) {
                    ProductStage::create([
                        'master_product_id' => $productId,
                        'master_stage_id'   => 2, // embroidery master ID
                        'status'            => 1,
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
                        ->keyBy('id');  // [id => BillOfMaterial]

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
                    $bom->meter      = $meter;
                    $bom->status     = 1;
                    $bom->save();

                    // Remove from $existing so we know it's been handled
                    unset($existing[$bomId]);

                } else {
                    // ✅ CREATE new row
                    BillOfMaterial::create([
                        'product_id' => $productId,
                        'fabric_sku' => $fabricSku,
                        'meter'      => $meter,
                        'status'     => 1,
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

    public function delete(Request $request){
        $data = ProductionGoods::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

    public function colors(){
        $data = MasterColor::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function sizes(){
        $data = MasterSizeMeasurement::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function designs(){
        $data = MasterDesign::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function materials(){
        $data = MasterMaterial::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function fabrics(){
        $data = Fabric::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function items(){
        $data = ItemAttributeValue::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function product_types(){
        $data = MasterProductType::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function garment_patterns(){
        $data = MasterPattern::where('status',1)->orderBy('sku','asc')->get();
        return $data;
    }
    public function product_stages(){
        $data = MasterProductStage::where('status',1)->get();
        return $data;
    }

}