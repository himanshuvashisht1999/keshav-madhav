<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\CashPayment;
use App\Http\DataTable\Admin\Payment\Master\CashPaymentDataTable as DataTable;

class CashPaymentService
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
        $cashPayment = new CashPayment();
        $cashPayment->name = $request->name;
        $cashPayment->status = 1;
        $cashPayment->save();
        return true;
    }

    public function edit(Request $request)
    {
        return CashPayment::find($request->id);
    }

    public function update(Request $request)
    {
        $cashPayment = CashPayment::find($request->id);
        $cashPayment->name = $request->name;
        $cashPayment->save();
        return true;
    }

    public function delete(Request $request)
    {
        return CashPayment::where('id', $request->id)->update(['status' => 0]);
    }
}
