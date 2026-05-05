<?php

namespace App\Services\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Models\ConsumableVoucher;
use App\Models\ConsumableVoucherItem;
use App\Models\ConsumableGood;
use App\Http\DataTable\Admin\Payment\Voucher\ConsumableVoucherDataTable as DataTable;
use DB;
use File;

class ConsumableVoucherService
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
                $documentName = 'consumable_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vouchers/consumable'), $documentName);
                $documentName = 'uploads/vouchers/consumable/' . $documentName;
            }

            $voucher = ConsumableVoucher::create([
                'consumable_good_id' => $request->consumable_good_id,
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
                    ConsumableVoucherItem::create([
                        'consumable_voucher_id' => $voucher->id,
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

    public function edit(Request $request)
    {
        return ConsumableVoucher::with('items')->find($request->id);
    }

    public function update(Request $request)
    {
        return DB::transaction(function () use ($request) {
            $voucher = ConsumableVoucher::find($request->id);
            
            $documentName = $voucher->document;
            if ($request->hasFile('document')) {
                if ($documentName && file_exists(public_path($documentName))) {
                    unlink(public_path($documentName));
                }
                $file = $request->file('document');
                $documentName = 'consumable_' . time() . '_' . $file->getClientOriginalName();
                $file->move(public_path('uploads/vouchers/consumable'), $documentName);
                $documentName = 'uploads/vouchers/consumable/' . $documentName;
            }

            $voucher->update([
                'consumable_good_id' => $request->consumable_good_id,
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

            ConsumableVoucherItem::where('consumable_voucher_id', $voucher->id)->delete();
            foreach ($request->items as $item) {
                if (!empty($item['item_name'])) {
                    ConsumableVoucherItem::create([
                        'consumable_voucher_id' => $voucher->id,
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
            $voucher = ConsumableVoucher::find($request->id);
            if ($voucher->document && file_exists(public_path($voucher->document))) {
                unlink(public_path($voucher->document));
            }
            return $voucher->delete();
        });
    }
}
