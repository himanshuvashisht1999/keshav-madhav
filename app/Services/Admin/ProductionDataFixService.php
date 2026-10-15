<?php

namespace App\Services\Admin;

use Illuminate\Support\Facades\DB;

/**
 * One-time repair of old production data (Digitized Slips), following the business rules:
 *  - A unit's task for a lot is complete when its whole quantity has been sent on (balance 0),
 *    whichever button was used ("Save & Finalize" only finishes the slip itself).
 *  - Stitching counts only the Cutting pieces; Embroidery parts of a lot are the same pieces.
 *    Once a lot has completely left Stitching, nothing of it can still be pending at any stitching unit.
 *  - Embroidery parts waiting in Godam move to Stitching automatically when Cutting sends the lot.
 *  - A stage is finished for a lot when no unit of that stage still holds any of it.
 *  - Packing: what a packing unit holds is worked out from its own records (received - packed -
 *    dead/sampling/debit/rework), the same figure the packing screen uses.
 *
 * run(false) = preview (everything is done inside a transaction and rolled back),
 * run(true)  = apply (committed, and every old value is saved to storage/app/production-fix/).
 */
class ProductionDataFixService
{
    private const ENTRY_TABLES = [
        'order_stage_transactions' => 'Transfer',
        'order_printing_stage_transactions' => 'Printing',
        'order_printing_to_stiching_transactions' => 'Embroidery to Stitching',
    ];

