<?php

namespace App\Services;

use App\Models\LotPhoto;
use App\Models\OrderGodamStageTransaction;
use App\Models\OrderLot;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\OrderStageTransaction;
use App\Models\StageMasterUnit;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Lot photos: a Stitching unit sends photos for the lots that were sent to it (worker app > Lot Photo).
 * Every stage except Cutting and Printing sees them when it opens the lot; the admin sees them in the lot report.
 * Rules confirmed by the user on 18 Nov 2026:
 *  - only a Stitching login can send, and only for a lot that was sent to that login (any status);
 *  - a worker can delete only the photos that login sent; the admin can delete any;
 *  - delete hides the photo everywhere (status 0); the row and the file are kept.
 */
class LotPhotoService
{
    const FOLDER = 'assets/lot_photos';
    const STAGE_STITCHING = 4;
    /** Printing (1) and Cutting (3) are not shown lot photos. */
    const HIDDEN_FROM_STAGES = [1, 3];
    const MAX_BYTES = 8388608; // 8 MB after the phone has made the photo smaller

    /** The four tables that record "a lot was sent to a unit" (the same ones the worker app's My Work reads). */
    const RECEIVED_TABLES = [
        'cutting' => OrderStageTransaction::class,
        'printing' => OrderPrintingStageTransaction::class,
        'printing_to_stitching' => OrderPrintingToStichingTransaction::class,
        'godam' => OrderGodamStageTransaction::class,
    ];

    /** Creates the lot_photos table on first use (same as the migration), so live needs no manual step. */
    public static function ensureTable(): void
    {
        if (Schema::hasTable('lot_photos')) {
            return;
        }
        try {
            Schema::create('lot_photos', function (Blueprint $table) {
                $table->id();
                // same collation as lot_no in order_lots and the stage tables, so the columns can be compared and joined
                $table->string('lot_no')->collation('utf8mb4_general_ci')->index();
                $table->unsignedBigInteger('stage_master_unit_id')->index(); // the Stitching unit that sent it
                $table->string('image');
                $table->tinyInteger('status')->default(1);   // 1 = shown, 0 = deleted
                $table->string('removed_by', 50)->nullable(); // "unit:5" or "admin:1"
                $table->dateTime('removed_at')->nullable();
                $table->timestamps();
            });
        } catch (\Throwable $e) {
            // two first requests at the same moment: the other one created it
            if (!Schema::hasTable('lot_photos')) {
                throw $e;
            }
        }
    }

    public static function canSend($stageId): bool
    {
        return (int) $stageId === self::STAGE_STITCHING;
    }

    public static function canView($stageId): bool
    {
        return !in_array((int) $stageId, self::HIDDEN_FROM_STAGES, true);
    }

    /**
     * Every lot ever sent to this unit, whatever its status.
     * Each row: lot_no, design_no, left (pieces still with the unit), in_work, last_date.
     * Lots still in work come first, then the newest. "left" follows the My Work rule: when a lot came
     * from Cutting and also from Embroidery / Godam, only the Cutting rows are counted (same pieces).
     */
    public function lotsForUnit($unitId)
    {
        $norm = fn($v) => mb_strtolower(rtrim((string) $v));
        $lots = [];

        foreach (self::RECEIVED_TABLES as $source => $model) {
            $rows = $model::query()->toBase()
                ->where('sub_stage_id_to', $unitId)
                ->whereNotNull('lot_no')->where('lot_no', '!=', '')
                ->selectRaw('lot_no, SUM(remaining_quantity) as left_qty, MAX(created_at) as last_date')
                ->groupBy('lot_no')
                ->get();

            foreach ($rows as $r) {
                $key = $norm($r->lot_no);
                $lots[$key] ??= ['lot_no' => rtrim((string) $r->lot_no), 'by_source' => [], 'last_date' => null];
                $lots[$key]['by_source'][$source] = ($lots[$key]['by_source'][$source] ?? 0) + (float) $r->left_qty;
                if ($r->last_date && (!$lots[$key]['last_date'] || $r->last_date > $lots[$key]['last_date'])) {
                    $lots[$key]['last_date'] = $r->last_date;
                }
            }
        }

        if (empty($lots)) {
            return collect();
        }

        $designs = OrderLot::with('orderProductSet:id,design_number')
            ->whereIn('lot_no', array_map(fn($l) => (string) $l['lot_no'], $lots))
            ->orderBy('id')->get()
            ->groupBy(fn($l) => $norm($l->lot_no))
            ->map(fn($g) => $g->first()->orderProductSet->design_number ?? null);

        return collect($lots)->map(function ($l, $key) use ($designs) {
            $left = array_key_exists('cutting', $l['by_source']) ? $l['by_source']['cutting'] : array_sum($l['by_source']);
            return (object) [
                'lot_no' => $l['lot_no'],
                'design_no' => $designs->get($key),
                'left' => $left,
                'in_work' => $left > 0,
                'last_date' => $l['last_date'],
            ];
        })->sort(function ($a, $b) {
            if ($a->in_work !== $b->in_work) return $a->in_work ? -1 : 1;
            return strcmp((string) $b->last_date, (string) $a->last_date) ?: strnatcmp($a->lot_no, $b->lot_no);
        })->values();
    }

