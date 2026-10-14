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
        $search = $request->query('search');
        $typeId = $request->query('type_id'); // Adjustment Master ID or 'sales_agent'

        $masters = \App\Models\AdjustmentMaster::whereNotIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                $items = \App\Models\SalesAgent::where('status', 1)
                    ->when($search, function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->get()
                    ->map(function ($v) {
                        $v->party_type = 'sales_agent';
                        $v->master_id_val = 'sales_agent';
                        $ledgerData = $this->getLedgerData(new Request(), 'sales_agent', $v->id);
                        $v->balance = $ledgerData['party']->balance ?? 0;
                        return $v;
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
                            ->map(function ($v) use ($master) {
                                $v->party_type = strtolower($master->name);
                                $v->master_id_val = $master->id;
                                if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                                $ledgerData = $this->getLedgerData(new Request(), $v->party_type, $v->id);
                                $v->balance = $ledgerData['party']->balance ?? 0;
                                return $v;
                            });
                        $parties = $parties->concat($items);
                    }
                }
            }
        } else {
            // Default to ALL masters items (might be too many, but let's see)
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
                        ->map(function ($v) use ($master) {
                            $v->party_type = strtolower($master->name);
                            $v->master_id_val = $master->id;
                            if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                            $ledgerData = $this->getLedgerData(new Request(), $v->party_type, $v->id);
                            $v->balance = $ledgerData['party']->balance ?? 0;
                            return $v;
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
                ->map(function ($v) {
                    $v->party_type = 'sales_agent';
                    $v->master_id_val = 'sales_agent';
                    $ledgerData = $this->getLedgerData(new Request(), 'sales_agent', $v->id);
                    $v->balance = $ledgerData['party']->balance ?? 0;
                    return $v;
                });
            $parties = $parties->concat($agentItems);
        }

        // Sort by name
        $parties = $parties->sortBy('name');

        $totalPartiesCount = $parties->count();
        $totalCreditBalance = (float)$parties->where('balance', '>', 0)->sum('balance');
        $totalDebitBalance = (float)abs($parties->where('balance', '<', 0)->sum('balance'));
        $totalNetBalance = (float)$parties->sum('balance');

        return view('admin.ledger.party.index', compact('parties', 'masters', 'totalPartiesCount', 'totalCreditBalance', 'totalDebitBalance', 'totalNetBalance'));
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
            'typeId' => $typeId
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Party_Ledger_List_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportListExcel(Request $request)
    {
        $data = $this->getPartyListData($request);
        $parties = $data['parties'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Party Ledger Summary');

        // Header Title
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'SNAPKID - Party Financial Ledger Summary');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Date
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Generated on: ' . date('d M Y, h:i A'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['#', 'Party Name', 'Party Type', 'Phone', 'Current Balance (₹)'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:E4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $totalBalance = 0;
        foreach ($parties as $idx => $p) {
            $bal = (float)$p->balance;
            $totalBalance += $bal;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $p->name ?? '-');
            $sheet->setCellValue('C' . $row, ucfirst($p->party_type ?? '-'));
            $sheet->setCellValue('D' . $row, $p->phone ?? '-');
            $sheet->setCellValue('E' . $row, number_format(abs($bal), 2) . ' ' . ($bal >= 0 ? 'CR' : 'DR'));
            $row++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, number_format(abs($totalBalance), 2) . ' ' . ($totalBalance >= 0 ? 'CR' : 'DR'));
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Party_Ledger_List_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getPartyListData(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');

        $masters = \App\Models\AdjustmentMaster::whereNotIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                $items = \App\Models\SalesAgent::where('status', 1)
                    ->when($search, function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->get()
                    ->map(function ($v) {
                        $v->party_type = 'sales_agent';
                        $v->master_id_val = 'sales_agent';
                        $ledgerData = $this->getLedgerData(new Request(), 'sales_agent', $v->id);
                        $v->balance = $ledgerData['party']->balance ?? 0;
                        return $v;
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
                            ->map(function ($v) use ($master) {
                                $v->party_type = strtolower($master->name);
                                $v->master_id_val = $master->id;
                                if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                                $ledgerData = $this->getLedgerData(new Request(), $v->party_type, $v->id);
                                $v->balance = $ledgerData['party']->balance ?? 0;
                                return $v;
                            });
                        $parties = $parties->concat($items);
                    }
                }
            }
        } else {
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
                        ->limit(100)
                        ->get()
                        ->map(function ($v) use ($master) {
                            $v->party_type = strtolower($master->name);
                            $v->master_id_val = $master->id;
                            if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                            $ledgerData = $this->getLedgerData(new Request(), $v->party_type, $v->id);
                            $v->balance = $ledgerData['party']->balance ?? 0;
                            return $v;
                        });
                    $parties = $parties->concat($items);
                }
            }

            $agentItems = \App\Models\SalesAgent::where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->limit(100)
                ->get()
                ->map(function ($v) {
                    $v->party_type = 'sales_agent';
                    $v->master_id_val = 'sales_agent';
                    $ledgerData = $this->getLedgerData(new Request(), 'sales_agent', $v->id);
                    $v->balance = $ledgerData['party']->balance ?? 0;
                    return $v;
                });
            $parties = $parties->concat($agentItems);
        }

        $parties = $parties->sortBy('name');

        return compact('parties', 'masters');
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        return app(\App\Services\Ledger\PartyLedgerService::class)->build($request, $type, $id);
    }
}
