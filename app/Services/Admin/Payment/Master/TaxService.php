<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Tax;
use App\Http\DataTable\Admin\Payment\Master\TaxDataTable as DataTable;

class TaxService
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
        $tax = new Tax();
        $tax->name = $request->name;
        $tax->percentage = $request->percentage;
        $tax->status = 1;
        $tax->save();
        return true;
    }

    public function edit(Request $request)
    {
        return Tax::find($request->id);
    }

    public function update(Request $request)
    {
        $tax = Tax::find($request->id);
        $tax->name = $request->name;
        $tax->percentage = $request->percentage;
        $tax->save();
        return true;
    }

    public function delete(Request $request)
    {
        return Tax::where('id', $request->id)->update(['status' => 0]);
    }
}
