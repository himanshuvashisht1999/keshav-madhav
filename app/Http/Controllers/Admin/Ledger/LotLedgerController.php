<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\OrderLot;
use App\Models\FabricRollAssigning;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderGodamStageTransaction;
use App\Models\ProductionGoods;
use App\Models\DomesticInventoryHistory;
use App\Models\OrderPrintingToStichingTransaction;
use Carbon\Carbon;

class LotLedgerController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getLotListData($request, true);
        $lots = $data['lots'];
        return view('admin.ledger.lot.index', compact('lots'));
    }

    public function exportListPdf(Request $request)
    {
        $data = $this->getLotListData($request, false);
        $pdf = \PDF::loadView('admin.ledger.lot.list_pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Lot_Ledger_List_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportListExcel(Request $request)
    {
        $data = $this->getLotListData($request, false);
        $lots = $data['lots'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lot Ledger Summary');

        // Header Title
        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', 'SNAPKID - Lot Production Ledger Summary');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Date
        $sheet->mergeCells('A2:F2');
        $sheet->setCellValue('A2', 'Generated on: ' . date('d M Y, h:i A'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['Lot No', 'Order SKU', 'Customer', 'Fabric', 'Total Quantity', 'Current Stage'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:F4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $totalQty = 0;

        foreach ($lots as $lot) {
            $qty = (float)($lot->lot_quantity ?? 0);
            $totalQty += $qty;

            $sheet->setCellValue('A' . $row, $lot->lot_no ?? '-');
            $sheet->setCellValue('B' . $row, $lot->orderMain->sku ?? '-');
            $sheet->setCellValue('C' . $row, $lot->orderMain->customer->name ?? '-');
            $sheet->setCellValue('D' . $row, $lot->orderProductSet->fabric->name ?? '-');
            $sheet->setCellValue('E' . $row, $qty);
            $sheet->setCellValue('F' . $row, $lot->last_current_stage ?? 'N/A');

            $row++;
        }

        // Total Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->mergeCells('A' . $row . ':D' . $row);
        $sheet->setCellValue('E' . $row, $totalQty);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':F' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle('E5:E' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'F') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Lot_Ledger_List_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getLotListData(Request $request, $paginate = true)
    {
        $searchLot = $request->search;

        $query = OrderLot::with([
            'orderMain.customer',
            'orderProductSet.fabric',
            'orderProductSet.master_design_pattern',
            'orderProductSet.master_product_fitting',
            'orderProductSet.colors'
        ])
        ->when($searchLot, function ($q) use ($searchLot) {
            $q->where('lot_no', 'like', "%{$searchLot}%")
              ->orWhereHas('orderMain', function($q2) use ($searchLot) {
                  $q2->where('sku', 'like', "%{$searchLot}%");
              });
        })
        ->orderBy('id', 'desc');

        $lots = $paginate ? $query->paginate(20)->withQueryString() : $query->get();

        $lots->each(function ($lot) {
            $quantity = FabricRollAssigning::where('lot_no', $lot->lot_no)
                ->withSum('fabricRollAssigningsDetail as total', 'quantity')
                ->get()
                ->sum('total');
            $lot->lot_quantity = $quantity ?? 0;
            $lot->last_current_stage = getLastCurrentStage($lot->lot_no);
            return $lot;
        });

        return compact('lots', 'searchLot');
    }

    public function show(Request $request, $lot_no)
    {
        $data = $this->getLotLedgerData($request, $lot_no);
        return view('admin.ledger.lot.show', $data);
    }

    public function exportPdf(Request $request, $lot_no)
    {
        $data = $this->getLotLedgerData($request, $lot_no);
        $pdf = \PDF::loadView('admin.ledger.lot.pdf', $data)->setPaper('a4', 'portrait');
        return $pdf->download('Lot_Ledger_' . $lot_no . '_' . date('Y-m-d') . '.pdf');
    }

    public function exportExcel(Request $request, $lot_no)
    {
        $data = $this->getLotLedgerData($request, $lot_no);
        $lot = $data['lot'];
        $transactions = $data['transactions'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Lot Ledger');

        // Header Title
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'SNAPKID - Lot Ledger: ' . $lot->lot_no);
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Subtitle
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Order SKU: ' . ($lot->orderMain->sku ?? '-') . ' | Customer: ' . ($lot->orderMain->customer->name ?? '-') . ' | Stage: ' . ($lot->last_current_stage ?? 'N/A') . ' | Fabric: ' . ($lot->orderProductSet->fabric->name ?? '-'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['Date & Time', 'Type / Status', 'Particulars', 'Quantity (Pcs)', 'Balance (Pcs)'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:E4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        foreach ($transactions as $tx) {
            $qty = 0;
            if ($tx->type === 'Inward') {
                $qty = (float)($tx->inward ?? 0);
            } elseif ($tx->type === 'Outward') {
                $qty = (float)($tx->outward ?? 0);
            } else {
                $qty = (float)($tx->process_qty ?? 0);
            }

            $sheet->setCellValue('A' . $row, \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A'));
            $sheet->setCellValue('B' . $row, ucfirst($tx->status ?? $tx->type));
            $sheet->setCellValue('C' . $row, $tx->particulars ?? '-');
            $sheet->setCellValue('D' . $row, $qty);
            $sheet->setCellValue('E' . $row, (float)($tx->running_balance ?? 0));

            $row++;
        }

        $sheet->getStyle('D5:E' . $row)->getNumberFormat()->setFormatCode('#,##0');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Lot_Ledger_' . $lot_no . '_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getLotLedgerData(Request $request, $lot_no)
    {
        $lot = OrderLot::with([
            'orderMain.customer',
            'orderProductSet.fabric',
            'orderProductSet.master_design_pattern',
            'orderProductSet.master_product_fitting',
            'orderProductSet.colors'
        ])->where('lot_no', $lot_no)->firstOrFail();

        $lot->last_current_stage = getLastCurrentStage($lot_no);

        $transactions = collect();

        // 1. Initial Inward (Cutting / Assignment)
        $initialRolls = FabricRollAssigning::where('lot_no', $lot_no)
                ->withSum('fabricRollAssigningsDetail as total', 'quantity')
                ->get();
        $initialQty = $initialRolls->sum('total');
        
        $firstRollDate = $initialRolls->min('created_at') ?? $lot->created_at;

        if ($initialQty > 0) {
            $transactions->push((object)[
                'date' => Carbon::parse($firstRollDate),
                'type' => 'Inward',
                'particulars' => 'Initial Lot Assignment (Cutting)',
                'inward' => $initialQty,
                'outward' => 0,
            ]);
        }

        // 2. Fetch Process Transactions
        $stageTxs = OrderStageTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();
        $printTxs = OrderPrintingStageTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();
        $godamTxs = OrderGodamStageTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();
        $stitchTxs = OrderPrintingToStichingTransaction::with(['from_stage', 'to_stage', 'getToUnitMaster'])->where('lot_no', $lot_no)->get();

        $allTxs = $stageTxs->concat($printTxs)->concat($godamTxs)->concat($stitchTxs);

        foreach ($allTxs as $tx) {
            $qty = $tx->quantity;
            if ($qty > 0) {
                $fromObj = method_exists($tx, 'fromStage') ? $tx->fromStage : $tx->from_stage;
                $toObj = method_exists($tx, 'toStage') ? $tx->toStage : $tx->to_stage;
                
                $fromName = $fromObj ? $fromObj->name : 'N/A';
                $toName = $toObj ? $toObj->name : 'N/A';
                $unitName = $tx->getToUnitMaster ? $tx->getToUnitMaster->name : '';

                $status = 'processing';
                if ($toObj) {
                    $d = getLotDetails($lot_no, $toObj->id);
                    if ($d && isset($d['quantity'])) {
                        $remaining = (int) $d['remaining_quantity'];
                        $total = (int) $d['quantity'];
                        if ($total === 0) {
                            $status = 'pending';
                        } elseif ($remaining === 0) {
                            $status = 'completed';
                        } else {
                            $status = 'progress';
                        }
                    }
                }

                $transactions->push((object)[
                    'date' => Carbon::parse($tx->created_at),
                    'type' => 'Process',
                    'status' => $status,
                    'particulars' => "Moved from {$fromName} to {$toName}" . ($unitName ? " ({$unitName})" : ""),
                    'inward' => 0,
                    'outward' => 0,
                    'process_qty' => $qty,
                ]);
            }
        }

        // 3. Finished Goods / Packed
        $slipIds = $allTxs->pluck('production_slip_digitization_id')->filter()->unique();
        $packingMains = \App\Models\PackingMain::whereIn('slip_id', $slipIds)->get();
        
        foreach ($packingMains as $pm) {
            $packedQty = \App\Models\PackingItem::where('packing_main_id', $pm->id)->sum('quantity');
            if ($packedQty > 0) {
                $transactions->push((object)[
                    'date' => Carbon::parse($pm->packing_date ?? $pm->created_at),
                    'type' => 'Outward',
                    'particulars' => 'Packed / Sent to Finished Goods',
                    'inward' => 0,
                    'outward' => $packedQty,
                ]);
            }
        }

        // Sort by date
        $transactions = $transactions->sortBy(function($t) {
            return $t->date->timestamp;
        })->values();

        // Calculate running balance
        $balance = 0;
        foreach ($transactions as $tx) {
            if ($tx->type === 'Inward') {
                $balance += $tx->inward;
            } elseif ($tx->type === 'Outward') {
                $balance -= $tx->outward;
            }
            $tx->running_balance = $balance;
        }

        return compact('lot', 'transactions', 'initialQty');
    }
}
