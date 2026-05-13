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
            ->when($search, function ($q) use ($search) {
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
            ->when($vendorId, function ($q) use ($vendorId) {
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
            ->when($customerId, function ($q) use ($customerId) {
                $q->whereHas('order', fn($sq) => $sq->where('party_id', $customerId));
            })
            ->get();

        // B. Returns
        $returnsOutwards = FabricReturnDetail::with(['fabric_return.receipt.vendor', 'receipt_detail'])
            ->whereIn('fabric_receipt_detail_id', $receivedRollIds)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($vendorId, function ($q) use ($vendorId) {
                $q->whereHas('return.receipt', fn($sq) => $sq->where('vendor_id', $vendorId));
            })
            ->get();

        // C. Production (Cutting)
        $productionOutwards = FabricRollAssigning::with(['orderProductSet', 'stageMasterUnit'])
            ->whereIn('roll_no', $receivedRollNumbers->isEmpty() ? ['-'] : $receivedRollNumbers)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        // 3. Unify Transactions with Physical Attribution Logic
        $transactions = collect();

        // A. Add Grouped Inwards (Shipments)
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

        // B. Prepare Attribution Pool for Physical Usage
        // We use the difference between meter and remaining_quantity as the "True" outward meter
        $allInwardRolls = FabricReceiptDetail::where('fabric_id', $id)
            ->where('status', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $attributionPool = [];
        foreach ($allInwardRolls as $roll) {
            $attributionPool[$roll->roll_number][] = (object)[
                'id' => $roll->id,
                'available_to_attribute' => (float)$roll->meter - (float)$roll->remaining_quantity
            ];
        }

        // Helper to attribute meter from the physical pool
        $attributeFromPool = function($rollNo, $requestedMeter) use (&$attributionPool) {
            if (!isset($attributionPool[$rollNo])) return 0;
            $attributed = 0;
            foreach ($attributionPool[$rollNo] as $poolItem) {
                if ($poolItem->available_to_attribute > 0.001) {
                    $take = min($requestedMeter - $attributed, $poolItem->available_to_attribute);
                    $attributed += $take;
                    $poolItem->available_to_attribute -= $take;
                    if ($attributed >= $requestedMeter) break;
                }
            }
            return $attributed;
        };

        // C. Add Grouped Sales (Attribute from physical usage)
        $groupedSales = $salesOutwards->groupBy('agent_order_id');
        foreach ($groupedSales as $orderId => $items) {
            $first = $items->first();
            $actualOutward = 0;
            foreach($items as $item) {
                $actualOutward += $attributeFromPool($item->roll?->roll_number, $item->meter);
            }

            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->order?->party?->name ?? 'Customer',
                'particulars' => 'Sale: Order ' . ($first->order?->sku ?? '-'),
                'inward' => 0,
                'outward' => $actualOutward,
                'rolls' => $items->map(fn($s) => ['number' => $s->roll?->roll_number ?? '-', 'meter' => $s->meter])->values()
            ]);
        }

        // D. Add Grouped Returns
        $groupedReturns = $returnsOutwards->groupBy('fabric_return_id');
        foreach ($groupedReturns as $returnId => $details) {
            $first = $details->first();
            $actualOutward = 0;
            foreach($details as $d) {
                $actualOutward += $attributeFromPool($d->receipt_detail?->roll_number, $d->return_meter);
            }

            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->fabric_return?->receipt?->vendor?->name ?? 'Vendor',
                'particulars' => 'Return: ' . ($first->fabric_return?->return_number ?? '-'),
                'inward' => 0,
                'outward' => $actualOutward,
                'rolls' => $details->map(fn($r) => ['number' => $r->receipt_detail?->roll_number ?? '-', 'meter' => $r->return_meter])->values()
            ]);
        }

        // E. Add Grouped Production
        $groupedProduction = $productionOutwards->groupBy(function($item) {
            return $item->order_no . '|' . $item->lot_no;
        });
        foreach ($groupedProduction as $key => $items) {
            $first = $items->first();
            $actualOutward = 0;
            foreach($items as $p) {
                $actualOutward += $attributeFromPool($p->roll_no, $p->meter);
            }

            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->stageMasterUnit?->name ?? 'Internal Unit',
                'particulars' => 'Production: Lot ' . ($first->lot_no ?? '-') . ' (Ord: ' . ($first->order_no ?? '-') . ')',
                'inward' => 0,
                'outward' => $actualOutward,
                'rolls' => $items->map(fn($p) => ['number' => $p->roll_no ?? '-', 'meter' => $p->meter])->values()
            ]);
        }

        // F. Add reconciliation for remaining physical usage NOT captured in records
        foreach ($attributionPool as $rollNo => $poolItems) {
            foreach ($poolItems as $item) {
                if ($item->available_to_attribute > 0.01) {
                    $roll = FabricReceiptDetail::find($item->id);
                    $transactions->push((object)[
                        'date' => $roll->updated_at,
                        'type' => 'Outward',
                        'party' => 'Internal Usage',
                        'particulars' => 'Unrecorded Physical Usage (Roll ' . $rollNo . ')',
                        'inward' => 0,
                        'outward' => $item->available_to_attribute,
                        'rolls' => [['number' => $rollNo, 'meter' => $item->available_to_attribute]]
                    ]);
                }
            }
        }

        // 4. Calculate Opening Balance (Brought Forward)
        $openingBalanceAmount = 0;
        if ($startDate) {
            $inwardBefore = FabricReceiptDetail::where('fabric_id', $id)->where('status', '>', 0)->whereDate('created_at', '<', $startDate)->sum('meter');
            $salesBefore = AgentOrderFabricItem::whereHas('roll', fn($q) => $q->where('fabric_id', $id))->where('status', 'dispatched')->whereDate('created_at', '<', $startDate)->sum('meter');
            $returnsBefore = FabricReturnDetail::whereHas('receipt_detail', fn($q) => $q->where('fabric_id', $id))->whereDate('created_at', '<', $startDate)->sum('return_meter');
            $productionBefore = FabricRollAssigning::whereIn('roll_no', $receivedRollNumbers->isEmpty() ? ['-'] : $receivedRollNumbers)->whereHas('orderProductSet', fn($q) => $q->whereRaw("FIND_IN_SET(?, fabric_id)", [$id]))->whereDate('created_at', '<', $startDate)->sum('meter');
            $openingBalanceAmount = (float)$inwardBefore - (float)$salesBefore - (float)$returnsBefore - (float)$productionBefore;
        }

        // 5. Final Sort and Balance Calculation
        $transactions = $transactions->sortBy('date')->values();
        
        $balance = $openingBalanceAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->inward - $tx->outward);
            $tx->running_balance = $balance;
        }

        return view('admin.ledger.fabric.show', compact('fabric', 'transactions', 'startDate', 'endDate', 'vendors', 'customers', 'vendorId', 'customerId', 'openingBalanceAmount'));
    }
}
