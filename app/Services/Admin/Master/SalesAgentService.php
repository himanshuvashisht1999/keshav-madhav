<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use App\Models\SalesAgent;
use App\Http\DataTable\Admin\Master\SalesAgentDataTable as DataTable;

class SalesAgentService
{
    protected $datatable;
    protected $salesAgent;

    public function __construct(
        DataTable $datatable,
        SalesAgent $salesAgent
    ) {
        $this->datatable = $datatable;
        $this->salesAgent = $salesAgent;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function store(Request $request)
    {
        $save_data = new SalesAgent;
        $save_data->name = $request->name;
        $save_data->email = $request->email;
        $save_data->phone = $request->phone;
        $save_data->password = $request->password;
        $save_data->address = $request->address;
        $save_data->status = 1;
        $save_data->save();
        return true;
    }

    public function edit(Request $request)
    {
        $data = SalesAgent::where('id', $request->id)->first();
        return $data;
    }

    public function update(Request $request)
    {
        $update_data = SalesAgent::find($request->id);
        $update_data->name = $request->name;
        $update_data->email = $request->email;
        $update_data->phone = $request->phone;
        if (!empty($request->password)) {
            $update_data->password = $request->password;
        }
        $update_data->address = $request->address;
        $update_data->status = $request->status ?? 1;
        $update_data->save();
        return true;
    }

    public function delete(Request $request)
    {
        $data = SalesAgent::where('id', $request->id)->update([
            'status' => 0,
        ]);
        return $data;
    }

}
