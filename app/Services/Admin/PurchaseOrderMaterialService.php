<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\PurchaseOrderMaterial;
use App\Models\PurchaseOrderMaterialItem;
use App\Models\ItemAttributeValue;
use App\Models\Vendor;
use App\Models\GeneralSettings;
use App\Http\DataTable\Admin\PurchaseOrderMaterialDataTable as DataTable;

class PurchaseOrderMaterialService{

public function __construct(DataTable $datatable, PurchaseOrderMaterial $purchase_order_material){
      $this->datatable=$datatable;
      $this->purchase_order_material=$purchase_order_material;
}
public function index(Request $request){
return true;
}
public function indexList(Request $request){
  return $this->datatable->indexList($request);
}

public function store(Request $request){
  $save_data=new PurchaseOrderMaterial;
  $save_data->sku ='';
  $save_data->company_id = Auth::user()->company_id ?? 1;
  $save_data->sub_company_id = Auth::user()->sub_company_id ?? 1;
  $save_data->project_id = Auth::user()->project_id ?? 1;
  $save_data->date =$request->date;
  $save_data->vendor_id =$request->vendor_id;
  $save_data->delivery_date =$request->delivery_date;
  $save_data->is_notify =$request->is_notify;
  $save_data->status =1;
  $save_data->save();

  if($request->has('items') && is_array($request->items)){
    foreach($request->items as $single_data){
      $item_attr_value = ItemAttributeValue::where('sku', $single_data['item_sku'])->first();
      if($item_attr_value){
        $save_po_item = new PurchaseOrderMaterialItem;
        $save_po_item->company_id = Auth::user()->company_id ?? 1;
        $save_po_item->sub_company_id = Auth::user()->sub_company_id ?? 1;
        $save_po_item->project_id = Auth::user()->project_id ?? 1;
        $save_po_item->purchase_order_material_id = $save_data->id;
        $save_po_item->item_attribute_value_sku = $item_attr_value->sku;
        $save_po_item->quantity = $single_data['quantity'];
        $save_po_item->price = $single_data['price']; 
        $save_po_item->total_price = $single_data['quantity'] * $single_data['price'];
        $save_po_item->status = 1;
        $save_po_item->save(); 
      } 
    }
  }
  
  $sku_update = PurchaseOrderMaterial::where('id', $save_data->id)->update([
    'sku' => 'PO-'.$save_data->id, 
  ]);
  return true;
}

public function view(Request $request){
  $data = PurchaseOrderMaterial::with('items', 'vendor')->where('id', $request->id)->first();
  return $data;
}

public function edit(Request $request){
  $data = PurchaseOrderMaterial::where('id', $request->id)->first();
  return $data;
}

public function update(Request $request){
  $update_data = PurchaseOrderMaterial::find($request->id);
  $update_data->date = $request->date;
  $update_data->vendor_id = $request->vendor_id;
  $update_data->delivery_date = $request->delivery_date;
  $update_data->status = 1;
  $update_data->save();

  return true;
}

public function delete(Request $request){
  $data = PurchaseOrderMaterial::where('id', $request->id)->update(['status' => 0,]);
  return $data;
}
public function vendors(){
  $data = Vendor::where('status', 1)->get();
  return $data;
}

public function items(){
  $data = ItemAttributeValue::where('status', 1)->get();
  return $data;
}

public function general_setting(){
  $data = GeneralSettings::where('status', 1)->first();
  return $data;
}

}