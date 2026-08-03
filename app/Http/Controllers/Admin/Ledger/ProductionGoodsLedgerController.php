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
            // Inward: From DomesticInventoryHistory where new_product_id = good->id
            $good->total_inward = DomesticInventoryHistory::where('new_product_id', $good->id)
                ->where('type', '!=', 'transfer')
                ->sum('box_quantity');

            // Total Outward: From DomesticInventoryHistory where old_product_id = good->id (excluding transfer and stock_consume)
            $historyOutward = DomesticInventoryHistory::where('old_product_id', $good->id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->sum('box_quantity');

            // Outward from orders (whether dispatched or not)
            $orderOutward = DB::table('agent_order_items')
                ->where('product_id', $good->id)
                ->sum('box_qty');

            $good->total_outward = $historyOutward + $orderOutward;

            // Current Balance mathematically based on Ledger
            $good->current_balance = $good->total_inward - $good->total_outward;
        }

        return view('admin.ledger.production_goods.index', compact('goods', 'search'));
    }

    public function show(Request $request, $id)
    {
        $good = ProductionGoods::with('series')->findOrFail($id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Fetch all related history records (excluding transfer and stock_consume)
        $histories = DomesticInventoryHistory::where(function($q) use ($id) {
                $q->where('old_product_id', $id)
                  ->orWhere('new_product_id', $id);
            })
            ->whereNotIn('type', ['transfer', 'stock_consume'])
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->orderBy('created_at', 'asc')
            ->get();

        $transactions = collect();

        foreach ($histories as $history) {
            // Inward logic
            if ($history->new_product_id == $id) {
                $particulars = 'Inward / ' . ucfirst(str_replace('_', ' ', $history->type));
                if ($history->type === 'creation') $particulars = 'Production / Stock In';
                if ($history->type === 'attribute_change') $particulars = 'Attribute Change (In)';
                if ($history->type === 'Edit (Restored)') $particulars = 'Stock Restored';

                $transactions->push((object)[
                    'date' => $history->created_at,
                    'type' => 'Inward',
                    'particulars' => $particulars,
                    'inward' => (int)$history->box_quantity,
                    'outward' => 0,
                    'remarks' => $history->remarks ?: 'View Details',
                    'link' => route('admin.inventory.attribute-history.show', $history->id)
                ]);
            }
            // Outward logic (excluding stock_consume as it is now covered by orders)
            if ($history->old_product_id == $id) {
                $particulars = 'Outward / ' . ucfirst(str_replace('_', ' ', $history->type));
                if ($history->type === 'deletion') $particulars = 'Stock Deletion';
                if ($history->type === 'attribute_change') $particulars = 'Attribute Change (Out)';

                $transactions->push((object)[
                    'date' => $history->created_at,
                    'type' => 'Outward',
                    'particulars' => $particulars,
                    'inward' => 0,
                    'outward' => (int)$history->box_quantity,
                    'remarks' => $history->remarks ?: 'View Details',
                    'link' => route('admin.inventory.attribute-history.show', $history->id)
                ]);
            }
        }

        // Fetch Order Items as Outward, grouped by order to prevent duplicate rows
        $orderItems = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->where('agent_order_items.product_id', $id)
            ->when($startDate, fn($q) => $q->whereDate('agent_order_items.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('agent_order_items.created_at', '<=', $endDate))
            ->select(
                DB::raw('MIN(agent_order_items.created_at) as date'),
                DB::raw('SUM(agent_order_items.box_qty) as outward'),
                'agent_orders.id as remarks'
            )
            ->groupBy('agent_orders.id')
            ->get();

        foreach ($orderItems as $order) {
            $transactions->push((object)[
                'date' => $order->date,
                'type' => 'Outward',
                'particulars' => 'Order Added',
                'inward' => 0,
                'outward' => (int)$order->outward,
                'remarks' => 'Order No: ' . $order->remarks,
                'link' => route('admin.agent-orders.show', $order->remarks)
            ]);
        }

        // Calculate Opening Balance
        $openingBalanceAmount = 0;
        if ($startDate) {
            $inwardBefore = DomesticInventoryHistory::where('new_product_id', $id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->whereDate('created_at', '<', $startDate)->sum('box_quantity');

            $historyOutwardBefore = DomesticInventoryHistory::where('old_product_id', $id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->whereDate('created_at', '<', $startDate)->sum('box_quantity');

            $orderOutwardBefore = DB::table('agent_order_items')
                ->where('product_id', $id)
                ->whereDate('created_at', '<', $startDate)->sum('box_qty');

            $outwardBefore = $historyOutwardBefore + $orderOutwardBefore;

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
