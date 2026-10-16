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
    public function index(Request $request)
    {
        $query = JournalVoucher::query();

        // 1. Voucher Number / Keyword
        if ($request->filled('voucher_no')) {
            $search = trim($request->voucher_no);
            $query->where(function ($q) use ($search) {
                $q->where('voucher_no', 'LIKE', "%{$search}%")
                  ->orWhere('narration', 'LIKE', "%{$search}%");
            });
        }

        // 2. From & To Date
        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        // 3. Debit Master
        if ($request->filled('debit_master')) {
            $debitMasterId = $request->debit_master;
            $selectedMaster = AdjustmentMaster::find($debitMasterId);
            $matchingIds = $selectedMaster
                ? AdjustmentMaster::where('name', $selectedMaster->name)->pluck('id')->toArray()
                : [$debitMasterId];

            $query->whereHas('items', function ($q) use ($matchingIds) {
                $q->where('type', 'debit')->whereIn('master_type', $matchingIds);
            });
        }

        // 4. Credit Master
        if ($request->filled('credit_master')) {
            $creditMasterId = $request->credit_master;
            $selectedMaster = AdjustmentMaster::find($creditMasterId);
            $matchingIds = $selectedMaster
                ? AdjustmentMaster::where('name', $selectedMaster->name)->pluck('id')->toArray()
                : [$creditMasterId];

            $query->whereHas('items', function ($q) use ($matchingIds) {
                $q->where('type', 'credit')->whereIn('master_type', $matchingIds);
            });
        }

        // 5. Debit Amount Range
        if ($request->filled('min_debit')) {
            $query->where('total_debit', '>=', (float) $request->min_debit);
        }
        if ($request->filled('max_debit')) {
            $query->where('total_debit', '<=', (float) $request->max_debit);
        }

        // 6. Credit Amount Range
        if ($request->filled('min_credit')) {
            $query->where('total_credit', '>=', (float) $request->min_credit);
        }
        if ($request->filled('max_credit')) {
            $query->where('total_credit', '<=', (float) $request->max_credit);
        }

        // Calculate totals based on filters
        $totalVouchers = (clone $query)->count();
        $totalDebit = (float) (clone $query)->sum('total_debit');
        $totalCredit = (float) (clone $query)->sum('total_credit');

        // Paginate vouchers with query string preserved
        $vouchers = $query->with('items')->latest('date')->latest('id')->paginate(20)->withQueryString();
        $this->attachVoucherItemDetails($vouchers);

        $masters = AdjustmentMaster::where('status', 1)->orderBy('name')->get()->unique('name');

        return view('admin.payment.journal_voucher.index', compact(
            'vouchers',
            'masters',
            'totalVouchers',
            'totalDebit',
            'totalCredit'
        ));
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
        $this->attachVoucherItemDetails(collect([$voucher]));
        return $voucher;
    }

    private function attachVoucherItemDetails($vouchers)
    {
        $allMasters = AdjustmentMaster::all()->keyBy('id');
        
        $modelIdMap = [];
        foreach ($vouchers as $voucher) {
            foreach ($voucher->items as $item) {
                $master = $allMasters->get($item->master_type);
                if ($master && class_exists($master->model_name)) {
                    $model = $master->model_name;
                    if ($model == 'App\Models\AgentOrder') $model = 'App\Models\MasterCustomer';
                    $modelIdMap[$model][] = $item->master_id;
                }
            }
        }

        $loadedParties = [];
        foreach ($modelIdMap as $model => $ids) {
            $uniqueIds = array_unique($ids);
            try {
                $parties = $model::whereIn('id', $uniqueIds)->get()->keyBy('id');
                $loadedParties[$model] = $parties;
            } catch (\Exception $e) {
                $loadedParties[$model] = collect();
            }
        }

        foreach ($vouchers as $voucher) {
            $debitItems = [];
            $creditItems = [];
            foreach ($voucher->items as $item) {
                $master = $allMasters->get($item->master_type);
                $item->master_name = $master ? $master->name : 'Unknown';
                
                $model = $master && class_exists($master->model_name) ? $master->model_name : null;
                if ($model == 'App\Models\AgentOrder') $model = 'App\Models\MasterCustomer';

                if ($model && isset($loadedParties[$model]) && isset($loadedParties[$model][$item->master_id])) {
                    $party = $loadedParties[$model][$item->master_id];
                    $item->party_name = $party->name ?? $party->bank_name ?? 'N/A';
                } else {
                    $item->party_name = 'N/A';
                }

                if ($item->type == 'debit') {
                    $debitItems[] = $item;
                } else {
                    $creditItems[] = $item;
                }
            }
            $voucher->debit_items = collect($debitItems);
            $voucher->credit_items = collect($creditItems);
        }
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

        // One sign rule for every account, same as adjustments and every ledger screen:
        // Credit = +, Debit = -. (Committee, bank, hulayati, machinery, loan, factory head,
        // discount and salary accounts used to get the opposite sign here.)
        $net = ($type == 'debit') ? $amount : -$amount;
        if ($action == 'reverse')
            $net = -$net;

        if (isset($item->balance)) {
            $item->balance -= $net;
        } elseif (isset($item->amount)) {
            $item->amount -= $net;
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
