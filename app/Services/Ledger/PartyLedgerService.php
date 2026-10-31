<?php

namespace App\Services\Ledger;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use App\Models\AgentOrderDispatch;
use App\Models\AgentOrderReturn;
use App\Models\FabricReceipt;
use App\Models\FabricReturn;
use App\Models\Payment;
use DB;

/**
 * The ONE party-ledger calculator. Admin and Owner ledger screens, exports and the balance
 * check all use this class, so a party's opening, transactions and closing balance are the
 * same number everywhere.
 *
 * build() returns: party (with ->balance = closing balance), transactions (with running_balance),
 * type, startDate, endDate, openingBalAmount (opening for the period), viewMode, groupedLedgers,
 * availableTypes (+ periodTotalDebit, periodTotalCredit, closingBalance for non agent ledgers).
 */
class PartyLedgerService
{
    /** Note under the totals of a party list with "All types" when a sales agent is listed (admin and owner lists, PDF, Excel). */
    public const AGENT_NOTE = 'A sales agent row is the total of its shops. The totals count each shop once: in its own row when the shop is listed, otherwise as part of its agent. With a Balance or Closing amount filter, only shops that pass it are counted, so the totals can differ from the agent rows.';

    /** The same note for the detailed report (web, PDF, Excel). */
    public const AGENT_NOTE_DETAILED = self::AGENT_NOTE . ' The mixed list counts the same way. In the party-wise view an agent\'s section shows its full statement (all its shops), while the totals still count each shop once.';

