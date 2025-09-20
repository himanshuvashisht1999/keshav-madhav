<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\PurchaseOrder;
use App\Models\Fabric;
use App\Models\Vendor;
use App\Models\PurchaseOrderItem;
use App\Models\GeneralSettings;
use App\Mail\PurchaseOrderMail;
use Illuminate\Support\Facades\Mail;
use App\Http\DataTable\Admin\PurchaseOrderDataTable as DataTable;

class PurchaseOrderService {
    public function __construct(
        DataTable $datatable,
        PurchaseOrder $purchase_order
    ) {
        $this->datatable= $datatable;
        $this->purchase_order = $purchase_order;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
       
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new PurchaseOrder;
        $save_data->sku = $request->sku;
        $save_data->date = $request->date;
        $save_data->vendor_id = $request->vendor_id;
        $save_data->delivery_date = $request->delivery_date;
        $save_data->status = 1;
        $save_data->save();

        foreach($request->fabrics as $single_data){
            $fab_data = Fabric::where('id',$single_data['fabric_id'])->first();
            if($fab_data){
                $save_po_item = new PurchaseOrderItem;
                $save_po_item->purchase_order_id = $save_data->id;
                $save_po_item->fabric_sku = $fab_data->sku;
                $save_po_item->sku = $single_data['sku'];
                $save_po_item->fabric_id = $fab_data->id;
                $save_po_item->meter = $single_data['meter'];
                $save_po_item->price = $single_data['price'];
                $save_po_item->total_price = $single_data['meter'] * $single_data['price'];
                $save_po_item->save();

            }
            
        }
        // Reload purchase order with relationships for email
        $data = PurchaseOrder::with('items','vendor')->find($save_data->id);

        // ✅ Send email with same $data you use in view
        if ($data->vendor && $data->vendor->email) {
            Mail::to($data->vendor->email)->send(new PurchaseOrderMail($data));
        }

        
        return true;
    }
    public function view(Request $request){
        $data = PurchaseOrder::with('items','vendor')->where('id',$request->id)->first();
        return $data;
    }

    public function edit(Request $request){
        $data = PurchaseOrder::where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = PurchaseOrder::find($request->id);
        $update_data->sku = $request->sku;
        $update_data->date = $request->date;
        $update_data->vendor_id = $request->vendor_id;
        $update_data->delivery_date = $request->delivery_date;
        $update_data->status = 1;
        $update_data->save();

        return true;
    }

    public function delete(Request $request){
        $data = PurchaseOrder::where('id',$request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }
    public function vendors(){
        $data = Vendor::where('status',1)->get();
        return $data;
    }
    public function fabrics(){
        $data = Fabric::where('status',1)->get();
        return $data;
    }
    public function general_setting(){
        $data = GeneralSettings::where('status',1)->first();
        return $data;
    }
    

}