    /** Was this lot ever sent to this unit? */
    public function unitHasLot($unitId, $lotNo): bool
    {
        $lotNo = (string) $lotNo; // text, never a number: as a number MySQL would also match "2011-2" for lot 2011
        if ($lotNo === '') {
            return false;
        }
        foreach (self::RECEIVED_TABLES as $model) {
            if ($model::where('sub_stage_id_to', $unitId)->where('lot_no', $lotNo)->exists()) {
                return true;
            }
        }
        return false;
    }

    /** Photos shown for a lot (not deleted, file present), oldest first, with the sender's name. */
    public function forLot($lotNo)
    {
        self::ensureTable();

        return LotPhoto::with('unit:id,name')
            ->where('lot_no', (string) $lotNo)
            ->where('status', 1)
            ->orderBy('id')
            ->get()
            ->filter(fn($p) => $p->image && file_exists(public_path(self::FOLDER . '/' . $p->image)))
            ->values();
    }

    /**
     * Saves one photo (a JPEG data URL made by the phone) for a lot of this unit.
     * Throws \InvalidArgumentException with a message for the worker when the photo cannot be used.
     */
    public function store(StageMasterUnit $unit, $lotNo, $photoData): LotPhoto
    {
        self::ensureTable();

        $base64 = preg_replace('/^data:image\/\w+;base64,/', '', (string) $photoData);
        $bytes = base64_decode(str_replace(' ', '+', $base64), true);
        if ($bytes === false || $bytes === '') {
            throw new \InvalidArgumentException('The photo could not be read. Please take it again.');
        }
        if (strlen($bytes) > self::MAX_BYTES) {
            throw new \InvalidArgumentException('The photo is too big. Please take it again.');
        }
        $info = @getimagesizefromstring($bytes);
        if (!$info || ($info['mime'] ?? '') !== 'image/jpeg') {
            throw new \InvalidArgumentException('This file is not a photo. Please take the photo again.');
        }

        $folder = public_path(self::FOLDER);
        if (!is_dir($folder) && !@mkdir($folder, 0777, true) && !is_dir($folder)) {
            throw new \RuntimeException('Could not create the lot photo folder.');
        }

        $file = 'lot-photo-' . $unit->id . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.jpg';
        if (file_put_contents($folder . '/' . $file, $bytes) === false) {
            throw new \RuntimeException('Could not save the lot photo file.');
        }

        try {
            return LotPhoto::create([
                'lot_no' => (string) $lotNo,
                'stage_master_unit_id' => $unit->id,
                'image' => $file,
                'status' => 1,
            ]);
        } catch (\Throwable $e) {
            @unlink($folder . '/' . $file); // no row, so do not leave a file nobody can see
            throw $e;
        }
    }

    /** Hides a photo everywhere. $by = "unit:<id>" or "admin:<id>". The row and the file are kept. */
    public function remove(LotPhoto $photo, string $by): void
    {
        $photo->update(['status' => 0, 'removed_by' => $by, 'removed_at' => now()]);
    }
}
