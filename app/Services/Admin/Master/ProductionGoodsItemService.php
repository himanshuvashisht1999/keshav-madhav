<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\ProductionGoodsItem;
use App\Models\ItemAttribute;
use App\Models\ProductionGoods;
use App\Models\ItemAttributeValue;
use App\Models\BillOfMaterial;
use DB;
use Auth;
use App\Http\DataTable\Admin\Master\ProductionGoodsItemsDataTable as DataTable;

class ProductionGoodsItemService {
    public function __construct(
        DataTable $datatable,
        ProductionGoodsItem $production_goods_item
    ) {
        $this->datatable= $datatable;
        $this->production_goods_item= $production_goods_item;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        DB::beginTransaction();

        try {
            // fabric logic 
            if (!empty($request->old_fabric_sku) && is_array($request->old_fabric_sku)) {
                $updated_fabric_id = [];
                foreach ($request->old_fabric_sku as $fabric_id => $val_sku) {
                    $isExist = BillOfMaterial::where('product_id', $request->product_id)
                        ->where('fabric_sku', $val_sku)
                        ->where('id', '!=' , $fabric_id)
                        ->exists();
                    if ($isExist){
                        throw new \Exception("Fabric SKU ({$val_sku}) already exists for this product.");
                        
                    } else {
                        $updated_fabric_id[] = $fabric_id;
                        $fabricData = BillOfMaterial::find($fabric_id);
                        if ($fabricData) {
                            $fabricData->update([
                                'fabric_sku' => $val_sku,
                                'meter' => $request->old_fabric_meter[$fabric_id] ?? 0,
                            ]);
                        }
                    }
                }

                $deletedFabricIds = array_diff($request->old_fabric_id, $updated_fabric_id);
                if (!empty($deletedFabricIds)) {
                    BillOfMaterial::whereIn('id', $deletedFabricIds)->delete();
                }
            }
            if (is_array($request->fabric_sku) && is_array($request->fabric_meter) && count($request->fabric_sku) == count($request->fabric_meter)){
                for($i = 0; $i < count($request->fabric_sku); $i++){
                    $isExist = BillOfMaterial::where('product_id', $request->product_id)
                        ->where('fabric_sku', $request->fabric_sku[$i])
                        ->exists();
                    if ($isExist){
                        throw new \Exception("Fabric SKU ({$request->fabric_sku[$i]}) already exists for this product.");
                        
                    } else {
                        $save_data = new BillOfMaterial;
                        $save_data->product_id = $request->product_id;
                        $save_data->fabric_sku = $request->fabric_sku[$i];
                        $save_data->meter = $request->fabric_meter[$i];
                        $save_data->status = 1;
                        $save_data->save();
                    }
                }
            }
            // if any fabric added then status change of the product 
            $BillOfMaterial = BillOfMaterial::where("product_id", $request->product_id)
                                ->where('status', 1)
                                ->exists(); 
            if ($BillOfMaterial) {
                $product = ProductionGoods::find($request->product_id);  
                if ($product) {  
                    $product->update([
                        'status' => 1,
                    ]);
                }
            }
            //  items logic 
            if (!empty($request->old_items_sku) && is_array($request->old_items_sku)) {
                $updated_items_id = [];
                foreach ($request->old_items_sku as $item_id => $val_sku) {
                    $isExist = ProductionGoodsItem::where('product_id', $request->product_id)
                        ->where('item_attribute_value_sku', $val_sku)
                        ->where('id', '!=' , $item_id)
                        ->exists();
                    if ($isExist){
                        throw new \Exception("Item SKU  ({$val_sku}) already exists for this product.");
                    } else {
                        $updated_items_id[] = $item_id;
                        $itemData = ProductionGoodsItem::find($item_id);
                        if ($itemData) {
                            $itemData->update([
                                'item_attribute_value_sku' => $val_sku,
                                'quantity' => $request->old_item_quantity[$item_id] ?? 0,
                            ]);
                        }
                    }
                }

                $deletedItemIds = array_diff($request->old_items_id, $updated_items_id);
                if (!empty($deletedItemIds)) {
                    ProductionGoodsItem::whereIn('id', $deletedItemIds)->delete();
                }
            }
            if (is_array($request->items_sku) && is_array($request->item_quantity) && count($request->items_sku) == count($request->item_quantity)){
                for($i = 0; $i < count($request->items_sku); $i++){
                    $isExist = ProductionGoodsItem::where('product_id', $request->product_id)
                        ->where('item_attribute_value_sku', $request->items_sku[$i])
                        ->exists();
                    if ($isExist){
                        throw new \Exception("Item SKU  ({$request->items_sku[$i]}) already exists for this product.");
                    } else {
                        $save_data = new ProductionGoodsItem;
                        $save_data->product_id = $request->product_id;
                        $save_data->item_attribute_value_sku = $request->items_sku[$i];
                        $save_data->quantity = $request->item_quantity[$i];
                        $save_data->status = 1;
                        $save_data->save();
                    }
                }
                
            }
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    public function getItemCatogeriesValue(){
        $data = ItemAttributeValue::where('status',1)->get();
        return $data;
    }
    public function getProductItems(Request $request){
        $data = ProductionGoodsItem::where([
                    'product_id' => $request->id,
                    'status' => 1
                ])->get();
        return $data;
    }

    public function getProductFebrics(Request $request){
        $data = BillOfMaterial::where([
                    'product_id' => $request->id,
                    'status' => 1
                ])->get();
        return $data;
    }

   

}