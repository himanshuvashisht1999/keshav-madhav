<?php

namespace App\Services\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Committee;
use App\Models\MasterOpeningBalance;
use App\Http\DataTable\Admin\Payment\Master\CommitteeDataTable as DataTable;

class CommitteeService
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
        $committee = new Committee();
        $committee->name = $request->name;
        $committee->amount = $request->amount;
        $balance = $request->balance ?? 0;
        if ($request->balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $committee->balance = $balance;
        $committee->period = $request->period;
        $committee->status = 1;
        $committee->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'committee',
                'master_id' => $committee->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->balance_type ?? 'Credit',
            ]
        );

        return true;
    }

    public function edit(Request $request)
    {
        return Committee::with('currentOpeningBalance')->find($request->id);
    }

    public function update(Request $request)
    {
        $committee = Committee::find($request->id);
        $committee->name = $request->name;
        $committee->amount = $request->amount;
        $balance = $request->balance ?? 0;
        if ($request->balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $committee->balance = $balance;
        $committee->period = $request->period;
        $committee->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'committee',
                'master_id' => $committee->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->balance_type ?? 'Credit',
            ]
        );

        return true;
    }

    public function delete(Request $request)
    {
        $adjustmentMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\Committee')->pluck('id');
        
        $hasAdjustments = \App\Models\PaymentAdjustment::whereIn('adjustment_master_id', $adjustmentMasterIds)
            ->where('ref_id', (string)$request->id)
            ->exists();

        if (!$hasAdjustments) {
            \App\Models\MasterOpeningBalance::where('master_type', 'committee')
                ->where('master_id', $request->id)
                ->delete();
                
            return Committee::where('id', $request->id)->delete();
        }

        return Committee::where('id', $request->id)->update(['status' => 0]);
    }
}
