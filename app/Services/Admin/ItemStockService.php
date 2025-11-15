<?php

namespace App\Services\Admin;

use Illuminate\Http\Request;


use App\Models\ItemStock;
use App\Http\DataTable\Admin\ItemStockDataTable as DataTable;

class ItemStockService
{
    public function __construct(
        DataTable $datatable,
        ItemStock $item_stock
    ) {
        $this->datatable = $datatable;
        $this->item_stock = $item_stock;
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
        $data = ItemStock::where('id', $request->id)->first();
        return $data;
    }

    public function itemIndexList(Request $request){
        return $this->datatable->itemIndexList($request);
    }
}
