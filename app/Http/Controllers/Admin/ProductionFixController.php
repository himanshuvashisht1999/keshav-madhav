<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\ProductionDataFixService;
use Illuminate\Support\Facades\Storage;

/**
 * /admin/production-fix : shows exactly what the one-time repair of old Digitized Slips and Packing data will change,
 * and one button applies it (old values are saved to storage/app/production-fix/).
 */
class ProductionFixController extends Controller
{
    public function show(ProductionDataFixService $fix)
    {
        try {
            $plan = $fix->run(false);
        } catch (\Throwable $e) {
            return redirect()->route('admin.uploaded-slips.index')->with('error', 'Production repair page: ' . $e->getMessage());
        }
        $runs = collect(Storage::disk('local')->files('production-fix'))->sort()->reverse()->values();

        return view('admin.production_fix.show', compact('plan', 'runs'));
    }

    public function run(ProductionDataFixService $fix)
    {
        try {
            $result = $fix->run(true);
        } catch (\Throwable $e) {
            return redirect()->route('admin.production-fix.show')->with('fix_error', 'Nothing was changed: ' . $e->getMessage());
        }

        return redirect()->route('admin.production-fix.show')->with('fix_result', [
            'stitching' => count($result['stitching']),
            'godam' => count($result['godam']),
            'completed' => count($result['completed']),
            'timings_done' => count($result['timings_done']),
            'timings_reopened' => count($result['timings_reopened']),
            'packing_balances' => count($result['packing']['balances']),
            'packing_dates_done' => count($result['packing']['dates_done']),
            'packing_dates_reopened' => count($result['packing']['dates_reopened']),
            'domestic_boxes' => array_sum(array_column($result['packing']['domestic_boxes'], 'boxes')),
            'log_file' => $result['log_file'],
        ]);
    }
}
