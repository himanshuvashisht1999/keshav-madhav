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

class PartyLedgerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id'); // Adjustment Master ID or 'sales_agent'

        $masters = \App\Models\AdjustmentMaster::where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                $items = \App\Models\SalesAgent::where('status', 1)
                    ->when($search, function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->get()
                    ->map(function ($v) {
                        $v->party_type = 'sales_agent';
                        $v->master_id_val = 'sales_agent';
                        $v->balance = $v->shops()->sum('balance');
                        return $v;
                    });
                $parties = $parties->concat($items);
            } else {
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

            // Also load Sales Agents in ALL view
            $agentItems = \App\Models\SalesAgent::where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->limit(100)
                ->get()
                ->map(function ($v) {
                    $v->party_type = 'sales_agent';
                    $v->master_id_val = 'sales_agent';
                    $v->balance = $v->shops()->sum('balance');
                    return $v;
                });
            $parties = $parties->concat($agentItems);
        }

        // Sort by name
        $parties = $parties->sortBy('name');

        return view('admin.ledger.party.index', compact('parties', 'masters'));
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        return view('admin.ledger.party.show', $data);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $pdf = \PDF::loadView('admin.ledger.party.download', $data);
        $name = str_replace(' ', '_', $data['party']->name) . '_Ledger_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $viewMode = $request->query('view_mode', 'mix');
        $groupedLedgers = [];

        if ($type === 'sales_agent') {
            $party = \App\Models\SalesAgent::findOrFail($id);
            $shops = \App\Models\MasterCustomer::where('sales_agent_id', $id)->get();
            $customerIds = $shops->pluck('id')->toArray();

            $selectedCustomerId = $request->query('customer_id');
            if ($selectedCustomerId && in_array($selectedCustomerId, $customerIds)) {
                $customerIds = [$selectedCustomerId];
                $selectedCustomer = $shops->firstWhere('id', $selectedCustomerId);
                $party->balance = $selectedCustomer ? $selectedCustomer->balance : 0;
                $viewMode = 'mix';
            } else {
                $party->balance = $shops->sum('balance');
            }
            
            $transactions = collect();
            
            // 1. Sales (Dispatches) - DEBIT
            $dispatches = AgentOrderDispatch::whereIn('master_customer_id', $customerIds)
                ->where('party_type', 'customer')
                ->where('status', 'dispatched')
                ->when($startDate, fn($q) => $q->whereDate('dispatch_date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('dispatch_date', '<=', $endDate))
                ->get();

            foreach ($dispatches as $d) {
                $transactions->push((object) [
                    'customer_id' => $d->master_customer_id,
                    'date' => $d->dispatch_date,
                    'created_at' => $d->created_at,
                    'type' => 'Sale',
                    'ref' => 'Dispatch #' . $d->id,
                    'debit' => (float) $d->grand_total,
                    'credit' => 0,
                    'description' => ($d->shop->name ?? 'Shop') . ' - Sales Dispatch: ' . ($d->remark ?? '-'),
                    'view_url' => route('admin.agent-orders.dispatches.show', $d->id)
                ]);
            }

            // 2. Standard Order Dispatches - DEBIT
            $orderDispatches = \App\Models\OrderDispatch::whereIn('customer_id', $customerIds)
                ->when($startDate, fn($q) => $q->whereDate('dispatch_date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('dispatch_date', '<=', $endDate))
                ->get();

            foreach ($orderDispatches as $od) {
                $transactions->push((object) [
                    'customer_id' => $od->customer_id,
                    'date' => $od->dispatch_date,
                    'created_at' => $od->created_at,
                    'type' => 'Order Dispatch',
                    'ref' => 'OD #' . ($od->sku ?? $od->id),
                    'debit' => (float) $od->total_amount,
                    'credit' => 0,
                    'description' => ($od->customer->name ?? 'Customer') . ' - Regular Order Dispatch',
                    'view_url' => route('admin.order-dispatch.view', ['id' => $od->id])
                ]);
            }

            // 3. Sales Returns - CREDIT
            $salesReturns = AgentOrderReturn::whereHas('dispatch', function ($q) use ($customerIds) {
                $q->whereIn('master_customer_id', $customerIds)->where('party_type', 'customer');
            })
                ->when($startDate, fn($q) => $q->whereDate('return_date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('return_date', '<=', $endDate))
                ->get();

            foreach ($salesReturns as $r) {
                $transactions->push((object) [
                    'customer_id' => $r->dispatch->master_customer_id ?? null,
                    'date' => $r->return_date,
                    'created_at' => $r->created_at,
                    'type' => 'Sale Return',
                    'ref' => 'Return #' . $r->id,
                    'debit' => 0,
                    'credit' => (float) $r->grand_total,
                    'description' => ($r->dispatch->shop->name ?? 'Shop') . ' - Sales Return',
                    'view_url' => route('admin.agent-orders.returns.show', $r->id)
                ]);
            }

            // 4. Payments
            $payments = Payment::whereIn('party_id', $customerIds)
                ->where('party_type', \App\Models\MasterCustomer::class)
                ->where(function($q) {
                    $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
                      ->orWhereNull('paymentable_type');
                })
                ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate))
                ->get();

            foreach ($payments as $p) {
                $isCredit = in_array($p->payment_type, ['received', 'credit']);
                $isDebit = in_array($p->payment_type, ['paid', 'debit']);

                if ($isCredit) {
                    $debit = 0;
                    $credit = (float) $p->amount;
                    $desc = ($p->party->name ?? 'Customer') . ' - Payment Received (' . $p->payment_mode . ')';
                } elseif ($isDebit) {
                    $debit = (float) $p->amount;
                    $credit = 0;
                    $desc = ($p->party->name ?? 'Customer') . ' - Payment Paid (' . $p->payment_mode . ')';
                } else {
                    $debit = (float) $p->amount;
                    $credit = 0;
                    $desc = ($p->party->name ?? 'Customer') . ' - Adjustment (' . $p->payment_mode . ')';
                }

                $transactions->push((object) [
                    'customer_id' => $p->party_id,
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

            // 5. Finished Goods Purchases (Inventory Purchase) - CREDIT
            $inventoryPurchases = \App\Models\DomesticInventoryPurchase::whereIn('customer_id', $customerIds)
                ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
                ->get();

            foreach ($inventoryPurchases as $ip) {
                $transactions->push((object) [
                    'customer_id' => $ip->customer_id,
                    'date' => $ip->created_at,
                    'created_at' => $ip->created_at,
                    'type' => 'Inventory Purchase',
                    'ref' => 'InvPur #' . $ip->id,
                    'debit' => 0,
                    'credit' => (float) $ip->total_amount,
                    'description' => ($ip->customer->name ?? 'Customer') . ' - Inventory Purchase: ' . ($ip->remarks ?? '-'),
                    'view_url' => route('admin.inventory.purchase_history.show', ['id' => $ip->id])
                ]);
            }

            // 6. Journal Vouchers
            $customerMaster = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\MasterCustomer')->first();
            $customerMasterId = $customerMaster ? $customerMaster->id : 18;

            $vouchers = \App\Models\JournalVoucherItem::with('voucher')
                ->where('master_type', $customerMasterId)
                ->whereIn('master_id', $customerIds)
                ->whereHas('voucher', function($q) use ($startDate, $endDate) {
                    $q->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate))
                      ->when($endDate, fn($q2) => $q2->whereDate('date', '<=', $endDate));
                })
                ->get();

            foreach ($vouchers as $v) {
                $isCredit = strtolower($v->type) === 'credit';
                
                // Fetch the master customer name
                $cName = \App\Models\MasterCustomer::find($v->master_id)->name ?? 'Customer';

                $transactions->push((object) [
                    'customer_id' => $v->master_id,
                    'date' => $v->voucher->date,
                    'created_at' => $v->created_at,
                    'type' => 'Journal Voucher',
                    'ref' => $v->voucher->voucher_no,
                    'debit' => $isCredit ? 0 : (float) $v->amount,
                    'credit' => $isCredit ? (float) $v->amount : 0,
                    'description' => $cName . ' - ' . ($v->narration ?: $v->voucher->narration ?: 'Journal Entry'),
                    'view_url' => route('admin.payment.journal-voucher.show', $v->voucher->id)
                ]);
            }

            // 7. Opening Balance
            $openingBalAmount = 0;
            $openingBalances = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
                ->whereIn('master_id', $customerIds)
                ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
                ->get();

            foreach ($openingBalances as $ob) {
                $balanceType = strtolower(trim($ob->balance_type));
                $obAmount = (float) $ob->amount;
                if ($balanceType === 'debit') {
                    $openingBalAmount -= $obAmount;
                } else {
                    $openingBalAmount += $obAmount;
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

            return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'shops');
        }

        // Resolve Master
        $master = \App\Models\AdjustmentMaster::where('name', 'LIKE', $type)->first();
        if (!$master) {
            // Fallback for direct names
            if ($type === 'vendor') $master = \App\Models\AdjustmentMaster::where('name', 'Vendor')->first();
            elseif ($type === 'customer') $master = \App\Models\AdjustmentMaster::where('name', 'Customer')->first();
        }

        if (!$master) abort(404, "Invalid Ledger Type");

        $modelName = $master->model_name;
        $party = $modelName::findOrFail($id);

        $transactions = collect();

        // Special Detailed Logic for Customers, Vendors, Banks and Cash
        $isBankOrCash = in_array(strtolower($master->name), ['bank account', 'cash master']);
        if (strtolower($master->name) === 'customer' || strtolower($master->name) === 'vendor' || $isBankOrCash) {
            $isCustomer = strtolower($master->name) === 'customer';
            $isVendor = strtolower($master->name) === 'vendor';
            
            $partyIdField = $isCustomer ? 'master_customer_id' : 'master_vendor_id';
            $directIdField = $isCustomer ? 'customer_id' : 'vendor_id';

        // 1. Sales (Dispatches) - DEBIT
        $dispatches = AgentOrderDispatch::where($partyIdField, $id)
            ->where('party_type', $type)
            ->where('status', 'dispatched')
            ->when($startDate, fn($q) => $q->whereDate('dispatch_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('dispatch_date', '<=', $endDate))
            ->get();

        foreach ($dispatches as $d) {
            $transactions->push((object) [
                'date' => $d->dispatch_date,
                'created_at' => $d->created_at,
                'type' => 'Sale',
                'ref' => 'Dispatch #' . $d->id,
                'debit' => (float) $d->grand_total,
                'credit' => 0,
                'description' => 'Sales Dispatch: ' . ($d->remark ?? '-'),
                'view_url' => route('admin.agent-orders.dispatches.show', $d->id)
            ]);
        }

        // 2. Standard Order Dispatches - DEBIT
        if ($type === 'customer') {
            $orderDispatches = \App\Models\OrderDispatch::where($directIdField, $id)
                ->when($startDate, fn($q) => $q->whereDate('dispatch_date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('dispatch_date', '<=', $endDate))
                ->get();

            foreach ($orderDispatches as $od) {
                $transactions->push((object) [
                    'date' => $od->dispatch_date,
                    'created_at' => $od->created_at,
                    'type' => 'Order Dispatch',
                    'ref' => 'OD #' . ($od->sku ?? $od->id),
                    'debit' => (float) $od->total_amount,
                    'credit' => 0,
                    'description' => 'Regular Order Dispatch',
                    'view_url' => route('admin.order-dispatch.view', ['id' => $od->id])
                ]);
            }
        }

        // 3. Sales Returns - CREDIT
        $salesReturns = AgentOrderReturn::whereHas('dispatch', function ($q) use ($id, $partyIdField, $type) {
            $q->where($partyIdField, $id)->where('party_type', $type);
        })
            ->when($startDate, fn($q) => $q->whereDate('return_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('return_date', '<=', $endDate))
            ->get();

        foreach ($salesReturns as $r) {
            $transactions->push((object) [
                'date' => $r->return_date,
                'created_at' => $r->created_at,
                'type' => 'Sale Return',
                'ref' => 'Return #' . $r->id,
                'debit' => 0,
                'credit' => (float) $r->grand_total,
                'description' => 'Sales Return',
                'view_url' => route('admin.agent-orders.returns.show', $r->id)
            ]);
        }

        // 4. Payments
        $paymentsQuery = Payment::query();
        if ($isBankOrCash) {
            $paymentsQuery->where('payment_method_id', $id)
                ->where('payment_method_type', $modelName);
        } else {
            $paymentsQuery->where('party_id', $id)
                ->where('party_type', $modelName);
        }

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
                // For 'adjustment' or other types, we need a fallback or smarter logic
                // For now, if it's not received/credit, assume it's a debit unless we improve the storage
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

        // 5. Finished Goods Purchases (Inventory Purchase) - CREDIT
        $inventoryPurchases = \App\Models\DomesticInventoryPurchase::where($directIdField, $id)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        foreach ($inventoryPurchases as $ip) {
            $transactions->push((object) [
                'date' => $ip->created_at,
                'created_at' => $ip->created_at,
                'type' => 'Inventory Purchase',
                'ref' => 'InvPur #' . $ip->id,
                'debit' => 0,
                'credit' => (float) $ip->total_amount,
                'description' => 'Inventory Purchase: ' . ($ip->remarks ?? '-'),
                'view_url' => route('admin.inventory.purchase_history.show', ['id' => $ip->id])
            ]);
        }

        // 6. Vendor Specific: Fabric Purchases (Inwards) - CREDIT
        if ($type === 'vendor') {
            $receipts = FabricReceipt::where('vendor_id', $id)
                ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
                ->get();

            foreach ($receipts as $r) {
                $transactions->push((object) [
                    'date' => $r->created_at,
                    'created_at' => $r->created_at,
                    'type' => 'Fabric Purchase',
                    'ref' => 'Receipt #' . $r->sku,
                    'debit' => 0,
                    'credit' => (float) $r->total_amount,
                    'description' => 'Fabric Inward (Shipment: ' . ($r->shipment_id ?? '-') . ')',
                    'view_url' => route('admin.fabric_receipt.view', ['id' => $r->id])
                ]);
            }

            // 7. Vendor Specific: Returns to Vendor (Purchase Returns) - DEBIT
            $pReturns = FabricReturn::whereHas('receipt', function ($q) use ($id) {
                $q->where('vendor_id', $id);
            })
                ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
                ->get();

            foreach ($pReturns as $pr) {
                $transactions->push((object) [
                    'date' => $pr->date,
                    'created_at' => $pr->created_at,
                    'type' => 'Fabric Return',
                    'ref' => 'Return #' . ($pr->return_number ?? $pr->id),
                    'debit' => (float) $pr->total_amount,
                    'credit' => 0,
                    'description' => 'Fabric Return to Vendor',
                    'view_url' => route('admin.report.fabric_return_view', $pr->id)
                ]);
            }
        }

        // 7b. Adjustments (for Bank/Cash or others)
        if ($isBankOrCash) {
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
        }
    } else {
        // Generic Logic for other masters: Just fetch PaymentAdjustments
            $adjustments = \App\Models\PaymentAdjustment::where('adjustment_master_id', $master->id)
                ->where('ref_id', $id)
                ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
                ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
                ->get();

            foreach ($adjustments as $adj) {
                $isCredit = $adj->type === 'credit';
                $transactions->push((object) [
                    'date' => $adj->date,
                    'created_at' => $adj->created_at,
                    'type' => 'Adjustment',
                    'ref' => $adj->batch_id ?? ('Adj #' . $adj->id),
                    'debit' => $isCredit ? 0 : (float) $adj->amount,
                    'credit' => $isCredit ? (float) $adj->amount : 0,
                    'description' => $adj->remarks ?: ($isCredit ? 'Credit Adjustment' : 'Debit Adjustment'),
                    'view_url' => $adj->batch_id ? route('admin.payment.adjustment.show', $adj->batch_id) : '#'
                ]);
            }

            // Generic Payments for this model
            $paymentsQuery = Payment::query();
            $paymentsQuery->where('party_id', $id)
                ->where('party_type', $modelName)
                ->where(function($q) {
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

            // Voucher Specific Logic
            if (strtolower($master->name) === 'contractor') {
                $contractorVouchers = \App\Models\ContractorVoucher::where('contractor_id', $id)
                    ->when($startDate, fn($q) => $q->whereDate('voucher_date', '>=', $startDate))
                    ->when($endDate, fn($q) => $q->whereDate('voucher_date', '<=', $endDate))
                    ->get();

                foreach ($contractorVouchers as $cv) {
                    $transactions->push((object) [
                        'date' => $cv->voucher_date,
                        'created_at' => $cv->created_at,
                        'type' => 'Contractor Voucher',
                        'ref' => 'Voucher #' . $cv->voucher_number,
                        'debit' => 0,
                        'credit' => (float) $cv->total_amount,
                        'description' => 'Contractor Voucher: ' . ($cv->remarks ?? '-'),
                        'view_url' => '#'
                    ]);
                }
            } elseif (strtolower($master->name) === 'washing master') {
                $washingVouchers = \App\Models\WashingVoucher::where('washing_master_id', $id)
                    ->when($startDate, fn($q) => $q->whereDate('voucher_date', '>=', $startDate))
                    ->when($endDate, fn($q) => $q->whereDate('voucher_date', '<=', $endDate))
                    ->get();

                foreach ($washingVouchers as $wv) {
                    $transactions->push((object) [
                        'date' => $wv->voucher_date,
                        'created_at' => $wv->created_at,
                        'type' => 'Washing Voucher',
                        'ref' => 'Voucher #' . $wv->voucher_number,
                        'debit' => 0,
                        'credit' => (float) $wv->total_amount,
                        'description' => 'Washing Voucher: ' . ($wv->remarks ?? '-'),
                        'view_url' => '#'
                    ]);
                }
            } elseif (strtolower($master->name) === 'consumable good') {
                $consumableVouchers = \App\Models\ConsumableVoucher::where('consumable_good_id', $id)
                    ->when($startDate, fn($q) => $q->whereDate('voucher_date', '>=', $startDate))
                    ->when($endDate, fn($q) => $q->whereDate('voucher_date', '<=', $endDate))
                    ->get();

                foreach ($consumableVouchers as $cv) {
                    $transactions->push((object) [
                        'date' => $cv->voucher_date,
                        'created_at' => $cv->created_at,
                        'type' => 'Consumable Voucher',
                        'ref' => 'Voucher #' . $cv->voucher_number,
                        'debit' => 0,
                        'credit' => (float) $cv->total_amount,
                        'description' => 'Consumable Voucher: ' . ($cv->remarks ?? '-'),
                        'view_url' => '#'
                    ]);
                }
            }
        }

        // Fetch Journal Vouchers - Applicable to all masters
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

        // 8. Opening Balance
        $lookupType = str_replace(' ', '_', strtolower($type));
        // Specific mapping overrides
        if ($lookupType === 'machinery_account') $lookupType = 'machinery';
        if ($lookupType === 'loan_account') $lookupType = 'loan';
        if ($lookupType === 'hulayati_master') $lookupType = 'hulayati';
        if ($lookupType === 'factory_head_master') $lookupType = 'factory_head';

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

            // Same day, use created_at as tie-breaker
            return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
        })->values();

        $balance = $openingBalAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->credit - $tx->debit);
            $tx->running_balance = $balance;
        }

        return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'viewMode', 'groupedLedgers');
    }
}
