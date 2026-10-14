<?php

namespace App\Console\Commands;

use App\Services\Ledger\BalanceReconciler;
use Illuminate\Console\Command;

/**
 * php artisan balances:check
 *
 * Compares every party's stored balance with its ledger and saves the report shown on the
 * admin "Balance check" page / header badge. Exit code 1 when anything differs.
 * Scheduled daily (see App\Console\Kernel).
 */
class BalancesCheck extends Command
{
    protected $signature = 'balances:check {--details : list every mismatch}';
    protected $description = 'Check that every stored balance equals its ledger';

    public function handle(BalanceReconciler $reconciler)
    {
        $report = $reconciler->checkAndStore();

        $this->line("Checked {$report['checked']} accounts at {$report['checked_at']}.");
        if ($report['mismatch_count'] === 0) {
            $this->info('ALL BALANCES MATCH THEIR LEDGERS (0 differences).');
            return 0;
        }

        $groups = collect($report['mismatches'])->groupBy('group');
        $this->error("{$report['mismatch_count']} account(s) differ from their ledger:");
        foreach ($groups as $g => $rows) {
            $this->line("  Group {$g}: {$rows->count()}  (total gap " . number_format($rows->sum(fn($r) => abs($r['difference'])), 2) . ')');
        }

        if ($this->option('details')) {
            $this->table(
                ['Group', 'Type', 'ID', 'Name', 'Stored', 'Ledger', 'Difference'],
                collect($report['mismatches'])->map(fn($r) => [$r['group'], $r['type'], $r['id'], mb_substr($r['name'], 0, 28), number_format($r['stored'], 2), number_format($r['ledger'], 2), number_format($r['difference'], 2)])->all()
            );
        }
        $this->line('List: /admin/balance-issues   Correct: /admin/balance-fix');
        return 1;
    }
}
