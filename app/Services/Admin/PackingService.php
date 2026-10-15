<?php

namespace App\Services\Admin;

use App\Models\ProductionSlipDigitization;
use App\Models\PackingMain;
use App\Models\PackingCarton;

use App\Models\PackingItem;
use App\Models\OrderMain;
use App\Models\OrderStageTransaction;
use App\Models\OrderStageTransactionDetail;
use App\Models\OrderLot;
use App\Models\DomesticInventory;

use Illuminate\Support\Facades\DB;

class PackingService
{
    public function getPackingList()
    {
        return PackingMain::with(['order.customer'])
            ->where('slip_id', '!=', 0)
            ->orderBy('id', 'desc')
            ->get();
    }

    public function indexList($request)
    {
        // List individual Packing Sessions (Slips)
        $query = PackingMain::with(['order.customer'])->where('slip_id', '!=', 0);

        // Filter by Order No (SKU)
        if ($request->has('order_no') && !empty($request->order_no)) {
            $query->whereHas('order', function ($q) use ($request) {
                $q->where('sku', 'like', '%' . $request->order_no . '%');
            });
        }

        // Filter by Customer Name
        if ($request->has('customer_name') && !empty($request->customer_name)) {
            $query->whereHas('order.customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->customer_name . '%');
            });
        }

        // Filter by Date Range
        if ($request->has('start_date') && !empty($request->start_date)) {
            $query->whereDate('packing_date', '>=', $request->start_date);
        }
        if ($request->has('end_date') && !empty($request->end_date)) {
            $query->whereDate('packing_date', '<=', $request->end_date);
        }

