<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\ConsumableGood;
use App\Http\DataTable\Admin\Payment\Master\ConsumableGoodDataTable as DataTable;

class ConsumableGoodService
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
        $consumableGood = new ConsumableGood();
        $consumableGood->name = $request->name;
        $consumableGood->status = 1;
        $consumableGood->save();
        return true;
    }

    public function edit(Request $request)
    {
        return ConsumableGood::find($request->id);
    }

    public function update(Request $request)
    {
        $consumableGood = ConsumableGood::find($request->id);
        $consumableGood->name = $request->name;
        $consumableGood->save();
        return true;
    }

    public function delete(Request $request)
    {
        return ConsumableGood::where('id', $request->id)->update(['status' => 0]);
    }
}
