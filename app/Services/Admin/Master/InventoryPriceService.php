<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use App\Models\InventoryPrice;
use App\Models\InventoryPriceImage;
use App\Models\DomesticInventory;
use App\Http\DataTable\Admin\Master\InventoryPriceDataTable as DataTable;

class InventoryPriceService
{
    protected $datatable;

    public function __construct(DataTable $datatable)
    {
        $this->datatable = $datatable;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {
        $mrp = $request->mrp;
        $sellingPrice = $request->selling_price;
        $status = $request->status ?? 1;
        $size_set_id = $request->size_set_id;
        $name = $request->name ?? '';

        $uploadedImages = [];
        if ($request->hasFile('images')) {
            $uploadPath = public_path('uploads/inventory_prices');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            foreach ($request->file('images') as $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $imageName);
                $uploadedImages[] = $imageName;
            }
        }

        foreach ($request->color_ids as $color_id) {
            $price = InventoryPrice::updateOrCreate(
                [
                    'design_id' => $request->design_id,
                    'color_id' => $color_id,
                    'size_set_id' => $size_set_id,
                ],
                [
                    'name' => $name,
                    'mrp' => $mrp,
                    'selling_price' => $sellingPrice,
                    'price' => $sellingPrice,
                    'status' => $status,
                ]
            );

            // Associate uploaded images
            foreach ($uploadedImages as $index => $imageName) {
                InventoryPriceImage::create([
                    'inventory_price_id' => $price->id,
                    'image_path' => $imageName,
                    'is_main' => ($index == 0 && !$price->image) ? 1 : 0
                ]);

                if ($index == 0 && !$price->image) {
                    $price->update(['image' => $imageName]);
                }
            }

            // Sync to inventory
            $this->updateInventoryPrices($request->design_id, $color_id, $size_set_id, $mrp, $sellingPrice, $name);
        }
        return true;
    }

    public function edit(Request $request)
    {
        return InventoryPrice::find($request->id);
    }

    public function update(Request $request)
    {
        $name = $request->name ?? '';
        $price = InventoryPrice::find($request->id);
        $mrp = $request->mrp;
        $sellingPrice = $request->selling_price;
        $status = $request->status ?? 1;
        $price->name = $request->name ?? '';
        $price->mrp = $mrp;
        $price->selling_price = $sellingPrice;
        $price->price = $sellingPrice;
        $price->status = $status;
        $price->save();

        if ($request->hasFile('images')) {
            $uploadPath = public_path('uploads/inventory_prices');
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            foreach ($request->file('images') as $index => $image) {
                $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
                $image->move($uploadPath, $imageName);

                InventoryPriceImage::create([
                    'inventory_price_id' => $price->id,
                    'image_path' => $imageName,
                    'is_main' => 0
                ]);

                // If no main image exists, set this as main
                if (!$price->image) {
                    $price->update(['image' => $imageName]);
                }
            }
        }

        // Update inventory prices for existing stock
        $this->updateInventoryPrices($price->design_id, $price->color_id, $price->size_set_id, $mrp, $sellingPrice, $name);

        return true;
    }

    public function delete(Request $request)
    {
        return InventoryPrice::where('id', $request->id)->update(['status' => 0]);
    }

    public function getPendingPricingItems()
    {
        // Get unique combinations in Domestic Inventory (Grouped by Size Set)
        $inventoryGroups = DomesticInventory::select('product_id', 'color_id', 'size_set_id', 'design_number', 'product_name', 'color_name', 'size_set_name')
            ->groupBy('product_id', 'color_id', 'size_set_id', 'design_number', 'product_name', 'color_name', 'size_set_name')
            ->get();

        $pending = [];

        foreach ($inventoryGroups as $item) {
            // Check if this combination exists in master pricing
            $exists = InventoryPrice::where('design_id', $item->product_id)
                ->where('color_id', $item->color_id)
                ->where('size_set_id', $item->size_set_id)
                ->exists();

            if (!$exists) {
                $pending[] = $item;
            }
        }

        return $pending;
    }

    private function updateInventoryPrices($design_id, $color_id, $size_set_id, $mrp, $selling_price, $name)
    {
        DomesticInventory::where('product_id', $design_id)
            ->where('color_id', $color_id)
            ->where('size_set_id', $size_set_id)
            ->update([
                'mrp' => $mrp,
                'selling_price' => $selling_price,
                'price' => $selling_price,
                'product_name' => $name,
            ]);
    }
}
