<?php

namespace App\Services\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Models\WashingVoucher;
use App\Models\WashingVoucherItem;
use App\Models\WashingMaster;
use App\Models\FabricRollAssigning;
use App\Http\DataTable\Admin\Payment\Voucher\WashingVoucherDataTable as DataTable;
use DB;
use File;

class WashingVoucherService
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
                $documentName = 'washing_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vouchers/washing'), $documentName);
                $documentName = 'uploads/vouchers/washing/' . $documentName;
            }

            $voucher = WashingVoucher::create([
                'washing_master_id' => $request->washing_master_id,
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
                    WashingVoucherItem::create([
                        'washing_voucher_id' => $voucher->id,
                        'order_lot_id' => $item['order_lot_id'] ?? null,
                        'item_name' => $item['item_name'],
                        'quantity' => $item['quantity'],
                        'rate' => $item['rate'],
                        'amount' => $item['amount'],
                    ]);
                }
            }

            // Update balance
            $washingMaster = WashingMaster::find($request->washing_master_id);
            if ($washingMaster) {
                $washingMaster->balance += $request->total_amount;
                $washingMaster->save();
            }

            return $voucher;
        });
    }

    public function edit(Request $request)
    {
        return WashingVoucher::with(['items', 'washingMaster', 'orderLot'])->find($request->id);
    }

    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $voucher = WashingVoucher::find($request->id);
            
            $documentName = $voucher->document;
            if ($request->hasFile('document')) {
                if ($documentName && file_exists(public_path($documentName))) {
                    unlink(public_path($documentName));
                }
                $file = $request->file('document');
                $documentName = 'washing_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vouchers/washing'), $documentName);
                $documentName = 'uploads/vouchers/washing/' . $documentName;
            }

            $oldWashingMasterId = $voucher->washing_master_id;
            $oldAmount = $voucher->total_amount;

            $voucher->update([
                'washing_master_id' => $request->washing_master_id,
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
            if ($oldWashingMasterId == $request->washing_master_id) {
                $washingMaster = WashingMaster::find($request->washing_master_id);
                if ($washingMaster) {
                    $washingMaster->balance = $washingMaster->balance - $oldAmount + $request->total_amount;
                    $washingMaster->save();
                }
            } else {
                $oldWashingMaster = WashingMaster::find($oldWashingMasterId);
                if ($oldWashingMaster) {
                    $oldWashingMaster->balance -= $oldAmount;
                    $oldWashingMaster->save();
                }

                $newWashingMaster = WashingMaster::find($request->washing_master_id);
                if ($newWashingMaster) {
                    $newWashingMaster->balance += $request->total_amount;
                    $newWashingMaster->save();
                }
            }


            WashingVoucherItem::where('washing_voucher_id', $voucher->id)->delete();
            foreach ($request->items as $item) {
                if (!empty($item['item_name'])) {
                    WashingVoucherItem::create([
                        'washing_voucher_id' => $voucher->id,
                        'order_lot_id' => $item['order_lot_id'] ?? null,
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
            $voucher = WashingVoucher::find($request->id);
            if ($voucher->document && file_exists(public_path($voucher->document))) {
                unlink(public_path($voucher->document));
            }
            
            // Update balance
            $washingMaster = WashingMaster::find($voucher->washing_master_id);
            if ($washingMaster) {
                $washingMaster->balance -= $voucher->total_amount;
                $washingMaster->save();
            }

            return $voucher->delete();
        });
    }
}
