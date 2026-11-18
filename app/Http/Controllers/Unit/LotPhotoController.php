<?php

namespace App\Http\Controllers\Unit;

use App\Http\Controllers\Controller;
use App\Models\LotPhoto;
use App\Models\StageMasterUnit;
use App\Services\LotPhotoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

/**
 * Worker app > "Lot Photo" (Stitching logins only): pick one of your lots, take a photo, submit.
 * The same lot stays selected after each photo. Rules are in LotPhotoService.
 */
class LotPhotoController extends Controller
{
    private function unit()
    {
        return session()->has('unit_auth') ? StageMasterUnit::find(session('unit_auth')['id']) : null;
    }

    public function index(Request $request, LotPhotoService $service)
    {
        $unit = $this->unit();
        if (!$unit) {
            return redirect()->route('unit.login');
        }
        if (!LotPhotoService::canSend($unit->master_stage_id)) {
            return redirect()->route('unit.dashboard')->withErrors(['Lot Photo is only for Stitching units.']);
        }

        $lots = $service->lotsForUnit($unit->id);

        // only a lot from this login's own list can be selected
        $selected = null;
        if ($request->filled('lot_no')) {
            $wanted = mb_strtolower(trim((string) $request->lot_no));
            $selected = $lots->first(fn($l) => mb_strtolower($l->lot_no) === $wanted);
        }
        $photos = $selected ? $service->forLot($selected->lot_no) : collect();

        return view('unit.lot_photos', compact('unit', 'lots', 'selected', 'photos'));
    }

    public function store(Request $request, LotPhotoService $service)
    {
        $unit = $this->unit();
        if (!$unit) {
            return redirect()->route('unit.login');
        }
        if (!LotPhotoService::canSend($unit->master_stage_id)) {
            return redirect()->route('unit.dashboard')->withErrors(['Lot Photo is only for Stitching units.']);
        }

        $lotNo = trim((string) $request->input('lot_no'));
        if ($lotNo === '') {
            return redirect()->route('unit.lot-photos')->withErrors(['Please select a lot first.']);
        }
        if (!$service->unitHasLot($unit->id, $lotNo)) {
            return redirect()->route('unit.lot-photos')->withErrors(['Lot ' . $lotNo . ' was not sent to you.']);
        }
        if (!$request->filled('photo_data')) {
            return redirect()->route('unit.lot-photos', ['lot_no' => $lotNo])->withErrors(['Please take a photo first.']);
        }

        try {
            $service->store($unit, $lotNo, $request->input('photo_data'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->route('unit.lot-photos', ['lot_no' => $lotNo])->withErrors([$e->getMessage()]);
        } catch (\Throwable $e) {
            Log::error('Lot photo save failed', ['unit_id' => $unit->id, 'lot_no' => $lotNo, 'error' => $e->getMessage()]);
            return redirect()->route('unit.lot-photos', ['lot_no' => $lotNo])->withErrors(['The photo could not be saved. Please try again.']);
        }

        // back to the same lot, so the next photo needs only "Take Photo" and "Submit"
        return redirect()->route('unit.lot-photos', ['lot_no' => $lotNo])->withSuccess('Photo sent for Lot ' . $lotNo . '.');
    }

    /** A worker can delete only the photos sent from this login. */
    public function destroy($id)
    {
        $unit = $this->unit();
        if (!$unit) {
            return redirect()->route('unit.login');
        }

        LotPhotoService::ensureTable();
        $photo = LotPhoto::where('status', 1)->find($id);
        if (!$photo || (int) $photo->stage_master_unit_id !== (int) $unit->id) {
            return redirect()->back()->withErrors(['You can delete only the photos you sent.']);
        }

        app(LotPhotoService::class)->remove($photo, 'unit:' . $unit->id);

        return redirect()->back()->withSuccess('Photo deleted.');
    }
}
