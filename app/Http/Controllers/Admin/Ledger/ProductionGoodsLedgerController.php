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
        $warehouseIds = $request->query('warehouse_ids', []);
        
        $hasUnassigned = in_array('unassigned', $warehouseIds);
        $filteredWarehouseIds = array_filter($warehouseIds, fn($id) => $id !== 'unassigned');

        $warehouses = \App\Models\Storeroom::where('status', 1)->get();

        $goods = ProductionGoods::with(['series', 'variants.sizeSet'])
            ->where('status', 1)
            ->when($search, function ($q) use ($search) {
                $q->where('name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('design_number', 'LIKE', "%$search%");
            })
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        foreach ($goods as $good) {
            foreach ($good->variants as $variant) {
                // Inward: From DomesticInventoryHistory where new_product_id = good->id and new_size_set_id = variant size set
                $inwardQuery = DB::table('domestic_inventory_histories')
                    ->where('new_product_id', $good->id)
                    ->where('new_size_set_id', $variant->master_size_measurement_id)
                    ->where('type', '!=', 'transfer');
                
                if (!empty($warehouseIds)) {
                    $inwardQuery->leftJoin('racks', 'domestic_inventory_histories.new_rack_id', '=', 'racks.id')
                        ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                                  ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                        ->whereNull('racks.storeroom_id');
                                });
                            }
                        });
                }
                
                $variant->total_inward = $inwardQuery->sum('box_quantity');

                // Total Outward: From DomesticInventoryHistory where old_product_id = good->id (excluding transfer and stock_consume)
                $outwardHistoryQuery = DB::table('domestic_inventory_histories')
                    ->where('old_product_id', $good->id)
                    ->where('old_size_set_id', $variant->master_size_measurement_id)
                    ->whereNotIn('type', ['transfer', 'stock_consume']);
                    
                if (!empty($warehouseIds)) {
                    $outwardHistoryQuery->leftJoin('racks', 'domestic_inventory_histories.old_rack_id', '=', 'racks.id')
                        ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                                  ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                        ->whereNull('racks.storeroom_id');
                                });
                            }
                        });
                }
                
                $historyOutward = $outwardHistoryQuery->sum('box_quantity');

                // Outward from orders (whether dispatched or not)
                $orderQuery = DB::table('agent_order_items')
                    ->where('agent_order_items.product_id', $good->id)
                    ->where('agent_order_items.size_set_id', $variant->master_size_measurement_id);
                    
                if (!empty($warehouseIds)) {
                    $orderQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                               ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                                   if (!empty($filteredWarehouseIds)) {
                                       $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                                   }
                                   if ($hasUnassigned) {
                                       $q->orWhereNull('racks.storeroom_id');
                                   }
                               });
                }
                
                $orderOutward = $orderQuery->sum('agent_order_items.box_qty');

                $variant->total_outward = $historyOutward + $orderOutward;

                // Current Balance mathematically based on Ledger
                $variant->current_balance = $variant->total_inward - $variant->total_outward;
            }
        }

        // --- Overall Totals Calculation ---
        $inwardTotalQuery = DB::table('domestic_inventory_histories')
            ->join('production_goods', 'domestic_inventory_histories.new_product_id', '=', 'production_goods.id')
            ->where('production_goods.status', 1)
            ->where('domestic_inventory_histories.type', '!=', 'transfer');
            
        if ($search) {
            $inwardTotalQuery->where(function($q) use ($search) {
                $q->where('production_goods.name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('production_goods.design_number', 'LIKE', "%$search%");
            });
        }
        
        if (!empty($warehouseIds)) {
            $inwardTotalQuery->leftJoin('racks', 'domestic_inventory_histories.new_rack_id', '=', 'racks.id')
                ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                    if (!empty($filteredWarehouseIds)) {
                        $q->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                          ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                    }
                    if ($hasUnassigned) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                ->whereNull('racks.storeroom_id');
                        });
                    }
                });
        }
        $totalInwardOverall = $inwardTotalQuery->sum('domestic_inventory_histories.box_quantity');

        $outwardHistoryTotalQuery = DB::table('domestic_inventory_histories')
            ->join('production_goods', 'domestic_inventory_histories.old_product_id', '=', 'production_goods.id')
            ->where('production_goods.status', 1)
            ->whereNotIn('domestic_inventory_histories.type', ['transfer', 'stock_consume']);
            
        if ($search) {
            $outwardHistoryTotalQuery->where(function($q) use ($search) {
                $q->where('production_goods.name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('production_goods.design_number', 'LIKE', "%$search%");
            });
        }
        
        if (!empty($warehouseIds)) {
            $outwardHistoryTotalQuery->leftJoin('racks', 'domestic_inventory_histories.old_rack_id', '=', 'racks.id')
                ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                    if (!empty($filteredWarehouseIds)) {
                        $q->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                          ->orWhereIn('racks.storeroom_id', $filteredWarehouseIds);
                    }
                    if ($hasUnassigned) {
                        $q->orWhere(function($sub) {
                            $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                ->whereNull('racks.storeroom_id');
                        });
                    }
                });
        }
        $totalOutwardHistoryOverall = $outwardHistoryTotalQuery->sum('domestic_inventory_histories.box_quantity');

        $orderTotalQuery = DB::table('agent_order_items')
            ->join('production_goods', 'agent_order_items.product_id', '=', 'production_goods.id')
            ->where('production_goods.status', 1);
            
        if ($search) {
            $orderTotalQuery->where(function($q) use ($search) {
                $q->where('production_goods.name_of_garment', 'LIKE', "%$search%")
                  ->orWhere('production_goods.design_number', 'LIKE', "%$search%");
            });
        }
        
        if (!empty($warehouseIds)) {
            $orderTotalQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                    if (!empty($filteredWarehouseIds)) {
                        $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                    }
                    if ($hasUnassigned) {
                        $q->orWhereNull('racks.storeroom_id');
                    }
                });
        }
        $totalOutwardOrderOverall = $orderTotalQuery->sum('agent_order_items.box_qty');
        
        $totalOutwardOverall = $totalOutwardHistoryOverall + $totalOutwardOrderOverall;
        $totalBalanceOverall = $totalInwardOverall - $totalOutwardOverall;

        return view('admin.ledger.production_goods.index', compact(
            'goods', 'search', 'warehouses', 'warehouseIds',
            'totalInwardOverall', 'totalOutwardOverall', 'totalBalanceOverall'
        ));
    }

    public function show(Request $request, $id, $size_set_id)
    {
        $data = $this->getLedgerData($request, $id, $size_set_id);
        return view('admin.ledger.production_goods.show', $data);
    }

    public function exportPdf(Request $request, $id, $size_set_id)
    {
        $data = $this->getLedgerData($request, $id, $size_set_id);
        $pdf = \PDF::loadView('admin.ledger.production_goods.pdf', $data);
        $name = 'Production_Goods_Ledger_' . $data['good']->design_number . '_' . $data['sizeSet']->name . '_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    private function getLedgerData(Request $request, $id, $size_set_id)
    {
        $good = ProductionGoods::with('series')->findOrFail($id);
        $sizeSet = \App\Models\MasterSizeMeasurement::findOrFail($size_set_id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $warehouseIds = $request->query('warehouse_ids', []);
        
        $hasUnassigned = in_array('unassigned', $warehouseIds);
        $filteredWarehouseIds = array_filter($warehouseIds, fn($id) => $id !== 'unassigned');

        // Fetch all related history records (excluding transfer and stock_consume)
        $histories = DomesticInventoryHistory::select('domestic_inventory_histories.*', 'domestic_inventory_purchases.purchase_date')
            ->leftJoin('racks as new_rack', 'domestic_inventory_histories.new_rack_id', '=', 'new_rack.id')
            ->leftJoin('racks as old_rack', 'domestic_inventory_histories.old_rack_id', '=', 'old_rack.id')
            ->leftJoin('domestic_inventory_purchases', 'domestic_inventory_histories.purchase_id', '=', 'domestic_inventory_purchases.id')
            ->where(function($q) use ($id, $size_set_id, $warehouseIds, $filteredWarehouseIds, $hasUnassigned) {
                $q->where(function($q1) use ($id, $size_set_id, $warehouseIds, $filteredWarehouseIds, $hasUnassigned) {
                    $q1->where('domestic_inventory_histories.old_product_id', $id)
                       ->where('domestic_inventory_histories.old_size_set_id', $size_set_id);
                    if (!empty($warehouseIds)) {
                        $q1->where(function($q1a) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q1a->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                                    ->orWhereIn('old_rack.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q1a->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                        ->whereNull('old_rack.storeroom_id');
                                });
                            }
                        });
                    }
                })
                ->orWhere(function($q2) use ($id, $size_set_id, $warehouseIds, $filteredWarehouseIds, $hasUnassigned) {
                    $q2->where('domestic_inventory_histories.new_product_id', $id)
                       ->where('domestic_inventory_histories.new_size_set_id', $size_set_id);
                    if (!empty($warehouseIds)) {
                        $q2->where(function($q2a) use ($filteredWarehouseIds, $hasUnassigned) {
                            if (!empty($filteredWarehouseIds)) {
                                $q2a->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                                    ->orWhereIn('new_rack.storeroom_id', $filteredWarehouseIds);
                            }
                            if ($hasUnassigned) {
                                $q2a->orWhere(function($sub) {
                                    $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                        ->whereNull('new_rack.storeroom_id');
                                });
                            }
                        });
                    }
                });
            })
            ->whereNotIn('domestic_inventory_histories.type', ['transfer', 'stock_consume'])
            ->when($startDate, fn($q) => $q->whereDate('domestic_inventory_histories.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('domestic_inventory_histories.created_at', '<=', $endDate))
            ->orderBy('domestic_inventory_histories.created_at', 'asc')
            ->with(['newRack', 'oldRack'])
            ->get();

        $transactions = collect();

        foreach ($histories as $history) {
            $actualNewWarehouseId = $history->new_warehouse_id ?? $history->newRack?->storeroom_id ?? null;
            // Inward logic
            $matchesWarehouseInward = empty($warehouseIds) || 
                                      ($hasUnassigned && is_null($actualNewWarehouseId)) || 
                                      (!empty($filteredWarehouseIds) && in_array($actualNewWarehouseId, $filteredWarehouseIds));
                                      
            if ($history->new_product_id == $id && $history->new_size_set_id == $size_set_id && $matchesWarehouseInward) {
                $particulars = 'Inward / ' . ucfirst(str_replace('_', ' ', $history->type));
                if ($history->type === 'creation') $particulars = 'Production / Stock In';
                if ($history->type === 'attribute_change') $particulars = 'Attribute Change (In)';
                if ($history->type === 'Edit (Restored)') $particulars = 'Stock Restored';

                $transactions->push((object)[
                    'date' => $history->purchase_date ? \Carbon\Carbon::parse($history->purchase_date) : $history->created_at,
                    'type' => 'Inward',
                    'particulars' => $particulars,
                    'inward' => (int)$history->box_quantity,
                    'outward' => 0,
                    'remarks' => $history->remarks ?: 'View Details',
                    'link' => route('admin.inventory.attribute-history.show', $history->id)
                ]);
            }
            
            $actualOldWarehouseId = $history->old_warehouse_id ?? $history->oldRack?->storeroom_id ?? null;
            // Outward logic (excluding stock_consume as it is now covered by orders)
            $matchesWarehouseOutward = empty($warehouseIds) || 
                                       ($hasUnassigned && is_null($actualOldWarehouseId)) || 
                                       (!empty($filteredWarehouseIds) && in_array($actualOldWarehouseId, $filteredWarehouseIds));
                                       
            if ($history->old_product_id == $id && $history->old_size_set_id == $size_set_id && $matchesWarehouseOutward) {
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
        $orderQuery = DB::table('agent_order_items')
            ->join('agent_orders', 'agent_order_items.agent_order_id', '=', 'agent_orders.id')
            ->leftJoin('master_customers', 'agent_orders.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_orders.master_vendor_id', '=', 'vendors.id')
            ->where('agent_order_items.product_id', $id)
            ->where('agent_order_items.size_set_id', $size_set_id);
            
        if (!empty($warehouseIds)) {
            $orderQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                       ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                           if (!empty($filteredWarehouseIds)) $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                           if ($hasUnassigned) $q->orWhereNull('racks.storeroom_id');
                       });
        }
            
        $orderItems = $orderQuery->when($startDate, fn($q) => $q->whereDate('agent_order_items.created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('agent_order_items.created_at', '<=', $endDate))
            ->select(
                DB::raw('MIN(agent_order_items.created_at) as date'),
                DB::raw('SUM(agent_order_items.box_qty) as outward'),
                'agent_orders.id as remarks',
                'agent_orders.party_type',
                'master_customers.name as customer_name',
                'vendors.name as vendor_name'
            )
            ->groupBy('agent_orders.id', 'agent_orders.party_type', 'master_customers.name', 'vendors.name')
            ->get();

        foreach ($orderItems as $order) {
            $partyName = $order->party_type === 'vendor' ? $order->vendor_name : $order->customer_name;
            $partyNameStr = $partyName ? " ($partyName)" : '';
            
            $transactions->push((object)[
                'date' => $order->date,
                'type' => 'Outward',
                'particulars' => 'Order Added',
                'inward' => 0,
                'outward' => (int)$order->outward,
                'remarks' => 'Order No: ' . $order->remarks . $partyNameStr,
                'link' => route('admin.agent-orders.show', $order->remarks)
            ]);
        }

        // Calculate Opening Balance
        $openingBalanceAmount = 0;
        if ($startDate) {
            $inwardBeforeQuery = DB::table('domestic_inventory_histories')
                ->where('new_product_id', $id)
                ->where('new_size_set_id', $size_set_id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->whereDate('domestic_inventory_histories.created_at', '<', $startDate);
                
            if (!empty($warehouseIds)) {
                $inwardBeforeQuery->leftJoin('racks as new_rack', 'domestic_inventory_histories.new_rack_id', '=', 'new_rack.id')
                    ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                        if (!empty($filteredWarehouseIds)) {
                            $q->whereIn('domestic_inventory_histories.new_warehouse_id', $filteredWarehouseIds)
                              ->orWhereIn('new_rack.storeroom_id', $filteredWarehouseIds);
                        }
                        if ($hasUnassigned) {
                            $q->orWhere(function($sub) {
                                $sub->whereNull('domestic_inventory_histories.new_warehouse_id')
                                    ->whereNull('new_rack.storeroom_id');
                            });
                        }
                    });
            }
            $inwardBefore = $inwardBeforeQuery->sum('box_quantity');

            $outwardBeforeQuery = DB::table('domestic_inventory_histories')
                ->where('old_product_id', $id)
                ->where('old_size_set_id', $size_set_id)
                ->whereNotIn('type', ['transfer', 'stock_consume'])
                ->whereDate('domestic_inventory_histories.created_at', '<', $startDate);
                
            if (!empty($warehouseIds)) {
                $outwardBeforeQuery->leftJoin('racks as old_rack', 'domestic_inventory_histories.old_rack_id', '=', 'old_rack.id')
                    ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                        if (!empty($filteredWarehouseIds)) {
                            $q->whereIn('domestic_inventory_histories.old_warehouse_id', $filteredWarehouseIds)
                              ->orWhereIn('old_rack.storeroom_id', $filteredWarehouseIds);
                        }
                        if ($hasUnassigned) {
                            $q->orWhere(function($sub) {
                                $sub->whereNull('domestic_inventory_histories.old_warehouse_id')
                                    ->whereNull('old_rack.storeroom_id');
                            });
                        }
                    });
            }
            $historyOutwardBefore = $outwardBeforeQuery->sum('box_quantity');

            $orderOutwardBeforeQuery = DB::table('agent_order_items')
                ->where('agent_order_items.product_id', $id)
                ->where('agent_order_items.size_set_id', $size_set_id)
                ->whereDate('agent_order_items.created_at', '<', $startDate);
                
            if (!empty($warehouseIds)) {
                $orderOutwardBeforeQuery->leftJoin('racks', 'agent_order_items.rack_id', '=', 'racks.id')
                                        ->where(function($q) use ($filteredWarehouseIds, $hasUnassigned) {
                                            if (!empty($filteredWarehouseIds)) $q->whereIn('racks.storeroom_id', $filteredWarehouseIds);
                                            if ($hasUnassigned) $q->orWhereNull('racks.storeroom_id');
                                        });
            }
            $orderOutwardBefore = $orderOutwardBeforeQuery->sum('agent_order_items.box_qty');

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

        $warehouses = !empty($filteredWarehouseIds) ? \App\Models\Storeroom::whereIn('id', $filteredWarehouseIds)->get() : collect();
        if ($hasUnassigned) {
            $warehouses->push((object)['id' => 'unassigned', 'name' => 'Unassigned (No Warehouse)']);
        }

        return compact('good', 'sizeSet', 'transactions', 'startDate', 'endDate', 'openingBalanceAmount', 'warehouses', 'warehouseIds');
    }
}
