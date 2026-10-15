<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\FabricDataFixService;
use Illuminate\Support\Facades\Storage;

/**
 * /admin/fabric-fix : shows exactly what the one-time repair of old fabric PO data will change,
 * and one button applies it (old values are saved to storage/app/fabric-fix/).
 */
class FabricFixController extends Controller
{
    public function show(FabricDataFixService $fix)
    {
        try {
            $plan = $fix->run(false);
        } catch (\Throwable $e) {
            return redirect()->route('admin.purchase_order.index')->with('error', 'Fabric repair page: ' . $e->getMessage());
        }
        $runs = collect(Storage::disk('local')->files('fabric-fix'))->sort()->reverse()->values();

        return view('admin.fabric_fix.show', compact('plan', 'runs'));
    }

    public function run(FabricDataFixService $fix)
    {
        try {
            $result = $fix->run(true);
        } catch (\Throwable $e) {
            return redirect()->route('admin.fabric-fix.show')->with('fix_error', 'Nothing was changed: ' . $e->getMessage());
        }

        return redirect()->route('admin.fabric-fix.show')->with('fix_result', [
            'relinked' => count($result['relinked']),
            'auto_pos' => count($result['auto_pos']),
            'lines' => count($result['lines']),
            'log_file' => $result['log_file'],
        ]);
    }
}
