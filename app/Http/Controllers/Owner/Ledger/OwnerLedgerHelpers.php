<?php

namespace App\Http\Controllers\Owner\Ledger;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

/**
 * Helpers shared by the owner Party Ledger and Bank & Cash Ledger (read-only pages).
 * The figures themselves always come from the ledger services, exactly as on the admin pages.
 */
trait OwnerLedgerHelpers
{
    /** Parties / accounts per page of the list (more rows load with "Load more"). */
    protected function listPerPage(): int
    {
        return 50;
    }

    /** Entries per page of a statement (the totals above always cover the whole period). */
    protected function statementPerPage(): int
    {
        return 100;
    }

    /**
     * Most entries one PDF may hold. dompdf needs about 0.5 MB per row: the CASH BOOK with all
     * 1,847 entries ran out of the 512 MB memory limit (HTTP 500), 400-500 rows stay well inside it.
     */
    protected function pdfMaxRows(): int
    {
        return 500;
    }

    /** From / To dates of a list (Y-m-d), same rule as the admin list: a wrong date is ignored, the two are swapped if From is after To. */
    protected function listPeriod(Request $request): array
    {
        $date = function ($value) {
            if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
                return null;
            }
            [$y, $m, $d] = array_map('intval', explode('-', $value));
            return checkdate($m, $d, $y) ? $value : null;
        };
        $start = $date($request->query('start_date'));
        $end = $date($request->query('end_date'));
        if ($start && $end && $start > $end) {
            [$start, $end] = [$end, $start];
        }
        return [$start, $end];
    }

    /** Statement pages: only real dates reach the ledger service (a bad date in the link used to give an error page). */
    protected function cleanPeriod(Request $request): void
    {
        [$start, $end] = $this->listPeriod($request);
        foreach (['start_date' => $start, 'end_date' => $end] as $key => $value) {
            $value ? $request->query->set($key, $value) : $request->query->remove($key);
        }
    }

    /** Closing filters of the list (same as admin): min / max closing (absolute amount) and Dr / Cr only. */
    protected function applyClosingFilters(Collection $parties, Request $request): Collection
    {
        $minClosing = $request->query('min_closing', $request->query('min_amount'));
        $maxClosing = $request->query('max_closing', $request->query('max_amount'));
        $balanceType = strtolower(trim((string) $request->query('balance_type')));

        if ($minClosing !== null && $minClosing !== '') {
            $minVal = (float) $minClosing;
            $parties = $parties->filter(fn($p) => abs((float) $p->balance) >= $minVal);
        }
        if ($maxClosing !== null && $maxClosing !== '') {
            $maxVal = (float) $maxClosing;
            $parties = $parties->filter(fn($p) => abs((float) $p->balance) <= $maxVal);
        }
        if ($balanceType === 'credit') {
            $parties = $parties->filter(fn($p) => (float) $p->balance > 0);
        } elseif ($balanceType === 'debit') {
            $parties = $parties->filter(fn($p) => (float) $p->balance < 0);
        }
        return $parties;
    }

    /**
     * One page of a statement. Returns [paginator, brought forward balance]:
     * page 1 starts from the opening balance, a later page from the balance after the last row of the page before.
     */
    protected function statementPage(Collection $transactions, Request $request, float $opening): array
    {
        $perPage = $this->statementPerPage();
        $total = $transactions->count();
        $lastPage = max(1, (int) ceil($total / $perPage));
        $page = min(max(1, (int) $request->query('page', 1)), $lastPage);
        $offset = ($page - 1) * $perPage;
        $broughtForward = $offset > 0 ? (float) ($transactions[$offset - 1]->running_balance ?? $opening) : $opening;

        $paginator = new LengthAwarePaginator(
            $transactions->slice($offset, $perPage)->values(),
            $total,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        return [$paginator, $broughtForward];
    }

    /** Message shown instead of a PDF that would be too big to build (null = the PDF can be made). */
    protected function pdfRefusal(int $rows, $startDate, $endDate): ?string
    {
        if ($rows <= $this->pdfMaxRows()) {
            return null;
        }
        $limit = number_format($this->pdfMaxRows());
        if (!$startDate && !$endDate) {
            return 'This ledger has ' . number_format($rows) . ' entries, too many for one PDF (up to ' . $limit
                . '). Please choose a From and To date (for example one month), apply, and download the PDF again.';
        }
        return 'This period has ' . number_format($rows) . ' entries, too many for one PDF (up to ' . $limit
            . '). Please choose a shorter period (From / To dates) and download the PDF again.';
    }

    /** PDF file name without '/' or '\' (a name like 'TAX A/C' made the download fail with HTTP 500). */
    protected function pdfFileName($name): string
    {
        $base = trim((string) preg_replace('/[^\pL\pN._-]+/u', '_', (string) $name), '_.');
        return ($base !== '' ? $base : 'Ledger') . '_Ledger_' . date('Y-m-d') . '.pdf';
    }
}
