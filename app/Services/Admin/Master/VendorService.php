<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\PurchaseAgent;
use App\Models\MasterOpeningBalance;
use App\Http\DataTable\Admin\Master\VendorDataTable as DataTable;

class VendorService {
    protected $datatable;
    protected $vendor;
    public function __construct(
        DataTable $datatable,
        Vendor $vendor
    ) {
        $this->datatable= $datatable;
        $this->vendor= $vendor;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function store(Request $request){
        $save_data = new Vendor;
        $save_data->name = $request->name;
        $save_data->items = serialize($request->items);
        $save_data->phone = $request->phone;
        $save_data->email = $request->email;
        $save_data->address = $request->address;
        $save_data->purchase_agent_id = $request->purchase_agent_id;
        
        $balance = $request->balance ?? 0;
        if ($request->type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $save_data->balance = $balance;
        
        $save_data->sku = NULL;
        $save_data->status = $request->status;
        $save_data->description = $request->description;
        $save_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'vendor',
                'master_id' => $save_data->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->type ?? 'Credit',
            ]
        );

        return true;
    }

    public function edit(Request $request){
        $data = $this->vendor->with('currentOpeningBalance')->where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = $this->vendor->find($request->id);
        
        $update_data->name = $request->name;
        $update_data->items = serialize($request->items);
        $update_data->phone = $request->phone;
        $update_data->email = $request->email;
        $update_data->address = $request->address;
        $update_data->purchase_agent_id = $request->purchase_agent_id;
        
        $balance = $request->balance ?? 0;
        if ($request->type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $update_data->balance = $balance;
        
        $update_data->sku = NULL;
        $update_data->status = $request->status;
        $update_data->description = $request->description;
        $update_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'vendor',
                'master_id' => $update_data->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->type ?? 'Credit',
            ]
        );

        return true;
    }

    public function delete(Request $request){
        $data = $this->vendor->where('id',$request->id)->update([
            'status' => 3,
        ]);
        return $data;
    }

    public function items(){
        $data = Item::where('status',1)->get();
        return $data;
    }

    public function purchaseAgents(){
        $data = PurchaseAgent::where('status',1)->get();
        return $data;
    }

}