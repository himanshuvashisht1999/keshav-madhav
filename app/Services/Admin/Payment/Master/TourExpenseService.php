<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\TourExpense;
use App\Http\DataTable\Admin\Payment\Master\TourExpenseDataTable as DataTable;

class TourExpenseService
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
        $tourExpense = new TourExpense();
        $tourExpense->name = $request->name;
        $tourExpense->status = 1;
        $tourExpense->save();
        return true;
    }

    public function edit(Request $request)
    {
        return TourExpense::find($request->id);
    }

    public function update(Request $request)
    {
        $tourExpense = TourExpense::find($request->id);
        $tourExpense->name = $request->name;
        $tourExpense->save();
        return true;
    }

    public function delete(Request $request)
    {
        return TourExpense::where('id', $request->id)->update(['status' => 0]);
    }
}
