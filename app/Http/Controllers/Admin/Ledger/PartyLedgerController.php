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
        $type = $request->query('type'); // 'vendor' or 'customer'

        $parties = collect();

        if (!$type || $type === 'vendor') {
            $vendors = Vendor::where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->get()
                ->map(function ($v) {
                    $v->party_type = 'vendor';
                    return $v;
                });
            $parties = $parties->concat($vendors);
        }

        if (!$type || $type === 'customer') {
            $customers = MasterCustomer::where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->get()
                ->map(function ($c) {
                    $c->party_type = 'customer';
                    return $c;
                });
            $parties = $parties->concat($customers);
        }

        // Sort by name
        $parties = $parties->sortBy('name');

        return view('admin.ledger.party.index', compact('parties'));
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

        if ($type === 'vendor') {
            $party = Vendor::findOrFail($id);
        } else {
            $party = MasterCustomer::findOrFail($id);
        }

        $transactions = collect();

        if ($type === 'customer') {
            $partyModel = 'App\Models\MasterCustomer';
            $partyIdField = 'master_customer_id';
            $directIdField = 'customer_id';
        } else {
            $partyModel = 'App\Models\Vendor';
            $partyIdField = 'master_vendor_id';
            $directIdField = 'vendor_id';
        }

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
                'description' => 'Sales Dispatch: ' . ($d->remark ?? '-')
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
                    'description' => 'Regular Order Dispatch'
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
                'description' => 'Sales Return'
            ]);
        }

        // 4. Payments
        $payments = Payment::where('party_id', $id)
            ->where('party_type', $partyModel)
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
                'description' => $desc . ($p->remarks ? ': ' . $p->remarks : '')
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
                'description' => 'Inventory Purchase: ' . ($ip->remarks ?? '-')
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
                    'description' => 'Fabric Inward (Shipment: ' . ($r->shipment_id ?? '-') . ')'
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
                    'description' => 'Fabric Return to Vendor'
                ]);
            }
        }

        // 8. Opening Balance
        $openingBalance = \App\Models\MasterOpeningBalance::where('master_type', $type)
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
            $balance += ($tx->debit - $tx->credit);
            $tx->running_balance = $balance;
        }

        return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount');
    }
}
