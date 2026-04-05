<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\FareExpense;
use App\Http\DataTable\Admin\Payment\Master\FareExpenseDataTable as DataTable;

class FareExpenseService
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
        $fareExpense = new FareExpense();
        $fareExpense->name = $request->name;
        $fareExpense->status = 1;
        $fareExpense->save();
        return true;
    }

    public function edit(Request $request)
    {
        return FareExpense::find($request->id);
    }

    public function update(Request $request)
    {
        $fareExpense = FareExpense::find($request->id);
        $fareExpense->name = $request->name;
        $fareExpense->save();
        return true;
    }

    public function delete(Request $request)
    {
        return FareExpense::where('id', $request->id)->update(['status' => 0]);
    }
}
