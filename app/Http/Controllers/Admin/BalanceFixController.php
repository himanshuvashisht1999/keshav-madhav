<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Ledger\BalanceReconciler;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Two pages only:
 *   /admin/balance-issues  -> list of accounts whose balance does not match their ledger (+ what was corrected)
 *   /admin/balance-fix     -> one button: prepare the database (one time) and correct every wrong balance
 */
class BalanceFixController extends Controller
{
    /** One-time database changes the balance fix needs (run in this order). */
    private const MIGRATIONS = [
        'database/migrations/2026_10_14_100000_make_opening_balances_single_per_party.php',
        'database/migrations/2026_10_14_120000_create_balance_corrections_table.php',
    ];

    /** URL 2: list only */
    public function issues(BalanceReconciler $reconciler)
    {
        @set_time_limit(600);
        $prepared = $this->isPrepared();
        $wrong = $prepared ? collect($reconciler->checkAndStore()['mismatches']) : collect();
        $corrected = $prepared ? DB::table('balance_corrections')->orderByDesc('id')->get() : collect();

        return view('admin.balance_fix.issues', compact('prepared', 'wrong', 'corrected'));
    }

    /** URL 1: page with the single "Correct database" button */
    public function show(BalanceReconciler $reconciler)
    {
        @set_time_limit(600);
        $prepared = $this->isPrepared();
        $wrong = $prepared ? collect($reconciler->checkAndStore()['mismatches']) : null;

        return view('admin.balance_fix.fix', compact('prepared', 'wrong'));
    }

    /** URL 1 button: prepare (one time) + correct every wrong balance + check again */
    public function run(BalanceReconciler $reconciler)
    {
        @set_time_limit(900);

        // 1. One-time preparation (safe to run again: already-run steps are skipped)
        foreach (self::MIGRATIONS as $path) {
            try {
                Artisan::call('migrate', ['--path' => $path, '--force' => true]);
            } catch (\Throwable $e) {
                return redirect()->route('admin.balance-fix.show')->with('fix_error', 'Database preparation stopped, nothing was changed: ' . $e->getMessage());
            }
        }
        if (!$this->isPrepared()) {
            return redirect()->route('admin.balance-fix.show')->with('fix_error', 'Database preparation did not complete. Nothing was corrected.');
        }

        // 2. Correct every account whose stored balance differs from its ledger (each change is logged)
        $wrong = $reconciler->scan()['mismatches'];
        $done = $reconciler->apply($wrong, 'balance-fix:' . (Auth::guard('admin')->user()->name ?? 'admin'));

        // 3. Check everything again
        $report = $reconciler->checkAndStore();

        return redirect()->route('admin.balance-fix.show')->with('fix_result', [
            'corrected' => count($done),
            'still_wrong' => $report['mismatch_count'],
            'checked' => $report['checked'],
        ]);
    }

    /** True when the one-time preparation is done on this database. */
    private function isPrepared(): bool
    {
        return Schema::hasTable('balance_corrections')
            && !DB::table('payment_opening_balances')->where('financial_year', '!=', \App\Models\MasterOpeningBalance::OPENING_KEY)->exists();
    }
}
