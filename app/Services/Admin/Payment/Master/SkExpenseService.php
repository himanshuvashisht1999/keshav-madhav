<?php

namespace App\Services\Admin\Payment\Master;

use App\Models\SkExpense;
use Illuminate\Http\Request;
use App\Http\DataTable\Admin\Payment\Master\SkExpenseDataTable as DataTable;

class SkExpenseService
{
    public function index()
    {
        return [];
    }

    public function indexList(Request $request)
    {
        $dataTable = new DataTable();
        return $dataTable->indexList($request);
    }

    public function store($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = 1;
        return SkExpense::create($input);
    }

    public function edit(Request $request)
    {
        return SkExpense::find($request->id);
    }

    public function update($request)
    {
        $input = $request->validated();
        $input['name'] = $request->name;
        $input['status'] = $request->status ?? 1;
        $skExpense = SkExpense::find($request->id);
        if ($skExpense) {
            $skExpense->update($input);
            return $skExpense;
        }
        return false;
    }

    public function delete($request)
    {
        $skExpense = SkExpense::find($request->id);
        if ($skExpense) {
            $skExpense->delete();
            return true;
        }
        return false;
    }
}