    public function run(bool $apply): array
    {
        @set_time_limit(900);

        // The preview relies on rolling the transaction back: every table touched must support that (InnoDB)
        $tables = ['order_stage_transactions', 'order_printing_stage_transactions', 'order_printing_to_stiching_transactions',
            'order_godam_stage_transactions', 'order_godam_stage_transaction_details', 'order_lot_stage_timings', 'packing_cartons'];
        $notSafe = DB::table('information_schema.TABLES')->where('TABLE_SCHEMA', DB::getDatabaseName())
            ->whereIn('TABLE_NAME', $tables)->where('ENGINE', '!=', 'InnoDB')->pluck('TABLE_NAME');
        if ($notSafe->isNotEmpty()) {
            throw new \RuntimeException('These tables cannot undo changes (not InnoDB), so the repair will not run: ' . $notSafe->implode(', '));
        }

        DB::beginTransaction();
        try {
            $units = DB::table('stage_master_units')->pluck('name', 'id');
            $stages = DB::table('master_product_stages')->pluck('name', 'id');
            $outgoing = $this->outgoingIndex();
            $undo = [];

            // Packing first: it settles packing balances before finished entries are marked below
            $packing = $this->fixPacking($units, $undo);
            $stitching = $this->closeStitching($units, $outgoing, $undo);
            $godam = $this->closeGodamParts($units, $undo);
            $completed = $this->markFinishedEntries($units, $stages, $outgoing, $undo);
            $timings = $this->fixStageEndDates($stages, $outgoing, $undo);

            $result = [
                'stitching' => $stitching,
                'godam' => $godam,
                'completed' => $completed,
                'timings_done' => $timings['done'],
                'timings_reopened' => $timings['reopened'],
                'packing' => $packing,
                'log_file' => null,
            ];

            if ($apply) {
                $file = 'production-fix/production-fix-' . now()->format('Ymd-His') . '.json';
                \Illuminate\Support\Facades\Storage::disk('local')->put($file, json_encode([
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

    private function key($lot): string
    {
        return mb_strtolower(rtrim((string) $lot));
    }

    /**
     * Latest onward move per "stage|unit|lot" and per "stage|*|lot" (date + slip file),
     * from every kind of move (transfer, printing, embroidery->stitching, godam).
     */
    private function outgoingIndex(): array
    {
        $rows = collect();
        foreach (['order_stage_transactions', 'order_printing_stage_transactions', 'order_printing_to_stiching_transactions', 'order_godam_stage_transactions'] as $t) {
            $rows = $rows->concat(DB::table($t)->select('lot_no', 'from_stage_id', 'sub_stage_id', 'production_slip_digitization_id', 'production_datetime', 'created_at')->get());
        }
        $slipFiles = DB::table('production_slip_digitization')
            ->whereIn('id', $rows->pluck('production_slip_digitization_id')->filter()->unique()->values())
            ->pluck('slip_file', 'id');

        $index = [];
        foreach ($rows as $r) {
            $when = $r->production_datetime ?: $r->created_at;
            $entry = ['date' => $when, 'file' => $slipFiles[$r->production_slip_digitization_id] ?? null];
            foreach ([$r->from_stage_id . '|' . $r->sub_stage_id . '|' . $this->key($r->lot_no), $r->from_stage_id . '|*|' . $this->key($r->lot_no)] as $k) {
                if (!isset($index[$k]) || $when > $index[$k]['date']) {
                    $index[$k] = $entry;
                }
            }
        }
        return $index;
    }

    /**
     * Open Stitching entries that are not real work any more:
     *  (a) the lot has completely left Stitching (sent on >= Cutting pieces received), or
     *  (b) Embroidery parts at a unit that has already sent all its Cutting pieces of the lot.
     */
    private function closeStitching($units, array $outgoing, array &$undo): array
    {
        $cut = DB::table('order_stage_transactions')->where('to_stage_id', 4)
            ->select('lot_no', DB::raw('SUM(quantity) q'))->groupBy('lot_no')->pluck('q', 'lot_no')
            ->mapWithKeys(fn($q, $l) => [$this->key($l) => (float) $q]);
        $sent = collect();
        foreach (['order_stage_transactions', 'order_printing_stage_transactions', 'order_godam_stage_transactions'] as $t) {
            DB::table($t)->where('from_stage_id', 4)->select('lot_no', DB::raw('SUM(quantity) q'))->groupBy('lot_no')->get()
                ->each(function ($r) use (&$sent) {
                    $k = $this->key($r->lot_no);
                    $sent[$k] = ($sent[$k] ?? 0) + (float) $r->q;
                });
        }
        // Cutting rows per unit+lot: [any, any still open]
        $cutRows = [];
        foreach (DB::table('order_stage_transactions')->where('to_stage_id', 4)->select('lot_no', 'sub_stage_id_to', 'remaining_quantity')->get() as $r) {
            $k = $r->sub_stage_id_to . '|' . $this->key($r->lot_no);
            $cutRows[$k] = ($cutRows[$k] ?? false) || $r->remaining_quantity > 0;
        }

        $closed = [];
        foreach (['order_stage_transactions' => 'Cutting', 'order_printing_to_stiching_transactions' => 'Embroidery'] as $table => $source) {
            $open = DB::table($table)->where('to_stage_id', 4)->where('remaining_quantity', '>', 0)->get();
            foreach ($open as $row) {
                $lk = $this->key($row->lot_no);
                $leftStitching = ($cut[$lk] ?? 0) > 0 && ($sent[$lk] ?? 0) >= $cut[$lk];
                $unitKey = $row->sub_stage_id_to . '|' . $lk;
                $unitCuttingDone = $source === 'Embroidery' && array_key_exists($unitKey, $cutRows) && $cutRows[$unitKey] === false;
                if (!$leftStitching && !$unitCuttingDone) continue;

                $thisUnitSent = isset($outgoing['4|' . $row->sub_stage_id_to . '|' . $lk]);
                $reason = $unitCuttingDone
                    ? 'Embroidery parts of a lot this unit has finished'
                    : ($thisUnitSent ? 'Lot has already left Stitching' : 'Lot was stitched and sent on by another stitching unit');

                $undo[] = ['table' => $table, 'id' => $row->id, 'remaining_quantity' => $row->remaining_quantity, 'status' => $row->status];
                DB::table($table)->where('id', $row->id)->update(['remaining_quantity' => 0, 'status' => 2]);
                $closed[] = [
                    'lot_no' => $row->lot_no, 'unit' => $units[$row->sub_stage_id_to] ?? ('#' . $row->sub_stage_id_to),
                    'source' => $source, 'pcs' => (float) $row->remaining_quantity, 'reason' => $reason,
                ];
            }
        }
        usort($closed, fn($a, $b) => [$a['unit'], $a['lot_no']] <=> [$b['unit'], $b['lot_no']]);
        return $closed;
    }

    /**
     * Embroidery parts still sitting in Godam although Cutting has already sent the lot to Stitching
     * (they move to Stitching automatically with the Cutting pieces).
     */
    private function closeGodamParts($units, array &$undo): array
    {
        $closed = [];
        $rows = DB::table('order_godam_stage_transactions as g')
            ->where('g.from_stage_id', 1)->where('g.remaining_quantity', '>', 0)
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('order_stage_transactions as s')
                    ->whereColumn('s.lot_no', 'g.lot_no')->where('s.to_stage_id', 4);
            })
            ->select('g.*')->get();
        foreach ($rows as $row) {
            $details = DB::table('order_godam_stage_transaction_details')->where('order_godam_stage_transaction_id', $row->id)->get(['id', 'remaining_quantity']);
            $undo[] = ['table' => 'order_godam_stage_transactions', 'id' => $row->id, 'remaining_quantity' => $row->remaining_quantity, 'status' => $row->status, 'complete_date' => $row->complete_date,
                'details' => $details->map(fn($d) => ['id' => $d->id, 'remaining_quantity' => $d->remaining_quantity])->all()];
            DB::table('order_godam_stage_transactions')->where('id', $row->id)->update([
                'remaining_quantity' => 0, 'status' => 2, 'complete_date' => $row->complete_date ?: now(),
            ]);
            DB::table('order_godam_stage_transaction_details')->where('order_godam_stage_transaction_id', $row->id)->update(['remaining_quantity' => 0]);
            $closed[] = ['lot_no' => $row->lot_no, 'unit' => $units[$row->sub_stage_id_to] ?? ('#' . $row->sub_stage_id_to), 'pcs' => (float) $row->remaining_quantity];
        }
        return $closed;
    }

    /**
     * Entries whose whole quantity has been sent on but which were never marked complete.
     */
    private function markFinishedEntries($units, $stages, array $outgoing, array &$undo): array
    {
        $done = [];
        foreach (self::ENTRY_TABLES as $table => $kind) {
            $rows = DB::table($table)->where('remaining_quantity', '<=', 0)
                ->whereNull('image')
                ->where(function ($q) {
                    $q->whereNull('is_closed_for_unit')->orWhere('is_closed_for_unit', '!=', 1);
                })->get();
            foreach ($rows as $row) {
                $lk = $this->key($row->lot_no);
                $out = $outgoing[$row->to_stage_id . '|' . $row->sub_stage_id_to . '|' . $lk]
                    ?? $outgoing[$row->to_stage_id . '|*|' . $lk]
                    ?? null;
                $update = ['is_closed_for_unit' => 1];
                if (empty($row->image) && $out && $out['file']) $update['image'] = $out['file'];
                if (empty($row->complete_date)) $update['complete_date'] = $out['date'] ?? $row->updated_at;

                $undo[] = ['table' => $table, 'id' => $row->id, 'is_closed_for_unit' => $row->is_closed_for_unit, 'image' => $row->image, 'complete_date' => $row->complete_date];
                DB::table($table)->where('id', $row->id)->update($update);
                $done[] = [
                    'lot_no' => $row->lot_no, 'unit' => $units[$row->sub_stage_id_to] ?? ('#' . $row->sub_stage_id_to),
                    'stage' => $stages[$row->to_stage_id] ?? '-', 'kind' => $kind,
                    'completed_on' => $update['complete_date'] ?? $row->complete_date,
                ];
            }
        }
        usort($done, fn($a, $b) => [$a['stage'], $a['unit'], $a['lot_no']] <=> [$b['stage'], $b['unit'], $b['lot_no']]);
        return $done;
    }

    /**
     * Stage end date per lot: set it when no unit of the stage still holds the lot,
     * clear it when a unit of the stage still holds some of it.
     * Not touched: Cutting (closed with Task Complete) and Packing / Dispatch (completed by the packing screen).
     */
    private function fixStageEndDates($stages, array $outgoing, array &$undo): array
    {
        $entered = [];
        $holds = [];
        foreach ([
            'order_stage_transactions' => null,
            'order_printing_stage_transactions' => null,
            'order_godam_stage_transactions' => null,
        ] as $table => $_) {
            foreach (DB::table($table)->select('lot_no', 'to_stage_id', DB::raw('MAX(remaining_quantity) mx'))->groupBy('lot_no', 'to_stage_id')->get() as $r) {
                $k = $r->to_stage_id . '|' . $this->key($r->lot_no);
                $entered[$k] = true;
                if ($r->mx > 0) $holds[$k] = true;
            }
        }
        // Embroidery parts count at Stitching only for a unit that got no Cutting pieces of the lot
        $p2s = DB::table('order_printing_to_stiching_transactions as p')->select('p.lot_no', 'p.remaining_quantity',
            DB::raw('EXISTS(SELECT 1 FROM order_stage_transactions c WHERE c.lot_no = p.lot_no AND c.sub_stage_id_to = p.sub_stage_id_to AND c.to_stage_id = 4) AS has_cutting'))->get();
        foreach ($p2s as $r) {
            $k = '4|' . $this->key($r->lot_no);
            $entered[$k] = true;
            if ($r->remaining_quantity > 0 && !$r->has_cutting) $holds[$k] = true;
        }

        $doneRows = [];
        $reopenRows = [];
        // Cutting (Task Complete button) and Packing / Dispatch (packing screen) manage their own completion
        foreach (DB::table('order_lot_stage_timings')->whereNotIn('master_stage_id', [3, 11, 12])->get() as $t) {
            $k = $t->master_stage_id . '|' . $this->key($t->lot_no);
            if (!isset($entered[$k])) continue;
            $holding = isset($holds[$k]);
            if (!$holding && empty($t->complete_date)) {
                $date = $outgoing[$t->master_stage_id . '|*|' . $this->key($t->lot_no)]['date'] ?? $t->updated_at;
                $undo[] = ['table' => 'order_lot_stage_timings', 'id' => $t->id, 'complete_date' => $t->complete_date, 'status' => $t->status];
                DB::table('order_lot_stage_timings')->where('id', $t->id)->update(['complete_date' => $date, 'status' => 2]);
                $doneRows[] = ['lot_no' => $t->lot_no, 'stage' => $stages[$t->master_stage_id] ?? '-', 'date' => $date];
            } elseif ($holding && !empty($t->complete_date)) {
                $undo[] = ['table' => 'order_lot_stage_timings', 'id' => $t->id, 'complete_date' => $t->complete_date, 'status' => $t->status];
                DB::table('order_lot_stage_timings')->where('id', $t->id)->update(['complete_date' => null, 'status' => 1]);
                $reopenRows[] = ['lot_no' => $t->lot_no, 'stage' => $stages[$t->master_stage_id] ?? '-', 'was' => $t->complete_date];
            }
        }
        usort($doneRows, fn($a, $b) => [$a['stage'], $a['lot_no']] <=> [$b['stage'], $b['lot_no']]);
        usort($reopenRows, fn($a, $b) => [$a['stage'], $a['lot_no']] <=> [$b['stage'], $b['lot_no']]);
        return ['done' => $doneRows, 'reopened' => $reopenRows];
    }

    /**
     * Packing (stage 11).
     *  - balances: a unit's stored balance of a lot is LOWERED to what its records say it still holds
     *    (pieces were packed / sent out without the balance being reduced). It is never raised: a balance
     *    lower than the records is only listed.
     *  - end dates: filled when no packing unit holds any piece of the lot, cleared when one still does
     *    (old "Finalize" stamped every lot of the order).
     *  - things a person must look at (already dispatched, packed without a lot number...) are only listed.
     */
    private function fixPacking($units, array &$undo): array
    {
        $service = app(PackingService::class);
        $out = [
            'balances' => [], 'dates_done' => [], 'dates_reopened' => [], 'domestic_boxes' => [],
            'over_packed' => [], 'balance_low' => [], 'packed_not_received' => [], 'lotless_hold' => [], 'duplicate_cartons' => [],
        ];

        // order|unit pairs with cartons saved without a lot number: the lot figures of that order at that unit are incomplete
        $lotless = DB::table('packing_items as i')
            ->join('packing_mains as m', 'm.id', '=', 'i.packing_main_id')
            ->join('production_slip_digitization as s', 's.id', '=', 'm.slip_id')
            ->whereNull('i.lot_no')->select('m.order_main_id', 's.stage_master_unit_id')->distinct()->get()
            ->mapWithKeys(fn($r) => [$r->order_main_id . '|' . $r->stage_master_unit_id => true]);
        $lotOrder = DB::table('order_lots')->pluck('order_main_id', 'lot_no')->mapWithKeys(fn($o, $l) => [$this->key($l) => $o]);

        // 1) balances per lot and packing unit
        $received = DB::table('order_stage_transactions')->where('to_stage_id', 11)->whereNotNull('sub_stage_id_to')
            ->orderBy('id')->get(['id', 'lot_no', 'sub_stage_id_to', 'quantity', 'remaining_quantity', 'status', 'is_closed_for_unit']);
        $entered = [];      // lot => true
        $receivedAt = [];   // unit|lot => true
        $holds = [];        // lot => ['UNIT pcs', ...]
        $lotlessLots = [];  // lot => true
        foreach ($received->groupBy('sub_stage_id_to') as $unitId => $unitRows) {
            $unitName = $units[$unitId] ?? ('#' . $unitId);
            $stock = [];
            foreach ($service->unitLotStock($unitRows->pluck('lot_no')->unique()->all(), $unitId) as $l => $st) {
                $stock[$this->key($l)] = $st;
            }
            foreach ($unitRows->groupBy(fn($r) => $this->key($r->lot_no)) as $lk => $lotRows) {
                $st = $stock[$lk] ?? null;
                if (!$st) continue;
                $lotNo = $lotRows->first()->lot_no;
                $entered[$lk] = true;
                $receivedAt[$unitId . '|' . $lk] = true;
                if ($st['pending'] > 0) {
                    $holds[$lk][] = $unitName . ' ' . $st['pending'];
                    if (isset($lotless[($lotOrder[$lk] ?? 0) . '|' . $unitId])) $lotlessLots[$lk] = true;
                }
                $used = $st['packed'] + $st['outflow'] + $st['rework'];
                if ($used > $st['received']) {
                    $out['over_packed'][] = ['lot_no' => $lotNo, 'unit' => $unitName, 'received' => $st['received'], 'used' => $used];
                }

                $stored = (int) $lotRows->sum('remaining_quantity');
                if ($stored > $st['pending']) {
                    // take the extra off the oldest entries first, the same way packing does
                    $excess = $stored - $st['pending'];
                    foreach ($lotRows as $r) {
                        if ($excess <= 0) break;
                        if ($r->remaining_quantity <= 0) continue;
                        $take = min((int) $r->remaining_quantity, $excess);
                        $new = (int) $r->remaining_quantity - $take;
                        $undo[] = ['table' => 'order_stage_transactions', 'id' => $r->id, 'remaining_quantity' => $r->remaining_quantity, 'status' => $r->status, 'is_closed_for_unit' => $r->is_closed_for_unit];
                        DB::table('order_stage_transactions')->where('id', $r->id)->update([
                            'remaining_quantity' => $new, 'status' => $new <= 0 ? 2 : 1, 'is_closed_for_unit' => $new <= 0 ? 1 : 0,
                        ]);
                        $excess -= $take;
                    }
                    $out['balances'][] = ['lot_no' => $lotNo, 'unit' => $unitName, 'received' => $st['received'], 'used' => min($used, $st['received']), 'was' => $stored, 'now' => $st['pending']];
                } elseif ($stored < $st['pending']) {
                    $out['balance_low'][] = ['lot_no' => $lotNo, 'unit' => $unitName, 'stored' => $stored, 'records' => $st['pending']];
                }
            }
        }

        // 2) packed or sent out by a unit that never received the lot (only listed) + last packing activity per lot
        $lastActivity = [];
        $packedAnywhere = [];
        $note = function ($lot, $date) use (&$lastActivity) {
            $lk = $this->key($lot);
            if ($date && (!isset($lastActivity[$lk]) || $date > $lastActivity[$lk])) $lastActivity[$lk] = $date;
        };
        $byUnit = DB::table('packing_items as i')
            ->join('packing_mains as m', 'm.id', '=', 'i.packing_main_id')
            ->join('production_slip_digitization as s', 's.id', '=', 'm.slip_id')
            ->whereNotNull('i.lot_no')
            ->select('i.lot_no', 's.stage_master_unit_id as unit', DB::raw('SUM(i.quantity) q'), DB::raw('MAX(i.created_at) d'))
            ->groupBy('i.lot_no', 's.stage_master_unit_id')->get()
            ->concat(DB::table('production_outflow_inventories as o')
                ->join('production_slip_digitization as s', 's.id', '=', 'o.slip_id')
                ->whereNotNull('o.lot_no')->whereNotIn('o.type', ['packing', 'packing_divert'])
                ->select('o.lot_no', 's.stage_master_unit_id as unit', DB::raw('SUM(o.quantity) q'), DB::raw('MAX(o.created_at) d'))
                ->groupBy('o.lot_no', 's.stage_master_unit_id')->get());
        $notReceived = [];
        foreach ($byUnit as $r) {
            $lk = $this->key($r->lot_no);
            $packedAnywhere[$lk] = true;
            $note($r->lot_no, $r->d);
            $k = $r->unit . '|' . $lk;
            if (!isset($receivedAt[$k])) {
                $notReceived[$k] = ['lot_no' => $r->lot_no, 'unit' => $units[$r->unit] ?? ('#' . $r->unit), 'pcs' => ($notReceived[$k]['pcs'] ?? 0) + (int) $r->q];
            }
        }
        $out['packed_not_received'] = array_values($notReceived);
        foreach (DB::table('order_stage_transactions')->where('from_stage_id', 11)->select('lot_no', DB::raw('MAX(created_at) d'))->groupBy('lot_no')->get() as $r) {
            $note($r->lot_no, $r->d);
        }

        // 3) packing end dates
        foreach (DB::table('order_lot_stage_timings')->where('master_stage_id', 11)->get() as $t) {
            $lk = $this->key($t->lot_no);
            if (!isset($entered[$lk])) {
                // never reached packing and nothing of it was packed: an end date here is wrong
                if (!empty($t->complete_date) && !isset($packedAnywhere[$lk])) {
                    $undo[] = ['table' => 'order_lot_stage_timings', 'id' => $t->id, 'complete_date' => $t->complete_date, 'status' => $t->status];
                    DB::table('order_lot_stage_timings')->where('id', $t->id)->update(['complete_date' => null, 'status' => 1]);
                    $out['dates_reopened'][] = ['lot_no' => $t->lot_no, 'was' => $t->complete_date, 'why' => 'Lot never reached packing'];
                }
                continue;
            }
            if (isset($holds[$lk]) && !empty($t->complete_date)) {
                if (isset($lotlessLots[$lk])) {
                    $out['lotless_hold'][] = ['lot_no' => $t->lot_no, 'held' => implode(', ', $holds[$lk]), 'date' => $t->complete_date];
                    continue;
                }
                $undo[] = ['table' => 'order_lot_stage_timings', 'id' => $t->id, 'complete_date' => $t->complete_date, 'status' => $t->status];
                DB::table('order_lot_stage_timings')->where('id', $t->id)->update(['complete_date' => null, 'status' => 1]);
                $out['dates_reopened'][] = ['lot_no' => $t->lot_no, 'was' => $t->complete_date, 'why' => 'Still to pack: ' . implode(', ', $holds[$lk]) . ' pcs'];
            } elseif (!isset($holds[$lk]) && empty($t->complete_date)) {
                $date = $lastActivity[$lk] ?? $t->updated_at;
                $undo[] = ['table' => 'order_lot_stage_timings', 'id' => $t->id, 'complete_date' => $t->complete_date, 'status' => $t->status];
                DB::table('order_lot_stage_timings')->where('id', $t->id)->update(['complete_date' => $date, 'status' => 2]);
                $out['dates_done'][] = ['lot_no' => $t->lot_no, 'date' => $date];
            }
        }

        // 4) domestic boxes saved as normal cartons (status 1) instead of domestic stock (3), never dispatched
        $boxes = DB::table('packing_cartons as c')
            ->join('packing_mains as m', 'm.id', '=', 'c.packing_main_id')
            ->leftJoin('order_main as o', 'o.id', '=', 'm.order_main_id')
            ->where('c.status', 1)->where('c.barcode', 'like', 'D%')
            ->whereExists(function ($q) {
                $q->select(DB::raw(1))->from('domestic_inventories as d')
                    ->whereColumn('d.packing_main_id', 'c.packing_main_id')->whereColumn('d.barcode', 'c.barcode');
            })
            ->whereNotExists(function ($q) {
                $q->select(DB::raw(1))->from('order_dispatch_details as x')->whereColumn('x.carton_packing_id', 'c.id');
            })
            ->get(['c.id', 'c.packing_main_id', 'c.carton_no', 'c.status', 'o.sku']);
        foreach ($boxes->groupBy('packing_main_id') as $mainId => $list) {
            foreach ($list as $c) {
                $undo[] = ['table' => 'packing_cartons', 'id' => $c->id, 'status' => $c->status];
            }
            DB::table('packing_cartons')->whereIn('id', $list->pluck('id')->all())->update(['status' => 3]);
            $out['domestic_boxes'][] = ['session' => $mainId, 'order' => $list->first()->sku ?? '-', 'boxes' => $list->count()];
        }

        // 5) the same carton number used twice in one packing session (only listed: most are dispatched)
        $dups = DB::table('packing_cartons')->whereNotNull('carton_no')->where('carton_no', '!=', '')
            ->select('packing_main_id', 'carton_no', DB::raw('COUNT(*) n'), DB::raw('SUM(status = 2) dispatched'))
            ->groupBy('packing_main_id', 'carton_no')->havingRaw('COUNT(*) > 1')->get()->groupBy('packing_main_id');
        if ($dups->isNotEmpty()) {
            $mains = DB::table('packing_mains as m')
                ->leftJoin('production_slip_digitization as s', 's.id', '=', 'm.slip_id')
                ->leftJoin('order_main as o', 'o.id', '=', 'm.order_main_id')
                ->whereIn('m.id', $dups->keys()->all())->get(['m.id', 'm.slip_id', 's.stage_master_unit_id', 'o.sku'])->keyBy('id');
            foreach ($dups as $mainId => $list) {
                $m = $mains[$mainId] ?? null;
                $out['duplicate_cartons'][] = [
                    'session' => $mainId, 'slip' => $m->slip_id ?? '-', 'order' => $m->sku ?? '-',
                    'unit' => $m ? ($units[$m->stage_master_unit_id] ?? '-') : '-',
                    'cartons' => $list->pluck('carton_no')->implode(', '),
                    'dispatched' => (int) $list->sum('dispatched'),
                ];
            }
        }

        foreach (['balances', 'over_packed', 'balance_low', 'packed_not_received'] as $k) {
            usort($out[$k], fn($a, $b) => [$a['unit'], (string) $a['lot_no']] <=> [$b['unit'], (string) $b['lot_no']]);
        }
        foreach (['dates_done', 'dates_reopened', 'lotless_hold'] as $k) {
            usort($out[$k], fn($a, $b) => (string) $a['lot_no'] <=> (string) $b['lot_no']);
        }
        return $out;
    }
}
