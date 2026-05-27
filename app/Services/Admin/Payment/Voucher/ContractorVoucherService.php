<?php

namespace App\Services\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Models\ContractorVoucher;
use App\Models\ContractorVoucherItem;
use App\Models\Contractor;
use App\Models\FabricRollAssigning;
use App\Http\DataTable\Admin\Payment\Voucher\ContractorVoucherDataTable as DataTable;
use DB;
use File;

class ContractorVoucherService
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
        return DB::transaction(function () use ($request) {
            $documentName = null;
            if ($request->hasFile('document')) {
                $file = $request->file('document');
                $documentName = 'contractor_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vouchers/contractor'), $documentName);
                $documentName = 'uploads/vouchers/contractor/' . $documentName;
            }

            $voucher = ContractorVoucher::create([
                'contractor_id' => $request->contractor_id,
                'order_lot_id' => $request->order_lot_id,
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'sub_total' => $request->sub_total,
                'gst' => $request->gst,
                'other_charges' => $request->other_charges,
                'round_off' => $request->round_off,
                'total_amount' => $request->total_amount,
                'document' => $documentName,
                'remarks' => $request->remarks,
                'status' => 1,
            ]);

            foreach ($request->items as $item) {
                if (!empty($item['item_name'])) {
                    ContractorVoucherItem::create([
                        'contractor_voucher_id' => $voucher->id,
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            // Update balance
            $contractor = Contractor::find($request->contractor_id);
            if ($contractor) {
                $contractor->balance += $request->total_amount;
                $contractor->save();
            }

            return $voucher;
        });
    }

    public function edit(Request $request)
    {
        return ContractorVoucher::with(['items', 'contractor', 'orderLot'])->find($request->id);
    }

    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $voucher = ContractorVoucher::find($request->id);
            
            $documentName = $voucher->document;
            if ($request->hasFile('document')) {
                if ($documentName && file_exists(public_path($documentName))) {
                    unlink(public_path($documentName));
                }
                $file = $request->file('document');
                $documentName = 'contractor_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vouchers/contractor'), $documentName);
                $documentName = 'uploads/vouchers/contractor/' . $documentName;
            }

            $oldContractorId = $voucher->contractor_id;
            $oldAmount = $voucher->total_amount;

            $voucher->update([
                'contractor_id' => $request->contractor_id,
                'order_lot_id' => $request->order_lot_id,
                'voucher_date' => $request->voucher_date,
                'voucher_number' => $request->voucher_number,
                'sub_total' => $request->sub_total,
                'gst' => $request->gst,
                'other_charges' => $request->other_charges,
                'round_off' => $request->round_off,
                'total_amount' => $request->total_amount,
                'document' => $documentName,
                'remarks' => $request->remarks,
            ]);

            // Update balances
            if ($oldContractorId == $request->contractor_id) {
                $contractor = Contractor::find($request->contractor_id);
                if ($contractor) {
                    $contractor->balance = $contractor->balance - $oldAmount + $request->total_amount;
                    $contractor->save();
                }
            } else {
                $oldContractor = Contractor::find($oldContractorId);
                if ($oldContractor) {
                    $oldContractor->balance -= $oldAmount;
                    $oldContractor->save();
                }

                $newContractor = Contractor::find($request->contractor_id);
                if ($newContractor) {
                    $newContractor->balance += $request->total_amount;
                    $newContractor->save();
                }
            }


            ContractorVoucherItem::where('contractor_voucher_id', $voucher->id)->delete();
            foreach ($request->items as $item) {
                if (!empty($item['item_name'])) {
                    ContractorVoucherItem::create([
                        'contractor_voucher_id' => $voucher->id,
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            return $voucher;
        });
    }

    public function delete(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $voucher = ContractorVoucher::find($request->id);
            if ($voucher->document && file_exists(public_path($voucher->document))) {
                unlink(public_path($voucher->document));
            }
            
            // Update balance
            $contractor = Contractor::find($voucher->contractor_id);
            if ($contractor) {
                $contractor->balance -= $voucher->total_amount;
                $contractor->save();
            }

            return $voucher->delete();
        });
    }
}
