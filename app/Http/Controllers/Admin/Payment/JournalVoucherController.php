<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use App\Models\JournalVoucher;
use App\Models\JournalVoucherItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class JournalVoucherController extends Controller
{
    public function index()
    {
        $vouchers = JournalVoucher::with('items')->latest()->paginate(20);
        return view('admin.payment.journal_voucher.index', compact('vouchers'));
    }

    public function show($id)
    {
        $voucher = $this->getVoucherData($id);
        return view('admin.payment.journal_voucher.show', compact('voucher'));
    }

    public function download($id)
    {
        $voucher = $this->getVoucherData($id);
        $pdf = \PDF::loadView('admin.payment.journal_voucher.download', compact('voucher'));
        return $pdf->download($voucher->voucher_no . '.pdf');
    }

    private function getVoucherData($id)
    {
        $voucher = JournalVoucher::with(['items.voucher'])->findOrFail($id);
        
        foreach ($voucher->items as $item) {
            $master = AdjustmentMaster::find($item->master_type);
            $item->master_name = $master ? $master->name : 'Unknown';
            
            if ($master && class_exists($master->model_name)) {
                $model = $master->model_name;
                if ($model == 'App\Models\AgentOrder') $model = 'App\Models\MasterCustomer';
                $party = $model::find($item->master_id);
                $item->party_name = $party ? ($party->name ?? $party->bank_name ?? 'N/A') : 'N/A';
            } else {
                $item->party_name = 'N/A';
            }
        }
        return $voucher;
    }

    public function create()
    {
        $masters = AdjustmentMaster::where('status', 1)->get();
        return view('admin.payment.journal_voucher.create', compact('masters'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'narration' => 'nullable|string',
            'master_type' => 'required|array',
            'master_id' => 'required|array',
            'type' => 'required|array', // debit or credit
            'amount' => 'required|array',
        ]);

        DB::beginTransaction();
        try {
            $totalDebit = 0;
            $totalCredit = 0;

            $voucher = JournalVoucher::create([
                'voucher_no' => 'JV-' . strtoupper(uniqid()),
                'date' => $request->date,
                'narration' => $request->narration,
                'created_by' => Auth::id(),
            ]);

            foreach ($request->master_type as $key => $masterId) {
                $type = $request->type[$key];
                $amount = (float) $request->amount[$key];

                JournalVoucherItem::create([
                    'journal_voucher_id' => $voucher->id,
                    'master_type' => $masterId,
                    'master_id' => $request->master_id[$key],
                    'amount' => $amount,
                    'type' => $type,
                    'narration' => $request->item_narration[$key] ?? null,
                ]);

                if ($type == 'debit')
                    $totalDebit += $amount;
                else
                    $totalCredit += $amount;

                // Update Balances
                $this->updateBalance($masterId, $request->master_id[$key], $amount, $type, 'add', $voucher);
            }

            $voucher->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Update Payment records with correct voucher id
            \App\Models\Payment::where('reference_id', $voucher->voucher_no)->update(['paymentable_id' => $voucher->id]);

            DB::commit();
            return redirect()->route('admin.payment.journal-voucher.index')->with('success', 'Journal Voucher created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function edit($id)
    {
        $voucher = JournalVoucher::with('items')->findOrFail($id);
        $masters = AdjustmentMaster::where('status', 1)->get();
        return view('admin.payment.journal_voucher.create', compact('voucher', 'masters'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'date' => 'required|date',
            'narration' => 'nullable|string',
            'master_type' => 'required|array',
            'master_id' => 'required|array',
            'type' => 'required|array',
            'amount' => 'required|array',
        ]);

        $voucher = JournalVoucher::findOrFail($id);

        DB::beginTransaction();
        try {
            // Reverse old balances and delete old payments
            foreach ($voucher->items as $item) {
                $this->updateBalance($item->master_type, $item->master_id, $item->amount, $item->type, 'reverse', $voucher);
            }
            \App\Models\Payment::where('reference_id', $voucher->voucher_no)->delete();
            $voucher->items()->delete();

            $totalDebit = 0;
            $totalCredit = 0;

            $voucher->update([
                'date' => $request->date,
                'narration' => $request->narration,
            ]);

            foreach ($request->master_type as $key => $masterId) {
                $type = $request->type[$key];
                $amount = (float) $request->amount[$key];

                JournalVoucherItem::create([
                    'journal_voucher_id' => $voucher->id,
                    'master_type' => $masterId,
                    'master_id' => $request->master_id[$key],
                    'amount' => $amount,
                    'type' => $type,
                    'narration' => $request->item_narration[$key] ?? null,
                ]);

                if ($type == 'debit')
                    $totalDebit += $amount;
                else
                    $totalCredit += $amount;

                // Update Balances
                $this->updateBalance($masterId, $request->master_id[$key], $amount, $type, 'add', $voucher);
            }

            $voucher->update([
                'total_debit' => $totalDebit,
                'total_credit' => $totalCredit,
            ]);

            // Ensure payments have correct ID
            \App\Models\Payment::where('reference_id', $voucher->voucher_no)->update(['paymentable_id' => $voucher->id]);

            DB::commit();
            return redirect()->route('admin.payment.journal-voucher.index')->with('success', 'Journal Voucher updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function destroy($id)
    {
        $voucher = JournalVoucher::with('items')->findOrFail($id);
        DB::beginTransaction();
        try {
            foreach ($voucher->items as $item) {
                $this->updateBalance($item->master_type, $item->master_id, $item->amount, $item->type, 'reverse', $voucher);
            }
            log_deletion('Journal Voucher', $id, [
                'voucher' => $voucher->toArray(),
                'items'   => $voucher->items ? $voucher->items->toArray() : []
            ]);

            \App\Models\Payment::where('reference_id', $voucher->voucher_no)->delete();
            $voucher->delete();
            DB::commit();
            return redirect()->route('admin.payment.journal-voucher.index')->with('success', 'Journal Voucher deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    private function updateBalance($masterId, $refId, $amount, $type, $action = 'add', $voucher = null)
    {
        $master = AdjustmentMaster::find($masterId);
        if (!$master)
            return;

        $modelName = $master->model_name;
        if (!class_exists($modelName))
            return;
        if ($modelName == 'App\Models\AgentOrder') {
            $modelName = 'App\Models\MasterCustomer';
        }
        if (!class_exists($modelName))
            return;

        $item = $modelName::find($refId);
        if (!$item)
            return;

        $isSpecial = in_array($masterId, [1, 12, 19, 26, 27, 28, 29, 30]);

        $net = ($type == 'debit') ? $amount : -$amount;
        if ($action == 'reverse')
            $net = -$net;

        if (isset($item->balance)) {
            if ($isSpecial) {
                $item->balance += $net;
            } else {
                $item->balance -= $net;
            }
        } elseif (isset($item->amount)) {
            if ($isSpecial) {
                $item->amount += $net;
            } else {
                $item->amount -= $net;
            }
        }
        $item->save();

        // Create Payment Record for Ledger consistency
        if ($action == 'add' && abs($net) > 0 && $voucher) {
            $this->createPaymentRecord($master, $item, $amount, $type, $voucher);
        }
    }

    private function createPaymentRecord($master, $item, $amount, $type, $voucher)
    {
        $category = 'journal_voucher';
        $partyType = $master->model_name;
        $partyId = $item->id;

        // Special handling for MasterCustomer (Domestic/Corporate)
        if ($master->model_name == 'App\Models\MasterCustomer') {
            $category = $item->type == 'corporate' ? 'corporate_order' : 'domestic_order';
        }

        \App\Models\Payment::create([
            'payment_category' => $category,
            'payment_type' => $type == 'debit' ? 'paid' : 'adjustment',
            'party_type' => $partyType,
            'party_id' => $partyId,
            'paymentable_type' => JournalVoucher::class,
            'paymentable_id' => $voucher->id ?? 0,
            'amount' => $amount,
            'payment_date' => $voucher->date,
            'payment_mode' => 'Journal',
            'reference_id' => $voucher->voucher_no,
            'remarks' => '[JV] ' . ($voucher->narration ?? ''),
            'created_by' => Auth::id(),
        ]);
    }
}
