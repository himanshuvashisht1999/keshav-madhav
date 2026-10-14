<?php

namespace App\Console\Commands;

use App\Models\MasterCustomer;
use App\Services\Admin\Master\CustomerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Safety net for the opening-balance change.
 *
 *   php artisan opening:check save   -> store every party's opening balance + customer ledger figures
 *   php artisan opening:check verify -> recompute and compare with the saved file (must show 0 differences)
 */
class OpeningBalanceCheck extends Command
{
    protected $signature = 'opening:check {mode : save or verify} {--file=opening_balance_snapshot.json}';
    protected $description = 'Save / verify a snapshot of opening balances and customer ledger figures';

    private const RANGES = [
        'all' => [null, null],
        'from_apr' => ['2026-04-01', null],
        'to_sep' => [null, '2026-09-30'],
        'mid' => ['2026-06-01', '2026-08-31'],
    ];

    public function handle()
    {
        $path = storage_path('app/' . $this->option('file'));
        $mode = $this->argument('mode');
        $now = $this->collect();

        if ($mode === 'save') {
            file_put_contents($path, json_encode($now, JSON_PRETTY_PRINT));
            $this->info('Snapshot saved: ' . $path);
            $this->line('openings: ' . count($now['openings']) . ' | customers checked: ' . count($now['customers']['all']));
            return 0;
        }

        if ($mode !== 'verify' || !is_file($path)) {
            $this->error('Use "save" first, then "verify". Snapshot file: ' . $path);
            return 1;
        }

        $before = json_decode(file_get_contents($path), true);
        $diffs = 0;

        foreach (['openings'] as $section) {
            foreach ($before[$section] + $now[$section] as $k => $_) {
                $a = $before[$section][$k] ?? null;
                $b = $now[$section][$k] ?? null;
                if ($a === null || $b === null || abs($a - $b) > 0.001) {
                    $diffs++;
                    $this->line("opening {$k}: before=" . var_export($a, true) . " now=" . var_export($b, true));
                }
            }
        }
        foreach ($before['ledger'] as $k => $a) {
            $b = $now['ledger'][$k] ?? null;
            if ($a != $b) {
                $diffs++;
                $this->line("ledger {$k}: before=" . json_encode($a) . " now=" . json_encode($b));
            }
        }
        foreach (self::RANGES as $name => $_) {
            foreach ($before['customers'][$name] as $id => $vals) {
                foreach (['opening_balance', 'closing_balance'] as $f) {
                    $b = $now['customers'][$name][$id][$f] ?? null;
                    if ($b === null || abs($vals[$f] - $b) > 0.001) {
                        $diffs++;
                        $this->line("customer {$id} [{$name}] {$f}: before={$vals[$f]} now=" . var_export($b, true));
                    }
                }
            }
        }

        $diffs === 0
            ? $this->info('VERIFIED: 0 differences (openings and customer ledger figures unchanged).')
            : $this->error("{$diffs} difference(s) found.");
        return $diffs === 0 ? 0 : 1;
    }

    private function collect(): array
    {
        $openings = [];
        foreach (DB::table('payment_opening_balances')->orderBy('id')->get() as $r) {
            $signed = strtolower(trim($r->balance_type)) === 'debit' ? -(float) $r->amount : (float) $r->amount;
            $key = $r->master_type . ':' . $r->master_id;
            $openings[$key] = ($openings[$key] ?? 0) + $signed;
        }

        $ids = MasterCustomer::where('status', '!=', 3)->orderBy('id')->pluck('id')->all();
        $svc = app(CustomerService::class);
        $customers = [];
        foreach (self::RANGES as $name => [$from, $to]) {
            foreach ($svc->calculateCustomerBalances($ids, $from, $to) as $id => $v) {
                $customers[$name][$id] = [
                    'opening_balance' => round($v['opening_balance'], 2),
                    'closing_balance' => round($v['closing_balance'], 2),
                ];
            }
        }
        ksort($openings);

        return ['openings' => $openings, 'customers' => $customers, 'ledger' => $this->collectLedgers()];
    }

    /** Opening / closing as the real party ledger screen computes them (sample parties of every type). */
    private function collectLedgers(): array
    {
        $controller = app(\App\Http\Controllers\Admin\Ledger\PartyLedgerController::class);
        $method = new \ReflectionMethod($controller, 'getLedgerData');
        $method->setAccessible(true);

        $out = [];
        $types = DB::table('payment_opening_balances')->distinct()->pluck('master_type');
        foreach ($types as $type) {
            $ids = DB::table('payment_opening_balances')->where('master_type', $type)->orderBy('master_id')->limit(6)->pluck('master_id');
            foreach ($ids as $id) {
                foreach ([[], ['start_date' => '2026-06-01'], ['start_date' => '2026-05-01', 'end_date' => '2026-09-30']] as $i => $q) {
                    $key = "{$type}:{$id}:v{$i}";
                    try {
                        $d = $method->invoke($controller, \Illuminate\Http\Request::create('/', 'GET', $q), $type, $id);
                        $out[$key] = [
                            'opening' => round((float) ($d['openingBalAmount'] ?? 0), 2),
                            'tx' => isset($d['transactions']) ? count($d['transactions']) : null,
                            'balance' => isset($d['party']->balance) ? round((float) $d['party']->balance, 2) : null,
                        ];
                    } catch (\Throwable $e) {
                        $out[$key] = 'err:' . substr(class_basename($e) . ' ' . $e->getMessage(), 0, 80);
                    }
                }
            }
        }
        ksort($out);
        return $out;
    }
}
