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

        // Add Inwards
        foreach ($inwards as $in) {
            $transactions->push((object)[
                'date' => $in->created_at,
                'type' => 'Inward',
                'party' => $in->fabric_receipt?->vendor?->name ?? 'Direct Purchase',
                'particulars' => 'Receipt: Roll ' . $in->roll_number . ' (Shp: ' . ($in->fabric_receipt?->shipment_id ?? '-') . ')',
                'inward' => (float)$in->meter,
                'outward' => 0,
            ]);
        }

        // Add reconciliation adjustments for each roll (if any)
        // We do this roll-by-roll to ensure local accuracy
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
                    // Only show in transactions if within date range (use updated_at as proxy for usage date)
                    $showAdj = true;
                    if ($startDate && $roll->updated_at->format('Y-m-d') < $startDate) $showAdj = false;
                    if ($endDate && $roll->updated_at->format('Y-m-d') > $endDate) $showAdj = false;
                    if ($vendorId || $customerId) $showAdj = false; // Adjustments are internal

                    if ($showAdj) {
                        $transactions->push((object)[
                            'date' => $roll->updated_at,
                            'type' => 'Outward',
                            'party' => 'Internal Production',
                            'particulars' => 'Usage: Roll ' . $roll->roll_number . ' (Cutting/Sampling)',
                            'inward' => 0,
                            'outward' => $unaccounted,
                        ]);
                    }
                }
            }
        }

        // Add recorded outflows
        foreach ($salesOutwards as $sale) {
            $transactions->push((object)[
                'date' => $sale->created_at,
                'type' => 'Outward',
                'party' => $sale->order?->party?->name ?? 'Customer',
                'particulars' => 'Sale: Order ' . ($sale->order?->sku ?? '-'),
                'inward' => 0,
                'outward' => (float)$sale->meter,
            ]);
        }

        foreach ($returnsOutwards as $ret) {
            $transactions->push((object)[
                'date' => $ret->created_at,
                'type' => 'Outward',
                'party' => $ret->fabric_return?->receipt?->vendor?->name ?? 'Vendor',
                'particulars' => 'Return: Roll ' . ($ret->receipt_detail?->roll_number ?? '-') . ' (Ret: ' . ($ret->fabric_return?->return_number ?? '-') . ')',
                'inward' => 0,
                'outward' => (float)$ret->return_meter,
            ]);
        }

        foreach ($productionOutwards as $prod) {
            $transactions->push((object)[
                'date' => $prod->created_at,
                'type' => 'Outward',
                'party' => $prod->stageMasterUnit?->name ?? 'Internal Unit',
                'particulars' => 'Production: Lot ' . ($prod->lot_no ?? '-') . ' (Ord: ' . ($prod->order_no ?? '-') . ')',
                'inward' => 0,
                'outward' => (float)$prod->meter,
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
