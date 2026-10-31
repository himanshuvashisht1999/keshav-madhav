<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use App\Models\AgentOrderDispatch;
use App\Models\AgentOrderReturn;
use App\Models\FabricReceipt;
use App\Models\FabricReturn;
use App\Models\Payment;
use DB;

class PartyLedgerController extends Controller
{
    /** Most parties one list PDF may hold (912 rows used about 390 MB of the 512 MB memory limit). */
    private const LIST_PDF_MAX_ROWS = 1000;

    /** Most parties the detailed report takes with "All types" (it builds one full statement per party). */
    private const DETAILED_MAX_PARTIES = 200;

    /** Most rows one detailed-report PDF may hold (dompdf needs about 0.5 MB per row; all sales agents needed 660 MB of the 512 MB limit). */
    private const DETAILED_PDF_MAX_ROWS = 500;

    public function __construct()
    {
        // Every filter of these pages takes one value: a list in the link (?search[]=x) gave an error page, so it is dropped
        $this->middleware(function ($request, $next) {
            $request->query->replace(array_filter($request->query->all(), fn($value) => !is_array($value)));
            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $data = $this->getPartyListData($request);
        $parties = $data['parties'];
        $masters = $data['masters'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $totalPartiesCount = $parties->count();
        $money = $this->totalsRows($parties, $request);
        $totalCreditBalance = (float)$money->where('balance', '>', 0)->sum('balance');
        $totalDebitBalance = (float)abs($money->where('balance', '<', 0)->sum('balance'));
        $totalNetBalance = (float)$money->sum('balance');
        $totalOpening = (float)$money->sum('opening_balance');
        $totalPeriodDebit = (float)$money->sum('period_debit');
        $totalPeriodCredit = (float)$money->sum('period_credit');

        $perPage = (int) $request->query('per_page', 25);
        if ($perPage < 1) $perPage = 25;
        // a page after the last one shows the last page (a huge page number gave an error page)
        $lastPage = max(1, (int) ceil($totalPartiesCount / $perPage));
        $currentPage = min(max(1, \Illuminate\Pagination\Paginator::resolveCurrentPage('page')), $lastPage);
        $currentItems = $parties->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $paginatedParties = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentItems,
            $totalPartiesCount,
            $perPage,
            $currentPage,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath(), 'query' => $request->query()]
        );

        $purchaseAgents = \App\Models\PurchaseAgent::where('status', 1)->orderBy('name', 'asc')->get();

        return view('admin.ledger.party.index', [
            'parties' => $paginatedParties,
            'masters' => $masters,
            'purchaseAgents' => $purchaseAgents,
            'totalPartiesCount' => $totalPartiesCount,
            'totalCreditBalance' => $totalCreditBalance,
            'totalDebitBalance' => $totalDebitBalance,
            'totalNetBalance' => $totalNetBalance,
            'totalOpening' => $totalOpening,
            'totalPeriodDebit' => $totalPeriodDebit,
            'totalPeriodCredit' => $totalPeriodCredit,
            'agentNote' => $this->agentNote($parties, $request),
            'startDate' => $startDate,
            'endDate' => $endDate,
            'customerType' => $data['customerType'] ?? null,
            'vendorType' => $data['vendorType'] ?? null,
        ]);
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        return view('admin.ledger.party.show', $data);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $pdf = \PDF::loadView('admin.ledger.party.download', $data);
        // no '/' or '\' in the file name: a name like 'TAX A/C' made the download fail (HTTP 500)
        $name = (trim((string) preg_replace('/[^\pL\pN._-]+/u', '_', (string) $data['party']->name), '_.') ?: 'Party') . '_Ledger_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    public function exportExcel(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $party = $data['party'];
        $transactions = $data['transactions'];
        $openingBalAmount = $data['openingBalAmount'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Party Ledger');

        // Header Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'SNAPKID - ' . ($party->name ?? 'Party') . ' Account Ledger');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->mergeCells('A2:H2');
        $periodText = 'All Dates';
        if ($data['startDate'] && $data['endDate']) {
            $periodText = $data['startDate'] . ' to ' . $data['endDate'];
        } elseif ($data['startDate']) {
            $periodText = 'From ' . $data['startDate'];
        } elseif ($data['endDate']) {
            $periodText = 'Up to ' . $data['endDate'];
        }
        $sheet->setCellValue('A2', 'Type: ' . ucfirst($type) . ' | Phone: ' . ($party->phone ?? '-') . ' | Period: ' . $periodText);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Balance Summary row
        $sheet->setCellValue('A3', 'Opening Balance: ₹ ' . number_format(abs($openingBalAmount), 2) . ' ' . ($openingBalAmount >= 0 ? 'CR' : 'DR'));
        $sheet->setCellValue('F3', 'Current Balance: ₹ ' . number_format(abs($party->balance), 2) . ' ' . ($party->balance >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A3:H3')->getFont()->setBold(true);

        // Table Headers
        $headers = ['Date', 'Type', 'Ref / Voucher No', 'Bill No', 'Particulars', 'Debit (₹)', 'Credit (₹)', 'Balance (₹)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '5', $h);
        }

        $headerRange = 'A5:H5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 6;
        $totalDebit = 0;
        $totalCredit = 0;

        // Opening balance row in table
        $sheet->setCellValue('A' . $row, $data['startDate'] ? \Carbon\Carbon::parse($data['startDate'])->format('d M Y') : '-');
        $sheet->setCellValue('B' . $row, 'Opening');
        $sheet->setCellValue('C' . $row, '-');
        $sheet->setCellValue('D' . $row, '-');
        $sheet->setCellValue('E' . $row, 'Opening Balance B/F');
        $sheet->setCellValue('F' . $row, $openingBalAmount < 0 ? abs($openingBalAmount) : 0);
        $sheet->setCellValue('G' . $row, $openingBalAmount >= 0 ? abs($openingBalAmount) : 0);
        $sheet->setCellValue('H' . $row, number_format(abs($openingBalAmount), 2) . ' ' . ($openingBalAmount >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setItalic(true);
        $row++;

        foreach ($transactions as $tx) {
            $debit = (float)($tx->debit ?? 0);
            $credit = (float)($tx->credit ?? 0);
            $bal = (float)($tx->running_balance ?? 0);

            $totalDebit += $debit;
            $totalCredit += $credit;

            $sheet->setCellValue('A' . $row, $tx->date ? \Carbon\Carbon::parse($tx->date)->format('d M Y') : '-');
            $sheet->setCellValue('B' . $row, $tx->type ?? '-');
            $sheet->setCellValue('C' . $row, $tx->ref ?? '-');
            $sheet->setCellValue('D' . $row, $tx->bill_no ?? '-');
            $sheet->setCellValue('E' . $row, $tx->description ?? '-');
            $sheet->setCellValue('F' . $row, $debit);
            $sheet->setCellValue('G' . $row, $credit);
            $sheet->setCellValue('H' . $row, number_format(abs($bal), 2) . ' ' . ($bal >= 0 ? 'CR' : 'DR'));

            $row++;
        }

        // Total Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('F' . $row, $totalDebit);
        $sheet->setCellValue('G' . $row, $totalCredit);
        $sheet->setCellValue('H' . $row, number_format(abs($party->balance), 2) . ' ' . ($party->balance >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // Number formats
        $sheet->getStyle('F6:G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = str_replace(' ', '_', $party->name ?? 'Party') . '_Ledger_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function detailedReport(Request $request)
    {
        $data = $this->getDetailedReportData($request);
        return view('admin.ledger.party.detailed_report', $data);
    }

    public function detailedReportPdf(Request $request)
    {
        $chosen = $this->detailedReportParties($request);
        if ($chosen['partyNotice']) {
            return redirect()->route('admin.ledger.party.detailedReport', $request->query())->with('error', $chosen['partyNotice']);
        }
        // party-wise: each party prints at least two rows, so a long list is refused before every statement is built
        // (the 692 customers took about 20 s only to be refused)
        $minRows = 2 * $chosen['filteredParties']->count();
        if ($chosen['viewMode'] === 'party_wise' && $minRows > self::DETAILED_PDF_MAX_ROWS) {
            return $this->detailedPdfTooBig($request, 'at least ' . number_format($minRows));
        }
        $data = $this->detailedReportFigures($request, $chosen);
        $rows = $this->detailedPdfRows($data);
        if ($rows > self::DETAILED_PDF_MAX_ROWS) {
            return $this->detailedPdfTooBig($request, number_format($rows));
        }
        $pdf = \PDF::loadView('admin.ledger.party.detailed_report_pdf', $data)->setPaper('a4', 'landscape');
        $name = 'Detailed_Party_Ledger_Report_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($name);
    }

    public function detailedReportExcel(Request $request)
    {
        $data = $this->getDetailedReportData($request);
        $viewMode = $data['viewMode'];
        $crdr = fn($v) => number_format(abs($v), 2) . ' ' . ($v >= 0 ? 'CR' : 'DR');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Detailed Ledger Report');

        // Header Title
        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', 'SNAPKID - Detailed Party Ledger Report');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->mergeCells('A2:J2');
        $periodText = $this->periodText($data['startDate'], $data['endDate']);
        $modeText = $viewMode === 'party_wise' ? 'Party-wise (Grouped)' : 'Mix (Consolidated)';
        $subtitle = 'Period: ' . $periodText . '   |   View Mode: ' . $modeText . '   |   Parties: ' . $data['filteredParties']->count();
        if (!empty($data['customerType'])) {
            $subtitle .= '   |   Customer Type: ' . ucfirst($data['customerType']);
        }
        if (!empty($data['vendorType'])) {
            $subtitle .= '   |   Vendor Type: ' . $this->vendorTypeText($data['vendorType']);
        }
        $subtitle .= '   |   Generated: ' . date('d M Y, h:i A');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Summary row
        $sheet->setCellValue('A3', 'Opening Bal: ₹ ' . number_format(abs($data['totalOpeningBalance']), 2) . ' ' . ($data['totalOpeningBalance'] >= 0 ? 'CR' : 'DR'));
        $sheet->setCellValue('E3', 'Period Debit: ₹ ' . number_format($data['totalPeriodDebit'], 2));
        $sheet->setCellValue('G3', 'Period Credit: ₹ ' . number_format($data['totalPeriodCredit'], 2));
        $sheet->setCellValue('I3', 'Closing Bal: ₹ ' . number_format(abs($data['totalClosingBalance']), 2) . ' ' . ($data['totalClosingBalance'] >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A3:J3')->getFont()->setBold(true);

        // Headers
        $headers = ['Date', 'Party Name', 'Party Type', 'Type', 'Ref / Voucher', 'Bill No', 'Particulars / Description', 'Debit (₹)', 'Credit (₹)', 'Balance (₹)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '5', $h);
        }

        $headerRange = 'A5:J5';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 6;
        if (!empty($data['partyNotice'])) {
            // the chosen party is not in this list: the sheet says so instead of showing rows
            $sheet->mergeCells("A{$row}:J{$row}");
            $sheet->setCellValue("A{$row}", $data['partyNotice']);
            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true)->getColor()->setARGB('FFB45309');
            $row++;
        } elseif ($viewMode === 'party_wise') {
            foreach ($data['detailedParties'] as $dp) {
                // Party Section Header
                $sheet->mergeCells("A{$row}:J{$row}");
                $sheet->setCellValue("A{$row}", ($dp->party->name ?? 'Party') . ' (' . ucfirst($dp->party->party_type ?? '') . ')  |  Opening: ' . $crdr($dp->opening_balance) . '  |  Closing: ' . $crdr($dp->closing_balance));
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true)->getColor()->setARGB('FF05421C');
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFE8F5E9');
                $row++;

                // Opening row
                $sheet->setCellValue('A' . $row, $data['startDate'] ? \Carbon\Carbon::parse($data['startDate'])->format('d M Y') : '-');
                $sheet->setCellValue('B' . $row, $dp->party->name ?? '-');
                $sheet->setCellValue('C' . $row, ucfirst($dp->party->party_type ?? '-'));
                $sheet->setCellValue('D' . $row, 'Opening');
                $sheet->setCellValue('G' . $row, 'Opening Balance B/F');
                $sheet->setCellValue('H' . $row, $dp->opening_balance < 0 ? abs($dp->opening_balance) : 0);
                $sheet->setCellValue('I' . $row, $dp->opening_balance >= 0 ? abs($dp->opening_balance) : 0);
                $sheet->setCellValue('J' . $row, $crdr($dp->opening_balance));
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);
                $row++;

                foreach ($dp->transactions as $tx) {
                    $sheet->setCellValue('A' . $row, $tx->date ? \Carbon\Carbon::parse($tx->date)->format('d M Y') : '-');
                    $sheet->setCellValue('B' . $row, $dp->party->name ?? '-');
                    $sheet->setCellValue('C' . $row, ucfirst($dp->party->party_type ?? '-'));
                    $sheet->setCellValue('D' . $row, $tx->type ?? '-');
                    $sheet->setCellValue('E' . $row, $tx->ref ?? '-');
                    $sheet->setCellValue('F' . $row, $tx->bill_no ?? '-');
                    $sheet->setCellValue('G' . $row, $tx->description ?? '-');
                    $sheet->setCellValue('H' . $row, (float)($tx->debit ?? 0));
                    $sheet->setCellValue('I' . $row, (float)($tx->credit ?? 0));
                    $bal = (float)($tx->running_balance ?? 0);
                    $sheet->setCellValue('J' . $row, $crdr($bal));
                    $row++;
                }

                // Subtotal row
                $sheet->setCellValue('A' . $row, 'Total for ' . ($dp->party->name ?? 'Party'));
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue('H' . $row, (float)$dp->period_debit);
                $sheet->setCellValue('I' . $row, (float)$dp->period_credit);
                $sheet->setCellValue('J' . $row, $crdr($dp->closing_balance));
                $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
                $row++;
            }
        } else {
            // Mix mode
            // Opening balance row
            $sheet->setCellValue('A' . $row, $data['startDate'] ? \Carbon\Carbon::parse($data['startDate'])->format('d M Y') : '-');
            $sheet->setCellValue('B' . $row, 'All Filtered Parties');
            $sheet->setCellValue('C' . $row, '-');
            $sheet->setCellValue('D' . $row, 'Opening');
            $sheet->setCellValue('G' . $row, 'Opening Balance Brought Forward');
            $sheet->setCellValue('H' . $row, $data['totalOpeningBalance'] < 0 ? abs($data['totalOpeningBalance']) : 0);
            $sheet->setCellValue('I' . $row, $data['totalOpeningBalance'] >= 0 ? abs($data['totalOpeningBalance']) : 0);
            $sheet->setCellValue('J' . $row, $crdr($data['totalOpeningBalance']));
            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setItalic(true);
            $row++;

            foreach ($data['allMixTransactions'] as $tx) {
                $sheet->setCellValue('A' . $row, $tx->date ? \Carbon\Carbon::parse($tx->date)->format('d M Y') : '-');
                $sheet->setCellValue('B' . $row, $tx->party_name ?? '-');
                $sheet->setCellValue('C' . $row, ucfirst($tx->party_type ?? '-'));
                $sheet->setCellValue('D' . $row, $tx->type ?? '-');
                $sheet->setCellValue('E' . $row, $tx->ref ?? '-');
                $sheet->setCellValue('F' . $row, $tx->bill_no ?? '-');
                $sheet->setCellValue('G' . $row, $tx->description ?? '-');
                $sheet->setCellValue('H' . $row, (float)($tx->debit ?? 0));
                $sheet->setCellValue('I' . $row, (float)($tx->credit ?? 0));
                $bal = (float)($tx->running_balance ?? 0);
                $sheet->setCellValue('J' . $row, $crdr($bal));
                $row++;
            }
        }

        // Grand Total Row
        $sheet->setCellValue('A' . $row, 'Grand Total');
        $sheet->mergeCells("A{$row}:G{$row}");
        $sheet->setCellValue('H' . $row, (float)$data['totalPeriodDebit']);
        $sheet->setCellValue('I' . $row, (float)$data['totalPeriodCredit']);
        $sheet->setCellValue('J' . $row, $crdr($data['totalClosingBalance']));
        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFDDE7D8');
        if (!empty($data['agentNote'])) {
            $sheet->mergeCells('A' . ($row + 1) . ':J' . ($row + 1)); // merged: the note does not widen the Date column
            $sheet->setCellValue('A' . ($row + 1), $data['agentNote']);
        }

        $sheet->getStyle('H6:I' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Detailed_Party_Ledger_Report_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function getDetailedReportData(Request $request)
    {
        return $this->detailedReportFigures($request, $this->detailedReportParties($request));
    }

    /**
     * Parties of the detailed report: the list for the filters, or only the chosen party (selected_party).
     * A chosen party that is not in the list gives no parties and a partyNotice (not every party, as before).
     */
    private function detailedReportParties(Request $request): array
    {
        // the statements read the dates from the request: only real dates, swapped if reversed (as the header shows them)
        $this->cleanPeriod($request);
        // the Shop filter of an agent statement is not a filter here: it would cut every agent down to one shop
        $request->query->remove('customer_id');
        $data = $this->getPartyListData($request);
        $parties = $data['parties'];

        $selectedParty = $request->query('selected_party');
        $partyNotice = null;

        $filteredParties = $parties;
        if (!empty($selectedParty)) {
            $matched = $parties->filter(function ($p) use ($selectedParty) {
                return ($p->party_type . '_' . $p->id) === $selectedParty;
            });
            if ($matched->isNotEmpty()) {
                $filteredParties = $matched;
            } else {
                $filteredParties = collect();
                $selectedParty = null;
                $partyNotice = 'The chosen party is not in this list for these filters (dates / balance filters). Clear the party or change the filters.';
            }
        }

        // "All types" builds one full statement per party: 1,155 parties took 16-36 s (130 s with dates under load)
        if ($this->allTypes($request) && !$selectedParty && $filteredParties->count() > self::DETAILED_MAX_PARTIES) {
            throw new \Illuminate\Http\Exceptions\HttpResponseException(
                redirect()->route('admin.ledger.party.index', $request->query())->with('error',
                    'Please pick a Master Type or a party for the detailed report (All types has '
                    . number_format($filteredParties->count()) . ' parties, too many for one report).')
            );
        }

        return $data + [
            'filteredParties' => $filteredParties,
            'selectedParty' => $selectedParty,
            'partyNotice' => $partyNotice,
            'viewMode' => $request->query('view_mode', 'mix'),
        ];
    }

    /** One statement per party of detailedReportParties(), with the totals and the mixed list. */
    private function detailedReportFigures(Request $request, array $chosen): array
    {
        $parties = $chosen['parties'];
        $masters = $chosen['masters'];
        $startDate = $chosen['startDate'];
        $endDate = $chosen['endDate'];
        $filteredParties = $chosen['filteredParties'];
        $selectedParty = $chosen['selectedParty'];
        $viewMode = $chosen['viewMode'];

        $allAvailableTypes = collect();
        $detailedParties = collect();
        $allMixTransactions = collect();

        $totalOpeningBalance = 0;
        $totalPeriodDebit = 0;
        $totalPeriodCredit = 0;
        $totalClosingBalance = 0;

        // each party's own statement, as its statement page shows it ('party_wise' made an agent's balance run per shop)
        $statementRequest = $request->duplicate(array_merge($request->query->all(), ['view_mode' => 'mix']));

        foreach ($filteredParties as $p) {
            $ledgerData = app(\App\Services\Ledger\PartyLedgerService::class)->build($statementRequest, $p->party_type, $p->id);

            $pOpening = (float)($ledgerData['openingBalAmount'] ?? 0);
            $pClosing = (float)($ledgerData['party']->balance ?? 0);
            $txCollection = collect($ledgerData['transactions'] ?? []);

            $pDebit = (float)$txCollection->sum('debit');
            $pCredit = (float)$txCollection->sum('credit');

            if (isset($ledgerData['availableTypes']) && is_array($ledgerData['availableTypes'])) {
                $allAvailableTypes = $allAvailableTypes->merge($ledgerData['availableTypes']);
            }

            $detailedParties->push((object)[
                'party' => $p,
                'opening_balance' => $pOpening,
                'closing_balance' => $pClosing,
                'period_debit' => $pDebit,
                'period_credit' => $pCredit,
                'transactions' => $txCollection
            ]);
        }

        // Totals and the mixed list. "All types": a sales agent is the total of its shops, and a shop that is
        // also one of these parties is counted once, in its own rows, so the agent adds only its other shops.
        // One chosen party is counted whole, as its statement (the balance filters only pick the parties of a list).
        $otherShops = $this->allTypes($request)
            ? app(\App\Services\Ledger\PartyLedgerService::class)->agentOtherShops($filteredParties, $startDate, $endDate, $selectedParty ? null : $this->balanceFilter($request))
            : [];
        foreach ($detailedParties as $dp) {
            $p = $dp->party;
            $countedTx = $dp->transactions;
            $opening = $dp->opening_balance;
            $closing = $dp->closing_balance;
            if ($this->allTypes($request) && $p->party_type === 'sales_agent') {
                // each entry of an agent statement carries the shop it belongs to (customer_id)
                $shops = collect($otherShops[$p->id] ?? []);
                $shopIds = $shops->pluck('id')->map(fn($id) => (int) $id)->all();
                $countedTx = $countedTx->filter(fn($tx) => in_array((int) ($tx->customer_id ?? 0), $shopIds, true))->values();
                $opening = round((float) $shops->sum('opening_balance'), 2);
                $closing = round((float) $shops->sum('balance'), 2);
            }

            $totalOpeningBalance += $opening;
            $totalPeriodDebit += (float)$countedTx->sum('debit');
            $totalPeriodCredit += (float)$countedTx->sum('credit');
            $totalClosingBalance += $closing;

            foreach ($countedTx as $tx) {
                // a copy: the mixed list sets its own running balance, the party's section keeps its statement's
                $tx = clone $tx;
                $tx->party_name = $p->name ?? '-';
                $tx->party_type = $p->party_type;
                $tx->party_id = $p->id;
                $tx->party_key = $p->party_type . '_' . $p->id;
                $allMixTransactions->push($tx);
            }
        }

        // Sort mix transactions chronologically
        $allMixTransactions = $allMixTransactions->sort(function ($a, $b) {
            $dateA = \Carbon\Carbon::parse($a->date)->format('Y-m-d');
            $dateB = \Carbon\Carbon::parse($b->date)->format('Y-m-d');
            if ($dateA !== $dateB) {
                return $dateA <=> $dateB;
            }
            return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
        })->values();

        // Calculate running balance for consolidated mix stream
        $runningBal = $totalOpeningBalance;
        foreach ($allMixTransactions as $tx) {
            $runningBal += ($tx->credit - $tx->debit);
            $tx->running_balance = $runningBal;
        }

        $availableTypes = $allAvailableTypes->unique()->filter()->values()->all();

        return [
            'parties' => $parties,
            'filteredParties' => $filteredParties,
            'detailedParties' => $detailedParties,
            'allMixTransactions' => $allMixTransactions,
            'totalOpeningBalance' => $totalOpeningBalance,
            'totalPeriodDebit' => $totalPeriodDebit,
            'totalPeriodCredit' => $totalPeriodCredit,
            'totalClosingBalance' => $totalClosingBalance,
            'availableTypes' => $availableTypes,
            'viewMode' => $viewMode,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'selectedParty' => $selectedParty,
            'partyNotice' => $chosen['partyNotice'],
            'typeId' => $request->query('type_id'),
            'customerType' => $chosen['customerType'] ?? null,
            'vendorType' => $chosen['vendorType'] ?? null,
            'purchaseAgents' => \App\Models\PurchaseAgent::where('status', 1)->orderBy('name', 'asc')->get(),
            'search' => $request->query('search'),
            'masters' => $masters,
            // one chosen party is its own statement: nothing to explain about shops counted once
            'agentNote' => $selectedParty ? null : $this->agentNote($filteredParties, $request, true),
        ];
    }

    public function exportListPdf(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');
        $data = $this->getPartyListData($request);

        // dompdf needs about 0.45 MB per row: the 1,155 parties of "All types" passed the 512 MB memory limit (HTTP 500)
        if ($data['parties']->count() > self::LIST_PDF_MAX_ROWS) {
            return redirect()->route('admin.ledger.party.index', $request->query())->with('error',
                'This list has ' . number_format($data['parties']->count()) . ' parties, too many for one PDF (up to '
                . number_format(self::LIST_PDF_MAX_ROWS) . '). Please pick a Master Type or another filter, or use Export Excel, which has every party.');
        }

        $money = $this->totalsRows($data['parties'], $request);
        $pdf = \PDF::loadView('admin.ledger.party.list_pdf', [
            'parties' => $data['parties'],
            'search' => $search,
            'typeId' => $typeId,
            'totalOpening' => (float) $money->sum('opening_balance'),
            'totalDebit' => (float) $money->sum('period_debit'),
            'totalCredit' => (float) $money->sum('period_credit'),
            'totalBalance' => (float) $money->sum('balance'),
            'agentNote' => $this->agentNote($data['parties'], $request),
            'customerType' => $data['customerType'] ?? null,
            'vendorType' => $data['vendorType'] ?? null,
            'startDate' => $data['startDate'],
            'endDate' => $data['endDate'],
        ])->setPaper('a4', 'landscape');

        return $pdf->download('Party_Ledger_List_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportListExcel(Request $request)
    {
        $data = $this->getPartyListData($request);
        $parties = $data['parties'];
        $crdr = fn($v) => number_format(abs($v), 2) . ' ' . ($v >= 0 ? 'CR' : 'DR');

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Party Ledger Summary');

        // Header Title
        $sheet->mergeCells('A1:H1');
        $sheet->setCellValue('A1', 'SNAPKID - Party Financial Ledger Summary');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Period and date
        $sheet->mergeCells('A2:H2');
        $subtitle = 'Period: ' . $this->periodText($data['startDate'], $data['endDate']);
        if ($request->query('min_closing')) $subtitle .= '   |   Min Closing: ₹ ' . number_format((float)$request->query('min_closing'), 2);
        if ($request->query('max_closing')) $subtitle .= '   |   Max Closing: ₹ ' . number_format((float)$request->query('max_closing'), 2);
        if ($request->query('balance_type')) $subtitle .= '   |   Balance: ' . strtoupper($request->query('balance_type'));
        if (!empty($data['customerType'])) $subtitle .= '   |   Customer Type: ' . ucfirst($data['customerType']);
        if (!empty($data['vendorType'])) $subtitle .= '   |   Vendor Type: ' . $this->vendorTypeText($data['vendorType']);
        $subtitle .= '   |   Generated on: ' . date('d M Y, h:i A');
        $sheet->setCellValue('A2', $subtitle);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['#', 'Party Name', 'Party Type', 'Phone', 'Opening Balance (₹)', 'Debit (₹)', 'Credit (₹)', 'Closing Balance (₹)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:H4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $money = $this->totalsRows($parties, $request);
        $totalBalance = (float) $money->sum('balance');
        $totalOpening = (float) $money->sum('opening_balance');
        $totalDebit = (float) $money->sum('period_debit');
        $totalCredit = (float) $money->sum('period_credit');
        foreach ($parties->values() as $idx => $p) {
            $bal = (float)$p->balance;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $status = (int) ($p->status ?? 1) === 1 ? '' : ((int) ($p->status ?? 1) === 3 ? ' (Deleted)' : ' (Inactive)');
            $sheet->setCellValue('B' . $row, ($p->name ?? '-') . $status);
            $sheet->setCellValue('C' . $row, ucfirst($p->party_type ?? '-'));
            $sheet->setCellValue('D' . $row, $p->phone ?? '-');
            $sheet->setCellValue('E' . $row, $crdr((float)$p->opening_balance));
            $sheet->setCellValue('F' . $row, (float)$p->period_debit);
            $sheet->setCellValue('G' . $row, (float)$p->period_credit);
            $sheet->setCellValue('H' . $row, $crdr($bal));
            $row++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, $crdr($totalOpening));
        $sheet->setCellValue('F' . $row, $totalDebit);
        $sheet->setCellValue('G' . $row, $totalCredit);
        $sheet->setCellValue('H' . $row, $crdr($totalBalance));
        $sheet->getStyle('A' . $row . ':H' . $row)->getFont()->setBold(true);
        if ($note = $this->agentNote($parties, $request)) {
            $sheet->mergeCells('A' . ($row + 1) . ':H' . ($row + 1)); // merged: the note does not widen the # column
            $sheet->setCellValue('A' . ($row + 1), $note);
        }
        $sheet->getStyle('A' . $row . ':H' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
        $sheet->getStyle('F5:G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Party_Ledger_List_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    /**
     * Rows the detailed PDF would print: the entries of the mixed list, or in party-wise view the entries of each
     * party (at least one line) plus one for its heading and total (692 nearly empty parties used 376 MB).
     */
    private function detailedPdfRows(array $data): int
    {
        if ($data['viewMode'] !== 'party_wise') {
            return $data['allMixTransactions']->count();
        }
        return (int) $data['detailedParties']->sum(fn($dp) => 1 + max(1, $dp->transactions->count()));
    }

    /** Back to the detailed report: too many rows for one PDF ($rows is the text of the count). */
    private function detailedPdfTooBig(Request $request, string $rows)
    {
        return redirect()->route('admin.ledger.party.detailedReport', $request->query())->with('error',
            'This report has ' . $rows . ' rows, too many for one PDF (up to ' . number_format(self::DETAILED_PDF_MAX_ROWS)
            . '). Please choose From / To dates or one party, or use the Excel, which has every row.');
    }

    /** No Master Type picked: the list has every type, sales agents and their shops together. */
    private function allTypes(Request $request): bool
    {
        return !$request->query('type_id');
    }

    /** Rows added up in the totals: each shop counted once (PartyLedgerService::totalsRows, same as the owner list). */
    private function totalsRows($parties, Request $request)
    {
        [$startDate, $endDate] = $this->listPeriod($request);
        return app(\App\Services\Ledger\PartyLedgerService::class)
            ->totalsRows($parties, $this->allTypes($request), $startDate, $endDate, $this->balanceFilter($request));
    }

    /** Does one party row pass the list's closing / balance filters? */
    private function balanceFilter(Request $request): callable
    {
        return fn($party) => $this->applyClosingFilters(collect([$party]), $request)->isNotEmpty();
    }

    /** Note under the totals, only with "All types" when a sales agent is listed (null = no note). */
    private function agentNote($parties, Request $request, bool $detailed = false): ?string
    {
        if (!$this->allTypes($request) || !$parties->contains('party_type', 'sales_agent')) {
            return null;
        }
        // one text for every page (PartyLedgerService), the owner list uses it too
        return $detailed ? \App\Services\Ledger\PartyLedgerService::AGENT_NOTE_DETAILED : \App\Services\Ledger\PartyLedgerService::AGENT_NOTE;
    }

    /** Statements and the detailed report: only real dates reach PartyLedgerService, swapped if reversed (as listPeriod). */
    private function cleanPeriod(Request $request): void
    {
        [$start, $end] = $this->listPeriod($request);
        foreach (['start_date' => $start, 'end_date' => $end] as $key => $value) {
            $value ? $request->query->set($key, $value) : $request->query->remove($key);
        }
    }

    /**
     * Parties of the list with their ledger figures for the chosen period (From / To dates, both optional):
     * opening_balance (balance on the From date), period_debit, period_credit and balance (closing on the To date).
     * Without dates: opening = the opening balance entered for the party, closing = today's balance.
     * Every active party is listed, and an inactive or deleted party too while its closing balance is not 0.
     * The figures come from PartyLedgerService::listFigures (the same records as the statement, added up per type).
     */
    private function getPartyListData(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');
        $customerType = $request->query('customer_type', $request->query('type'));
        $vendorType = $request->query('vendor_type', $request->query('purchase_agent_id'));
        [$startDate, $endDate] = $this->listPeriod($request);

        $masters = \App\Models\AdjustmentMaster::whereNotIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        // Customer type / Vendor type only filter their own Master Type (the select stays in the form when hidden)
        $picked = $typeId && $typeId !== 'sales_agent' ? \App\Models\AdjustmentMaster::find($typeId) : null;
        if (!$picked || !($picked->model_name === 'App\Models\MasterCustomer' || strtolower($picked->name) === 'customer')) {
            $customerType = null;
        }
        if (!$picked || !($picked->model_name === 'App\Models\Vendor' || strtolower($picked->name) === 'vendor')) {
            $vendorType = null;
        }

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                $parties = $this->agentParties($search);
            } else {
                if ($picked) {
                    $parties = $this->masterParties($picked, $search, $customerType, $vendorType);
                }
            }
        } else {
            // Default to ALL masters items (every party: the old limit of 100 per type left parties out of the totals)
            foreach ($masters as $master) {
                $parties = $parties->concat($this->masterParties($master, $search, $customerType, $vendorType));
            }

            // Also load Sales Agents in ALL view
            $parties = $parties->concat($this->agentParties($search));
        }

        app(\App\Services\Ledger\PartyLedgerService::class)->fillListFigures($parties, $startDate, $endDate);
        $parties = $parties->filter(function ($p) {
            return (int) ($p->status ?? 1) === 1 || abs((float) $p->balance) >= 0.005;
        });

        $parties = $this->applyClosingFilters($parties, $request);

        // Sort by name
        $parties = $parties->sortBy('name');

        return compact('parties', 'masters', 'startDate', 'endDate', 'customerType', 'vendorType');
    }

    /** Closing balance min / max (absolute amount) and balance type (Dr / Cr only), as requested. */
    private function applyClosingFilters($parties, Request $request)
    {
        $minClosing = $request->query('min_closing', $request->query('min_amount'));
        $maxClosing = $request->query('max_closing', $request->query('max_amount'));
        $balanceType = strtolower(trim((string)$request->query('balance_type')));

        if ($minClosing !== null && $minClosing !== '') {
            $minVal = (float)$minClosing;
            $parties = $parties->filter(function ($p) use ($minVal) {
                return abs((float)$p->balance) >= $minVal;
            });
        }

        if ($maxClosing !== null && $maxClosing !== '') {
            $maxVal = (float)$maxClosing;
            $parties = $parties->filter(function ($p) use ($maxVal) {
                return abs((float)$p->balance) <= $maxVal;
            });
        }

        if ($balanceType === 'credit') {
            $parties = $parties->filter(function ($p) {
                return (float)$p->balance > 0;
            });
        } elseif ($balanceType === 'debit') {
            $parties = $parties->filter(function ($p) {
                return (float)$p->balance < 0;
            });
        }

        return $parties;
    }

    private function vendorTypeText($vendorType, $purchaseAgents = null): string
    {
        if (empty($vendorType)) return '';
        if ($vendorType === 'direct') return 'Direct';
        if ($vendorType === 'agent') return 'Fabric Purchase Agent';
        $paId = str_starts_with($vendorType, 'pa_') ? (int) substr($vendorType, 3) : (int) $vendorType;
        if ($paId > 0) {
            $pa = ($purchaseAgents ? $purchaseAgents->firstWhere('id', $paId) : null) ?: \App\Models\PurchaseAgent::find($paId);
            return 'Purchase Agent: ' . ($pa ? $pa->name : $paId);
        }
        return ucfirst($vendorType);
    }

    /** Parties of one master for the list, any status (getPartyListData keeps the active ones and those with a balance). */
    private function masterParties($master, $search, $customerType, $vendorType)
    {
        $modelName = $master->model_name;
        if (!$modelName || !class_exists($modelName)) {
            return collect();
        }
        $isCustomer = ($modelName === 'App\Models\MasterCustomer' || strtolower($master->name) === 'customer');
        $isVendor = ($modelName === 'App\Models\Vendor' || strtolower($master->name) === 'vendor');

        return $modelName::query()
            ->when($search, function ($q) use ($search, $modelName) {
                if ($modelName == 'App\Models\BankAccount') {
                    $q->where('bank_name', 'LIKE', "%$search%");
                } else {
                    $q->where('name', 'LIKE', "%$search%");
                }
            })
            ->when($isCustomer && !empty($customerType), function ($q) use ($customerType) {
                if ($customerType == 'direct') {
                    $q->where('subtype', 'direct');
                } elseif ($customerType == 'agent') {
                    $q->where('subtype', 'agent');
                } else {
                    $q->where('type', $customerType);
                }
            })
            ->when($isVendor && !empty($vendorType), function ($q) use ($vendorType) {
                if ($vendorType === 'direct') {
                    $q->where(function ($sub) {
                        $sub->whereNull('purchase_agent_id')->orWhere('purchase_agent_id', 0);
                    });
                } elseif ($vendorType === 'agent') {
                    $q->whereNotNull('purchase_agent_id')->where('purchase_agent_id', '!=', 0);
                } elseif (str_starts_with($vendorType, 'pa_')) {
                    $agentId = (int) substr($vendorType, 3);
                    $q->where('purchase_agent_id', $agentId);
                } elseif (is_numeric($vendorType)) {
                    $q->where('purchase_agent_id', (int) $vendorType);
                }
            })
            ->get()
            ->map(function ($v) use ($master) {
                $v->party_type = strtolower($master->name);
                $v->master_id_val = $master->id;
                if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                return $v;
            });
    }

    /** Sales agents for the list, any status. */
    private function agentParties($search)
    {
        return \App\Models\SalesAgent::query()
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            })
            ->get()
            ->map(function ($v) {
                $v->party_type = 'sales_agent';
                $v->master_id_val = 'sales_agent';
                return $v;
            });
    }

    /** From / To dates of the list (Y-m-d); a wrong date is ignored and the two are swapped if From is after To. */
    private function listPeriod(Request $request): array
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

    private function periodText($startDate, $endDate): string
    {
        $f = fn($d) => \Carbon\Carbon::parse($d)->format('d M Y');
        if ($startDate && $endDate) return $f($startDate) . ' to ' . $f($endDate);
        if ($startDate) return 'From ' . $f($startDate);
        if ($endDate) return 'Up to ' . $f($endDate);
        return 'All dates';
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        // a date like 'abc' gave an error page, and reversed dates an empty statement
        $this->cleanPeriod($request);
        return app(\App\Services\Ledger\PartyLedgerService::class)->build($request, $type, $id);
    }
}