        $query->orderBy('id', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('order_no', function ($row) {
                return $row->order->sku ?? 'N/A';
            })
            ->addColumn('customer', function ($row) {
                return $row->order->customer->name ?? 'N/A';
            })
            ->addColumn('slip_id', function ($row) {
                return '#' . $row->slip_id;
            })
            ->addColumn('packing_date', function ($row) {
                return $row->packing_date ? date('d/m/Y', strtotime($row->packing_date)) : 'N/A';
            })
            ->addColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge px-2 py-1 font-weight-bold" style="background: #edf7e4; color: #05421c; border: 1px solid #c3e6cb; border-radius: 4px;">Finalized</span>';
                }
                return '<span class="badge px-2 py-1 font-weight-bold" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; border-radius: 4px;">In-Progress</span>';
            })
            ->addColumn('action', function ($row) {
                $btn = '<div class="d-flex align-items-center justify-content-center"><a href="' . route('admin.packing.view', $row->id) . '" class="erp-action-btn erp-btn-view" title="View Details"><i class="fas fa-eye"></i></a></div>';
                return $btn;
            })
            ->rawColumns(['status', 'action'])
            ->make(true);
    }

        public function getPackingDetailsForOrder($order_id)
    {
        return OrderMain::with([
            'customer',
            'packingMains.cartons.items.detail.orderProductSet.product',
            'packingMains.cartons.items.detail.orderProductSet.colors',
            'packingMains.cartons.items.detail.orderProductSet.size_measurement',
            'packingMains.cartons.rack.storeroom'
        ])->findOrFail($order_id);
    }

    public function getPackingSessionDetails($id)
    {
        $session = PackingMain::with([
            'order.customer',
            'cartons.items.detail.orderProductSet.product',
            'cartons.items.detail.orderProductSet.colors',
            'cartons.items.detail.orderProductSet.size_measurement',
            'cartons.rack.storeroom',
            'outflows.product',
            'outflows.color',
            'outflows.size',
            'outflows.responsibleUnit',
            'domesticInventories.product',
            'domesticInventories.sizeSet',
            'domesticInventories.color',
            'domesticInventories.rack.storeroom'
        ])->findOrFail($id);

        $reworks = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $session->slip_id)
            ->where('from_stage_id', 11)
            ->where(function($q) {
                $q->where('type', 'rework')->orWhere('type', 0);
            })
            ->with(['toStage', 'toUnit', 'details'])
            ->get();

        $lotNos = $reworks->pluck('lot_no')->unique()->toArray();
        $lots = \App\Models\OrderLot::whereIn('lot_no', $lotNos)
            ->with(['orderProductSet.product', 'orderProductSet.colors', 'orderProductSet.size_measurement'])
            ->get()
            ->keyBy('lot_no');

        foreach ($reworks as $rw) {
            $rw->lot_info = $lots->get($rw->lot_no);
        }

        $session->setRelation('reworks', $reworks);

        return $session;
    }
    /**
     * Get pending packing slips (Stage 11)
     */
    public function getPendingSlips()
    {
        // Check if already packed?
        // Maybe we need a status in ProductionSlipDigitization or just check if it exists in PackingMain

        return ProductionSlipDigitization::where('from_stage_id', 11) // Packing Stage
            ->whereDoesntHave('packingMain') // Assuming we add relation to ProductionSlipDigitization
            ->orderBy('id', 'desc')
            ->get();
    }

    public function getSlipDetails($id)
    {
        return ProductionSlipDigitization::findOrFail($id);
    }

    public static function sizeKey($size): string
    {
        return trim(strtoupper((string) $size));
    }

    /**
     * What a packing unit still holds of each lot, worked out from the records
     * (received at this unit - packed - dead/sampling/debit/rework outflows - old rework moves), in total and per size.
     * Everything is counted for THIS unit only, so a lot split between two packing units is not mixed up.
     * $exceptMainId / $exceptSlipId leave out one packing session (to show "pending when this session started").
     *
     * @return array [lot_no => ['received','packed','outflow','rework','pending','sizes' => [SIZE => pending]]]
     */
    public function unitLotStock(array $lotNos, $unitId, $exceptMainId = null, $exceptSlipId = null): array
    {
        $lotNos = array_values(array_unique(array_filter(array_map('strval', $lotNos), fn($l) => $l !== '')));
        if (empty($lotNos) || !$unitId) {
            return [];
        }
        $k = fn($l) => mb_strtolower(rtrim((string) $l));
        $stock = [];
        foreach ($lotNos as $l) {
            $stock[$k($l)] = ['lot_no' => $l, 'received' => 0, 'packed' => 0, 'outflow' => 0, 'rework' => 0, 'in_sizes' => [], 'out_sizes' => []];
        }
        $add = function ($lot, $field, $qty, $size = null) use (&$stock, $k) {
            $key = $k($lot);
            if (!isset($stock[$key])) return;
            $stock[$key][$field] += (int) $qty;
            if ($size !== null) {
                $bucket = $field === 'received' ? 'in_sizes' : 'out_sizes';
                $sz = self::sizeKey($size);
                $stock[$key][$bucket][$sz] = ($stock[$key][$bucket][$sz] ?? 0) + (int) $qty;
            }
        };

        // Received at this unit (with sizes)
        $received = DB::table('order_stage_transactions as t')
            ->leftJoin('order_stage_transaction_details as d', 'd.order_stage_transaction_id', '=', 't.id')
            ->where('t.to_stage_id', 11)->where('t.sub_stage_id_to', $unitId)->whereIn('t.lot_no', $lotNos)
            ->select('t.id', 't.lot_no', 't.quantity', 'd.size', 'd.quantity as size_qty')->get();
        $seenTx = [];
        foreach ($received as $r) {
            $key = $k($r->lot_no);
            if (!isset($stock[$key])) continue;
            if (!isset($seenTx[$r->id])) {
                $seenTx[$r->id] = true;
                $stock[$key]['received'] += (int) $r->quantity;
            }
            if ($r->size !== null) {
                $sz = self::sizeKey($r->size);
                $stock[$key]['in_sizes'][$sz] = ($stock[$key]['in_sizes'][$sz] ?? 0) + (int) $r->size_qty;
            }
        }

        // Packed by this unit (all its sessions, except the excluded one)
        DB::table('packing_items as i')
            ->join('packing_mains as m', 'm.id', '=', 'i.packing_main_id')
            ->join('production_slip_digitization as s', 's.id', '=', 'm.slip_id')
            ->leftJoin('order_products_set_details as d', 'd.id', '=', 'i.size_id')
            ->where('s.stage_master_unit_id', $unitId)->whereIn('i.lot_no', $lotNos)
            ->when($exceptMainId, fn($q) => $q->where('m.id', '!=', $exceptMainId))
            ->select('i.lot_no', 'i.quantity', 'd.size')->get()
            ->each(fn($r) => $add($r->lot_no, 'packed', $r->quantity, $r->size));

        // Dead / sampling / debit / rework recorded on this unit's packing slips
        DB::table('production_outflow_inventories as o')
            ->join('production_slip_digitization as s', 's.id', '=', 'o.slip_id')
            ->leftJoin('order_products_set_details as d', 'd.id', '=', 'o.size_id')
            ->where('s.stage_master_unit_id', $unitId)->whereIn('o.lot_no', $lotNos)
            ->whereNotIn('o.type', ['packing', 'packing_divert'])
            ->when($exceptSlipId, fn($q) => $q->where('o.slip_id', '!=', $exceptSlipId))
            ->select('o.lot_no', 'o.quantity', 'd.size')->get()
            ->each(fn($r) => $add($r->lot_no, 'outflow', $r->quantity, $r->size));

        // Old-style rework moves sent straight from this packing unit (not the ones created from a rework record)
        $assigned = DB::table('production_outflow_inventories')->whereNotNull('assigned_transaction_id')->pluck('assigned_transaction_id')->all();
        DB::table('order_stage_transactions as t')
            ->leftJoin('order_stage_transaction_details as d', 'd.order_stage_transaction_id', '=', 't.id')
            ->where('t.from_stage_id', 11)->where('t.sub_stage_id', $unitId)->where('t.type', 0)
            ->whereIn('t.lot_no', $lotNos)
            ->when(!empty($assigned), fn($q) => $q->whereNotIn('t.id', $assigned))
            ->when($exceptSlipId, fn($q) => $q->where('t.production_slip_digitization_id', '!=', $exceptSlipId))
            ->select('t.id', 't.lot_no', 't.quantity')->distinct()->get()
            ->each(fn($r) => $add($r->lot_no, 'rework', $r->quantity));
        // (their sizes)
        DB::table('order_stage_transactions as t')
            ->join('order_stage_transaction_details as d', 'd.order_stage_transaction_id', '=', 't.id')
            ->where('t.from_stage_id', 11)->where('t.sub_stage_id', $unitId)->where('t.type', 0)
            ->whereIn('t.lot_no', $lotNos)
            ->when(!empty($assigned), fn($q) => $q->whereNotIn('t.id', $assigned))
            ->when($exceptSlipId, fn($q) => $q->where('t.production_slip_digitization_id', '!=', $exceptSlipId))
            ->select('t.lot_no', 'd.size', 'd.quantity')->get()
            ->each(function ($r) use (&$stock, $k) {
                $key = $k($r->lot_no);
                if (!isset($stock[$key])) return;
                $sz = self::sizeKey($r->size);
                $stock[$key]['out_sizes'][$sz] = ($stock[$key]['out_sizes'][$sz] ?? 0) + (int) $r->quantity;
            });

        $result = [];
        foreach ($stock as $s) {
            $sizes = [];
            foreach ($s['in_sizes'] as $sz => $q) {
                $sizes[$sz] = max(0, $q - ($s['out_sizes'][$sz] ?? 0));
            }
            $pending = max(0, $s['received'] - $s['packed'] - $s['outflow'] - $s['rework']);
            // per-size figures can never add up to more than the lot's pending total
            $excess = array_sum($sizes) - $pending;
            if ($excess > 0) {
                foreach ($sizes as $sz => $q) {
                    if ($excess <= 0) break;
                    $cut = min($q, $excess);
                    $sizes[$sz] -= $cut;
                    $excess -= $cut;
                }
            }
            $result[$s['lot_no']] = [
                'received' => $s['received'], 'packed' => $s['packed'], 'outflow' => $s['outflow'], 'rework' => $s['rework'],
                'pending' => $pending, 'sizes' => $sizes,
            ];
        }
        return $result;
    }

    /**
     * Take $qty pieces of a lot off this packing unit's own incoming balance (oldest first).
     * Never touches another unit's stock.
     */
    public function deductUnitBalance($lotNo, $unitId, int $qty): void
    {
        if ($qty <= 0) return;
        $rows = OrderStageTransaction::where('to_stage_id', 11)->where('sub_stage_id_to', $unitId)
            ->where('lot_no', $lotNo)->where('remaining_quantity', '>', 0)
            ->orderBy('id')->lockForUpdate()->get();
        foreach ($rows as $row) {
            if ($qty <= 0) break;
            $take = min($row->remaining_quantity, $qty);
            $row->remaining_quantity -= $take;
            $row->status = $row->remaining_quantity <= 0 ? 2 : 1;
            $row->is_closed_for_unit = $row->remaining_quantity <= 0 ? 1 : 0;
            $row->save();
            $qty -= $take;
        }
    }

    /**
     * Give $qty pieces of a lot back to this packing unit's own incoming balance (newest first),
     * never more than each row originally received.
     */
    public function restoreUnitBalance($lotNo, $unitId, int $qty): void
    {
        if ($qty <= 0) return;
        $rows = OrderStageTransaction::where('to_stage_id', 11)->where('sub_stage_id_to', $unitId)
            ->where('lot_no', $lotNo)->orderByDesc('id')->lockForUpdate()->get();
        foreach ($rows as $row) {
            if ($qty <= 0) break;
            $space = max(0, $row->quantity - $row->remaining_quantity);
            if ($space <= 0) continue;
            $add = min($space, $qty);
            $row->remaining_quantity += $add;
            $row->status = 1;
            $row->is_closed_for_unit = 0;
            $row->save();
            $qty -= $add;
        }
    }

    /**
     * Nothing can be added to a packing session that is already finalized.
     */
    public function assertSessionOpen($slipId): void
    {
        if (PackingMain::where('slip_id', $slipId)->where('status', 1)->exists()) {
            throw new \Exception('This packing session is finalized.');
        }
    }

    /**
     * Does any packing unit still hold pieces of this lot (received - packed - outflows - rework > 0)?
     */
    public function packingStillHoldsLot($lotNo): bool
    {
        $units = DB::table('order_stage_transactions')->where('to_stage_id', 11)->where('lot_no', $lotNo)
            ->whereNotNull('sub_stage_id_to')->distinct()->pluck('sub_stage_id_to');
        foreach ($units as $unitId) {
            $s = $this->unitLotStock([$lotNo], $unitId)[(string) $lotNo] ?? null;
            if ($s && $s['pending'] > 0) return true;
        }
        return false;
    }

    /**
     * [{detail_id, qty, lot_no}] -> [lot_no => [SIZE => qty]] (quantities must be whole numbers of 1 or more)
     */
    public function neededFromItems(array $items): array
    {
        $need = [];
        foreach ($items as $item) {
            $qty = $item['qty'] ?? null;
            if ($qty === null || $qty === '' || (int) $qty === 0) continue;
            if (!is_numeric($qty) || (int) $qty != $qty || (int) $qty < 1) {
                throw new \Exception('Quantity must be a whole number of 1 or more.');
            }
            $detail = \App\Models\OrderProductSetDetail::find($item['detail_id'] ?? 0);
            if (!$detail) throw new \Exception('Size not found.');
            $lot = (string) ($item['lot_no'] ?? '');
            if ($lot === '') throw new \Exception('Lot number is missing.');
            $need[$lot][$detail->size] = ($need[$lot][$detail->size] ?? 0) + (int) $qty;
        }
        if (empty($need)) throw new \Exception('Please enter a quantity.');
        return $need;
    }

    /**
     * A carton can be deleted only while its session is open and it has not been dispatched.
     */
    public function assertCartonDeletable(PackingCarton $carton): void
    {
        $main = $carton->main ?? PackingMain::find($carton->packing_main_id);
        if ($main && $main->status == 1) {
            throw new \Exception("Carton {$carton->carton_no} belongs to a finalized packing session.");
        }
        if ($carton->status == 2 || \App\Models\OrderDispatchDetails::where('carton_packing_id', $carton->id)->exists()) {
            throw new \Exception("Carton {$carton->carton_no} is already dispatched and cannot be deleted.");
        }
    }

    /**
     * Refuse when the unit does not hold enough. $need = [lot_no => [SIZE => qty]].
     */
    public function assertUnitHolds(array $need, $unitId, $exceptMainId = null): void
    {
        $stock = $this->unitLotStock(array_keys($need), $unitId);
        foreach ($need as $lot => $sizes) {
            $s = $stock[(string) $lot] ?? null;
            if (!$s || $s['received'] <= 0) {
                throw new \Exception("Lot {$lot} was not received by this packing unit.");
            }
            $total = array_sum($sizes);
            if ($total > $s['pending']) {
                throw new \Exception("Lot {$lot}: only {$s['pending']} pcs left at this unit, you entered {$total}.");
            }
            if (!empty($s['sizes'])) {
                foreach ($sizes as $size => $qty) {
                    $have = $s['sizes'][self::sizeKey($size)] ?? 0;
                    if ($qty > $have) {
                        throw new \Exception("Lot {$lot}, size {$size}: only {$have} pcs left at this unit, you entered {$qty}.");
                    }
                }
            }
        }
    }

    public function getOrderDetails($sku)
    {
        return OrderMain::where('sku', $sku)
            ->with(['OrderProductSets.product_set_details', 'OrderProductSets.colors', 'OrderProductSets.size_measurement'])
            ->first();
    }

    public function getOrCreatePackingMain($slip_id, $order_id)
    {
        $packing = PackingMain::where('slip_id', $slip_id)->first();
        if (!$packing) {
            $packing = PackingMain::create([
                'slip_id' => $slip_id,
                'order_main_id' => $order_id,
                'packing_date' => now(),
                'status' => 0 // Draft
            ]);
        } elseif (!$packing->order_main_id && $order_id) {
            $packing->order_main_id = $order_id;
            $packing->save();
        }
        return $packing;
    }

        public function getPackingMainWithStructure($slip_id)
    {
        return PackingMain::where('slip_id', $slip_id)
            ->with([
                'cartons.items.detail.orderProductSet.product',
                'cartons.items.detail.orderProductSet.colors',
                'cartons.items.detail.orderProductSet.size_measurement'
            ])
            ->first();
    }

    public function getAvailableQuantitiesAtUnit($order_id, $unit_id)
    {
        // 1. Get all lot numbers for this order to trace pieces (including Pending status 0)
        $lots = \App\Models\OrderLot::where('order_main_id', $order_id)
            ->pluck('lot_no')
            ->toArray();

        if (empty($lots)) {
            return [];
        }

        // 2. Sum incoming quantities from OrderStageTransaction to this specific unit (Including Status 0)
        $incoming = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.to_stage_id', 11) // Packing Stage
            ->where(function($q) use ($unit_id) {
                $q->where('tx.sub_stage_id_to', $unit_id)
                  ->orWhereNull('tx.sub_stage_id_to');
            })
            ->where('tx.type', '!=', 'damage')
            ->select('det.size', DB::raw('SUM(det.quantity) as total_incoming'))
            ->groupBy('det.size')
            ->pluck('total_incoming', 'det.size')
            ->toArray();

        // 2.1 Subtract outgoing quantities (ALL Transfers Out - including outflows)
        $outgoing = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.from_stage_id', 11) // From Packing
            ->where('tx.sub_stage_id', $unit_id)
            ->where('tx.status', '>=', 0) // ALL moves out
            ->select('det.size', DB::raw('SUM(det.quantity) as total_outgoing'))
            ->groupBy('det.size')
            ->pluck('total_outgoing', 'det.size')
            ->toArray();

        // 3. Sum already packed quantities (Corporate via packing_items)
        $corporatePacked = DB::table('packing_items as pi')
            ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
            ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
            ->where('pm.order_main_id', $order_id)
            ->where('psd.stage_master_unit_id', $unit_id)
            ->select('pi.size_id', DB::raw('SUM(pi.quantity) as total_packed'))
            ->groupBy('pi.size_id')
            ->pluck('total_packed', 'pi.size_id')
            ->toArray();

        // 3. Sum Domestic Packed ONLY (via ProductionOutflowInventory type='packing')
        // This is necessary because domestic packing does NOT create a mirrored transaction.
        $domesticPacked = DB::table('production_outflow_inventories')
            ->where('order_main_id', $order_id)
            ->where('responsible_unit_id', $unit_id)
            ->where('type', 'packing')
            ->select('size_id', DB::raw('SUM(quantity) as total_packed'))
            ->groupBy('size_id')
            ->pluck('total_packed', 'size_id')
            ->toArray();

        // 4. Map size names and calculate availability
        $available = [];
        $details = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
            ->where('ops.order_main_id', $order_id)
            ->select('order_products_set_details.*')
            ->get();

        foreach ($details as $detail) {
            $inQty = $incoming[$detail->size] ?? 0;
            $outQty = $outgoing[$detail->size] ?? 0;
            $corpQty = $corporatePacked[$detail->id] ?? 0;
            $domQty = $domesticPacked[$detail->id] ?? 0;

            // Available = [In] - [Out (Mirror)] - [Packed Corporate] - [Packed Domestic (Log)]
            $available[$detail->id] = (int) max(0, $inQty - $outQty - $corpQty - $domQty);
        }

        return $available;
    }

        public function getAvailableQuantitiesAtUnitPerLot($order_id, $unit_id)
    {
        $lots = \App\Models\OrderLot::where('order_main_id', $order_id)
            ->pluck('lot_no')
            ->toArray();

        if (empty($lots)) {
            return [];
        }

        // 1. Sum incoming quantities
        $incoming = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.to_stage_id', 11) // Packing Stage
            ->where(function($q) use ($unit_id) {
                $q->where('tx.sub_stage_id_to', $unit_id)
                  ->orWhereNull('tx.sub_stage_id_to');
            })
            ->where('tx.type', '!=', 'damage')
            ->select('tx.lot_no', 'det.size', DB::raw('SUM(det.quantity) as total_incoming'))
            ->groupBy('tx.lot_no', 'det.size')
            ->get();

        // 2. Subtract outgoing quantities
        $outgoing = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.from_stage_id', 11)
            ->where('tx.sub_stage_id', $unit_id)
            ->where('tx.status', '>=', 0)
            ->select('tx.lot_no', 'det.size', DB::raw('SUM(det.quantity) as total_outgoing'))
            ->groupBy('tx.lot_no', 'det.size')
            ->get();

        // 3. Sum Corporate Packed
        $corporatePacked = DB::table('packing_items as pi')
            ->join('packing_mains as pm', 'pi.packing_main_id', '=', 'pm.id')
            ->join('production_slip_digitization as psd', 'pm.slip_id', '=', 'psd.id')
            ->where('pm.order_main_id', $order_id)
            ->where('psd.stage_master_unit_id', $unit_id)
            ->whereNotNull('pi.lot_no')
            ->select('pi.lot_no', 'pi.size_id', DB::raw('SUM(pi.quantity) as total_packed'))
            ->groupBy('pi.lot_no', 'pi.size_id')
            ->get();

        // 4. Sum Domestic Packed
        $domesticPacked = DB::table('production_outflow_inventories')
            ->where('order_main_id', $order_id)
            ->where('responsible_unit_id', $unit_id)
            ->where('type', 'packing')
            ->whereNotNull('lot_no')
            ->select('lot_no', 'size_id', DB::raw('SUM(quantity) as total_packed'))
            ->groupBy('lot_no', 'size_id')
            ->get();

        // Group everything together
        $inMap = [];
        foreach($incoming as $row) {
            $inMap[$row->lot_no][$row->size] = $row->total_incoming;
        }

        $outMap = [];
        foreach($outgoing as $row) {
            $outMap[$row->lot_no][$row->size] = $row->total_outgoing;
        }

        $corpMap = [];
        foreach($corporatePacked as $row) {
            $corpMap[$row->lot_no][$row->size_id] = $row->total_packed;
        }

        $domMap = [];
        foreach($domesticPacked as $row) {
            $domMap[$row->lot_no][$row->size_id] = $row->total_packed;
        }

        // Output Structure: ['LOT-123' => [detail_id => qty]]
        $availablePerLot = [];
        
        $details = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
            ->where('ops.order_main_id', $order_id)
            ->select('order_products_set_details.*')
            ->get();

        foreach ($lots as $lot) {
            $availablePerLot[$lot] = [];
            foreach ($details as $detail) {
                $inQty = $inMap[$lot][$detail->size] ?? 0;
                $outQty = $outMap[$lot][$detail->size] ?? 0;
                $corpQty = $corpMap[$lot][$detail->id] ?? 0;
                $domQty = $domMap[$lot][$detail->id] ?? 0;

                $avail = (int) max(0, $inQty - $outQty - $corpQty - $domQty);
                $availablePerLot[$lot][$detail->id] = $avail;
            }
        }

        return $availablePerLot;
    }

    public function getIncomingQuantitiesAtUnit($order_id, $unit_id)
    {
        $lots = \App\Models\OrderLot::where('order_main_id', $order_id)->pluck('lot_no')->toArray();
        if (empty($lots)) return [];

        $incoming = DB::table('order_stage_transactions as tx')
            ->join('order_stage_transaction_details as det', 'tx.id', '=', 'det.order_stage_transaction_id')
            ->whereIn('tx.lot_no', $lots)
            ->where('tx.to_stage_id', 11) // Packing Stage
            ->where(function($q) use ($unit_id) {
                $q->where('tx.sub_stage_id_to', $unit_id)
                  ->orWhereNull('tx.sub_stage_id_to');
            })
            ->where('tx.type', '!=', 'damage')
            ->select('det.size', DB::raw('SUM(det.quantity) as total_incoming'))
            ->groupBy('det.size')
            ->pluck('total_incoming', 'det.size')
            ->toArray();

        $incoming_mapped = [];
        $details = \App\Models\OrderProductSetDetail::join('order_products_sets as ops', 'order_products_set_details.order_products_set_id', '=', 'ops.id')
            ->where('ops.order_main_id', $order_id)
            ->select('order_products_set_details.*')
            ->get();

        foreach ($details as $detail) {
            $incoming_mapped[$detail->id] = (int) ($incoming[$detail->size] ?? 0);
        }

        return $incoming_mapped;
    }

    public function bulkSaveCarton($data)
    {
        DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $boxes_per_carton = (int) ($data['boxes_per_carton'] ?? 1);
            $global_rack_id = $data['rack_id'] ?? null;
            $set_racks = $data['set_racks'] ?? []; // [set_id => rack_id]
            $packed_quantities = $this->getPackedQuantitiesForOrder($data['order_id']);

            // Get unit details for validation
            $slip_details = ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            // Auto-generate start carton sequence
            $max_carton = PackingCarton::selectRaw('MAX(CAST(carton_no AS UNSIGNED)) as max_no')->value('max_no');
            $next_available_no = ($max_carton ?: 0) + 1;

            // box_plan: [[ 'composition' => [ {id, qty}, ... ], 'rack_id' => ... ], ...]
            $box_plan = [];

            if (isset($data['mode']) && $data['mode'] == 'full_loose') {
                // Scenario 3: Full Order Loose (1 piece = 1 box)
                $order = \App\Models\OrderMain::with('OrderProductSets.product_set_details')->find($data['order_id']);
                foreach ($order->OrderProductSets as $set) {
                    foreach ($set->product_set_details as $detail) {
                        $remaining = $detail->total_quantity - ($packed_quantities[$detail->id] ?? 0);
                        for ($i = 0; $i < $remaining; $i++) {
                            $box_plan[] = [
                                'composition' => [['id' => $detail->id, 'qty' => 1]],
                                'rack_id' => $global_rack_id
                            ];
                        }
                    }
                }
            } elseif (isset($data['mode']) && $data['mode'] == 'full_sets') {
                // Scenario 4: Full Order Sets
                $order = \App\Models\OrderMain::with('OrderProductSets.product_set_details')->find($data['order_id']);
                foreach ($order->OrderProductSets as $set) {
                    // Logic to find how many full sets remain for this set
                    $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                    $min_remaining_sets = null;
                    $composition = []; // [{id, qty_per_box}]

                    foreach ($set->product_set_details as $detail) {
                        $remaining = $detail->total_quantity - ($packed_quantities[$detail->id] ?? 0);
                        $qty_per_set = $detail->total_quantity / $set_total_qty;
                        $composition[] = ['id' => $detail->id, 'qty' => $qty_per_set];

                        if ($qty_per_set > 0) {
                            $rem_sets = floor($remaining / $qty_per_set);
                            if ($min_remaining_sets === null || $rem_sets < $min_remaining_sets) {
                                $min_remaining_sets = $rem_sets;
                            }
                        }
                    }

                    $set_rack_id = $set_racks[$set->id] ?? $global_rack_id;
                    for ($i = 0; $i < $min_remaining_sets; $i++) {
                        $box_plan[] = [
                            'composition' => $composition,
                            'rack_id' => $set_rack_id
                        ];
                    }
                }
            } elseif (!empty($data['items'])) {
                // Scenario 1: Single Item Loose (Multi-box)
                $total_boxes = (int) ($data['total_boxes'] ?? 1);
                foreach ($data['items'] as $item) {
                    $did = $item['detail_id'];
                    $qty_per_box = (int) $item['qty_per_box'];
                    if ($qty_per_box > 0) {
                        for ($i = 0; $i < $total_boxes; $i++) {
                            $box_plan[] = [
                                'composition' => [['id' => $did, 'qty' => $qty_per_box]],
                                'rack_id' => $global_rack_id
                            ];
                        }
                    }
                }
            } elseif ($set_id = ($data['set_id'] ?? null)) {
                // Scenario 2: Single Set (Multi-box)
                $total_boxes = (int) ($data['total_boxes'] ?? 1);
                $size_set_str = $data['size_set'] ?? '';
                $sizes = preg_split('/[,\s\.]+/', $size_set_str);
                $size_freq = [];
                foreach ($sizes as $s) {
                    $s = trim($s);
                    if ($s === '')
                        continue;
                    $size_freq[$s] = ($size_freq[$s] ?? 0) + 1;
                }

                $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_id);
                if (!$set)
                    throw new \Exception("Set not found.");

                $detail_map = [];
                foreach ($set->product_set_details as $detail) {
                    $detail_map[$detail->size] = $detail->id;
                }

                $composition = [];
                foreach ($size_freq as $size_name => $count) {
                    if (isset($detail_map[$size_name])) {
                        $composition[] = ['id' => $detail_map[$size_name], 'qty' => $count];
                    } else {
                        throw new \Exception("Size '$size_name' not found in the selected set.");
                    }
                }

                $set_rack_id = $set_racks[$set_id] ?? $global_rack_id;
                for ($i = 0; $i < $total_boxes; $i++) {
                    $box_plan[] = [
                        'composition' => $composition,
                        'rack_id' => $set_rack_id
                    ];
                }
            }

            if (empty($box_plan)) {
                throw new \Exception("No items selected or no remaining items to pack.");
            }

            // Global Validation & Total Calculation
            $total_items_to_pack = 0;
            $items_summary = []; // [detail_id => total_qty]
            $items_summary = []; // [detail_id => total_qty]
            foreach ($box_plan as $plan_item) {
                foreach ($plan_item['composition'] as $item) {
                    $items_summary[$item['id']] = ($items_summary[$item['id']] ?? 0) + $item['qty'];
                    $total_items_to_pack += $item['qty'];
                }
            }

            foreach ($items_summary as $did => $total_needed) {
                $detail = \App\Models\OrderProductSetDetail::find($did);
                if (!$detail)
                    throw new \Exception("Item detail not found for ID: $did");

                // HARD LIMIT: Cannot pack more than what was delivered to the unit
                $avl_at_unit = $unit_available[$did] ?? 0;
                if ($total_needed > $avl_at_unit) {
                    throw new \Exception("Insufficient quantity at Unit for '{$detail->size}'. Requested: $total_needed Pcs, Available at Unit: $avl_at_unit Pcs.");
                }
            }

            // Group box_plan by rack_id
            $grouped_plans = [];
            foreach ($box_plan as $plan) {
                $rid = $plan['rack_id'] ?: 0; // Use 0 for no rack
                $grouped_plans[$rid][] = $plan['composition'];
            }

            $carton_count = 0;
            $total_boxes_to_create = count($box_plan);
            $datePrefix = date('ymd');

            $next_available_no = \App\Models\PackingCarton::where('carton_no', 'LIKE', $datePrefix . '%')
                ->max('carton_no') ?? ($datePrefix . '000');
            $next_available_no = (int)$next_available_no + 1;

            foreach ($grouped_plans as $rid => $compositions) {
                $actual_rack_id = $rid ?: null;
                $boxes_in_group = count($compositions);
                $boxes_remaining_in_group = $boxes_in_group;
                $comp_idx = 0;

                while ($boxes_remaining_in_group > 0) {
                    $carton_no_str = (string) $next_available_no;

                    $carton = \App\Models\PackingCarton::create([
                        'packing_main_id' => $main->id,
                        'carton_no' => $carton_no_str,
                        'rack_id' => $actual_rack_id,
                        'status' => 0, // Draft
                    ]);

                    $boxes_in_this_carton = min($boxes_per_carton, $boxes_remaining_in_group);

                    for ($b = 0; $b < $boxes_in_this_carton; $b++) {
                        $box_composition = $compositions[$comp_idx++];

                        foreach ($box_composition as $item) {
                            $detail = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($item['id']);
                            $fallbackPrice = ($detail && $detail->orderProductSet && $detail->orderProductSet->total_quantity > 0) ? ($detail->orderProductSet->basic_amount / $detail->orderProductSet->total_quantity) : 0;
                            
                            \App\Models\PackingItem::create([
                                'packing_main_id' => $main->id,
                                'packing_carton_id' => $carton->id,
                                'size_id' => $item['id'],
                                'quantity' => $item['qty'],
                                'selling_price' => $fallbackPrice,
                                'mrp' => 0
                            ]);

                            $unit_available[$item['id']] -= $item['qty'];
                            // (Relaxed) Quantity Validation - Allow overages as requested by user
                            if ($unit_available[$item['id']] < -50) { // Keep a broad limit or just comment? Let's comment.
                                /* $detail = \App\Models\OrderProductSetDetail::find($item['id']);
                                throw new \Exception("Insufficient quantity available at unit for size '{$detail->size}'"); */
                            }
                        }
                    }

                    $boxes_remaining_in_group -= $boxes_in_this_carton;
                    $next_available_no++;
                    $carton_count++;
                }
            }

            // Adjust remaining Qty in OrderStageTransaction
            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $selectedLots = \App\Models\PackingSelectedLot::where('slip_id', $data['slip_id'])->pluck('lot_no')->toArray();
            $orderLots = \App\Models\OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();
            $lotsToQuery = !empty($selectedLots) ? $selectedLots : $orderLots;

            $orderStageTransactions = \App\Models\OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
                ->where(function($q) use ($slip_details) {
                    $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                      ->orWhereNull('sub_stage_id_to');
                })
                ->whereIn('lot_no', $lotsToQuery)
                ->orderBy('id')
                ->get();

            $remaining_to_deduct = $total_items_to_pack;
            foreach ($orderStageTransactions as $transaction) {
                if ($remaining_to_deduct <= 0)
                    break;
                if ($transaction->remaining_quantity <= 0)
                    continue;

                if ($transaction->remaining_quantity > $remaining_to_deduct) {
                    $transaction->remaining_quantity -= $remaining_to_deduct;
                    $remaining_to_deduct = 0;
                } else {
                    $remaining_to_deduct -= $transaction->remaining_quantity;
                    $transaction->remaining_quantity = 0;
                }
                $transaction->save();
            }

            DB::commit();
            return ['status' => 'success', 'message' => "Successfully packed $carton_count cartons and $total_boxes_to_create boxes."];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

        public function saveCarton($data)
    {
        \DB::beginTransaction();
        try {
            $packed_pcs = 0;
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);

            $carton = \App\Models\PackingCarton::create([
                'packing_main_id' => $main->id,
                'carton_no' => $data['carton_no'],
                'rack_id' => $data['rack_id'] ?? null,
                'note' => $data['note'] ?? null,
                'status' => 0,
            ]);

            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            if ((isset($data['items']) && count($data['items']) > 0) || (isset($data['sets']) && count($data['sets']) > 0)) {
                if (isset($data['items']) && is_array($data['items'])) {
                    foreach ($data['items'] as $item) {
                        if ($item['quantity'] <= 0) continue;
                        $detail = \App\Models\OrderProductSetDetail::with('orderProductSet')->find($item['size_id']);
                        $needed_pcs = $item['quantity'];
                        
                        $entry = [
                            'selected_lots' => $data['selected_lots'] ?? [],
                            'rack_id' => $data['rack_id'] ?? null,
                        ];
                        
                        $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, 1, $entry, $slip_details, $data);
                        $packed_pcs += $needed_pcs;
                    }
                }

                if (isset($data['sets']) && is_array($data['sets'])) {
                    foreach ($data['sets'] as $set_item) {
                        if ($set_item['quantity'] <= 0) continue;
                        $set = \App\Models\OrderProductSet::with('product_set_details')->find($set_item['set_id']);
                        if (!$set) continue;
                        
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                        foreach ($set->product_set_details as $detail) {
                            $ratio = $detail->total_quantity / $set_total_qty;
                            $needed_pcs = ceil($ratio * $set_item['quantity']);
                            if ($needed_pcs <= 0) continue;
                            
                            $entry = [
                                'selected_lots' => $data['selected_lots'] ?? [],
                                'rack_id' => $data['rack_id'] ?? null,
                            ];
                            $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, 1, $entry, $slip_details, $data);
                            $packed_pcs += $needed_pcs;
                        }
                    }
                }
            }

            \DB::commit();
            return ['status' => 'success', 'message' => "Successfully packed $packed_pcs pieces into Carton {$carton->carton_no}."];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    private function processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, $requested_boxes, $entry, $slip_details, $data) {
        $selectedLots = !empty($entry['selected_lots']) 
            ? (array)$entry['selected_lots'] 
            : (!empty($data['selected_lots']) ? (array)$data['selected_lots'] : []);

        if (empty($selectedLots) && !empty($slip_details)) {
            $selectedLots = \App\Models\PackingSelectedLot::where('slip_id', $slip_details->id)->pluck('lot_no')->toArray();
        }

        $validLotsForSize = \App\Models\OrderLot::where('order_products_set_id', $detail->order_products_set_id ?? 0)->pluck('lot_no')->toArray();
        if (empty($validLotsForSize)) {
            $validLotsForSize = \App\Models\OrderLot::where('order_main_id', $data['order_id'])->pluck('lot_no')->toArray();
        }

        if (!empty($selectedLots)) {
            $lotsToDeduct = array_values(array_intersect($selectedLots, $validLotsForSize));
            if (empty($lotsToDeduct)) {
                $lotsToDeduct = $selectedLots;
            }
        } else {
            $lotsToDeduct = $validLotsForSize;
        }

        $orderStageTransactions = \App\Models\OrderStageTransaction::where('to_stage_id', $slip_details->from_stage_id)
            ->where(function($q) use ($slip_details) {
                $q->where('sub_stage_id_to', $slip_details->stage_master_unit_id)
                  ->orWhereNull('sub_stage_id_to');
            })
            ->whereIn('lot_no', $lotsToDeduct)
            ->orderBy('id')->get();

        $rem = $needed_pcs;
        $pieces_per_box = $needed_pcs > 0 ? ($needed_pcs / $requested_boxes) : 1;

        foreach ($orderStageTransactions as $tx) {
            if ($rem <= 0) break;
            if ($tx->remaining_quantity <= 0) continue;
            
            $take = min($rem, $tx->remaining_quantity);
            
            $tx->remaining_quantity -= $take;
            $tx->save();
            
            $rem -= $take;
            
            $boxes_for_this_lot = round($take / $pieces_per_box, 2);

            \App\Models\PackingItem::create([
                'packing_main_id' => $main->id,
                'packing_carton_id' => $carton->id,
                'size_id' => $detail->id,
                'lot_no' => $tx->lot_no,
                'total_boxes' => $boxes_for_this_lot,
                'rack_id' => $entry['rack_id'] ?? $carton->rack_id,
                'quantity' => $take,
                'selling_price' => $entry['price'] ?? 0,
                'mrp' => $entry['mrp'] ?? 0
            ]);
        }
        
        if ($rem > 0) {
            $boxes_for_this_lot = round($rem / $pieces_per_box, 2);
            \App\Models\PackingItem::create([
                'packing_main_id' => $main->id,
                'packing_carton_id' => $carton->id,
                'size_id' => $detail->id,
                'lot_no' => null,
                'total_boxes' => $boxes_for_this_lot,
                'rack_id' => $entry['rack_id'] ?? $carton->rack_id,
                'quantity' => $rem,
                'selling_price' => $entry['price'] ?? 0,
                'mrp' => $entry['mrp'] ?? 0
            ]);
        }
    }

        public function saveMultiCartonPlan($data)
    {
        \DB::beginTransaction();
        try {
            $main = $this->getOrCreatePackingMain($data['slip_id'], $data['order_id']);
            $order = \App\Models\OrderMain::find($data['order_id']);
            $is_domestic = $order && strtolower(trim($order->order_type)) === 'domestic';
            $plan = $data['plan'] ?? [];
            if (empty($plan))
                throw new \Exception("Empty plan provided.");

            $slip_details = \App\Models\ProductionSlipDigitization::find($data['slip_id']);
            $unit_available = $this->getAvailableQuantitiesAtUnit($data['order_id'], $slip_details->stage_master_unit_id);

            $carton_groups = [];
            $requested_pcs = [];
            foreach ($plan as $entry) {
                $carton_groups[$entry['carton_no']][] = $entry;

                if ($entry['type'] === 'set' || $entry['type'] === 'domestic') {
                    $set = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                    if ($set) {
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;
                        foreach ($set->product_set_details as $detail) {
                            $pcs_per_set = ceil($detail->total_quantity / $set_total_qty);
                            $requested_pcs[$detail->id] = ($requested_pcs[$detail->id] ?? 0) + ($pcs_per_set * $entry['quantity']);
                        }
                    }
                } else {
                    $did = $entry['content_id'];
                    $requested_pcs[$did] = ($requested_pcs[$did] ?? 0) + $entry['quantity'];
                }
            }

            foreach ($requested_pcs as $did => $needed) {
                $avl = $unit_available[$did] ?? 0;
                if ($needed > $avl) {
                    $detail = \App\Models\OrderProductSetDetail::find($did);
                    $sz = $detail ? $detail->size : 'N/A';
                    throw new \Exception("Insufficient quantity at Unit for '{$sz}'. Needed: $needed Pcs, Available at Unit: $avl Pcs.");
                }
            }

            $total_pcs = 0;
            $carton_count = 0;

            foreach ($carton_groups as $cno => $entries) {
                $rack_id = $entries[0]['rack_id'] ?? null;
                $carton = \App\Models\PackingCarton::create([
                    'packing_main_id' => $main->id,
                    'carton_no' => $cno,
                    'rack_id' => $rack_id,
                    'status' => 0
                ]);

                foreach ($entries as $entry) {
                    $qty = (int) $entry['quantity'];
                    $barcode = $entry['barcode'] ?? null;

                    if ($barcode && !$carton->barcode) {
                        $carton->update(['barcode' => $barcode]);
                    }

                    if ($is_domestic && $entry['type'] === 'domestic') {
                        $stockSet = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                        if (!$stockSet) throw new \Exception("Stock Set not found for domestic entry.");

                        // Sync fitting and pattern from corporate order set to production_goods product if not set
                        $prod = \App\Models\ProductionGoods::find($entry['product_id']);
                        if ($prod && (empty($prod->master_product_fitting_id) || empty($prod->master_pattern_id))) {
                            $prod->update([
                                'master_product_fitting_id' => $stockSet->master_product_fitting_id ?? $prod->master_product_fitting_id,
                                'master_pattern_id' => $stockSet->master_design_pattern_id ?? $prod->master_pattern_id
                            ]);
                        }

                        $ss_total = $stockSet->set_quantity > 0 ? $stockSet->set_quantity : 1;
                        $pcs_per_box = 0;
                        foreach ($stockSet->product_set_details as $detail) {
                            $pcs_per_box += ceil($detail->total_quantity / $ss_total);
                        }

                        $gen_barcode = 'D' . $entry['product_id'] . 'S' . $entry['size_set_id'] . 'C' . $entry['color_id'];
                        $final_barcode = $barcode ?: $gen_barcode;

                        $inventory = \App\Models\DomesticInventory::where('barcode', $final_barcode)
                            ->where('rack_id', $rack_id)
                            ->where('order_main_id', $data['order_id'])
                            ->first();

                        if ($inventory) {
                            $inventory->total_boxes += $qty;
                            $inventory->save();
                        } else {
                            \App\Models\DomesticInventory::create([
                                'order_main_id' => $data['order_id'],
                                'packing_main_id' => $main->id,
                                'rack_id' => $rack_id,
                                'product_id' => $entry['product_id'],
                                'color_id' => $entry['color_id'],
                                'size_set_id' => $entry['size_set_id'],
                                'quantity' => $pcs_per_box,
                                'total_boxes' => $qty,
                                'barcode' => $final_barcode,
                                'status' => 1
                            ]);
                        }

                        foreach ($stockSet->product_set_details as $detail) {
                            $pieces = ceil($detail->total_quantity / $ss_total) * $qty;
                            $this->processLotDeductionAndCreateItem($main, $carton, $detail, $pieces, $qty, $entry, $slip_details, $data);
                            
                            \App\Models\ProductionOutflowInventory::create([
                                'order_main_id' => $data['order_id'],
                                'slip_id' => $data['slip_id'],
                                'product_id' => $entry['product_id'],
                                'color_id' => $entry['color_id'],
                                'size_id' => $detail->id,
                                'quantity' => $pieces,
                                'type' => 'packing',
                                'responsible_unit_id' => $slip_details->stage_master_unit_id,
                                'remarks' => "Domestic Packing",
                                'lot_no' => !empty($entry['selected_lots']) ? $entry['selected_lots'][0] : null
                            ]);
                            $total_pcs += $pieces;
                        }
                    } else if ($entry['type'] === 'set') {
                        $set = \App\Models\OrderProductSet::with('product_set_details')->find($entry['content_id']);
                        if (!$set) throw new \Exception("Set Pattern not found.");
                        $set_total_qty = $set->set_quantity > 0 ? $set->set_quantity : 1;

                        foreach ($set->product_set_details as $detail) {
                            $ratio = $detail->total_quantity / $set_total_qty;
                            $needed_pcs = ceil($ratio * $qty); 
                            
                            if ($needed_pcs > 0) {
                                $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, $qty, $entry, $slip_details, $data);
                                $total_pcs += $needed_pcs;
                            }
                        }
                    } else {
                        $did = $entry['content_id'];
                        $detail = \App\Models\OrderProductSetDetail::find($did);
                        $needed_pcs = $qty; 
                        
                        $this->processLotDeductionAndCreateItem($main, $carton, $detail, $needed_pcs, 1, $entry, $slip_details, $data);
                        $total_pcs += $needed_pcs;
                    }
                }
                $carton_count++;
            }

            \DB::commit();
            return ['status' => 'success', 'message' => "Successfully processed $carton_count cartons ($total_pcs pieces total)."];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function finalizePacking($main_id, $completion_date = null)
    {
        DB::beginTransaction();
        try {
            $packing_main = PackingMain::with('cartons.items', 'order')->find($main_id);
            if (!$packing_main) {
                throw new \Exception("Packing record not found.");
            }

            $order = $packing_main->order;
            $is_domestic = $order && strtolower($order->order_type) == 'domestic';

            // Mark Packing Main as Finalized (1)
            $packing_main->update(['status' => 1]);

            // Packing end date: only for the lots of THIS session, and only when no packing unit
            // still holds any piece of the lot (a lot is complete when it has been fully packed / sent out)
            $completion_datetime = $completion_date ? date('Y-m-d H:i:s', strtotime($completion_date)) : now()->toDateTimeString();
            $sessionLots = PackingItem::where('packing_main_id', $packing_main->id)->whereNotNull('lot_no')->pluck('lot_no')
                ->merge(\App\Models\ProductionOutflowInventory::where('slip_id', $packing_main->slip_id)->pluck('lot_no'))
                ->merge(\App\Models\PackingSelectedLot::where('slip_id', $packing_main->slip_id)->pluck('lot_no'))
                ->filter()->unique()->values()->all();

            foreach ($sessionLots as $lotNo) {
                if ($this->packingStillHoldsLot($lotNo)) {
                    continue;
                }
                $timing = \App\Models\OrderLotStageTiming::where('lot_no', $lotNo)->where('master_stage_id', 11)->first();
                $timingData = ['complete_date' => $completion_datetime, 'status' => 2];
                if (!$timing || !$timing->start_date) {
                    $timingData['start_date'] = $completion_datetime;
                }
                if (!$timing || !$timing->end_date) {
                    $timingData['end_date'] = $completion_datetime;
                }
                \App\Models\OrderLotStageTiming::updateOrCreate(['lot_no' => $lotNo, 'master_stage_id' => 11], $timingData);
            }

            // Update non-dispatched cartons in this session (NEVER reset already dispatched cartons)
            if ($is_domestic) {
                PackingCarton::where('packing_main_id', $main_id)
                    ->where('status', '!=', 2)
                    ->update(['status' => 3]);
            } else {
                // For corporate order: Keep domestic/diverted cartons as status 3 (Inventory), update only pending cartons (status 0) to status 1 (Ready for Dispatch)
                PackingCarton::where('packing_main_id', $main_id)
                    ->whereNotIn('status', [2, 3])
                    ->update(['status' => 1]);
            }

            // If Domestic, Move to Inventory table (Consolidated)
            // Domestic inventory creation is already handled during box creation (saveDomesticBulk & saveMultiCartonPlan)

            ProductionSlipDigitization::where('id', $packing_main->slip_id)->update([
                'status' => 1
            ]);

            DB::commit();
            return [
                'status' => 'success', 
                'message' => 'Packing finalized successfully.',
                'order_type' => $order ? strtolower(trim($order->order_type)) : '',
                'packing_main_id' => $packing_main->id,
                'redirect_url' => route('admin.packing.view', $packing_main->id)
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function getPackedQuantitiesForOrder($order_id)
    {
        return PackingItem::join('packing_mains', 'packing_items.packing_main_id', '=', 'packing_mains.id')
            ->where('packing_mains.order_main_id', $order_id)
            ->select('packing_items.size_id', DB::raw('SUM(packing_items.quantity) as total_packed'))
            ->groupBy('packing_items.size_id')
            ->pluck('total_packed', 'packing_items.size_id')
            ->toArray();
    }
    public function createAdHocSet($data)
    {
        DB::beginTransaction();
        try {
            // Check if Order exists
            $order = OrderMain::findOrFail($data['order_id']);

            // Create OrderProductSet
            // We need to fill required fields. Some might be null or dummy for ad-hoc sets.
            // Assuming 0 for price related fields if not critical for packing.
            $set = \App\Models\OrderProductSet::create([
                'order_main_id' => $order->id,
                'order_id' => $order->id, // Legacy field?
                'set_quantity' => $data['quantity'], // Total Sets requested
                'remark' => 'Created via Ad-Hoc Packing',
                'status' => 1,
                // Required fields based on Model fillable (assuming nullable in DB or defaults)
                // If strict mode is on, we might fail if we don't provide everything.
                // Assuming basic fields are enough for now based on context.
                'company_id' => $order->company_id ?? 1,
            ]);

            // Create Details
            if (isset($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $item) {
                    $qty_per_set = $item['qty_per_set'];
                    $total_qty = $data['quantity'] * $qty_per_set;

                    \App\Models\OrderProductSetDetail::create([
                        'order_products_set_id' => $set->id,
                        'size' => $item['size_name'], // String name (e.g., "22")
                        'order_product_id' => $item['product_id'] ?? null, // Link to original product if known
                        'total_quantity' => $total_qty,
                        'remaining_quantity' => $total_qty, // Initially all remaining
                        'color_id' => $item['color_id'] ?? null
                    ]);
                }
            }

            DB::commit();
            return ['status' => 'success', 'set' => $set];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
        public function deleteCarton($carton_id)
    {
        \DB::beginTransaction();
        try {
            $carton = \App\Models\PackingCarton::with(['items', 'main'])->findOrFail($carton_id);

            // Safety: never delete a carton of a finalized session or one that is already dispatched
            $this->assertCartonDeletable($carton);

            $slip_details = \App\Models\ProductionSlipDigitization::find($carton->main->slip_id);
            if (!$slip_details) {
                throw new \Exception("Production slip details not found.");
            }

            // Give the pieces back to THIS packing unit's own balance (never another unit's, never above received)
            foreach (\App\Models\PackingItem::where('packing_carton_id', $carton->id)->get() as $item) {
                if ($item->quantity > 0 && $item->lot_no) {
                    $this->restoreUnitBalance((string) $item->lot_no, $slip_details->stage_master_unit_id, (int) $item->quantity);
                }
            }
            
            \App\Models\PackingItem::where('packing_carton_id', $carton->id)->delete();
            $carton->delete();

            \DB::commit();
            return ['status' => 'success', 'message' => 'Carton and associated items deleted successfully. Inventory restored.'];
        } catch (\Exception $e) {
            \DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

        public function reassignRework($data)
    {
        DB::beginTransaction();
        try {
            $orderMainId = $data['order_id'];
            $slipId = $data['slip_id'];
            $rackId = $data['rack_id'] ?? null;
            $toStageId = $data['to_stage_id'] ?? null;
            $toUnitId = $data['to_unit_id'] ?? null;
            $items = $data['items']; // [{detail_id, qty, lot_no}]
            $remarks = $data['remarks'] ?? 'Defect stored in rack for rework';
            $slipMain = \App\Models\ProductionSlipDigitization::findOrFail($slipId);

            $this->assertSessionOpen($slipId);
            // Only pieces this packing unit really holds can be sent to rework
            $this->assertUnitHolds($this->neededFromItems($items), $slipMain->stage_master_unit_id);

            $totalStoredPcs = 0;

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                $lotNo = $item['lot_no'];
                if ($qty <= 0) continue;

                $detail = \App\Models\OrderProductSetDetail::findOrFail($item['detail_id']);
                $set = \App\Models\OrderProductSet::findOrFail($detail->order_products_set_id);

                // 1. Create Outflow Record stored in Rack (Status: stored / pending assignment)
                \App\Models\ProductionOutflowInventory::create([
                    'type' => 'rework',
                    'order_main_id' => $orderMainId,
                    'slip_id' => $slipId,
                    'lot_no' => $lotNo,
                    'rack_id' => $rackId,
                    'product_id' => $set->production_goods_id,
                    'color_id' => $set->color_id,
                    'size_id' => $detail->id,
                    'quantity' => $qty,
                    'responsible_stage_id' => $toStageId ?: null,
                    'responsible_unit_id' => $toUnitId ?: null,
                    'remarks' => $remarks,
                    'status' => 'stored'
                ]);

                // 2. DEDUCT from this packing unit's own balance only
                $this->deductUnitBalance($lotNo, $slipMain->stage_master_unit_id, $qty);

                $totalStoredPcs += $qty;
            }

            DB::commit();
            return [
                'status' => 'success',
                'message' => "Successfully stored $totalStoredPcs defect piece(s) in rack. Admin can assign them for rework from the Rework List."
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function assignReworkFromRack($data)
    {
        DB::beginTransaction();
        try {
            $ids = $data['ids'] ?? [];
            if (empty($ids) || !is_array($ids)) {
                throw new \Exception("Please select at least one rework record to assign.");
            }

            $toStageId = $data['to_stage_id'] ?? null;
            $toUnitId = $data['to_unit_id'] ?? null;
            $remarks = $data['remarks'] ?? null;

            if (!$toStageId || !$toUnitId) {
                throw new \Exception("Please select target Stage and Unit for rework assignment.");
            }

            $records = \App\Models\ProductionOutflowInventory::with(['orderMain', 'size.orderProductSet', 'rack.storeroom', 'slip'])
                ->whereIn('id', $ids)
                ->where('type', 'rework')
                ->where('status', 'stored')
                ->get();

            if ($records->isEmpty()) {
                throw new \Exception("No pending stored rework records found for the selected items.");
            }

            // Group by slip_id & lot_no so that each lot has a distinct clean OrderStageTransaction
            $grouped = $records->groupBy(function($item) {
                return $item->slip_id . '_' . $item->lot_no;
            });

            $totalAssignedCount = 0;

            foreach ($grouped as $groupKey => $groupItems) {
                $first = $groupItems->first();
                $lotNo = $first->lot_no;
                $slipId = $first->slip_id;
                $slipMain = $first->slip;
                $totalLotPcs = $groupItems->sum('quantity');

                $rackName = $first->rack ? ($first->rack->storeroom ? $first->rack->storeroom->name . ' - ' : '') . $first->rack->name : 'Rack';
                $unitIdFrom = $slipMain ? $slipMain->stage_master_unit_id : null;

                $sourceTx = OrderStageTransaction::where('to_stage_id', 11)
                    ->where('lot_no', $lotNo)
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$sourceTx) {
                    $sourceTx = OrderStageTransaction::where('lot_no', $lotNo)->orderBy('id', 'desc')->first();
                }

                $txRemarks = ($remarks ? $remarks . ' ' : '') . "[Assigned from $rackName]";

                // Create OrderStageTransaction for REWORK
                $newTx = OrderStageTransaction::create([
                    'company_id' => $sourceTx ? $sourceTx->company_id : 1,
                    'sub_company_id' => $sourceTx ? $sourceTx->sub_company_id : null,
                    'project_id' => $sourceTx ? $sourceTx->project_id : null,
                    'sku' => $sourceTx ? $sourceTx->sku : ($first->orderMain ? $first->orderMain->sku : null),
                    'order_product_id' => $sourceTx ? $sourceTx->order_product_id : null,
                    'from_stage_id' => 11, // From Packing
                    'to_stage_id' => $toStageId,
                    'sub_stage_id' => $unitIdFrom,
                    'sub_stage_id_to' => $toUnitId,
                    'lot_no' => $lotNo,
                    'quantity' => $totalLotPcs,
                    'remaining_quantity' => $totalLotPcs,
                    'remarks' => $txRemarks,
                    'production_datetime' => now(),
                    'production_slip_digitization_id' => $slipId,
                    'status' => 1,
                    'type' => 'rework'
                ]);

                // Create transaction details & update outflow record
                foreach ($groupItems as $item) {
                    OrderStageTransactionDetail::create([
                        'order_stage_transaction_id' => $newTx->id,
                        'size' => $item->size ? $item->size->size : 'N/A',
                        'quantity' => $item->quantity
                    ]);

                    $item->status = 'assigned';
                    $item->assigned_stage_id = $toStageId;
                    $item->assigned_unit_id = $toUnitId;
                    $item->assigned_transaction_id = $newTx->id;
                    $item->assigned_at = now();
                    $item->assigned_by = auth()->id();
                    $item->save();

                    $totalAssignedCount += $item->quantity;
                }
            }

            DB::commit();
            return [
                'status' => 'success',
                'message' => "Successfully assigned $totalAssignedCount piece(s) for rework to the selected unit."
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function reassignReworkUnit($data)
    {
        DB::beginTransaction();
        try {
            $id = $data['id'];
            $toStageId = $data['to_stage_id'];
            $toUnitId = $data['to_unit_id'];
            $remarks = $data['remarks'] ?? null;

            if (!$toStageId || !$toUnitId) {
                throw new \Exception("Please select the new Stage and Unit.");
            }

            $outflow = \App\Models\ProductionOutflowInventory::lockForUpdate()->findOrFail($id);
            if ($outflow->type !== 'rework') {
                throw new \Exception("Invalid record type.");
            }

            $records = collect([$outflow]);
            if ($outflow->assigned_transaction_id) {
                $tx = OrderStageTransaction::lockForUpdate()->find($outflow->assigned_transaction_id);
                if ($tx) {
                    // the unit may not have started on it yet (same rule as deleting a rework)
                    $this->assertReworkNotStarted($tx, 'moved to another unit');
                    $tx->to_stage_id = $toStageId;
                    $tx->sub_stage_id_to = $toUnitId;
                    if ($remarks) {
                        $tx->remarks = $remarks;
                    }
                    $tx->save();
                }
                // every defect record sent in the same rework move goes with it
                $records = \App\Models\ProductionOutflowInventory::where('assigned_transaction_id', $outflow->assigned_transaction_id)->get();
            }

            foreach ($records as $record) {
                $record->assigned_stage_id = $toStageId;
                $record->assigned_unit_id = $toUnitId;
                if ($remarks) {
                    $record->remarks = $remarks;
                }
                $record->save();
            }

            DB::commit();
            return ['status' => 'success', 'message' => $records->count() > 1
                ? "Rework unit assignment updated for {$records->count()} records sent together in the same rework."
                : "Rework unit assignment updated successfully."];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    public function recordDeadStock($data)
    {
        return $this->recordConsolidatedOutflow('dead', $data);
    }



    public function checkCartonNo($carton_no)
    {
        $exists = PackingCarton::where('carton_no', $carton_no)->exists();
        return $exists;
    }

    public function recordSamplingStock($data)
    {
        return $this->recordConsolidatedOutflow('sampling', $data);
    }

    public function recordUnitDebit($data)
    {
        return $this->recordConsolidatedOutflow('debit', $data);
    }

        private function recordConsolidatedOutflow($type, $data)
    {
        DB::beginTransaction();
        try {
            $orderMainId = $data['order_id'];
            $slipId = $data['slip_id'];
            $rackId = $data['rack_id'] ?? null;
            $items = $data['items']; // [{detail_id, qty, lot_no}]
            $remarks = $data['remarks'] ?? ($type . ' outflow');

            $slipMain = \App\Models\ProductionSlipDigitization::findOrFail($slipId);

            $this->assertSessionOpen($slipId);
            // Only pieces this packing unit really holds can be recorded as dead / sampling / debit
            $this->assertUnitHolds($this->neededFromItems($items), $slipMain->stage_master_unit_id);

            foreach ($items as $item) {
                $qty = (int) $item['qty'];
                $lotNo = $item['lot_no'];
                if ($qty <= 0)
                    continue;

                $detail = \App\Models\OrderProductSetDetail::findOrFail($item['detail_id']);
                $set = \App\Models\OrderProductSet::findOrFail($detail->order_products_set_id);

                // Find a source transaction that contains pieces for this specific SKU (or at least this Lot)
                $sourceTx = OrderStageTransaction::where('to_stage_id', 11)
                    ->where(function($q) use ($slipMain) {
                        $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                          ->orWhereNull('sub_stage_id_to');
                    })
                    ->where('lot_no', $lotNo)
                    ->where(function ($q) use ($set) {
                        $q->where('sku', $set->sku)->orWhereNull('sku');
                    })
                    ->orderBy('id', 'desc')
                    ->first();

                if (!$sourceTx) {
                    // Fallback to ANY transaction for this lot if SKU mismatch (common in Domestic)
                    $sourceTx = OrderStageTransaction::where('to_stage_id', 11)
                        ->where(function($q) use ($slipMain) {
                            $q->where('sub_stage_id_to', $slipMain->stage_master_unit_id)
                              ->orWhereNull('sub_stage_id_to');
                        })
                        ->where('lot_no', $lotNo)
                        ->orderBy('id', 'desc')
                        ->first();
                }

                if (!$sourceTx)
                    throw new \Exception("No incoming production pieces found for piece: " . $detail->size . " in Lot $lotNo");

                // 2. Create Log Record
                $outflowLog = \App\Models\ProductionOutflowInventory::create([
                    'type' => $type,
                    'order_main_id' => $orderMainId,
                    'slip_id' => $slipId,
                    'lot_no' => $lotNo,
                    'rack_id' => $rackId,
                    'product_id' => $set->production_goods_id,
                    'color_id' => $set->color_id,
                    'size_id' => $detail->id,
                    'quantity' => $qty,
                    'responsible_stage_id' => $data['stage_id'] ?? null,
                    'responsible_unit_id' => $data['unit_id'] ?? $slipMain->stage_master_unit_id,
                    'per_piece_amount' => $item['per_piece_amount'] ?? 0,
                    'total_amount' => ($item['per_piece_amount'] ?? 0) * $qty,
                    'discount' => $data['discount'] ?? 0,
                    'remarks' => $remarks
                ]);

                // 3. Deduct from this packing unit's own balance only
                $this->deductUnitBalance($lotNo, $slipMain->stage_master_unit_id, $qty);
            }

            DB::commit();
            return ['status' => 'success', 'message' => "Successfully recorded $type pieces."];

        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
    public function deleteOutflow($id)
    {
        DB::beginTransaction();
        try {
            $outflow = \App\Models\ProductionOutflowInventory::with('orderMain')->findOrFail($id);
            if ($outflow->type === 'rework') {
                // rework records have their own delete (it also removes the rework move sent to a unit)
                DB::rollBack();
                return $this->deleteRework($outflow->id);
            }
            $slip = \App\Models\ProductionSlipDigitization::find($outflow->slip_id);

            // 1. MIRROR REMOVAL - only an exact barcode link. (The old "guess by sku/type" fallback is gone:
            //    the type column is numeric, so it could match and delete an unrelated rework move.)
            $mirror = null;
            if (!empty($outflow->barcode)) {
                $mirror = \App\Models\OrderStageTransaction::where('remarks', 'LIKE', "%[MirrorLink:" . $outflow->barcode . "]%")->first();
            }

            if ($mirror) {
                if ($mirror->quantity <= $outflow->quantity) {
                    $mirror->details()->delete();
                    $mirror->delete();
                } else {
                    $mirror->quantity -= $outflow->quantity;
                    $mirror->save();
                    $det = $mirror->details()->first();
                    if ($det) {
                        $det->quantity = max(0, $det->quantity - $outflow->quantity);
                        $det->save();
                    }
                }
            }

            // 2. Give the pieces back to this packing unit's own balance (never another unit's)
            $packingUnitId = $slip ? $slip->stage_master_unit_id : null;
            if ($packingUnitId && $outflow->lot_no) {
                $this->restoreUnitBalance((string) $outflow->lot_no, $packingUnitId, (int) $outflow->quantity);
            }

            $outflow->delete();
            DB::commit();
            return ['status' => 'success', 'message' => 'Record deleted and stock restored.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete a rework record (ProductionOutflowInventory id - what the pack screen and the Rework List send).
     * If it was already assigned to a unit and that unit has started on it, it cannot be deleted.
     */
    public function deleteRework($id)
    {
        DB::beginTransaction();
        try {
            $outflow = \App\Models\ProductionOutflowInventory::find($id);
            if (!$outflow || $outflow->type !== 'rework') {
                throw new \Exception("Rework record not found.");
            }

            if ($outflow->assigned_transaction_id) {
                $assignedTx = \App\Models\OrderStageTransaction::find($outflow->assigned_transaction_id);
                if ($assignedTx) {
                    $this->assertReworkNotStarted($assignedTx);
                    $assignedTx->details()->delete();
                    $assignedTx->delete();
                }
            }

            // Give the pieces back to this packing unit's own balance
            $slip = \App\Models\ProductionSlipDigitization::find($outflow->slip_id);
            if ($slip && $outflow->lot_no) {
                $this->restoreUnitBalance((string) $outflow->lot_no, $slip->stage_master_unit_id, (int) $outflow->quantity);
            }

            $outflow->delete();
            DB::commit();
            return ['status' => 'success', 'message' => 'Defect/Rework record deleted and stock restored to packing.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * Delete an old-style rework move (OrderStageTransaction from Packing) - used when a packing session is reset.
     */
    public function deleteLegacyRework($txId)
    {
        DB::beginTransaction();
        try {
            $rework = \App\Models\OrderStageTransaction::find($txId);
            if (!$rework || $rework->from_stage_id != 11 || (int) $rework->type !== 0) {
                throw new \Exception("Rework move not found.");
            }
            $this->assertReworkNotStarted($rework);

            if ($rework->sub_stage_id && $rework->lot_no) {
                $this->restoreUnitBalance((string) $rework->lot_no, $rework->sub_stage_id, (int) $rework->quantity);
            }
            $rework->details()->delete();
            $rework->delete();

            DB::commit();
            return ['status' => 'success', 'message' => 'Rework deleted and pieces reverted to stock.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }

    /**
     * A rework move that the receiving unit has already started on (sent some on, or closed) must not be deleted.
     */
    private function assertReworkNotStarted(\App\Models\OrderStageTransaction $tx, string $action = 'deleted'): void
    {
        $started = $tx->remaining_quantity < $tx->quantity || !empty($tx->image) || $tx->is_closed_for_unit == 1
            || \App\Models\OrderStageTransaction::where('lot_no', $tx->lot_no)->where('from_stage_id', $tx->to_stage_id)
                ->where('sub_stage_id', $tx->sub_stage_id_to)->where('id', '>', $tx->id)->exists();
        if ($started) {
            $unit = \App\Models\StageMasterUnit::find($tx->sub_stage_id_to);
            throw new \Exception("Lot {$tx->lot_no}: the rework was already started by " . ($unit->name ?? 'the unit') . ", so it cannot be {$action}.");
        }
    }

    public function deletePackingSession($slipId)
    {
        DB::beginTransaction();
        try {
            $main = PackingMain::where('slip_id', $slipId)->first();
            if (!$main) {
                $main = PackingMain::find($slipId);
            }
            if (!$main) {
                throw new \Exception("Packing session not found for this slip.");
            }

            // Safety check: Ensure no cartons are dispatched
            $cartonIds = PackingCarton::where('packing_main_id', $main->id)->pluck('id')->toArray();
            $hasDispatchedCartons = PackingCarton::where('packing_main_id', $main->id)->where('status', 2)->exists()
                || (!empty($cartonIds) && \App\Models\OrderDispatchDetails::whereIn('carton_packing_id', $cartonIds)->exists())
                || ($main->order && $main->order->status == 3);

            if ($hasDispatchedCartons) {
                throw new \Exception("Cannot delete packing session because some or all cartons have already been dispatched.");
            }

            $is_domestic = false;
            $order = \App\Models\OrderMain::find($main->order_main_id);
            if ($order && strtolower(trim($order->order_type)) === 'domestic') {
                $is_domestic = true;
            }

            // Lots this session worked on (only their packing end date is cleared below)
            $sessionLots = PackingItem::where('packing_main_id', $main->id)->whereNotNull('lot_no')->pluck('lot_no')
                ->merge(\App\Models\ProductionOutflowInventory::where('slip_id', $main->slip_id)->pluck('lot_no'))
                ->merge(\App\Models\PackingSelectedLot::where('slip_id', $main->slip_id)->pluck('lot_no'))
                ->filter()->unique()->values()->all();

            // Every step must succeed - otherwise nothing is reset
            $must = function ($result, $what) {
                if (($result['status'] ?? 'error') !== 'success') {
                    throw new \Exception("Reset stopped while undoing {$what}: " . ($result['message'] ?? 'unknown error') . ' Nothing was changed.');
                }
            };

            // 1. Undo this slip's dead / sampling / debit / rework records
            $outflows = \App\Models\ProductionOutflowInventory::where('slip_id', $main->slip_id)->get();
            foreach ($outflows as $outflow) {
                $must($outflow->type === 'rework' ? $this->deleteRework($outflow->id) : $this->deleteOutflow($outflow->id), "{$outflow->type} record #{$outflow->id}");
            }

            // 2. Undo old-style rework moves made on THIS slip only (never other slips of the same order)
            $assigned = \App\Models\ProductionOutflowInventory::whereNotNull('assigned_transaction_id')->pluck('assigned_transaction_id')->all();
            $reworks = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $main->slip_id)
                ->where('from_stage_id', 11)
                ->where('type', 0)
                ->when(!empty($assigned), fn($q) => $q->whereNotIn('id', $assigned))
                ->get();
            foreach ($reworks as $rework) {
                $must($this->deleteLegacyRework($rework->id), "rework move #{$rework->id}");
            }

            // 3. Delete All Cartons (This will restore OrderStageTransaction stock)
            $cartons = PackingCarton::where('packing_main_id', $main->id)->get();
            foreach ($cartons as $carton) {
                // We use the existing deleteCarton but remove the finalized check temporarily
                $carton->main->status = 0; // Temporarily un-finalize to allow deletion
                $carton->main->save();

                if ($is_domestic) {
                    $packingItems = \App\Models\PackingItem::where('packing_carton_id', $carton->id)->get();
                    foreach ($packingItems as $item) {
                        $od = \App\Models\OrderProductSetDetail::find($item->size_id);
                        if ($od) {
                            $od->remaining_quantity += $item->quantity;
                            $od->save();
                        }
                    }
                }

                $must($this->deleteCarton($carton->id), "carton {$carton->carton_no}");
            }

            // 4. Clear the packing end date of this session's lots only
            if (!empty($sessionLots)) {
                \App\Models\OrderLotStageTiming::whereIn('lot_no', $sessionLots)
                    ->where('master_stage_id', 11)
                    ->update(['complete_date' => null, 'status' => 1]);
            }

            // 5. Delete Domestic Inventory, Selected Lots, and Main Record
            \App\Models\DomesticInventory::where('packing_main_id', $main->id)->delete();
            \App\Models\PackingSelectedLot::where('slip_id', $main->slip_id)->delete();

            // 6. Reset Production Slip Digitization Status to 0 (Pending Packing) if slip exists
            if ($main->slip_id) {
                \App\Models\ProductionSlipDigitization::where('id', $main->slip_id)->update(['status' => 0]);
            }

            $main->delete();

            DB::commit();
            return ['status' => 'success', 'message' => 'Packing session deleted and stock restored successfully.'];
        } catch (\Exception $e) {
            DB::rollBack();
            return ['status' => 'error', 'message' => $e->getMessage()];
        }
    }
}

