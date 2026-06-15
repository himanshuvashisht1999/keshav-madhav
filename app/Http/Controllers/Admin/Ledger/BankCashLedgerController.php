<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use App\Models\AgentOrderDispatch;
use App\Models\AgentOrderReturn;
use App\Models\FabricReceipt;
use App\Models\FabricReturn;
use App\Models\Payment;
use DB;

class BankCashLedgerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id'); // Adjustment Master ID or 'sales_agent'

        $masters = \App\Models\AdjustmentMaster::whereIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
                $master = \App\Models\AdjustmentMaster::find($typeId);
                if ($master) {
                    $modelName = $master->model_name;
                    if (class_exists($modelName)) {
                        $items = $modelName::where('status', 1)
                            ->when($search, function ($q) use ($search, $modelName) {
                                if ($modelName == 'App\Models\BankAccount') {
                                    $q->where('bank_name', 'LIKE', "%$search%");
                                } else {
                                    $q->where('name', 'LIKE', "%$search%");
                                }
                            })
                            ->get()
                            ->map(function ($v) use ($master) {
                                $v->party_type = strtolower($master->name);
                                $v->master_id_val = $master->id;
                                if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                                return $v;
                            });
                        $parties = $parties->concat($items);
                    }
            }
        } else {
            // Default to ALL masters items (might be too many, but let's see)
            foreach ($masters as $master) {
                $modelName = $master->model_name;
                if (class_exists($modelName)) {
                    $items = $modelName::where('status', 1)
                        ->when($search, function ($q) use ($search, $modelName) {
                            if ($modelName == 'App\Models\BankAccount') {
                                $q->where('bank_name', 'LIKE', "%$search%");
                            } else {
                                $q->where('name', 'LIKE', "%$search%");
                            }
                        })
                        ->limit(100) // Safety limit for "All" view
                        ->get()
                        ->map(function ($v) use ($master) {
                            $v->party_type = strtolower($master->name);
                            $v->master_id_val = $master->id;
                            if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                            return $v;
                        });
                    $parties = $parties->concat($items);
                }
            }

        }

        // Sort by name
        $parties = $parties->sortBy('name');

        return view('admin.ledger.bank_cash.index', compact('parties', 'masters'));
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        return view('admin.ledger.bank_cash.show', $data);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $pdf = \PDF::loadView('admin.ledger.bank_cash.download', $data);
        $name = str_replace(' ', '_', $data['party']->name) . '_Ledger_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Resolve Master
        $master = \App\Models\AdjustmentMaster::where('name', 'LIKE', $type)->first();
        if (!$master || !in_array(strtolower($master->name), ['bank account', 'cash master'])) {
            abort(404, "Invalid Ledger Type");
        }

        $modelName = $master->model_name;
        $party = $modelName::findOrFail($id);

        $transactions = collect();

        // 1. Payments
        $paymentsQuery = Payment::query();
        $paymentsQuery->where('payment_method_id', $id)
            ->where('payment_method_type', $modelName);

        // Exclude Journal Voucher payments to avoid double counting with JournalVoucherItem query
        $paymentsQuery->where(function($q) {
            $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
              ->orWhereNull('paymentable_type');
        });

        $payments = $paymentsQuery
            ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->get();

        foreach ($payments as $p) {
            $isCredit = in_array($p->payment_type, ['received', 'credit']);
            $isDebit = in_array($p->payment_type, ['paid', 'debit']);

            if ($isCredit) {
                $debit = 0;
                $credit = (float) $p->amount;
                $desc = 'Payment Received (' . $p->payment_mode . ')';
            } elseif ($isDebit) {
                $debit = (float) $p->amount;
                $credit = 0;
                $desc = 'Payment Paid (' . $p->payment_mode . ')';
            } else {
                $debit = (float) $p->amount;
                $credit = 0;
                $desc = 'Adjustment (' . $p->payment_mode . ')';
            }

            $transactions->push((object) [
                'date' => $p->payment_date,
                'created_at' => $p->created_at,
                'type' => 'Payment',
                'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                'debit' => $debit,
                'credit' => $credit,
                'description' => $desc . ($p->remarks ? ': ' . $p->remarks : ''),
                'view_url' => route('admin.payment.history.show', $p->id)
            ]);
        }

        // 2. Adjustments
        $mode = strtolower($master->name) === 'bank account' ? 'bank' : 'cash';
        $adjustments = \App\Models\PaymentAdjustment::where('payment_mode', $mode)
            ->where('payment_account_id', $id)
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->get();

        foreach ($adjustments as $adj) {
            // For the bank/cash account, the type is reverse of the party's adjustment type
            $isCredit = $adj->type === 'debit'; 
            
            $transactions->push((object) [
                'date' => $adj->date,
                'created_at' => $adj->created_at,
                'type' => 'Adjustment',
                'ref' => $adj->batch_id ?? ('Adj #' . $adj->id),
                'debit' => $isCredit ? 0 : (float) $adj->amount,
                'credit' => $isCredit ? (float) $adj->amount : 0,
                'description' => '[Dist] ' . ($adj->remarks ?: $adj->entity_name),
                'view_url' => $adj->batch_id ? route('admin.payment.adjustment.show', $adj->batch_id) : '#'
            ]);
        }

        // 3. Fetch Journal Vouchers
        $masterIds = \App\Models\AdjustmentMaster::where('model_name', $modelName)->pluck('id');
        $vouchers = \App\Models\JournalVoucherItem::with('voucher')
            ->whereIn('master_type', $masterIds)
            ->where('master_id', $id)
            ->whereHas('voucher', function($q) use ($startDate, $endDate) {
                $q->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate))
                  ->when($endDate, fn($q2) => $q2->whereDate('date', '<=', $endDate));
            })
            ->get();

        foreach ($vouchers as $v) {
            $isCredit = strtolower($v->type) === 'credit';
            $transactions->push((object) [
                'date' => $v->voucher->date,
                'created_at' => $v->created_at,
                'type' => 'Journal Voucher',
                'ref' => $v->voucher->voucher_no,
                'debit' => $isCredit ? 0 : (float) $v->amount,
                'credit' => $isCredit ? (float) $v->amount : 0,
                'description' => $v->narration ?: $v->voucher->narration ?: 'Journal Entry',
                'view_url' => route('admin.payment.journal-voucher.show', $v->voucher->id)
            ]);
        }

        // 4. Opening Balance
        $lookupType = str_replace(' ', '_', strtolower($type));

        $openingBalance = \App\Models\MasterOpeningBalance::whereIn('master_type', [$type, $lookupType, str_replace('_', ' ', $lookupType)])
            ->where('master_id', $id)
            ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
            ->first();

        $openingBalAmount = 0;
        if ($openingBalance) {
            $balanceType = strtolower(trim($openingBalance->balance_type));
            $openingBalAmount = (float) $openingBalance->amount;
            
            // Debit = Negative, Credit = Positive
            if ($balanceType === 'debit') {
                $openingBalAmount = -$openingBalAmount;
            }
        }

        // Sort and Calculate Balance
        $transactions = $transactions->sort(function ($a, $b) {
            $dateA = \Carbon\Carbon::parse($a->date)->format('Y-m-d');
            $dateB = \Carbon\Carbon::parse($b->date)->format('Y-m-d');

            if ($dateA != $dateB) {
                return $dateA <=> $dateB;
            }

            return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
        })->values();

        $balance = $openingBalAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->credit - $tx->debit);
            $tx->running_balance = $balance;
        }

        $viewMode = 'mix';

        return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'viewMode');
    }
}
