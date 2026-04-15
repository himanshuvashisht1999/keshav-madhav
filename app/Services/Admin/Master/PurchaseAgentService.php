<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use App\Models\PurchaseAgent;
use App\Http\DataTable\Admin\Master\PurchaseAgentDataTable as DataTable;

class PurchaseAgentService
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
        $save_data = new PurchaseAgent;
        $save_data->name = $request->name;
        $save_data->email = $request->email;
        $save_data->phone = $request->phone;
        $save_data->address = $request->address;
        $save_data->status = 1;
        $save_data->save();

        return true;
    }

    public function edit(Request $request)
    {
        return PurchaseAgent::find($request->id);
    }

    public function update(Request $request)
    {
        $update_data = PurchaseAgent::find($request->id);
        if ($update_data) {
            $update_data->name = $request->name;
            $update_data->email = $request->email;
            $update_data->phone = $request->phone;
            $update_data->address = $request->address;
            $update_data->status = $request->status ?? 1;
            $update_data->save();
            return true;
        }
        return false;
    }

    public function delete(Request $request)
    {
        $data = PurchaseAgent::find($request->id);
        if ($data) {
            if ($data->vendors()->count() > 0) {
                return ['status' => false, 'message' => 'Cannot delete purchase agent as it is linked to vendors.'];
            }
            $data->delete();
            return ['status' => true, 'message' => 'Purchase agent deleted successfully.'];
        }
        return ['status' => false, 'message' => 'Purchase agent not found.'];
    }
}
