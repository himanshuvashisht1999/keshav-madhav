<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Interest;
use App\Http\DataTable\Admin\Payment\Master\InterestDataTable as DataTable;

class InterestService
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
        $interest = new Interest();
        $interest->name = $request->name;
        $interest->percentage = $request->percentage;
        $interest->status = 1;
        $interest->save();
        return true;
    }

    public function edit(Request $request)
    {
        return Interest::find($request->id);
    }

    public function update(Request $request)
    {
        $interest = Interest::find($request->id);
        $interest->name = $request->name;
        $interest->percentage = $request->percentage;
        $interest->save();
        return true;
    }

    public function delete(Request $request)
    {
        return Interest::where('id', $request->id)->update(['status' => 0]);
    }
}
