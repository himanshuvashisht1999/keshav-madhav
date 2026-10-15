<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\CorporateDataFixService;
use Illuminate\Support\Facades\Storage;

/**
 * /admin/corporate-fix : shows exactly what the one-time repair of old corporate order data will change,
 * and one button applies it (old values are saved to storage/app/corporate-fix/).
 */
class CorporateFixController extends Controller
{
    public function show(CorporateDataFixService $fix)
    {
        try {
            $plan = $fix->run(false);
        } catch (\Throwable $e) {
            return redirect()->route('admin.product_order.indexOrder')->with('error', 'Corporate repair page: ' . $e->getMessage());
        }
        $runs = collect(Storage::disk('local')->files('corporate-fix'))->sort()->reverse()->values();

        return view('admin.corporate_fix.show', compact('plan', 'runs'));
    }

    public function run(CorporateDataFixService $fix)
    {
        try {
            $result = $fix->run(true);
        } catch (\Throwable $e) {
            return redirect()->route('admin.corporate-fix.show')->with('fix_error', 'Nothing was changed: ' . $e->getMessage());
        }

        return redirect()->route('admin.corporate-fix.show')->with('fix_result', [
            'tasks' => count($result['tasks']),
            'cartons' => count($result['cartons']),
            'orders' => count($result['orders']),
            'log_file' => $result['log_file'],
        ]);
    }
}
