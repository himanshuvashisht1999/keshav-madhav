<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\FabricReceiptDetail;
use App\Models\FabricRollAssigning;
use App\Models\AgentOrderFabricItem;
use App\Models\FabricReturnDetail;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use DB;

class FabricLedgerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $fabrics = Fabric::with(['fabric_vendor'])
            ->where('status', 1)
            ->when($search, function($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->paginate(15)
            ->withQueryString();

        foreach ($fabrics as $fabric) {
            // Total Inward (Status > 0 means it arrived)
            $fabric->total_inward = FabricReceiptDetail::where('fabric_id', $fabric->id)
                ->where('status', '>', 0)
                ->sum('meter');
            
            // Current Balance (Sum of remaining for all active/partially used rolls)
            $fabric->current_balance = FabricReceiptDetail::where('fabric_id', $fabric->id)
                ->where('status', '>', 0)
                ->sum('remaining_quantity');
                
            // Total Outward is the difference (Sales + Production + Returns + Adjustments for these specific rolls)
            $fabric->total_outward = $fabric->total_inward - $fabric->current_balance;
        }

        return view('admin.ledger.fabric.index', compact('fabrics'));
    }

    public function show(Request $request, $id)
    {
        $fabric = Fabric::findOrFail($id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $vendorId = $request->query('vendor_id');
        $customerId = $request->query('customer_id');

        $vendors = Vendor::orderBy('name')->get();
        $customers = MasterCustomer::orderBy('name')->get();

        // 1. Fetch relevant Inward rolls (Status > 0)
        $receivedRollsQuery = FabricReceiptDetail::where('fabric_id', $id)
            ->where('status', '>', 0);
        
        $receivedRollIds = (clone $receivedRollsQuery)->pluck('id');
        $receivedRollNumbers = (clone $receivedRollsQuery)->pluck('roll_number');

        $inwards = FabricReceiptDetail::with(['fabric_receipt.vendor'])
            ->whereIn('id', $receivedRollIds)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($vendorId, function($q) use ($vendorId) {
                $q->whereHas('fabric_receipt', fn($sq) => $sq->where('vendor_id', $vendorId));
            })
            ->get();

        // 2. Fetch recorded Outwards (Only for the received rolls)
        
        // A. Sales (Agent Orders)
        $salesOutwards = AgentOrderFabricItem::with(['order.party', 'roll'])
            ->whereIn('fabric_receipt_detail_id', $receivedRollIds)
            ->where('status', 'dispatched')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($customerId, function($q) use ($customerId) {
                $q->whereHas('order', fn($sq) => $sq->where('party_id', $customerId));
            })
            ->get();

        // B. Returns
        $returnsOutwards = FabricReturnDetail::with(['fabric_return.receipt.vendor', 'receipt_detail'])
            ->whereIn('fabric_receipt_detail_id', $receivedRollIds)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($vendorId, function($q) use ($vendorId) {
                $q->whereHas('return.receipt', fn($sq) => $sq->where('vendor_id', $vendorId));
            })
            ->get();

        // C. Production (Cutting)
        $productionOutwards = FabricRollAssigning::with(['orderProductSet', 'stageMasterUnit'])
            ->whereIn('roll_no', $receivedRollNumbers->isEmpty() ? ['-'] : $receivedRollNumbers)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        // 3. Unify Transactions
        $transactions = collect();

        // Add Grouped Inwards (Shipments)
        $groupedInwards = $inwards->groupBy('fabric_receipt_id');
        foreach ($groupedInwards as $receiptId => $rolls) {
            $first = $rolls->first();
            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Inward',
                'party' => $first->fabric_receipt?->vendor?->name ?? 'Direct Purchase',
                'particulars' => 'Receipt (Shipment: ' . ($first->fabric_receipt?->shipment_id ?? '-') . ')',
                'inward' => (float)$rolls->sum('meter'),
                'outward' => 0,
                'rolls' => $rolls->map(fn($r) => ['number' => $r->roll_number, 'meter' => $r->meter])->values()
            ]);
        }

        // Add reconciliation adjustments for each roll (if any)
        $allInwardRollsForCalc = FabricReceiptDetail::whereIn('id', $receivedRollIds)->get();
        foreach ($allInwardRollsForCalc as $roll) {
            $totalUsed = (float)$roll->meter - (float)$roll->remaining_quantity;
            if ($totalUsed > 0.001) {
                $recordedOutflow = 0;
                $recordedOutflow += (float)$salesOutwards->where('fabric_receipt_detail_id', $roll->id)->sum('meter');
                $recordedOutflow += (float)$returnsOutwards->where('fabric_receipt_detail_id', $roll->id)->sum('return_meter');
                $recordedOutflow += (float)$productionOutwards->where('roll_no', $roll->roll_number)->sum('meter');

                $unaccounted = $totalUsed - $recordedOutflow;
                if ($unaccounted > 0.01) {
                    $showAdj = true;
                    if ($startDate && $roll->updated_at->format('Y-m-d') < $startDate) $showAdj = false;
                    if ($endDate && $roll->updated_at->format('Y-m-d') > $endDate) $showAdj = false;
                    if ($vendorId || $customerId) $showAdj = false;

                    if ($showAdj) {
                        $transactions->push((object)[
                            'date' => $roll->updated_at,
                            'type' => 'Outward',
                            'party' => 'Internal Production',
                            'particulars' => 'Internal Usage (Roll ' . $roll->roll_number . ')',
                            'inward' => 0,
                            'outward' => $unaccounted,
                            'rolls' => [['number' => $roll->roll_number, 'meter' => $unaccounted]]
                        ]);
                    }
                }
            }
        }

        // Add Grouped Sales
        $groupedSales = $salesOutwards->groupBy('agent_order_id');
        foreach ($groupedSales as $orderId => $items) {
            $first = $items->first();
            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->order?->party?->name ?? 'Customer',
                'particulars' => 'Sale: Order ' . ($first->order?->sku ?? '-'),
                'inward' => 0,
                'outward' => (float)$items->sum('meter'),
                'rolls' => $items->map(fn($s) => ['number' => $s->roll?->roll_number ?? '-', 'meter' => $s->meter])->values()
            ]);
        }

        // Add Grouped Returns
        $groupedReturns = $returnsOutwards->groupBy('fabric_return_id');
        foreach ($groupedReturns as $returnId => $details) {
            $first = $details->first();
            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->fabric_return?->receipt?->vendor?->name ?? 'Vendor',
                'particulars' => 'Return: ' . ($first->fabric_return?->return_number ?? '-'),
                'inward' => 0,
                'outward' => (float)$details->sum('return_meter'),
                'rolls' => $details->map(fn($r) => ['number' => $r->receipt_detail?->roll_number ?? '-', 'meter' => $r->return_meter])->values()
            ]);
        }

        // Add Grouped Production
        $groupedProduction = $productionOutwards->groupBy(function($item) {
            return $item->order_no . '|' . $item->lot_no;
        });
        foreach ($groupedProduction as $key => $items) {
            $first = $items->first();
            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->stageMasterUnit?->name ?? 'Internal Unit',
                'particulars' => 'Production: Lot ' . ($first->lot_no ?? '-') . ' (Ord: ' . ($first->order_no ?? '-') . ')',
                'inward' => 0,
                'outward' => (float)$items->sum('meter'),
                'rolls' => $items->map(fn($p) => ['number' => $p->roll_no ?? '-', 'meter' => $p->meter])->values()
            ]);
        }


        // 4. Final Sort and Balance Calculation
        $transactions = $transactions->sortBy('date')->values();
        
        $balance = 0;
        foreach ($transactions as $tx) {
            $balance += ($tx->inward - $tx->outward);
            $tx->running_balance = $balance;
        }

        return view('admin.ledger.fabric.show', compact('fabric', 'transactions', 'startDate', 'endDate', 'vendors', 'customers', 'vendorId', 'customerId'));
    }
}
