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

        return view('admin.ledger.party.index', compact('parties', 'masters', 'totalPartiesCount', 'totalCreditBalance', 'totalDebitBalance', 'totalNetBalance',
            'totalOpening', 'totalPeriodDebit', 'totalPeriodCredit', 'startDate', 'endDate'));
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
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'SNAPKID - ' . ($party->name ?? 'Party') . ' Account Ledger');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->mergeCells('A2:G2');
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
        $sheet->setCellValue('E3', 'Current Balance: ₹ ' . number_format(abs($party->balance), 2) . ' ' . ($party->balance >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A3:G3')->getFont()->setBold(true);

        // Table Headers
        $headers = ['Date', 'Type', 'Ref / Voucher No', 'Particulars', 'Debit (₹)', 'Credit (₹)', 'Balance (₹)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '5', $h);
        }

        $headerRange = 'A5:G5';
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
        $sheet->setCellValue('D' . $row, 'Opening Balance B/F');
        $sheet->setCellValue('E' . $row, $openingBalAmount < 0 ? abs($openingBalAmount) : 0);
        $sheet->setCellValue('F' . $row, $openingBalAmount >= 0 ? abs($openingBalAmount) : 0);
        $sheet->setCellValue('G' . $row, number_format(abs($openingBalAmount), 2) . ' ' . ($openingBalAmount >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setItalic(true);
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
            $sheet->setCellValue('D' . $row, $tx->description ?? '-');
            $sheet->setCellValue('E' . $row, $debit);
            $sheet->setCellValue('F' . $row, $credit);
            $sheet->setCellValue('G' . $row, number_format(abs($bal), 2) . ' ' . ($bal >= 0 ? 'CR' : 'DR'));

            $row++;
        }

        // Total Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('E' . $row, $totalDebit);
        $sheet->setCellValue('F' . $row, $totalCredit);
        $sheet->setCellValue('G' . $row, number_format(abs($party->balance), 2) . ' ' . ($party->balance >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        // Number formats
        $sheet->getStyle('E6:F' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = str_replace(' ', '_', $party->name ?? 'Party') . '_Ledger_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
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
        $sheet->setCellValue('A2', 'Period: ' . $this->periodText($data['startDate'], $data['endDate']) . '   |   Generated on: ' . date('d M Y, h:i A'));
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
                        $items = $modelName::where('status', 1)
                            ->when($search, function ($q) use ($search, $modelName) {
                                if ($modelName == 'App\Models\BankAccount') {
                                    $q->where('bank_name', 'LIKE', "%$search%");
                                } else {
                                    $q->where('name', 'LIKE', "%$search%");
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
                    $items = $modelName::where('status', 1)
                        ->when($search, function ($q) use ($search, $modelName) {
                            if ($modelName == 'App\Models\BankAccount') {
                                $q->where('bank_name', 'LIKE', "%$search%");
                            } else {
                                $q->where('name', 'LIKE', "%$search%");
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

        // Sort by name
        $parties = $parties->sortBy('name');

        return compact('parties', 'masters', 'startDate', 'endDate');
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
