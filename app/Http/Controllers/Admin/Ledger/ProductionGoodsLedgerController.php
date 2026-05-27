<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionGoods;
use App\Models\DomesticInventoryHistory;
use App\Models\DomesticInventory;
use DB;

class ProductionGoodsLedgerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');

        $goods = ProductionGoods::with('series')
            ->where('status', 1)
            ->when($search, function ($q) use ($search) {
                $q->where('name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('design_number', 'LIKE', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        foreach ($goods as $good) {
            // Inward: From DomesticInventoryHistory where type is 'creation' or 'attribute_change' (new_product_id)
            $good->total_inward = DomesticInventoryHistory::where(function($q) use ($good) {
                    $q->where('type', 'creation')
                      ->where('new_product_id', $good->id);
                })
                ->orWhere(function($q) use ($good) {
                    $q->where('type', 'attribute_change')
                      ->where('new_product_id', $good->id);
                })
                ->sum('box_quantity');

            // Current Balance: From current DomesticInventory
            $good->current_balance = DomesticInventory::where('product_id', $good->id)
                ->sum('total_boxes');

            // Total Outward: From DomesticInventoryHistory where type is 'stock_consume', 'deletion', or 'attribute_change' (old_product_id)
            $good->total_outward = DomesticInventoryHistory::where(function($q) use ($good) {
                    $q->whereIn('type', ['stock_consume', 'deletion'])
                      ->where('old_product_id', $good->id);
                })
                ->orWhere(function($q) use ($good) {
                    $q->where('type', 'attribute_change')
                      ->where('old_product_id', $good->id);
                })
                ->sum('box_quantity');
        }

        return view('admin.ledger.production_goods.index', compact('goods', 'search'));
    }

    public function show(Request $request, $id)
    {
        $good = ProductionGoods::with('series')->findOrFail($id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Fetch all related history records
        $histories = DomesticInventoryHistory::where('old_product_id', $id)
            ->orWhere('new_product_id', $id)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'asc')
            ->get();

        $transactions = collect();

        foreach ($histories as $history) {
            // Inward logic
            if ($history->new_product_id == $id && in_array($history->type, ['creation', 'attribute_change'])) {
                $transactions->push((object)[
                    'date' => $history->created_at,
                    'type' => 'Inward',
                    'particulars' => $history->type === 'creation' ? 'Production / Stock In' : 'Attribute Change (In)',
                    'inward' => (int)$history->box_quantity,
                    'outward' => 0,
                    'remarks' => $history->remarks
                ]);
            }
            // Outward logic
            if ($history->old_product_id == $id && in_array($history->type, ['stock_consume', 'deletion', 'attribute_change'])) {
                $particulars = 'Outward / Stock Consume';
                if ($history->type === 'deletion') $particulars = 'Stock Deletion';
                if ($history->type === 'attribute_change') $particulars = 'Attribute Change (Out)';

                // If it's stock_consume, it might be related to an order. We don't have direct linking in history, 
                // but we label it based on type.
                $transactions->push((object)[
                    'date' => $history->created_at,
                    'type' => 'Outward',
                    'particulars' => $particulars,
                    'inward' => 0,
                    'outward' => (int)$history->box_quantity,
                    'remarks' => $history->remarks
                ]);
            }
        }

        // Calculate Opening Balance
        $openingBalanceAmount = 0;
        if ($startDate) {
            $inwardBefore = DomesticInventoryHistory::where(function($q) use ($id) {
                    $q->where('new_product_id', $id)
                      ->whereIn('type', ['creation', 'attribute_change']);
                })->whereDate('created_at', '<', $startDate)->sum('box_quantity');

            $outwardBefore = DomesticInventoryHistory::where(function($q) use ($id) {
                    $q->where('old_product_id', $id)
                      ->whereIn('type', ['stock_consume', 'deletion', 'attribute_change']);
                })->whereDate('created_at', '<', $startDate)->sum('box_quantity');

            $openingBalanceAmount = $inwardBefore - $outwardBefore;
        }

        // Running balance calculation
        $transactions = $transactions->sortBy('date')->values();
        $balance = $openingBalanceAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->inward - $tx->outward);
            $tx->running_balance = $balance;
        }

        return view('admin.ledger.production_goods.show', compact('good', 'transactions', 'startDate', 'endDate', 'openingBalanceAmount'));
    }
}
