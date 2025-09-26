<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;


use App\Models\Stock;
use App\Http\DataTable\Admin\StockDataTable as DataTable;

class StockService
{
    public function __construct(
        DataTable $datatable,
        Stock $stock
    ) {
        $this->datatable = $datatable;
        $this->stock = $stock;
    }

    public function index(Request $request)
    {
        return true;
    }
    public function indexList(Request $request)
    {

        return $this->datatable->indexList($request);
    }

    public function view(Request $request)
    {
        $data = Stock::with('expends')->where('id', $request->id)->first();
        return $data;
    }
}
