<?php

namespace App\Console\Commands;

use App\Services\Ledger\BalanceReconciler;
use Illuminate\Console\Command;

/**
 * php artisan balances:repair                      -> PREVIEW only: writes storage/app/balance_repair_preview.csv
 * php artisan balances:repair --apply --group=A    -> apply group A fixes
 * php artisan balances:repair --apply --group=B
 * php artisan balances:repair --apply --only="customer:12,vendor:7"   -> apply chosen accounts only
 *
 * Group C is never applied unless named explicitly with --group=C or --only, because a person
 * has to confirm those. Every change is logged in balance_corrections.
 */
class BalancesRepair extends Command
{
    protected $signature = 'balances:repair {--apply : actually change data (default is preview only)} {--group= : A, B, C or D} {--only= : comma list of ledger_type:id}';
    protected $description = 'Preview / apply the fixes that make stored balances equal their ledgers';

    public function handle(BalanceReconciler $reconciler)
    {
        $this->line('Scanning all accounts...');
        $scan = $reconciler->scan();
        $rows = collect($scan['mismatches']);
        $this->line("Checked {$scan['checked']} accounts, {$rows->count()} differ.");

        // Preview file for review (Excel opens it)
        $csv = storage_path('app/balance_repair_preview.csv');
        $fh = fopen($csv, 'w');
        fputcsv($fh, ['Group', 'Type', 'ID', 'Name', 'Stored balance', 'Ledger balance', 'Difference', 'Proposed fix', 'Reason']);
        foreach ($rows as $r) {
            $fix = $r['action'] === 'set_opening'
                ? 'Save opening balance ' . number_format($r['proposed_opening'], 2)
                : 'Set stored balance to ledger ' . number_format($r['ledger'], 2);
            fputcsv($fh, [$r['group'], $r['type'], $r['id'], $r['name'], $r['stored'], $r['ledger'], $r['difference'], $fix, $r['reason']]);
        }
        fclose($fh);
        $this->info("Preview written: {$csv}");

        foreach ($rows->groupBy('group') as $g => $groupRows) {
            $this->line("  Group {$g}: {$groupRows->count()}");
        }

        if (!$this->option('apply')) {
            $this->line('Preview only - nothing was changed. Add --apply with --group or --only to make changes.');
            return 0;
        }

        $selected = $rows;
        if ($this->option('only')) {
            $wanted = collect(explode(',', $this->option('only')))->map(fn($s) => strtolower(trim($s)))->filter();
            $selected = $rows->filter(fn($r) => $wanted->contains($r['ledger_type'] . ':' . $r['id']) || $wanted->contains(str_replace(' ', '_', $r['ledger_type']) . ':' . $r['id']));
        } elseif ($this->option('group')) {
            $selected = $rows->where('group', strtoupper($this->option('group')));
        } else {
            $this->error('Choose what to apply: --group=A|B|C|D or --only=type:id,...');
            return 1;
        }

        if ($selected->isEmpty()) {
            $this->warn('Nothing matched the selection.');
            return 0;
        }

        if (!$this->confirm("Apply {$selected->count()} fix(es)? Each change is logged in balance_corrections.", false)) {
            $this->line('Cancelled.');
            return 0;
        }

        $done = $reconciler->apply($selected->values()->all(), 'console:' . (get_current_user() ?: 'cli'));
        $this->info(count($done) . ' fix(es) applied and logged.');
        $this->line('Run php artisan balances:check to confirm.');
        return 0;
    }
}