    public function build(Request $request, $type, $id)
    {
        // The type comes from the URL: 'Customer' must give the same ledger as 'customer'
        // (the checks below compare it exactly, while the master lookup ignores case).
        $type = strtolower(trim((string) $type));
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $viewMode = $request->query('view_mode', 'mix');
        $groupedLedgers = [];

        // Bank / cash accounts: same calculator as the bank-cash ledger (both sides of every movement)
        if (in_array(strtolower(trim((string) $type)), ['bank account', 'cash master'])) {
            $data = app(BankLedgerService::class)->build($request, $type, $id);
            $data['groupedLedgers'] = [];
            $data['availableTypes'] = collect($data['transactions'])->pluck('type')->filter()->unique()->values()->all();
            $data['transactions'] = $this->applyLedgerFilters($data['transactions'], $request);
            return $data;
        }

        if ($type === 'sales_agent') {
            $party = \App\Models\SalesAgent::findOrFail($id);
            $shops = \App\Models\MasterCustomer::where('sales_agent_id', $id)->orderBy('id')->get();
            $customerIds = $shops->pluck('id')->toArray();
            // Shop names come from this list (every record below belongs to one of these shops), not one query per row
            $shopsById = $shops->keyBy('id');
            $shopName = function ($shopId, $default) use ($shopsById) {
                return ($shopId !== null && $shopsById->has($shopId)) ? ($shopsById[$shopId]->name ?? $default) : $default;
            };

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
                ->orderBy('id') // same row order with or without the ledger indexes
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
                    'description' => $shopName($d->master_customer_id, 'Shop') . ' - Sales Dispatch: ' . ($d->remark ?? '-'),
                    'view_url' => route('admin.agent-orders.dispatches.show', $d->id)
                ]);
            }

            // 2. Standard Order Dispatches - DEBIT
            $orderDispatches = \App\Models\OrderDispatch::whereIn('customer_id', $customerIds)
                ->orderBy('id')
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
                    'description' => $shopName($od->customer_id, 'Customer') . ' - Regular Order Dispatch',
                    'view_url' => route('admin.order-dispatch.view', ['id' => $od->id])
                ]);
            }

            // 3. Sales Returns - CREDIT
            $salesReturns = AgentOrderReturn::with('dispatch')->whereHas('dispatch', function ($q) use ($customerIds) {
                $q->whereIn('master_customer_id', $customerIds)->where('party_type', 'customer');
            })
                ->orderBy('id')
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
                    'description' => $shopName($r->dispatch->master_customer_id ?? null, 'Shop') . ' - Sales Return',
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
                
                
                ->orderBy('id')
                ->get();

            foreach ($payments as $p) {
                $isCredit = in_array($p->payment_type, ['received', 'credit']);
                $isDebit = in_array($p->payment_type, ['paid', 'debit']);

                if ($isCredit) {
                    $debit = 0;
                    $credit = (float) $p->amount;
                    $desc = $shopName($p->party_id, 'Customer') . ' - Payment Received (' . $p->payment_mode . ')';
                } elseif ($isDebit) {
                    $debit = (float) $p->amount;
                    $credit = 0;
                    $desc = $shopName($p->party_id, 'Customer') . ' - Payment Paid (' . $p->payment_mode . ')';
                } else {
                    $debit = (float) $p->amount;
                    $credit = 0;
                    $desc = $shopName($p->party_id, 'Customer') . ' - Adjustment (' . $p->payment_mode . ')';
                }

                $txType = $isCredit ? 'Receipt' : ($isDebit ? 'Payment' : 'Adjustment');
                $slipUrl = !empty($p->image) ? asset($p->image) : null;
                $transactions->push((object) [
                    'customer_id' => $p->party_id,
                    'date' => $p->payment_date,
                    'created_at' => $p->created_at,
                    'type' => $txType,
                    'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $desc . ($p->remarks ? ': ' . $p->remarks : ''),
                    'view_url' => route('admin.payment.history.show', $p->id),
                    'slip_url' => $slipUrl
                ]);
            }

            // 5. Finished Goods Purchases (Inventory Purchase) - CREDIT
            $inventoryPurchases = \App\Models\DomesticInventoryPurchase::whereIn('customer_id', $customerIds)
                ->orderBy('id')
                ->get();

            foreach ($inventoryPurchases as $ip) {
                $pDate = $ip->purchase_date ? \Carbon\Carbon::parse($ip->purchase_date)->format('Y-m-d') : \Carbon\Carbon::parse($ip->created_at)->format('Y-m-d');
                $transactions->push((object) [
                    'customer_id' => $ip->customer_id,
                    'date' => $pDate,
                    'created_at' => $ip->created_at,
                    'type' => 'Inventory Purchase',
                    'ref' => 'InvPur #' . $ip->id,
                    'debit' => 0,
                    'credit' => (float) $ip->total_amount,
                    'description' => $shopName($ip->customer_id, 'Customer') . ' - Inventory Purchase: ' . ($ip->remarks ?? '-'),
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
                    $q
                      ;
                })
                ->orderBy('id')
                ->get();

            foreach ($vouchers as $v) {
                $isCredit = strtolower($v->type) === 'credit';
                
                // Fetch the master customer name
                $cName = $shopName($v->master_id, 'Customer');

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
                ->where('financial_year', \App\Models\MasterOpeningBalance::openingKey())
                ->orderBy('id')
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
            $finalTransactions = collect();

            $shopPreBalances = [];
            if ($viewMode === 'party_wise') {
                foreach ($shops as $shop) {
                    $shopOpeningAmt = 0;
                    // same rows as loaded above for all shops (one opening row per party)
                    $shopOpening = $openingBalances->firstWhere('master_id', $shop->id);
                    if ($shopOpening) {
                        $balanceType = strtolower(trim($shopOpening->balance_type));
                        if ($balanceType === 'debit') {
                            $shopOpeningAmt -= (float) $shopOpening->amount;
                        } else {
                            $shopOpeningAmt += (float) $shopOpening->amount;
                        }
                    }
                    $shopPreBalances[$shop->id] = $shopOpeningAmt;
                }
            }

            foreach ($transactions as $tx) {
                $dateTx = \Carbon\Carbon::parse($tx->date)->format('Y-m-d');
                $isPre = $startDate && $dateTx < $startDate;
                
                if ($isPre) {
                    $openingBalAmount += ($tx->credit - $tx->debit);
                    $balance = $openingBalAmount;
                    if ($viewMode === 'party_wise' && isset($tx->customer_id)) {
                        if(isset($shopPreBalances[$tx->customer_id])) {
                            $shopPreBalances[$tx->customer_id] += ($tx->credit - $tx->debit);
                        }
                    }
                } else {
                    // transactions after the end date must not affect balances
                    if ($endDate && $dateTx > $endDate) continue;
                    $balance += ($tx->credit - $tx->debit);
                    $tx->running_balance = $balance;
                    $finalTransactions->push($tx);
                }
            }
            
            $availableTypes = $finalTransactions->pluck('type')->filter()->unique()->values()->all();
            $filteredTransactions = $this->applyLedgerFilters($finalTransactions, $request);

            if ($viewMode === 'party_wise') {
                foreach ($shops as $shop) {
                    $shopTx = $filteredTransactions->where('customer_id', $shop->id)->values();
                    $shopOpeningAmt = $shopPreBalances[$shop->id];
                    $shopBal = $shopOpeningAmt;
                    
                    foreach ($shopTx as $tx) {
                        $shopBal += ($tx->credit - $tx->debit);
                        $tx->running_balance = $shopBal;
                    }

                    $groupedLedgers[] = (object)[
                        'shop' => $shop,
                        'opening_balance' => $shopOpeningAmt,
                        'closing_balance' => $shopBal,
                        'transactions' => $shopTx
                    ];
                }
            }

            $transactions = $filteredTransactions;
            if (isset($party)) {
                $party->balance = $balance;
            }

            return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'shops', 'viewMode', 'groupedLedgers', 'availableTypes');
        }

        // Resolve Master
        $master = $this->ledgerMaster($type);

        if (!$master) abort(404, "Invalid Ledger Type");

        $modelName = $master->model_name;
        $party = $modelName::findOrFail($id);

        $transactions = collect();

        // Special Detailed Logic for Customers, Vendors
        if (strtolower($master->name) === 'customer' || strtolower($master->name) === 'vendor') {
            $isCustomer = strtolower($master->name) === 'customer';
            $isVendor = strtolower($master->name) === 'vendor';
            
            $partyIdField = $isCustomer ? 'master_customer_id' : 'master_vendor_id';
            $directIdField = $isCustomer ? 'customer_id' : 'vendor_id';

            // 1. Sales (Dispatches) - DEBIT
            $dispatches = AgentOrderDispatch::where($partyIdField, $id)
                ->where('party_type', $type)
                ->where('status', 'dispatched')
                ->orderBy('id') // same row order with or without the ledger indexes (as for sales agents)
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
                    ->orderBy('id')
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
                ->orderBy('id')
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
        $paymentsQuery->where('party_id', $id)
            ->where('party_type', $modelName);

        // Exclude Journal Voucher payments to avoid double counting with JournalVoucherItem query
        $paymentsQuery->where(function($q) {
            $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
              ->orWhereNull('paymentable_type');
        });

        $payments = $paymentsQuery
            
            
            ->orderBy('id')
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

            $txType = $isCredit ? 'Receipt' : ($isDebit ? 'Payment' : 'Adjustment');
            $slipUrl = !empty($p->image) ? asset($p->image) : null;
            $transactions->push((object) [
                'date' => $p->payment_date,
                'created_at' => $p->created_at,
                'type' => $txType,
                'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                'debit' => $debit,
                'credit' => $credit,
                'description' => $desc . ($p->remarks ? ': ' . $p->remarks : ''),
                'view_url' => route('admin.payment.history.show', $p->id),
                'slip_url' => $slipUrl
            ]);
        }

        // 5. Finished Goods Purchases (Inventory Purchase) - CREDIT
            $inventoryPurchases = \App\Models\DomesticInventoryPurchase::where($directIdField, $id)
                ->orderBy('id')
                ->get();

            foreach ($inventoryPurchases as $ip) {
                $pDate = $ip->purchase_date ? \Carbon\Carbon::parse($ip->purchase_date)->format('Y-m-d') : \Carbon\Carbon::parse($ip->created_at)->format('Y-m-d');
                $transactions->push((object) [
                    'date' => $pDate,
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
                    ->orderBy('id')
                    ->get();

                foreach ($receipts as $r) {
                    $rDate = $r->time ? \Carbon\Carbon::parse($r->time)->format('Y-m-d') : \Carbon\Carbon::parse($r->created_at)->format('Y-m-d');
                    $slipUrl = $r->challan_photo ? asset('assets/receipts/challan/' . $r->challan_photo) : null;
                    $transactions->push((object) [
                        'date' => $rDate,
                        'created_at' => $r->created_at,
                        'type' => 'Fabric Purchase',
                        'ref' => 'Receipt #' . $r->sku,
                        'bill_no' => $r->bill_no ?: '-',
                        'debit' => 0,
                        'credit' => (float) $r->total_amount,
                        'description' => 'Fabric Inward (Shipment: ' . ($r->shipment_id ?? '-') . ')',
                        'view_url' => route('admin.fabric_receipt.view', ['id' => $r->id]),
                        'slip_url' => $slipUrl
                    ]);
                }

                // 7. Vendor Specific: Returns to Vendor (Purchase Returns) - DEBIT
                $pReturns = FabricReturn::with('receipt')->where('vendor_id', $id)
                    ->orWhereHas('receipt', function ($q) use ($id) {
                        $q->where('vendor_id', $id);
                    })
                    ->orderBy('id')
                    ->get();

                foreach ($pReturns as $pr) {
                    $transactions->push((object) [
                        'date' => $pr->date,
                        'created_at' => $pr->created_at,
                        'type' => 'Fabric Return',
                        'ref' => 'Return #' . ($pr->return_number ?? $pr->id),
                        'bill_no' => $pr->receipt->bill_no ?? '-',
                        'debit' => (float) $pr->total_amount,
                        'credit' => 0,
                        'description' => 'Fabric Return to Vendor',
                        'view_url' => route('admin.report.fabric_return_view', $pr->id)
                    ]);
                }
            }
    } else {
        // Generic Logic for other masters: Just fetch PaymentAdjustments
            $adjustments = \App\Models\PaymentAdjustment::where('adjustment_master_id', $master->id)
                ->where('ref_id', (string) $id) // ref_id is text: compare as text
                
                
                ->orderBy('id')
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
                
                
                ->orderBy('id')
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

                $txType = $isCredit ? 'Receipt' : ($isDebit ? 'Payment' : 'Adjustment');
                $slipUrl = !empty($p->image) ? asset($p->image) : null;
                $transactions->push((object) [
                    'date' => $p->payment_date,
                    'created_at' => $p->created_at,
                    'type' => $txType,
                    'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                    'debit' => $debit,
                    'credit' => $credit,
                    'description' => $desc . ($p->remarks ? ': ' . $p->remarks : ''),
                    'view_url' => route('admin.payment.history.show', $p->id),
                    'slip_url' => $slipUrl
                ]);
            }

            // Voucher Specific Logic
            if (strtolower($master->name) === 'contractor') {
                $contractorVouchers = \App\Models\ContractorVoucher::where('contractor_id', $id)
                    ->orderBy('id')
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
                        'view_url' => route('admin.payment.voucher.contractor.edit', ['id' => $cv->id]),
                        'slip_url' => $cv->document ? asset($cv->document) : null
                    ]);
                }
            } elseif (strtolower($master->name) === 'washing master') {
                $washingVouchers = \App\Models\WashingVoucher::where('washing_master_id', $id)
                    ->orderBy('id')
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
                        'view_url' => route('admin.payment.voucher.washing.edit', ['id' => $wv->id]),
                        'slip_url' => $wv->document ? asset($wv->document) : null
                    ]);
                }
            } elseif (strtolower($master->name) === 'consumable good') {
                $consumableVouchers = \App\Models\ConsumableVoucher::where('consumable_good_id', $id)
                    ->orderBy('id')
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
                        'view_url' => route('admin.payment.voucher.consumable.show', ['id' => $cv->id]),
                        'slip_url' => $cv->document ? asset($cv->document) : null
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
                $q
                  ;
            })
            ->orderBy('id')
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
        $openingBalance = \App\Models\MasterOpeningBalance::whereIn('master_type', $this->openingTypes($type))
            ->where('master_id', $id)
            ->where('financial_year', \App\Models\MasterOpeningBalance::openingKey())
            ->orderBy('id') // the same row as listFigures() if there were ever two
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
        $finalTransactions = collect();

        foreach ($transactions as $tx) {
            $dateTx = \Carbon\Carbon::parse($tx->date)->format('Y-m-d');
            if ($startDate && $dateTx < $startDate) {
                $openingBalAmount += ($tx->credit - $tx->debit);
                $balance = $openingBalAmount; // running balance catches up
            } else {
                if ($endDate && $dateTx > $endDate) continue;
                $balance += ($tx->credit - $tx->debit);
                $tx->bill_no = $tx->bill_no ?? '-';
                $tx->running_balance = $balance;
                $finalTransactions->push($tx);
            }
        }

        $availableTypes = $finalTransactions->pluck('type')->filter()->unique()->values()->all();
        $transactions = $this->applyLedgerFilters($finalTransactions, $request);
        $party->balance = $balance;
        $periodTotalDebit = (float)$transactions->sum('debit');
        $periodTotalCredit = (float)$transactions->sum('credit');
        $closingBalance = $balance;

        return compact(
            'party', 'transactions', 'type', 'startDate', 'endDate',
            'openingBalAmount', 'viewMode', 'groupedLedgers', 'availableTypes',
            'periodTotalDebit', 'periodTotalCredit', 'closingBalance'
        );
    }

    /**
     * Opening, period debit, period credit and closing of many parties of one type at once, for the party
     * lists, their totals and exports. The same records, signs and date rules as build(), but each kind of
     * record is added up with one grouped query instead of one full ledger per party (a list of every
     * party took 10-15 s that way). Dates are Y-m-d or null, as on the list.
     * Returns [party id => ['opening' => , 'debit' => , 'credit' => , 'closing' => ]].
     */
    public function listFigures($type, array $ids, $startDate = null, $endDate = null): array
    {
        $type = strtolower(trim((string) $type));
        $ids = array_values(array_unique(array_map('intval', $ids)));
        if (!$ids) {
            return [];
        }
        $figures = [];
        foreach ($ids as $id) {
            $figures[$id] = ['opening' => 0.0, 'debit' => 0.0, 'credit' => 0.0, 'closing' => 0.0];
        }

        // Bank / cash accounts: their own calculator, one account at a time (the party lists do not show them)
        if (in_array($type, ['bank account', 'cash master'])) {
            $request = new Request(array_filter(['start_date' => $startDate, 'end_date' => $endDate]));
            foreach ($ids as $id) {
                $data = $this->build($request, $type, $id);
                $figures[$id] = [
                    'opening' => (float) $data['openingBalAmount'],
                    'debit' => (float) collect($data['transactions'])->sum('debit'),
                    'credit' => (float) collect($data['transactions'])->sum('credit'),
                    'closing' => (float) $data['party']->balance,
                ];
            }
            return $figures;
        }

        // A sales agent's ledger is the ledger of all its shops together
        if ($type === 'sales_agent') {
            $agentOfShop = MasterCustomer::whereIn('sales_agent_id', $ids)->pluck('sales_agent_id', 'id');
            foreach ($this->listFigures('customer', $agentOfShop->keys()->all(), $startDate, $endDate) as $shopId => $shop) {
                foreach ($shop as $key => $value) {
                    $figures[$agentOfShop[$shopId]][$key] = round($figures[$agentOfShop[$shopId]][$key] + $value, 2);
                }
            }
            return $figures;
        }

        $master = $this->ledgerMaster($type);
        if (!$master) abort(404, "Invalid Ledger Type");
        $modelName = $master->model_name;
        $masterName = strtolower($master->name);

        // Adds one kind of record: before the From date it goes into the opening, inside the period into debit / credit.
        // A record without a date counts on today's date, as Carbon::parse(null) does in build().
        $add = function ($query, string $partyColumn, string $date, string $debit, string $credit) use (&$figures, $startDate, $endDate) {
            $date = "COALESCE($date, CURDATE())";
            $inPeriod = '(' . ($startDate ? "$date >= ?" : '1') . ' AND ' . ($endDate ? "$date <= ?" : '1') . ')';
            $periodBindings = array_values(array_filter([$startDate, $endDate]));
            $rows = ($query instanceof \Illuminate\Database\Eloquent\Builder ? $query->toBase() : $query)
                ->selectRaw(
                    "$partyColumn as party_id,
                    SUM(CASE WHEN " . ($startDate ? "$date < ?" : '0') . " THEN ($credit) - ($debit) ELSE 0 END) as before_net,
                    SUM(CASE WHEN $inPeriod THEN $debit ELSE 0 END) as debit,
                    SUM(CASE WHEN $inPeriod THEN $credit ELSE 0 END) as credit",
                    array_merge($startDate ? [$startDate] : [], $periodBindings, $periodBindings)
                )
                ->groupBy($partyColumn)
                ->get();
            foreach ($rows as $row) {
                $id = (int) $row->party_id;
                if (isset($figures[$id])) {
                    $figures[$id]['opening'] += (float) $row->before_net;
                    $figures[$id]['debit'] += (float) $row->debit;
                    $figures[$id]['credit'] += (float) $row->credit;
                }
            }
        };
        // payment_type 'received' / 'credit' is a credit, anything else a debit (as in build())
        $paymentCredit = "CASE WHEN BINARY payments.payment_type IN ('received', 'credit') THEN payments.amount ELSE 0 END";
        $paymentDebit = "CASE WHEN BINARY payments.payment_type IN ('received', 'credit') THEN 0 ELSE payments.amount END";
        $payments = Payment::where('party_type', $modelName)
            ->whereIn('party_id', $ids)
            ->where(function ($q) {
                $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
                  ->orWhereNull('paymentable_type');
            });

        if ($masterName === 'customer' || $masterName === 'vendor') {
            $isCustomer = $masterName === 'customer';
            $partyIdField = $isCustomer ? 'master_customer_id' : 'master_vendor_id';
            $directIdField = $isCustomer ? 'customer_id' : 'vendor_id';

            $add(AgentOrderDispatch::whereIn($partyIdField, $ids)->where('party_type', $type)->where('status', 'dispatched'),
                $partyIdField, 'DATE(dispatch_date)', 'grand_total', '0');
            if ($type === 'customer') {
                $add(\App\Models\OrderDispatch::whereIn($directIdField, $ids), $directIdField, 'DATE(dispatch_date)', 'total_amount', '0');
            }
            $add(AgentOrderReturn::join('agent_order_dispatches as d', 'd.id', '=', 'agent_order_returns.agent_order_dispatch_id')
                    ->whereIn("d.$partyIdField", $ids)->where('d.party_type', $type),
                "d.$partyIdField", 'agent_order_returns.return_date', '0', 'agent_order_returns.grand_total');
            $add($payments, 'payments.party_id', 'payments.payment_date', $paymentDebit, $paymentCredit);
            $add(\App\Models\DomesticInventoryPurchase::whereIn($directIdField, $ids),
                $directIdField, 'COALESCE(purchase_date, DATE(created_at))', '0', 'total_amount');

            if ($type === 'vendor') {
                $add(FabricReceipt::whereIn('vendor_id', $ids), 'vendor_id', 'COALESCE(time, DATE(created_at))', '0', 'total_amount');
                // a return belongs to its own vendor and to the vendor of its receipt (counted once per vendor)
                $returns = FabricReturn::whereIn('vendor_id', $ids)
                    ->select('fabric_returns.id', 'fabric_returns.vendor_id as party_id', 'fabric_returns.date', 'fabric_returns.total_amount')
                    ->union(FabricReturn::join('fabric_receipts as fr', 'fr.id', '=', 'fabric_returns.fabric_receipt_id')
                        ->whereIn('fr.vendor_id', $ids)
                        ->select('fabric_returns.id', 'fr.vendor_id as party_id', 'fabric_returns.date', 'fabric_returns.total_amount'));
                $add(DB::query()->fromSub($returns, 'r'), 'r.party_id', 'r.date', 'r.total_amount', '0');
            }
        } else {
            // ref_id is text: compare as text, as build() does
            $add(\App\Models\PaymentAdjustment::where('adjustment_master_id', $master->id)->whereIn('ref_id', array_map('strval', $ids)),
                'ref_id', 'date',
                "CASE WHEN BINARY type = 'credit' THEN 0 ELSE amount END",
                "CASE WHEN BINARY type = 'credit' THEN amount ELSE 0 END");
            $add($payments, 'payments.party_id', 'payments.payment_date', $paymentDebit, $paymentCredit);

            $vouchers = [
                'contractor' => [\App\Models\ContractorVoucher::class, 'contractor_id'],
                'washing master' => [\App\Models\WashingVoucher::class, 'washing_master_id'],
                'consumable good' => [\App\Models\ConsumableVoucher::class, 'consumable_good_id'],
            ];
            if (isset($vouchers[$masterName])) {
                [$voucherModel, $column] = $vouchers[$masterName];
                $add($voucherModel::whereIn($column, $ids), $column, 'voucher_date', '0', 'total_amount');
            }
        }

        // Journal vouchers - applicable to all masters
        $add(\App\Models\JournalVoucherItem::join('journal_vouchers as v', 'v.id', '=', 'journal_voucher_items.journal_voucher_id')
                ->whereIn('journal_voucher_items.master_type', \App\Models\AdjustmentMaster::where('model_name', $modelName)->pluck('id'))
                ->whereIn('journal_voucher_items.master_id', $ids),
            'journal_voucher_items.master_id', 'v.date',
            "CASE WHEN BINARY LOWER(journal_voucher_items.type) = 'credit' THEN 0 ELSE journal_voucher_items.amount END",
            "CASE WHEN BINARY LOWER(journal_voucher_items.type) = 'credit' THEN journal_voucher_items.amount ELSE 0 END");

        // Opening balance: one row per party (Debit = negative, Credit = positive)
        $openings = \App\Models\MasterOpeningBalance::whereIn('master_type', $this->openingTypes($type))
            ->whereIn('master_id', $ids)
            ->where('financial_year', \App\Models\MasterOpeningBalance::openingKey())
            ->orderBy('id')
            ->get()
            ->unique('master_id');
        foreach ($openings as $ob) {
            $amount = (float) $ob->amount;
            $figures[(int) $ob->master_id]['opening'] += strtolower(trim($ob->balance_type)) === 'debit' ? -$amount : $amount;
        }

        foreach ($figures as $id => $f) {
            $figures[$id] = [
                'opening' => round($f['opening'], 2),
                'debit' => round($f['debit'], 2),
                'credit' => round($f['credit'], 2),
                'closing' => round($f['opening'] + $f['credit'] - $f['debit'], 2),
            ];
        }
        return $figures;
    }

    /**
     * Sets opening_balance, period_debit, period_credit and balance (closing) on every party of a list,
     * with listFigures() per party type (each party has ->party_type and ->id).
     */
    public function fillListFigures($parties, $startDate = null, $endDate = null)
    {
        foreach (collect($parties)->groupBy('party_type') as $type => $group) {
            $figures = $this->listFigures($type, $group->pluck('id')->all(), $startDate, $endDate);
            foreach ($group as $party) {
                $f = $figures[(int) $party->id];
                $party->opening_balance = $f['opening'];
                $party->period_debit = $f['debit'];
                $party->period_credit = $f['credit'];
                $party->balance = $f['closing'];
            }
        }
        return $parties;
    }

    /**
     * Rows to add up for the totals of a party list (each with opening_balance, period_debit, period_credit
     * and balance, as set by fillListFigures). One type: the rows as they are.
     * "All types": a sales-agent row is the total of its shops. A shop listed on its own is counted in its own
     * row; the agent stands for its other shops, added shop by shop and only when they pass the list's balance
     * filters ($keep), so a "Dr only" list adds only shops in Dr.
     */
    public function totalsRows(Collection $parties, bool $allTypes, $startDate = null, $endDate = null, ?callable $keep = null): Collection
    {
        if (!$allTypes) {
            return $parties->values();
        }
        $rows = $parties->where('party_type', '!=', 'sales_agent')->values();
        foreach ($this->agentOtherShops($parties, $startDate, $endDate, $keep) as $shops) {
            foreach ($shops as $shop) {
                $rows->push($shop);
            }
        }
        return $rows;
    }

    /**
     * [sales agent id => shop rows] for the agent rows of a list: the agent's shops that are not rows of the
     * list themselves, with their figures for the period, kept only when they pass $keep (the balance filters).
     */
    public function agentOtherShops(Collection $parties, $startDate = null, $endDate = null, ?callable $keep = null): array
    {
        $agentIds = $parties->where('party_type', 'sales_agent')->pluck('id')->all();
        if (!$agentIds) {
            return [];
        }
        $listed = $parties->where('party_type', 'customer')->pluck('id')->all();
        $shops = MasterCustomer::whereIn('sales_agent_id', $agentIds)->whereNotIn('id', $listed)->orderBy('id')
            ->get(['id', 'name', 'sales_agent_id', 'status']);
        $shops->each(function ($shop) {
            $shop->party_type = 'customer';
        });
        $this->fillListFigures($shops, $startDate, $endDate);

        $otherShops = [];
        foreach ($shops as $shop) {
            if (!$keep || $keep($shop)) {
                $otherShops[$shop->sales_agent_id][] = $shop;
            }
        }
        return $otherShops;
    }

    /** The master of a ledger type (the type in the URL is the master name), the same for a statement and a list. */
    private function ledgerMaster(string $type)
    {
        $master = \App\Models\AdjustmentMaster::where('name', 'LIKE', $type)->first();
        if (!$master) {
            // Fallback for direct names
            if ($type === 'vendor') $master = \App\Models\AdjustmentMaster::where('name', 'Vendor')->first();
            elseif ($type === 'customer') $master = \App\Models\AdjustmentMaster::where('name', 'Customer')->first();
        }
        return $master;
    }

    /** master_type values a party's opening balance row can be saved under for a ledger type. */
    private function openingTypes(string $type): array
    {
        $lookupType = str_replace(' ', '_', strtolower($type));
        // Specific mapping overrides
        if ($lookupType === 'machinery_account') $lookupType = 'machinery';
        if ($lookupType === 'loan_account') $lookupType = 'loan';
        if ($lookupType === 'hulayati_master') $lookupType = 'hulayati';
        if ($lookupType === 'factory_head_master') $lookupType = 'factory_head';
        if ($lookupType === 'salary_account') $lookupType = 'salary';     // SalaryService saves 'salary'
        if ($lookupType === 'discount_account') $lookupType = 'discount'; // DiscountService saves 'discount'

        return [$type, $lookupType, str_replace('_', ' ', $lookupType)];
    }

    private function applyLedgerFilters($transactions, Request $request)
    {
        $transactionType = $request->query('transaction_type');
        $adjustmentType = $request->query('adjustment_type');
        $refNo = $request->query('ref_no');
        $debitValue = $request->query('debit_value');
        $creditValue = $request->query('credit_value');
        $hasSlip = $request->query('has_slip');

        if (!$transactionType && !$adjustmentType && !$refNo && $debitValue === null && $creditValue === null && !$hasSlip) {
            return $transactions;
        }

        return $transactions->filter(function ($tx) use ($transactionType, $adjustmentType, $refNo, $debitValue, $creditValue, $hasSlip) {
            // 0. Slip attachment filter
            if ($hasSlip && $hasSlip !== 'all') {
                if ($hasSlip === 'yes' && empty($tx->slip_url)) {
                    return false;
                }
                if ($hasSlip === 'no' && !empty($tx->slip_url)) {
                    return false;
                }
            }
            // 1. Transaction Type filter
            if ($transactionType && $transactionType !== 'all') {
                $matchType = false;
                if (strcasecmp($tx->type, $transactionType) === 0) {
                    $matchType = true;
                } elseif (strcasecmp($transactionType, 'payment') === 0 && (strcasecmp($tx->type, 'payment') === 0 || $tx->debit > 0)) {
                    $matchType = true;
                } elseif (strcasecmp($transactionType, 'receipt') === 0 && (strcasecmp($tx->type, 'receipt') === 0 || $tx->credit > 0)) {
                    $matchType = true;
                } elseif (strcasecmp($transactionType, 'adjustment') === 0 && (stripos($tx->type, 'adjustment') !== false || stripos($tx->ref ?? '', 'adj') !== false || stripos($tx->description ?? '', 'adjustment') !== false)) {
                    $matchType = true;
                }
                if (!$matchType) return false;
            }

            // 2. Adjustment filter: 1. Payment (Debit / Paid), 2. Receipt (Credit / Received)
            if ($adjustmentType && $adjustmentType !== 'all') {
                if ($adjustmentType === 'payment') {
                    if (!($tx->debit > 0)) return false;
                } elseif ($adjustmentType === 'receipt') {
                    if (!($tx->credit > 0)) return false;
                }
            }

            // 3. Ref No filter
            if ($refNo && trim($refNo) !== '') {
                $refSearch = trim($refNo);
                $txRef = (string)($tx->ref ?? '');
                $txBill = (string)($tx->bill_no ?? '');
                if (stripos($txRef, $refSearch) === false && stripos((string)($tx->description ?? ''), $refSearch) === false && stripos($txBill, $refSearch) === false) {
                    return false;
                }
            }

            // 4. Debit Value filter
            if ($debitValue !== null && trim($debitValue) !== '') {
                $val = floatval(preg_replace('/[^0-9.]/', '', $debitValue));
                if ($val > 0) {
                    $exact = abs($tx->debit - $val) < 0.01;
                    $partial = str_contains((string)$tx->debit, trim($debitValue));
                    if (!$exact && !$partial) return false;
                }
            }

            // 5. Credit Value filter
            if ($creditValue !== null && trim($creditValue) !== '') {
                $val = floatval(preg_replace('/[^0-9.]/', '', $creditValue));
                if ($val > 0) {
                    $exact = abs($tx->credit - $val) < 0.01;
                    $partial = str_contains((string)$tx->credit, trim($creditValue));
                    if (!$exact && !$partial) return false;
                }
            }

            return true;
        })->values();
    }
}
