<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderLot;
use App\Models\FabricRollAssigning;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderGodamStageTransaction;
use App\Models\ProductionGoods;
use App\Models\DomesticInventoryHistory;
use App\Models\OrderPrintingToStichingTransaction;
use Carbon\Carbon;

class LotLedgerController extends Controller
{
    public function index(Request $request)
    {
        $searchLot = $request->search;

        $query = OrderLot::with([
            'orderMain.customer',
            'orderProductSet.fabric',
            'orderProductSet.master_design_pattern',
            'orderProductSet.master_product_fitting',
            'orderProductSet.colors'
        ])
        ->when($searchLot, function ($q) use ($searchLot) {
            $q->where('lot_no', 'like', "%{$searchLot}%")
              ->orWhereHas('orderMain', function($q2) use ($searchLot) {
                  $q2->where('sku', 'like', "%{$searchLot}%");
              });
        })
        ->orderBy('id', 'desc');

        $lots = $query->paginate(20)->withQueryString();

        $lots->through(function ($lot) {
            $quantity = FabricRollAssigning::where('lot_no', $lot->lot_no)
                ->withSum('fabricRollAssigningsDetail as total', 'quantity')
                ->get()
                ->sum('total');
            $lot->lot_quantity = $quantity ?? 0;
            $lot->last_current_stage = getLastCurrentStage($lot->lot_no);
            return $lot;
        });

        return view('admin.ledger.lot.index', compact('lots'));
    }

    public function show(Request $request, $lot_no)
    {
        $lot = OrderLot::with([
            'orderMain.customer',
            'orderProductSet.fabric',
            'orderProductSet.master_design_pattern',
            'orderProductSet.master_product_fitting',
            'orderProductSet.colors'
        ])->where('lot_no', $lot_no)->firstOrFail();

        $lot->last_current_stage = getLastCurrentStage($lot_no);

        $transactions = collect();

        // 1. Initial Inward (Cutting / Assignment)
        $initialRolls = FabricRollAssigning::where('lot_no', $lot_no)
                ->withSum('fabricRollAssigningsDetail as total', 'quantity')
                ->get();
        $initialQty = $initialRolls->sum('total');
        
        $firstRollDate = $initialRolls->min('created_at') ?? $lot->created_at;

        if ($initialQty > 0) {
            $transactions->push((object)[
                'date' => Carbon::parse($firstRollDate),
                'type' => 'Inward',
                'particulars' => 'Initial Lot Assignment (Cutting)',
                'inward' => $initialQty,
                'outward' => 0,
            ]);
        }

        // 2. Fetch Process Transactions
        $stageTxs = OrderStageTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();
        $printTxs = OrderPrintingStageTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();
        $godamTxs = OrderGodamStageTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();
        $stitchTxs = OrderPrintingToStichingTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();

        $allTxs = $stageTxs->concat($printTxs)->concat($godamTxs)->concat($stitchTxs);

        foreach ($allTxs as $tx) {
            $qty = $tx->quantity;
            if ($qty > 0) {
                // Ensure we handle from_stage and to_stage correctly across different models
                $fromObj = method_exists($tx, 'fromStage') ? $tx->fromStage : $tx->from_stage;
                $toObj = method_exists($tx, 'toStage') ? $tx->toStage : $tx->to_stage;
                
                $fromName = $fromObj ? $fromObj->name : 'N/A';
                $toName = $toObj ? $toObj->name : 'N/A';
                $unitName = $tx->getToUnitMaster ? $tx->getToUnitMaster->name : '';

                $status = 'processing';
                if ($toObj) {
                    $d = getLotDetails($lot_no, $toObj->id);
                    if ($d && isset($d['quantity'])) {
                        $remaining = (int) $d['remaining_quantity'];
                        $total = (int) $d['quantity'];
                        if ($total === 0) {
                            $status = 'pending';
                        } elseif ($remaining === 0) {
                            $status = 'completed';
                        } else {
                            $status = 'progress';
                        }
                    }
                }

                $transactions->push((object)[
                    'date' => Carbon::parse($tx->created_at),
                    'type' => 'Process',
                    'status' => $status,
                    'particulars' => "Moved from {$fromName} to {$toName}" . ($unitName ? " ({$unitName})" : ""),
                    'inward' => 0,
                    'outward' => 0,
                    'process_qty' => $qty,
                ]);
            }
        }

        // 3. Finished Goods / Packed
        // We can find packing mains associated with slips from these transactions
        $slipIds = $allTxs->pluck('production_slip_digitization_id')->filter()->unique();
        $packingMains = \App\Models\PackingMain::whereIn('slip_id', $slipIds)->get();
        
        foreach ($packingMains as $pm) {
            $packedQty = \App\Models\PackingBox::where('packing_main_id', $pm->id)->sum('items_sum_quantity'); // This might need a join or items sum
            $packedQty = \App\Models\PackingItem::where('packing_main_id', $pm->id)->sum('quantity');
            if ($packedQty > 0) {
                $transactions->push((object)[
                    'date' => Carbon::parse($pm->packing_date ?? $pm->created_at),
                    'type' => 'Outward',
                    'particulars' => 'Packed / Sent to Finished Goods',
                    'inward' => 0,
                    'outward' => $packedQty,
                ]);
            }
        }

        // Sort by date
        $transactions = $transactions->sortBy(function($t) {
            return $t->date->timestamp;
        })->values();

        // Calculate running balance
        $balance = 0;
        foreach ($transactions as $tx) {
            if ($tx->type === 'Inward') {
                $balance += $tx->inward;
            } elseif ($tx->type === 'Outward') {
                $balance -= $tx->outward;
            }
            $tx->running_balance = $balance;
        }

        return view('admin.ledger.lot.show', compact('lot', 'transactions', 'initialQty'));
    }
}
