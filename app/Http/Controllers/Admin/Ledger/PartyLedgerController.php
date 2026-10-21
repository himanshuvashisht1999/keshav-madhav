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
    public function index(Request $request)
    {
        $data = $this->getPartyListData($request);
        $parties = $data['parties'];
        $masters = $data['masters'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $totalPartiesCount = $parties->count();
        $totalCreditBalance = (float)$parties->where('balance', '>', 0)->sum('balance');
        $totalDebitBalance = (float)abs($parties->where('balance', '<', 0)->sum('balance'));
        $totalNetBalance = (float)$parties->sum('balance');
        $totalOpening = (float)$parties->sum('opening_balance');
        $totalPeriodDebit = (float)$parties->sum('period_debit');
        $totalPeriodCredit = (float)$parties->sum('period_credit');

        $perPage = (int) $request->query('per_page', 25);
        if ($perPage < 1) $perPage = 25;
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage('page');
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
        $name = str_replace(' ', '_', $data['party']->name) . '_Ledger_' . date('Y-m-d') . '.pdf';
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
        $data = $this->getDetailedReportData($request);
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
        if ($viewMode === 'party_wise') {
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
        $data = $this->getPartyListData($request);
        $parties = $data['parties'];
        $masters = $data['masters'];
        $startDate = $data['startDate'];
        $endDate = $data['endDate'];

        $selectedParty = $request->query('selected_party');
        $viewMode = $request->query('view_mode', 'mix');

        $filteredParties = $parties;
        if (!empty($selectedParty)) {
            $matched = $parties->filter(function ($p) use ($selectedParty) {
                return ($p->party_type . '_' . $p->id) === $selectedParty;
            });
            if ($matched->isNotEmpty()) {
                $filteredParties = $matched;
            } else {
                $selectedParty = null;
            }
        }

        $allAvailableTypes = collect();
        $detailedParties = collect();
        $allMixTransactions = collect();

        $totalOpeningBalance = 0;
        $totalPeriodDebit = 0;
        $totalPeriodCredit = 0;
        $totalClosingBalance = 0;

        foreach ($filteredParties as $p) {
            $ledgerData = app(\App\Services\Ledger\PartyLedgerService::class)->build($request, $p->party_type, $p->id);

            $pOpening = (float)($ledgerData['openingBalAmount'] ?? 0);
            $pClosing = (float)($ledgerData['party']->balance ?? 0);
            $txCollection = collect($ledgerData['transactions'] ?? []);

            $pDebit = (float)$txCollection->sum('debit');
            $pCredit = (float)$txCollection->sum('credit');

            $totalOpeningBalance += $pOpening;
            $totalPeriodDebit += $pDebit;
            $totalPeriodCredit += $pCredit;
            $totalClosingBalance += $pClosing;

            if (isset($ledgerData['availableTypes']) && is_array($ledgerData['availableTypes'])) {
                $allAvailableTypes = $allAvailableTypes->merge($ledgerData['availableTypes']);
            }

            foreach ($txCollection as $tx) {
                $tx->party_name = $p->name ?? '-';
                $tx->party_type = $p->party_type;
                $tx->party_id = $p->id;
                $tx->party_key = $p->party_type . '_' . $p->id;
                $allMixTransactions->push($tx);
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
            'typeId' => $request->query('type_id'),
            'customerType' => $data['customerType'] ?? null,
            'vendorType' => $data['vendorType'] ?? null,
            'purchaseAgents' => \App\Models\PurchaseAgent::where('status', 1)->orderBy('name', 'asc')->get(),
            'search' => $request->query('search'),
            'masters' => $masters,
        ];
    }

    public function exportListPdf(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');
        $data = $this->getPartyListData($request);

        $pdf = \PDF::loadView('admin.ledger.party.list_pdf', [
            'parties' => $data['parties'],
            'search' => $search,
            'typeId' => $typeId,
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
        $totalBalance = 0;
        $totalOpening = 0;
        $totalDebit = 0;
        $totalCredit = 0;
        foreach ($parties->values() as $idx => $p) {
            $bal = (float)$p->balance;
            $totalBalance += $bal;
            $totalOpening += (float)$p->opening_balance;
            $totalDebit += (float)$p->period_debit;
            $totalCredit += (float)$p->period_credit;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $p->name ?? '-');
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
     * Parties of the list with their ledger figures for the chosen period (From / To dates, both optional):
     * opening_balance (balance on the From date), period_debit, period_credit and balance (closing on the To date).
     * Without dates: opening = the opening balance entered for the party, closing = today's balance.
     */
    private function getPartyListData(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');
        $customerType = $request->query('customer_type', $request->query('type'));
        $vendorType = $request->query('vendor_type', $request->query('purchase_agent_id'));
        [$startDate, $endDate] = $this->listPeriod($request);
        $ledgerRequest = new Request(array_filter(['start_date' => $startDate, 'end_date' => $endDate]));

        $masters = \App\Models\AdjustmentMaster::whereNotIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                $items = \App\Models\SalesAgent::where('status', 1)
                    ->when($search, function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->get()
                    ->map(function ($v) use ($ledgerRequest) {
                        $v->party_type = 'sales_agent';
                        $v->master_id_val = 'sales_agent';
                        return $this->withLedgerFigures($v, $ledgerRequest);
                    });
                $parties = $parties->concat($items);
            } else {
                $master = \App\Models\AdjustmentMaster::find($typeId);
                if ($master) {
                    $modelName = $master->model_name;
                    if (class_exists($modelName)) {
                        $isCustomer = ($modelName === 'App\Models\MasterCustomer' || strtolower($master->name) === 'customer');
                        $isVendor = ($modelName === 'App\Models\Vendor' || strtolower($master->name) === 'vendor');
                        $items = $modelName::where('status', 1)
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
                            ->map(function ($v) use ($master, $ledgerRequest) {
                                $v->party_type = strtolower($master->name);
                                $v->master_id_val = $master->id;
                                if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                                return $this->withLedgerFigures($v, $ledgerRequest);
                            });
                        $parties = $parties->concat($items);
                    }
                }
            }
        } else {
            // Default to ALL masters items
            foreach ($masters as $master) {
                $modelName = $master->model_name;
                if (class_exists($modelName)) {
                    $isCustomer = ($modelName === 'App\Models\MasterCustomer' || strtolower($master->name) === 'customer');
                    $isVendor = ($modelName === 'App\Models\Vendor' || strtolower($master->name) === 'vendor');
                    $items = $modelName::where('status', 1)
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
                        ->limit(100) // Safety limit for "All" view
                        ->get()
                        ->map(function ($v) use ($master, $ledgerRequest) {
                            $v->party_type = strtolower($master->name);
                            $v->master_id_val = $master->id;
                            if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                            return $this->withLedgerFigures($v, $ledgerRequest);
                        });
                    $parties = $parties->concat($items);
                }
            }

            // Also load Sales Agents in ALL view
            $agentItems = \App\Models\SalesAgent::where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->limit(100)
                ->get()
                ->map(function ($v) use ($ledgerRequest) {
                    $v->party_type = 'sales_agent';
                    $v->master_id_val = 'sales_agent';
                    return $this->withLedgerFigures($v, $ledgerRequest);
                });
            $parties = $parties->concat($agentItems);
        }

        // Filter by closing balance min / max and balance type if requested
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

        // Sort by name
        $parties = $parties->sortBy('name');

        return compact('parties', 'masters', 'startDate', 'endDate', 'customerType', 'vendorType');
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

    /** Opening, debit, credit and closing of one party for the period, from the same ledger as its statement. */
    private function withLedgerFigures($v, Request $ledgerRequest)
    {
        $ledgerData = $this->getLedgerData($ledgerRequest, $v->party_type, $v->id);
        $v->opening_balance = (float) ($ledgerData['openingBalAmount'] ?? 0);
        $v->period_debit = (float) collect($ledgerData['transactions'] ?? [])->sum('debit');
        $v->period_credit = (float) collect($ledgerData['transactions'] ?? [])->sum('credit');
        $v->balance = $ledgerData['party']->balance ?? 0;
        return $v;
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
        return app(\App\Services\Ledger\PartyLedgerService::class)->build($request, $type, $id);
    }
}
