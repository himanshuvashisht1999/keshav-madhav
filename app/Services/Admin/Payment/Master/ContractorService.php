<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Contractor;
use App\Http\DataTable\Admin\Payment\Master\ContractorDataTable as DataTable;

class ContractorService
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
        $contractor = new Contractor();
        $contractor->name = $request->name;
        $contractor->phone = $request->phone;
        $contractor->address = $request->address;
        $contractor->status = 1;
        $contractor->save();
        return true;
    }

    public function edit(Request $request)
    {
        return Contractor::find($request->id);
    }

    public function update(Request $request)
    {
        $contractor = Contractor::find($request->id);
        $contractor->name = $request->name;
        $contractor->phone = $request->phone;
        $contractor->address = $request->address;
        $contractor->save();
        return true;
    }

    public function delete(Request $request)
    {
        return Contractor::where('id', $request->id)->update(['status' => 0]);
    }
}
