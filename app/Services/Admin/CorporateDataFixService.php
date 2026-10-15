<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * One-time repair of corporate order data left by bugs that are now fixed.
 *
 *  1. Cutting tasks made twice by a double click (same order line, same cutting master, same quantity, within 15
 *     seconds) on which nothing was cut, while the line has more pieces given to cutting than ordered, and empty
 *     tasks of 0 pieces: deleted.
 *  2. Cartons that are in a dispatch but show "ready": marked dispatched again.
 *  3. Corporate orders whose status does not match what was dispatched: status set from the dispatched pieces.
 *  Lists only (nothing changed): order lines given to cutting more than once where the extra task was already
 *  used, dispatches whose saved total differs from their items, purchase lines whose boxes are no longer in stock.
 *
 * run(false) = preview (everything is done inside a transaction and rolled back),
 * run(true)  = apply (committed, and every old value is saved to storage/app/corporate-fix/).
 */
class CorporateDataFixService
{
    private const TABLES = ['order_cutting_stage', 'packing_cartons', 'order_main'];

    public function run(bool $apply): array
    {
        @set_time_limit(600);

        $notSafe = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', self::TABLES)->where('ENGINE', '!=', 'InnoDB')->pluck('TABLE_NAME');
        if ($notSafe->isNotEmpty()) {
            throw new \RuntimeException('These tables cannot undo changes (not InnoDB), so the repair will not run: ' . $notSafe->implode(', '));
        }

        DB::beginTransaction();
        try {
            $undo = [];
            $result = [
                'tasks' => $this->duplicateTasks($undo),
                'cartons' => $this->dispatchedCartons($undo),
                'orders' => $this->orderStatuses($undo),
                'over_assigned' => $this->overAssigned(),
                'totals' => $this->dispatchTotals(),
                'no_stock' => $this->purchaseWithoutStock(),
                'log_file' => null,
            ];

            if ($apply) {
                $file = 'corporate-fix/corporate-fix-' . now()->format('Ymd-His') . '.json';
                Storage::disk('local')->put($file, json_encode([
                    'run_at' => now()->toDateTimeString(),
                    'run_by' => auth()->id(),
                    'old_values' => $undo,
                ], JSON_PRETTY_PRINT));
                $result['log_file'] = $file;
                DB::commit();
            } else {
                DB::rollBack();
            }
            return $result;
        } catch (\Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /** 1. Double-click copies of a cutting task (not started) and empty tasks of 0 pieces. */
    private function duplicateTasks(array &$undo): array
    {
        $tasks = DB::table('order_cutting_stage')->where('is_po', 0)->orderBy('id')->get();
        $ordered = DB::table('order_products_sets')->pluck('total_quantity', 'id');
        // pieces given to cutting per order line (all tasks, PO lines too)
        $assigned = DB::table('order_cutting_stage')->groupBy('set_product_id')->selectRaw('set_product_id, SUM(quantity) AS q')->pluck('q', 'set_product_id')->map(fn($q) => (int) $q)->all();
        $remove = [];
        foreach ($tasks as $t) {
            if ((int) $t->quantity <= 0 && (int) $t->remaining_quantity <= 0) {
                $remove[$t->id] = [$t, 'Empty task of 0 pieces'];
                continue;
            }
            // nothing cut on it (a copy closed with Task Complete but never cut counts too)
            $notStarted = (int) $t->remaining_quantity === (int) $t->quantity;
            if (!$notStarted) {
                continue;
            }
            $earlier = $tasks->first(fn($e) => $e->id < $t->id && (int) $e->set_product_id === (int) $t->set_product_id
                && (int) $e->to_assign_id === (int) $t->to_assign_id && (int) $e->quantity === (int) $t->quantity
                && abs(strtotime($t->created_at) - strtotime($e->created_at)) <= 15 && !isset($remove[$e->id]));
            // only a surplus copy: the line keeps at least the pieces it ordered
            $surplus = isset($assigned[$t->set_product_id]) && $assigned[$t->set_product_id] - (int) $t->quantity >= (int) ($ordered[$t->set_product_id] ?? PHP_INT_MAX);
            if ($earlier && $surplus) {
                $assigned[$t->set_product_id] -= (int) $t->quantity;
                $remove[$t->id] = [$t, "Copy of task {$earlier->sku} made " . abs(strtotime($t->created_at) - strtotime($earlier->created_at)) . ' second(s) later'];
            }
        }

        $out = [];
        foreach ($remove as $id => [$t, $why]) {
            $set = DB::table('order_products_sets')->where('id', $t->set_product_id)->first();
            $undo[] = ['table' => 'order_cutting_stage', 'id' => $id, '_deleted' => true, 'old' => (array) $t];
            DB::table('order_cutting_stage')->where('id', $id)->delete();
            $unit = DB::table('stage_master_units')->where('id', $t->to_assign_id)->value('name');
            $out[] = ['task' => $t->sku, 'order_line' => $set->sku ?? '-', 'design' => $set->design_number ?? '-', 'unit' => $unit ?? '-',
                'pieces' => (int) $t->quantity, 'pending' => (int) $t->remaining_quantity, 'created' => $t->created_at, 'why' => $why,
                'order_id' => $t->order_main_id];
        }
        return $out;
    }

    /** 2. Cartons in a dispatch that show "ready" (not moved to stock). */
    private function dispatchedCartons(array &$undo): array
    {
        $rows = DB::table('order_dispatch_details as d')->join('packing_cartons as c', 'c.id', '=', 'd.carton_packing_id')
            ->join('order_dispatch as o', 'o.id', '=', 'd.order_dispatch_id')->where('c.status', '!=', 2)
            ->whereNotExists(fn($q) => $q->from('domestic_inventories as di')->whereColumn('di.packing_carton_id', 'c.id'))
            ->select('c.id', 'c.carton_no', 'c.status', 'c.updated_at', 'o.sku as dispatch', 'o.id as dispatch_id')->orderBy('c.id')->get();
        foreach ($rows as $r) {
            $undo[] = ['table' => 'packing_cartons', 'id' => $r->id, 'old' => ['status' => $r->status, 'updated_at' => $r->updated_at]];
        }
        if ($rows->isNotEmpty()) {
            DB::table('packing_cartons')->whereIn('id', $rows->pluck('id')->all())->update(['status' => 2]);
        }
        return $rows->map(fn($r) => (array) $r)->all();
    }

    /** 3. Corporate orders whose status does not follow the dispatched pieces. */
    private function orderStatuses(array &$undo): array
    {
        $dispatch = app(OrderDispatchService::class);
        $names = [1 => 'Pending', 2 => 'Partly dispatched', 3 => 'Completed'];
        $out = [];
        foreach (DB::table('order_main')->where('order_type', 'corporate')->orderBy('id')->get() as $o) {
            $d = $dispatch->getOrderDispatchData($o->id);
            if ($d['packed'] <= 0) {
                continue;
            }
            $should = $d['remaining'] == 0 ? 3 : 2;
            if ((int) $o->status >= $should) {
                continue; // only raised (an order closed by hand stays closed)
            }
            $undo[] = ['table' => 'order_main', 'id' => $o->id, 'old' => ['status' => $o->status, 'updated_at' => $o->updated_at]];
            DB::table('order_main')->where('id', $o->id)->update(['status' => $should]);
            $out[] = ['id' => $o->id, 'order' => $o->sku, 'dispatched' => $d['packed'], 'ordered' => $d['total'],
                'was' => $names[(int) $o->status] ?? ('status ' . $o->status), 'now' => $names[$should]];
        }
        return $out;
    }

    /** List only: order lines given to cutting for more pieces than ordered, where the extra task was already used. */
    private function overAssigned(): array
    {
        return DB::table('order_products_sets as s')
            ->selectRaw('s.id, s.sku, s.design_number, s.total_quantity, (SELECT SUM(c.quantity) FROM order_cutting_stage c WHERE c.set_product_id = s.id) AS assigned, (SELECT GROUP_CONCAT(CONCAT(c.sku, ": ", c.quantity, " pcs, ", c.remaining_quantity, " not cut") SEPARATOR " | ") FROM order_cutting_stage c WHERE c.set_product_id = s.id) AS tasks')
            ->havingRaw('assigned > s.total_quantity')->orderBy('s.id')->get()->map(fn($r) => (array) $r)->all();
    }

    /** List only: dispatches whose saved total differs from their items (bill rule: items - discount + other charges + GST). */
    private function dispatchTotals(): array
    {
        $svc = app(OrderDispatchService::class);
        $out = [];
        foreach (\App\Models\OrderDispatch::orderBy('id')->get() as $d) {
            $v = $svc->view(new \Illuminate\Http\Request(['id' => $d->id]));
            if (!$v) {
                continue;
            }
            $should = (float) $v['filteredGrandTotal'];
            if (abs((float) $d->total_amount - $should) > 1) {
                $out[] = ['id' => $d->id, 'dispatch' => $d->sku, 'saved' => (float) $d->total_amount, 'items' => (float) $v['filteredSubtotal'], 'by_items' => $should,
                    'difference' => round((float) $d->total_amount - $should, 2)];
            }
        }
        return $out;
    }

    /** List only: purchase lines of production POs waiting for dispatch whose boxes are no longer in stock. */
    private function purchaseWithoutStock(): array
    {
        return DB::table('domestic_inventory_histories as h')
            ->join('domestic_inventory_purchases as p', 'p.id', '=', 'h.purchase_id')
            ->join('production_pos as po', 'po.id', '=', 'p.production_po_id')
            ->leftJoin('order_main as m', 'm.id', '=', 'po.order_main_id')
            ->leftJoin('production_goods as g', 'g.id', '=', 'h.new_product_id')
            ->whereNotIn('h.id', fn($q) => $q->from('order_dispatch_purchases')->select('purchase_history_id'))
            ->selectRaw('h.id, po.po_number, m.sku AS order_sku, g.design_number, h.box_quantity, h.pieces_per_box,
                (SELECT COALESCE(SUM(di.total_boxes), 0) FROM domestic_inventories di WHERE di.product_id = h.new_product_id AND di.color_id = h.new_color_id
                    AND di.size_set_id = h.new_size_set_id AND (h.pieces_per_box IS NULL OR h.pieces_per_box = 0 OR di.quantity = h.pieces_per_box)) AS in_stock')
            ->havingRaw('in_stock < h.box_quantity')->orderBy('h.id')->get()->map(fn($r) => (array) $r)->all();
    }
}
