<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\InventoryDataFixService;
use Illuminate\Support\Facades\Storage;

/**
 * /admin/inventory-fix : shows exactly what the one-time repair of old inventory data will change,
 * and one button applies it (old values are saved to storage/app/inventory-fix/).
 */
class InventoryFixController extends Controller
{
    public function show(InventoryDataFixService $fix)
    {
        try {
            $plan = $fix->run(false);
        } catch (\Throwable $e) {
            return redirect()->route('admin.inventory.warehouse_stock')->with('error', 'Inventory repair page: ' . $e->getMessage());
        }
        $runs = collect(Storage::disk('local')->files('inventory-fix'))->sort()->reverse()->values();

        return view('admin.inventory_fix.show', compact('plan', 'runs'));
    }

    public function run(InventoryDataFixService $fix)
    {
        try {
            $result = $fix->run(true);
        } catch (\Throwable $e) {
            return redirect()->route('admin.inventory-fix.show')->with('fix_error', 'Nothing was changed: ' . $e->getMessage());
        }

        return redirect()->route('admin.inventory-fix.show')->with('fix_result', [
            'unpicked' => count($result['unpicked']),
            'redispatched' => count($result['redispatched']),
            'purchase_lines' => array_sum(array_column($result['purchase_lines'], 'lines')),
            'negatives' => count($result['negatives']),
            'log_file' => $result['log_file'],
        ]);
    }
}
