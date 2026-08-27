<?php

namespace App\Services\Admin\Report;

use App\Models\ProductionSlipDigitization;
use App\Models\MasterProductStage;
use App\Models\StageMasterUnit;
use App\Models\OrderLot;
use App\Models\FabricRollAssigning;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\OrderGodamStageTransaction;
use App\Models\PackingMain;
use App\Models\GeneralSettings;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SlipReportService
{
    /**
     * Get paginated slip-wise report with metrics and filters
     */
    public function getSlipWiseReport(Request $request)
    {
        $query = ProductionSlipDigitization::with([
            'fromStage',
            'toStage',
            'getUnitMaster',
            'orderLots.orderProductSet.orderMain.customer',
            'orderStageTransaction.to_stage',
            'orderStageTransaction.getToUnitMaster',
            'orderStageTransaction.orderProduct.orderProductSet.orderMain.customer',
            'orderPrintingStageTransaction.to_stage',
            'orderPrintingStageTransaction.getToUnitMaster',
            'orderPrintingStageTransaction.orderProduct.orderProductSet.orderMain.customer',
            'orderPrintingToStichingTransaction.to_stage',
            'orderPrintingToStichingTransaction.getToUnitMaster',
            'orderPrintingToStichingTransaction.orderProduct.orderProductSet.orderMain.customer',
            'orderGodamStageTransaction.to_stage',
            'orderGodamStageTransaction.getToUnitMaster',
            'packingMain.cartons.items',
            'fabricRollAssignings.fabricRollAssigningsDetail'
        ])
        ->where('status', '!=', 3)
        ->orderBy('id', 'desc');

        // Filter: Date Range / Single Date
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date . ' 00:00:00',
                $request->end_date . ' 23:59:59'
            ]);
        } elseif ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        } elseif ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        } elseif ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Filter: From Stage
        if ($request->filled('from_stage_id')) {
            $query->where('from_stage_id', $request->from_stage_id);
        }

        // Filter: Uploaded By Unit
        if ($request->filled('stage_master_unit_id')) {
            $query->where('stage_master_unit_id', $request->stage_master_unit_id);
        }

        // Filter: Status
        if ($request->has('status') && $request->status !== '' && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Filter: Bill Number / Slip Number
        if ($request->filled('bill_number')) {
            $query->where(function($q) use ($request) {
                $q->where('bill_number', 'like', '%' . $request->bill_number . '%')
                  ->orWhere('id', $request->bill_number);
            });
        }

        // Filter: Lot Number
        if ($request->filled('lot_no')) {
            $lot_no = $request->lot_no;
            $query->where(function ($q) use ($lot_no) {
                $q->where('lot_no', 'like', '%' . $lot_no . '%')
                    ->orWhereHas('orderLots', function ($sq) use ($lot_no) {
                        $sq->where('lot_no', 'like', '%' . $lot_no . '%');
                    })
                    ->orWhereHas('orderPrintingStageTransaction', function ($pq) use ($lot_no) {
                        $pq->where('lot_no', 'like', '%' . $lot_no . '%');
                    })
                    ->orWhereHas('orderStageTransaction', function ($tq) use ($lot_no) {
                        $tq->where('lot_no', 'like', '%' . $lot_no . '%');
                    })
                    ->orWhereHas('orderPrintingToStichingTransaction', function ($stq) use ($lot_no) {
                        $stq->where('lot_no', 'like', '%' . $lot_no . '%');
                    })
                    ->orWhereHas('orderGodamStageTransaction', function ($gtq) use ($lot_no) {
                        $gtq->where('lot_no', 'like', '%' . $lot_no . '%');
                    });
            });
        }

        // Filter: To Stage
        if ($request->filled('to_stage_id')) {
            $to_stage_id = $request->to_stage_id;
            $query->where(function ($q) use ($to_stage_id) {
                $q->where('to_stage_id', $to_stage_id)
                    ->orWhereHas('orderStageTransaction', function ($sq) use ($to_stage_id) {
                        $sq->where('to_stage_id', $to_stage_id);
                    })
                    ->orWhereHas('orderPrintingStageTransaction', function ($pq) use ($to_stage_id) {
                        $pq->where('to_stage_id', $to_stage_id);
                    })
                    ->orWhereHas('orderPrintingToStichingTransaction', function ($tq) use ($to_stage_id) {
                        $tq->where('to_stage_id', $to_stage_id);
                    })
                    ->orWhereHas('orderGodamStageTransaction', function ($gq) use ($to_stage_id) {
                        $gq->where('to_stage_id', $to_stage_id);
                    });
            });
        }

        // Calculate KPI Metrics before pagination
        $kpiQuery = clone $query;
        $allMatchingSlips = $kpiQuery->get();

        $totalSlips = $allMatchingSlips->count();
        $digitizedSlips = $allMatchingSlips->where('status', 1)->count();
        $pendingSlips = $allMatchingSlips->where('status', 0)->count();
        $skippedSlips = $allMatchingSlips->where('status', 2)->count();

        $allUniqueLots = collect();
        $totalProcessedPieces = 0;

        foreach ($allMatchingSlips as $s) {
            $sData = $this->aggregateSlipEntriesSummary($s);
            $totalProcessedPieces += $sData['total_quantity'];
            $allUniqueLots = $allUniqueLots->merge($sData['lots']);
        }

        $kpis = [
            'total_slips' => $totalSlips,
            'digitized_slips' => $digitizedSlips,
            'pending_slips' => $pendingSlips,
            'skipped_slips' => $skippedSlips,
            'total_pieces' => $totalProcessedPieces,
            'total_unique_lots' => $allUniqueLots->unique()->filter()->count(),
        ];

        // Paginate results
        $perPage = $request->get('per_page', 20);
        $slips = $query->paginate($perPage)->withQueryString();

        // Attach computed summaries to each paginated slip
        $slips->getCollection()->transform(function ($slip) {
            $slip->computed_data = $this->aggregateSlipEntriesSummary($slip);
            return $slip;
        });

        $stages = MasterProductStage::where('status', 1)->get();
        $units = StageMasterUnit::where('status', 1)->get();

        return compact('slips', 'kpis', 'stages', 'units');
    }

    /**
     * Aggregates entry counts, distinct lots, and quantities for a single slip
     */
    public function aggregateSlipEntriesSummary(ProductionSlipDigitization $slip)
    {
        $lotsWithQty = [];
        $totalQty = 0;
        $entriesCount = 0;
        $destinationStages = collect();
        $destinationUnits = collect();
        $customers = collect();

        // 1. Lots from Cutting
        if ($slip->orderLots && $slip->orderLots->isNotEmpty()) {
            foreach ($slip->orderLots as $lot) {
                $entriesCount++;
                $lotNo = $lot->lot_no;
                if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                
                if ($lot->orderProductSet && $lot->orderProductSet->orderMain && $lot->orderProductSet->orderMain->customer) {
                    $customers->push($lot->orderProductSet->orderMain->customer->name);
                }
            }
        }

        // Rolls quantity (for cutting slips)
        if ($slip->fabricRollAssignings && $slip->fabricRollAssignings->isNotEmpty()) {
            foreach ($slip->fabricRollAssignings as $roll) {
                $lotNo = $roll->lot_no;
                $rollQty = $roll->fabricRollAssigningsDetail ? $roll->fabricRollAssigningsDetail->sum('quantity') : 0;
                if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                $lotsWithQty[$lotNo] += $rollQty;
                $totalQty += $rollQty;
            }
        }

        // 2. Printing Transactions
        if ($slip->orderPrintingStageTransaction && $slip->orderPrintingStageTransaction->isNotEmpty()) {
            foreach ($slip->orderPrintingStageTransaction as $pt) {
                $entriesCount++;
                $lotNo = $pt->lot_no;
                if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                $lotsWithQty[$lotNo] += $pt->quantity;
                $totalQty += $pt->quantity;

                if ($pt->to_stage) $destinationStages->push($pt->to_stage->name);
                if ($pt->getToUnitMaster) $destinationUnits->push($pt->getToUnitMaster->name);
                if ($pt->orderProduct && $pt->orderProduct->orderProductSet && $pt->orderProduct->orderProductSet->orderMain && $pt->orderProduct->orderProductSet->orderMain->customer) {
                    $customers->push($pt->orderProduct->orderProductSet->orderMain->customer->name);
                }
            }
        }

        // 3. Stage Transactions (Stitching, Washing, Finishing, etc.)
        if ($slip->orderStageTransaction && $slip->orderStageTransaction->isNotEmpty()) {
            foreach ($slip->orderStageTransaction as $st) {
                $entriesCount++;
                $lotNo = $st->lot_no;
                if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                $lotsWithQty[$lotNo] += $st->quantity;
                $totalQty += $st->quantity;

                if ($st->to_stage) $destinationStages->push($st->to_stage->name);
                if ($st->getToUnitMaster) $destinationUnits->push($st->getToUnitMaster->name);
                if ($st->orderProduct && $st->orderProduct->orderProductSet && $st->orderProduct->orderProductSet->orderMain && $st->orderProduct->orderProductSet->orderMain->customer) {
                    $customers->push($st->orderProduct->orderProductSet->orderMain->customer->name);
                }
            }
        }

        // 4. Printing To Stitching Transactions
        if ($slip->orderPrintingToStichingTransaction && $slip->orderPrintingToStichingTransaction->isNotEmpty()) {
            foreach ($slip->orderPrintingToStichingTransaction as $pst) {
                $entriesCount++;
                $lotNo = $pst->lot_no;
                if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                $lotsWithQty[$lotNo] += $pst->quantity;
                $totalQty += $pst->quantity;

                if ($pst->to_stage) $destinationStages->push($pst->to_stage->name);
                if ($pst->getToUnitMaster) $destinationUnits->push($pst->getToUnitMaster->name);
                if ($pst->orderProduct && $pst->orderProduct->orderProductSet && $pst->orderProduct->orderProductSet->orderMain && $pst->orderProduct->orderProductSet->orderMain->customer) {
                    $customers->push($pst->orderProduct->orderProductSet->orderMain->customer->name);
                }
            }
        }

        // 5. Godam Stage Transactions
        if ($slip->orderGodamStageTransaction && $slip->orderGodamStageTransaction->isNotEmpty()) {
            foreach ($slip->orderGodamStageTransaction as $gt) {
                $entriesCount++;
                $lotNo = $gt->lot_no;
                if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                $lotsWithQty[$lotNo] += $gt->quantity;
                $totalQty += $gt->quantity;

                if ($gt->to_stage) $destinationStages->push($gt->to_stage->name);
                if ($gt->getToUnitMaster) $destinationUnits->push($gt->getToUnitMaster->name);
                if ($gt->orderProduct && $gt->orderProduct->orderProductSet && $gt->orderProduct->orderProductSet->orderMain && $gt->orderProduct->orderProductSet->orderMain->customer) {
                    $customers->push($gt->orderProduct->orderProductSet->orderMain->customer->name);
                }
            }
        }

        // 6. Packing Main & Items
        if ($slip->packingMain) {
            $pm = $slip->packingMain;
            $entriesCount++;
            if ($pm->cartons) {
                foreach ($pm->cartons as $c) {
                    if ($c->items) {
                        foreach ($c->items as $it) {
                            $lotNo = $it->lot_no ?? 'Packing';
                            if (!isset($lotsWithQty[$lotNo])) $lotsWithQty[$lotNo] = 0;
                            $lotsWithQty[$lotNo] += $it->quantity;
                            $totalQty += $it->quantity;
                        }
                    }
                }
            }
        }

        // Fallback for single lot slip if transactions not loaded
        if (empty($lotsWithQty) && $slip->lot_no) {
            $lotsWithQty[$slip->lot_no] = 0;
        }

        if ($slip->toStage) {
            $destinationStages->push($slip->toStage->name);
        }

        return [
            'entries_count' => max(1, $entriesCount),
            'lots' => array_keys($lotsWithQty),
            'lots_with_qty' => $lotsWithQty,
            'total_quantity' => $totalQty,
            'destinations' => $destinationStages->unique()->filter()->values()->toArray(),
            'destination_units' => $destinationUnits->unique()->filter()->values()->toArray(),
            'customers' => $customers->unique()->filter()->values()->toArray()
        ];
    }

    /**
     * Get deep multi-entry detailed report for a specific slip
     */
    public function getSlipDetailedReport($slipId)
    {
        $general_setting = GeneralSettings::where('status', 1)->first();
        
        $slip = ProductionSlipDigitization::with([
            'fromStage',
            'toStage',
            'getUnitMaster.masterFabricWarehouse',
            'orderProductSet.orderMain.customer',
            'orderProductSet.fabric',
            'orderProductSet.colors',
            'orderProductSet.master_design_pattern',
            'orderProductSet.master_product_fitting'
        ])->findOrFail($slipId);

        // Fetch Cutting / Rolls Sessions
        $lots = OrderLot::where('production_slip_digitization_id', $slip->id)
            ->with([
                'orderMain.customer',
                'orderProductSet.fabric',
                'orderProductSet.colors',
                'orderProductSet.size_measurement',
                'orderProductSet.master_design_pattern',
                'orderProductSet.master_product_fitting',
            ])->get();

        $rolls = collect();
        if ($lots->isNotEmpty()) {
            $rolls = FabricRollAssigning::where('production_slip_digitization_id', $slip->id)
                ->with(['fabricRollAssigningsDetail', 'stageMasterUnit.masterFabricWarehouse'])
                ->get();
        }

        // Fetch Printing Transactions
        $printings = OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.size_measurement',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        // Fetch Intermediate Stage Transactions
        $stage_tx = OrderStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.size_measurement',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        $printing_to_stitching_tx = OrderPrintingToStichingTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.size_measurement',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        $godam_tx = OrderGodamStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.size_measurement',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'godamDetails'
            ])->get();

        $godam_tx->each(function ($tx) {
            $tx->details = $tx->godamDetails;
        });

        $stage_transactions = $stage_tx->concat($printing_to_stitching_tx)->concat($godam_tx);

        // Fetch Packing Sessions
        $packing_details = collect();
        if ($slip->from_stage_id == 11) {
            $packing_details = PackingMain::where('slip_id', $slip->id)
                ->with(['order.customer', 'cartons.items.detail.orderProductSet.size_measurement'])
                ->get();
        }

        // Summary Calculations
        $totalInputQty = 0;
        $totalRemainingQty = 0;
        $distinctLots = collect();

        foreach ($rolls as $r) {
            if ($r->fabricRollAssigningsDetail) {
                $totalInputQty += $r->fabricRollAssigningsDetail->sum('quantity');
            }
            if ($r->lot_no) $distinctLots->push($r->lot_no);
        }

        foreach ($lots as $l) {
            if ($l->lot_no) $distinctLots->push($l->lot_no);
        }

        foreach ($printings as $p) {
            $totalInputQty += $p->quantity;
            $totalRemainingQty += $p->remaining_quantity;
            if ($p->lot_no) $distinctLots->push($p->lot_no);
        }

        foreach ($stage_transactions as $st) {
            $totalInputQty += $st->quantity;
            $totalRemainingQty += $st->remaining_quantity;
            if ($st->lot_no) $distinctLots->push($st->lot_no);
        }

        if ($packing_details->isNotEmpty()) {
            foreach ($packing_details as $pm) {
                foreach ($pm->cartons as $c) {
                    foreach ($c->items as $it) {
                        $totalInputQty += $it->quantity;
                        if ($it->lot_no) $distinctLots->push($it->lot_no);
                    }
                }
            }
        }

        $totalMovedOutflow = max(0, $totalInputQty - $totalRemainingQty);

        $summary = [
            'total_lots' => $distinctLots->unique()->filter()->count(),
            'total_sessions' => max(1, $lots->count() + $printings->count() + $stage_transactions->count() + $packing_details->count()),
            'total_pieces' => $totalInputQty,
            'total_moved_outflow' => $totalMovedOutflow,
            'total_remaining_balance' => $totalRemainingQty,
        ];

        return compact('slip', 'lots', 'rolls', 'printings', 'stage_transactions', 'packing_details', 'general_setting', 'summary');
    }

    /**
     * Generate Excel export of the slip-wise report
     */
    public function exportSlipWiseExcel(Request $request)
    {
        $reportData = $this->getSlipWiseReport($request);
        $slips = $reportData['slips'];

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Slip-wise Report');

        // Header Styling
        $sheet->setCellValue('A1', 'KESHAV MADHAV - SLIP-WISE PRODUCTION REPORT');
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Generated Date: ' . date('d M, Y H:i:s'));
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Table Columns
        $headers = [
            'A4' => 'Slip ID',
            'B4' => 'Bill No',
            'C4' => 'Date',
            'D4' => 'From Stage & Unit',
            'E4' => 'To Stage & Destination Unit',
            'F4' => 'Lots Involved (Pieces)',
            'G4' => 'Total Entries',
            'H4' => 'Total Pieces',
            'I4' => 'Status'
        ];

        foreach ($headers as $cell => $text) {
            $sheet->setCellValue($cell, $text);
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
        ];
        $sheet->getStyle('A4:I4')->applyFromArray($headerStyle);
        $sheet->getRowDimension('4')->setRowHeight(28);

        $row = 5;
        foreach ($slips as $slip) {
            $computed = $slip->computed_data;
            
            $fromStageText = ($slip->fromStage->name ?? '-') . ($slip->getUnitMaster ? ' (' . $slip->getUnitMaster->name . ')' : '');
            $toStageText = implode(', ', $computed['destinations']) ?: ($slip->toStage->name ?? '-');
            if (!empty($computed['destination_units'])) {
                $toStageText .= ' [' . implode(', ', $computed['destination_units']) . ']';
            }

            $lotsFormatted = [];
            foreach ($computed['lots_with_qty'] as $lot => $qty) {
                $lotsFormatted[] = '#' . $lot . ($qty > 0 ? " ({$qty})" : '');
            }
            $lotText = implode(', ', $lotsFormatted) ?: '-';

            $statusText = match($slip->status) {
                0 => 'Pending',
                1 => 'Digitized',
                2 => 'Skipped',
                default => 'Unknown'
            };

            $sheet->setCellValue('A' . $row, '#' . $slip->id);
            $sheet->setCellValue('B' . $row, $slip->bill_number ?? '-');
            $sheet->setCellValue('C' . $row, $slip->created_at->format('d M, Y'));
            $sheet->setCellValue('D' . $row, $fromStageText);
            $sheet->setCellValue('E' . $row, $toStageText);
            $sheet->setCellValue('F' . $row, $lotText);
            $sheet->setCellValue('G' . $row, $computed['entries_count']);
            $sheet->setCellValue('H' . $row, $computed['total_quantity']);
            $sheet->setCellValue('I' . $row, $statusText);

            $sheet->getStyle('A' . $row . ':I' . $row)->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('C' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('G' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('H' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle('I' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        foreach (range('A', 'I') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        $filename = 'Slip_Wise_Report_' . date('Y_m_d_His') . '.xlsx';
        $tempPath = storage_path('app/' . $filename);
        $writer->save($tempPath);

        return response()->download($tempPath, $filename)->deleteFileAfterSend(true);
    }
}
