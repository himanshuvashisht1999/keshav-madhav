<?php

// This is a scratch file for drafting the getVariationByBarcode logic.

// Assuming we have $productId, $scannedSizeSetId (from the barcode), and maybe $fairProduct (if scanned a fair barcode).

$isFairBarcode = isset($fairProduct);

$product = \App\Models\ProductionGoods::with(['series', 'variants'])->find($productId);

if (!$product) {
    return response()->json(['success' => false, 'message' => 'Product not found.']);
}

// 1. Identify all available size_set_ids for this product in the current context.
if ($isFairBarcode && $fairProduct) {
    $fairBatchId = $fairProduct->fair_batch_id;
    // Get all fair products in the same batch for the same product
    $availableFairProducts = \App\Models\FairProduct::where('product_id', $productId)
        ->where('fair_batch_id', $fairBatchId)
        ->get();
    
    $availableSizeSetIds = $availableFairProducts->pluck('size_set_id')->unique()->toArray();
    
    $discount_percentage = $fairProduct->discount_percent;
} else {
    // Normal barcode, get all size sets available in domestic inventory for this product
    $availableSizeSetIds = \App\Models\DomesticInventory::where('product_id', $productId)
        ->where('status', 1)
        ->pluck('size_set_id')
        ->unique()
        ->toArray();

    $agent_id = Auth::guard('sales_agent')->id();
    $discount_percentage = DB::table('sales_agent_brand_discounts')
        ->where('sales_agent_id', $agent_id)
        ->where('brand_id', $product->brand_id)
        ->value('discount_percentage') ?? 0;
}

if (!in_array($scannedSizeSetId, $availableSizeSetIds)) {
    // Fallback: at least include the scanned one
    $availableSizeSetIds[] = $scannedSizeSetId;
}

$isAdvanceSample = \DB::table('domestic_inventories')
    ->join('racks', 'domestic_inventories.rack_id', '=', 'racks.id')
    ->join('storerooms', 'racks.storeroom_id', '=', 'storerooms.id')
    ->where('domestic_inventories.product_id', $productId)
    ->where('storerooms.name', 'ADVANCE SAMPLE')
    ->exists();

$sizeSetsData = [];

$main_image_base = $product->photo;

foreach ($availableSizeSetIds as $sizeSetId) {
    // Get available colors for this size set
    $availableColorsQuery = \App\Models\DomesticInventory::where('product_id', $productId)
        ->where('size_set_id', $sizeSetId)
        ->where('domestic_inventories.status', 1);

    if ($isFairBarcode && $fairProduct) {
        // Find the specific fair product for this size set to get restricted colors
        $fp = $availableFairProducts->firstWhere('size_set_id', $sizeSetId);
        if ($fp && !empty($fp->color_ids)) {
            $availableColorsQuery->whereIn('color_id', $fp->color_ids);
        }
    }

    $availableColors = $availableColorsQuery
        ->join('master_colors', 'domestic_inventories.color_id', '=', 'master_colors.id')
        ->select('master_colors.id', 'master_colors.name', DB::raw('SUM(domestic_inventories.total_boxes) as available_boxes'), DB::raw('MAX(domestic_inventories.quantity) as pcs_per_box'))
        ->groupBy('master_colors.id', 'master_colors.name')
        ->get();
        
    $allocQuery = \DB::table('agent_order_items')
        ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
        ->where('agent_orders.status', 'pending')
        ->where('agent_order_items.product_id', $productId)
        ->where('agent_order_items.size_set_id', $sizeSetId);
    
    if ($request->filled('order_id')) {
        $allocQuery->where('agent_orders.id', '!=', $request->order_id);
    }
    
    $allocations = $allocQuery->select('color_id', \DB::raw('SUM(box_qty) as total_allocated'))
        ->groupBy('color_id')
        ->pluck('total_allocated', 'color_id');
        
    $variant = $product->variants->where('master_size_measurement_id', $sizeSetId)->first();
    if (!$variant) {
        $variant = $product->variants->first();
    }
    
    $mrp = $variant->mrp ?? 0;
    $unit_price = $mrp - ($mrp * $discount_percentage / 100);
    
    $main_image = $main_image_base;
    if (!$main_image && $variant && $variant->image) {
        $main_image = $variant->image;
    }

    foreach ($availableColors as $color) {
        if ($isAdvanceSample) {
            $color->available_boxes = 99999;
        } else {
            $allocated = $allocations->get($color->id) ?? 0;
            $color->available_boxes = max(0, $color->available_boxes - $allocated);
        }

        $cImg = DB::table('production_goods_variant_colors')
            ->join('production_goods_variants', 'production_goods_variant_colors.variant_id', '=', 'production_goods_variants.id')
            ->where('production_goods_variants.production_goods_id', $productId)
            ->where('production_goods_variants.master_size_measurement_id', $sizeSetId)
            ->where('production_goods_variant_colors.master_color_id', $color->id)
            ->whereNotNull('production_goods_variant_colors.image')
            ->value('production_goods_variant_colors.image');
        
        $color->image = $cImg ? asset('assets/products/' . $cImg) : ($main_image ? asset('assets/products/' . $main_image) : null);
    }
    
    $sizeSetsData[] = [
        'size_set_id' => (int)$sizeSetId,
        'size_set_name' => DB::table('master_size_measurements')->where('id', $sizeSetId)->value('name'),
        'mrp' => $mrp,
        'unit_price' => $unit_price,
        'colors' => $availableColors
    ];
}

return response()->json([
    'success' => true,
    'is_advance_sample' => $isAdvanceSample,
    'product' => [
        'id' => $product->id,
        'name' => trim(($product->series->name ?? '') . ' ' . $product->name_of_garment),
        'design_number' => $product->design_number,
        'image' => $main_image_base ? asset('assets/products/' . $main_image_base) : null,
    ],
    'scanned_size_set_id' => (int)$scannedSizeSetId,
    'size_sets' => $sizeSetsData
]);

