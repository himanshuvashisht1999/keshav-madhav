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

class BankCashLedgerController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getBankCashListData($request);
        return view('admin.ledger.bank_cash.index', $data);
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        return view('admin.ledger.bank_cash.show', $data);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $pdf = \PDF::loadView('admin.ledger.bank_cash.download', $data);
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
        $sheet->setTitle('Bank Cash Ledger');

        // Header Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'SNAPKID - ' . ($party->name ?? 'Account') . ' Ledger');
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

        $fileName = str_replace(' ', '_', $party->name ?? 'Account') . '_Ledger_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    public function exportListPdf(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');
        $data = $this->getBankCashListData($request);
        
        $pdf = \PDF::loadView('admin.ledger.bank_cash.list_pdf', [
            'parties' => $data['parties'],
            'search' => $search,
            'typeId' => $typeId
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Bank_Cash_Ledger_List_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportListExcel(Request $request)
    {
        $data = $this->getBankCashListData($request);
        $parties = $data['parties'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Bank Cash Ledger Summary');

        // Header Title
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'SNAPKID - Bank & Cash Accounts Ledger Summary');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Date
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Generated on: ' . date('d M Y, h:i A'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['#', 'Account Name', 'Type', 'Phone', 'Current Balance (₹)'];
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

        $fileName = 'Bank_Cash_Ledger_List_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getBankCashListData(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id');

        $masters = \App\Models\AdjustmentMaster::whereIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
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
        }

        $parties = $parties->sortBy('name');

        return compact('parties', 'masters');
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');

        // Resolve Master
        $master = \App\Models\AdjustmentMaster::where('name', 'LIKE', $type)->first();
        if (!$master || !in_array(strtolower($master->name), ['bank account', 'cash master'])) {
            abort(404, "Invalid Ledger Type");
        }

        $modelName = $master->model_name;
        $party = $modelName::findOrFail($id);

        $transactions = collect();

        // 1. Payments
        $paymentsQuery = Payment::query();
        $paymentsQuery->where('payment_method_id', $id)
            ->where('payment_method_type', $modelName);

        // Exclude Journal Voucher payments to avoid double counting with JournalVoucherItem query
        $paymentsQuery->where(function($q) {
            $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
              ->orWhereNull('paymentable_type');
        });

        $payments = $paymentsQuery
            ->when($startDate, fn($q) => $q->whereDate('payment_date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('payment_date', '<=', $endDate))
            ->get();

        foreach ($payments as $p) {
            $isCredit = in_array($p->payment_type, ['received', 'credit']);
            $isDebit = in_array($p->payment_type, ['paid', 'debit']);

            if ($isCredit) {
                $debit = 0;
                $credit = (float) $p->amount;
                $desc = 'Payment Received (' . $p->payment_mode . ')';
            } elseif ($isDebit) {
                $debit = (float) $p->amount;
                $credit = 0;
                $desc = 'Payment Paid (' . $p->payment_mode . ')';
            } else {
                $debit = (float) $p->amount;
                $credit = 0;
                $desc = 'Adjustment (' . $p->payment_mode . ')';
            }

            $transactions->push((object) [
                'date' => $p->payment_date,
                'created_at' => $p->created_at,
                'type' => 'Payment',
                'ref' => $p->reference_id ?? ('Pay #' . $p->id),
                'debit' => $debit,
                'credit' => $credit,
                'description' => $desc . ($p->remarks ? ': ' . $p->remarks : ''),
                'view_url' => route('admin.payment.history.show', $p->id)
            ]);
        }

        // 2. Adjustments
        $mode = strtolower($master->name) === 'bank account' ? 'bank' : 'cash';
        $adjustments = \App\Models\PaymentAdjustment::where('payment_mode', $mode)
            ->where('payment_account_id', $id)
            ->when($startDate, fn($q) => $q->whereDate('date', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('date', '<=', $endDate))
            ->get();

        $groupedAdjustments = $adjustments->groupBy(function($item) {
            return $item->batch_id ?: 'single_' . $item->id;
        });

        foreach ($groupedAdjustments as $key => $group) {
            if (str_starts_with($key, 'single_')) {
                $adj = $group->first();
                $isCredit = $adj->type === 'debit'; 
                $transactions->push((object) [
                    'date' => $adj->date,
                    'created_at' => $adj->created_at,
                    'type' => 'Adjustment',
                    'ref' => 'Adj #' . $adj->id,
                    'debit' => $isCredit ? 0 : (float) $adj->amount,
                    'credit' => $isCredit ? (float) $adj->amount : 0,
                    'description' => '[Dist] ' . ($adj->remarks ?: $adj->entity_name),
                    'view_url' => '#'
                ]);
            } else {
                $first = $group->first();
                $totalDebit = 0;
                $totalCredit = 0;
                foreach ($group as $adj) {
                    $isCredit = $adj->type === 'debit'; 
                    if ($isCredit) {
                        $totalCredit += (float) $adj->amount;
                    } else {
                        $totalDebit += (float) $adj->amount;
                    }
                }
                $transactions->push((object) [
                    'date' => $first->date,
                    'created_at' => $first->created_at,
                    'type' => 'Adjustment',
                    'ref' => $key,
                    'debit' => $totalDebit,
                    'credit' => $totalCredit,
                    'description' => 'Grouped Adjustments (' . $group->count() . ' entries)',
                    'view_url' => route('admin.payment.adjustment.show', $key)
                ]);
            }
        }

        // 3. Fetch Journal Vouchers
        $masterIds = \App\Models\AdjustmentMaster::where('model_name', $modelName)->pluck('id');
        $vouchers = \App\Models\JournalVoucherItem::with('voucher')
            ->whereIn('master_type', $masterIds)
            ->where('master_id', $id)
            ->whereHas('voucher', function($q) use ($startDate, $endDate) {
                $q->when($startDate, fn($q2) => $q2->whereDate('date', '>=', $startDate))
                  ->when($endDate, fn($q2) => $q2->whereDate('date', '<=', $endDate));
            })
            ->get();

        $groupedVouchers = $vouchers->groupBy('journal_voucher_id');

        foreach ($groupedVouchers as $jvId => $group) {
            $first = $group->first();
            $totalDebit = 0;
            $totalCredit = 0;
            foreach ($group as $v) {
                $isCredit = strtolower($v->type) === 'credit';
                if ($isCredit) {
                    $totalCredit += (float) $v->amount;
                } else {
                    $totalDebit += (float) $v->amount;
                }
            }
            $transactions->push((object) [
                'date' => $first->voucher->date,
                'created_at' => $first->created_at,
                'type' => 'Journal Voucher',
                'ref' => $first->voucher->voucher_no,
                'debit' => $totalDebit,
                'credit' => $totalCredit,
                'description' => $first->narration ?: $first->voucher->narration ?: 'Journal Entry' . ($group->count() > 1 ? ' (' . $group->count() . ' items)' : ''),
                'view_url' => route('admin.payment.journal-voucher.show', $first->voucher->id)
            ]);
        }

        // 4. Opening Balance
        $lookupType = str_replace(' ', '_', strtolower($type));

        $openingBalance = \App\Models\MasterOpeningBalance::whereIn('master_type', [$type, $lookupType, str_replace('_', ' ', $lookupType)])
            ->where('master_id', $id)
            ->where('financial_year', \App\Models\MasterOpeningBalance::getCurrentFinancialYear())
            ->first();

        $openingBalAmount = 0;
        if ($openingBalance) {
            $balanceType = strtolower(trim($openingBalance->balance_type));
            $openingBalAmount = (float) $openingBalance->amount;
            
            // Debit = Negative, Credit = Positive
            if ($balanceType === 'debit') {
                $openingBalAmount = -$openingBalAmount;
            }
        }

        if ($startDate) {
            $preDebit = 0;
            $preCredit = 0;

            $paymentsPre = Payment::query()
                ->where('payment_method_id', $id)
                ->where('payment_method_type', $modelName)
                ->where(function($q) {
                    $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
                      ->orWhereNull('paymentable_type');
                })
                ->whereDate('payment_date', '<', $startDate)
                ->get();

            foreach ($paymentsPre as $p) {
                $isCredit = in_array($p->payment_type, ['received', 'credit']);
                if ($isCredit) {
                    $preCredit += (float) $p->amount;
                } else {
                    $preDebit += (float) $p->amount;
                }
            }

            $adjPre = \App\Models\PaymentAdjustment::where('payment_mode', $mode)
                ->where('payment_account_id', $id)
                ->whereDate('date', '<', $startDate)
                ->get();

            foreach ($adjPre as $adj) {
                $isCredit = $adj->type === 'debit';
                if ($isCredit) {
                    $preCredit += (float) $adj->amount;
                } else {
                    $preDebit += (float) $adj->amount;
                }
            }

            $vouPre = \App\Models\JournalVoucherItem::with('voucher')
                ->whereIn('master_type', $masterIds)
                ->where('master_id', $id)
                ->whereHas('voucher', function($q) use ($startDate) {
                    $q->whereDate('date', '<', $startDate);
                })
                ->get();

            foreach ($vouPre as $v) {
                $isCredit = strtolower($v->type) === 'credit';
                if ($isCredit) {
                    $preCredit += (float) $v->amount;
                } else {
                    $preDebit += (float) $v->amount;
                }
            }

            $openingBalAmount += ($preCredit - $preDebit);
        }

        // Sort and Calculate Balance
        $transactions = $transactions->sort(function ($a, $b) {
            $dateA = \Carbon\Carbon::parse($a->date)->format('Y-m-d');
            $dateB = \Carbon\Carbon::parse($b->date)->format('Y-m-d');

            if ($dateA != $dateB) {
                return $dateA <=> $dateB;
            }

            return ($a->created_at ?? 0) <=> ($b->created_at ?? 0);
        })->values();

        $balance = $openingBalAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->credit - $tx->debit);
            $tx->running_balance = $balance;
        }
        
        $party->balance = $balance;

        $viewMode = 'mix';

        return compact('party', 'transactions', 'type', 'startDate', 'endDate', 'openingBalAmount', 'viewMode');
    }
}
