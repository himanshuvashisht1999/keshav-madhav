<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\PaymentType;
use App\Http\DataTable\Admin\Payment\Master\PaymentTypeDataTable as DataTable;

class PaymentTypeService
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
        $paymentType = new PaymentType();
        $paymentType->name = $request->name;
        $paymentType->status = 1;
        $paymentType->save();
        return true;
    }

    public function edit(Request $request)
    {
        return PaymentType::find($request->id);
    }

    public function update(Request $request)
    {
        $paymentType = PaymentType::find($request->id);
        $paymentType->name = $request->name;
        $paymentType->save();
        return true;
    }

    public function delete(Request $request)
    {
        return PaymentType::where('id', $request->id)->update(['status' => 0]);
    }
}
