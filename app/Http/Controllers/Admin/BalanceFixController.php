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

    /** URL 2 button: the "Already corrected" list as an Excel file */
    public function correctedExcel()
    {
        if (!$this->isPrepared()) {
            return redirect()->route('admin.balance-issues')->with('error', 'The database has not been prepared yet, so there is nothing to download.');
        }
        $rows = DB::table('balance_corrections')->orderByDesc('id')->get();
        if ($rows->isEmpty()) {
            return redirect()->route('admin.balance-issues')->with('error', 'Nothing has been corrected yet, so there is nothing to download.');
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx(self::correctionsSheet($rows));
        $fileName = 'Balance_Corrections_' . date('Y_m_d_His') . '.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']);
    }

    /** Excel sheet of the balance corrections (newest first), same data as the "Already corrected" list plus id, reason and who corrected it. */
    public static function correctionsSheet($rows): \PhpOffice\PhpSpreadsheet\Spreadsheet
    {
        $book = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $book->getActiveSheet();
        $sheet->setTitle('Already corrected');

        $headers = ['#', 'When', 'Type', 'Account ID', 'Account', 'Changed', 'Old', 'New', 'Difference (New - Old)', 'Group', 'Reason', 'Corrected by'];
        $last = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));

        $sheet->setCellValue('A1', 'Balance corrections - already corrected (' . $rows->count() . ')');
        $sheet->setCellValue('A2', 'Downloaded on ' . now()->format('d M Y, h:i A'));
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet->fromArray($headers, null, 'A4');
        $sheet->getStyle("A4:{$last}4")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'borders' => ['allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
        ]);

        $text = \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING;
        $r = 5;
        foreach ($rows->values() as $i => $c) {
            $old = (float) $c->old_value;
            $new = (float) $c->new_value;
            $sheet->setCellValue("A$r", $i + 1);
            // a real Excel date, so the column sorts and filters by date
            $sheet->setCellValue("B$r", \PhpOffice\PhpSpreadsheet\Shared\Date::PHPToExcel(\Carbon\Carbon::parse($c->created_at)));
            // text cells written as text, so a name starting with "=" is never read as a formula
            $sheet->setCellValueExplicit("C$r", ucwords(str_replace('_', ' ', (string) $c->ledger_type)), $text);
            $sheet->setCellValue("D$r", (int) $c->party_id);
            $sheet->setCellValueExplicit("E$r", (string) $c->party_name, $text);
            $sheet->setCellValueExplicit("F$r", $c->field === 'opening' ? 'Opening balance' : 'Balance', $text);
            $sheet->setCellValue("G$r", $old);
            $sheet->setCellValue("H$r", $new);
            $sheet->setCellValue("I$r", round($new - $old, 2));
            $sheet->setCellValueExplicit("J$r", (string) $c->group, $text);
            $sheet->setCellValueExplicit("K$r", (string) $c->reason, $text);
            $sheet->setCellValueExplicit("L$r", (string) $c->applied_by, $text);
            $r++;
        }
        $end = $r - 1;

        $sheet->getStyle("B5:B$end")->getNumberFormat()->setFormatCode('dd mmm yyyy, hh:mm AM/PM');
        $sheet->getStyle("G5:I$end")->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A4:{$last}$end")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
        $sheet->getStyle("K5:K$end")->getAlignment()->setWrapText(true);
        foreach (range('A', 'L') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize($col !== 'K');
        }
        $sheet->getColumnDimension('K')->setWidth(60);
        $sheet->setAutoFilter("A4:{$last}$end");
        $sheet->freezePane('A5');

        return $book;
    }

    /** True when the one-time preparation is done on this database. */
    private function isPrepared(): bool
    {
        return Schema::hasTable('balance_corrections')
            && !DB::table('payment_opening_balances')->where('financial_year', '!=', \App\Models\MasterOpeningBalance::OPENING_KEY)->exists();
    }
}
