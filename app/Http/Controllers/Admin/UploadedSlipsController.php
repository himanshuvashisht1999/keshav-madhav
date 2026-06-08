<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionSlipDigitization;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MasterProductStage;
use App\Models\FabricRollAssigningsDetail;
use App\Models\StageMasterUnit;
use App\Models\OrderLot;
use App\Models\FabricRollAssigning;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingStageTransactionDetail;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\GeneralSettings;
use App\Models\OrderPrintingToStichingTransactionDetail;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Storage;
use App\Models\OrderProductSetDetail;
use App\Models\FabricReceiptDetail;
use App\Models\OrderStageTransactionDetail;
use App\Services\Admin\PackingService;

class UploadedSlipsController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionSlipDigitization::with([
            'fromStage',
            'toStage',
            'getUnitMaster',
            'orderLots',
            'orderStageTransaction.to_stage',
            'orderStageTransaction.getToUnitMaster',
            'orderPrintingStageTransaction.to_stage',
            'orderPrintingStageTransaction.getToUnitMaster',
            'orderPrintingToStichingTransaction.to_stage',
            'orderPrintingToStichingTransaction.getToUnitMaster',
            'packingMain',
            'fabricRollAssignings'
        ])
            ->orderBy('id', 'desc')
            ->where('status', '!=', 3);

        if ($request->filled('from_stage_id')) {
            $query->where('from_stage_id', $request->from_stage_id);
        }

        if ($request->has('status') && $request->status !== '') {
            if ($request->status !== 'all') {
                $query->where('status', $request->status);
            }
        }
        // Removed the 'else' block that defaulted to status 0 (Pending) to show all slips by default.

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('stage_master_unit_id')) {
            $query->where('stage_master_unit_id', $request->stage_master_unit_id);
        }

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
                    });
            });
        }

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
                    });
            });
        }

        $slips = $query->paginate(20);
        $stages = MasterProductStage::where('status', 1)->get();
        $units = StageMasterUnit::where('status', 1)->get();

        return view('admin.uploaded_slips.index', compact('slips', 'stages', 'units'));
    }

    public function show($slipId)
    {
        $data = $this->getSlipData($slipId);
        return view('admin.uploaded_slips.show', $data);
    }
    public function download($slipId)
    {
        $data = $this->getSlipData($slipId);
        $pdf = Pdf::loadView('admin.uploaded_slips.pdf', $data)
            ->setPaper('A4', 'portrait');

        return $pdf->download('Production_Slip_' . $slipId . '.pdf');
    }

    public function getSlipData($slipId)
    {
        $general_setting = GeneralSettings::where('status', 1)->first();
        $slip = ProductionSlipDigitization::with([
            'fromStage',
            'getUnitMaster.masterFabricWarehouse',
            'orderProductSet.orderMain.customer',
            'orderProductSet.fabric',
            'orderProductSet.colors',
            'orderProductSet.master_design_pattern',
            'orderProductSet.master_product_fitting'
        ])->findOrFail($slipId);

        $data = [
            'slip' => $slip,
            'lots' => collect(),
            'rolls' => collect(),
            'printings' => collect(),
            'stage_transactions' => collect(),
            'general_setting' => $general_setting,
            'packing_details' => collect()
        ];

        /* =====================================================
         * 🟢 TYPE 1 → LOT / ROLLS ALLOT
         * ===================================================== */
        $data['lots'] = OrderLot::where('production_slip_digitization_id', $slip->id)
            ->with([
                'orderMain',
                'orderProductSet.fabric',
                'orderProductSet.colors',
                'orderProductSet.master_design_pattern',
                'orderProductSet.master_product_fitting',
            ])->get();

        if ($data['lots']->isNotEmpty()) {
            $data['rolls'] = FabricRollAssigning::where('production_slip_digitization_id', $slip->id)
                ->with(['fabricRollAssigningsDetail', 'stageMasterUnit.masterFabricWarehouse'])
                ->get();
        }

        /* =====================================================
         * 🔵 TYPE 2 → PRINTING
         * ===================================================== */
        $data['printings'] = OrderPrintingStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        /* =====================================================
         * 🟠 TYPE 3 → OTHER (STITCHING / HAND SLIP / TRANSFERS / GODAM)
         * ===================================================== */
        $stage_tx = OrderStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
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
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'details'
            ])->get();

        $godam_tx = \App\Models\OrderGodamStageTransaction::where('production_slip_digitization_id', $slip->id)
            ->with([
                'from_stage',
                'to_stage',
                'getToUnitMaster',
                'getFromUnitMaster',
                'orderProduct.orderProductSet.fabric',
                'orderProduct.orderProductSet.colors',
                'orderProduct.orderProductSet.master_design_pattern',
                'orderProduct.orderProductSet.master_product_fitting',
                'orderProduct.orderProductSet.orderMain.customer',
                'godamDetails'
            ])->get();

        // Normalize godamDetails to details for the view
        $godam_tx->each(function($tx) {
            $tx->details = $tx->godamDetails;
        });

        $data['stage_transactions'] = $stage_tx->concat($printing_to_stitching_tx)->concat($godam_tx);

        // Fetch packing details and outflows if applicable
        if ($slip->from_stage_id == 11) {
            $data['packing_details'] = \App\Models\PackingMain::where('slip_id', $slip->id)
                ->with(['order', 'cartons.boxes.items.detail', 'cartons.items.detail'])
                ->get();

            $data['outflows'] = \App\Models\ProductionOutflowInventory::where('slip_id', $slip->id)
                ->with(['product', 'color', 'size', 'rack.storeroom', 'responsibleStage', 'responsibleUnit'])
                ->get();

            $data['reworks'] = \App\Models\OrderStageTransaction::where('production_slip_digitization_id', $slip->id)
                ->where('type', 'rework')
                ->with(['toStage', 'toUnit', 'details'])
                ->get();
        } else {
            $data['packing_details'] = \App\Models\PackingMain::where('slip_id', $slip->id)
                ->with(['order', 'cartons.boxes.items.detail', 'cartons.items.detail'])
                ->get();
            $data['outflows'] = collect();
            $data['reworks'] = collect();
        }

        // Consolidate ALL Order Details from all digitized sessions
        $consolidated = [
            'lot_nos' => collect(),
            'order_nos' => collect(),
            'design_nos' => collect(),
            'fabrics' => collect(),
            'colors' => collect(),
            'patterns' => collect(),
            'customers' => collect(),
            'fittings' => collect(),
            'production_dates' => collect()
        ];

        // 1. From Lots
        foreach ($data['lots'] as $lot) {
            $consolidated['lot_nos']->push($lot->lot_no);
            if ($lot->production_datetime) $consolidated['production_dates']->push($lot->production_datetime);
            if ($lot->orderMain) $consolidated['order_nos']->push($lot->orderMain->sku);
            if ($lot->orderProductSet) {
                $ops = $lot->orderProductSet;
                if ($ops->design_number) $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric_names) {
                    foreach (explode(',', $ops->fabric_names) as $n) $consolidated['fabrics']->push(trim($n));
                }
                if ($ops->colors) $consolidated['colors']->push($ops->colors->name);
                if ($ops->master_design_pattern) $consolidated['patterns']->push($ops->master_design_pattern->name);
                if ($ops->master_product_fitting) $consolidated['fittings']->push($ops->master_product_fitting->name);
                if ($ops->orderMain?->customer) $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        // 2. From Printings
        foreach ($data['printings'] as $printing) {
            $consolidated['lot_nos']->push($printing->lot_no);
            if ($printing->production_datetime) $consolidated['production_dates']->push($printing->production_datetime);
            if ($printing->orderProduct?->orderProductSet) {
                $ops = $printing->orderProduct->orderProductSet;
                if ($ops->orderMain) $consolidated['order_nos']->push($ops->orderMain->sku);
                if ($ops->design_number) $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric_names) {
                    foreach (explode(',', $ops->fabric_names) as $n) $consolidated['fabrics']->push(trim($n));
                }
                if ($ops->colors) $consolidated['colors']->push($ops->colors->name);
                if ($ops->master_design_pattern) $consolidated['patterns']->push($ops->master_design_pattern->name);
                if ($ops->master_product_fitting) $consolidated['fittings']->push($ops->master_product_fitting->name);
                if ($ops->orderMain?->customer) $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        // 3. From Stage Transactions
        foreach ($data['stage_transactions'] as $tx) {
            $consolidated['lot_nos']->push($tx->lot_no);
            if ($tx->production_datetime) $consolidated['production_dates']->push($tx->production_datetime);
            if ($tx->orderProduct?->orderProductSet) {
                $ops = $tx->orderProduct->orderProductSet;
                if ($ops->orderMain) $consolidated['order_nos']->push($ops->orderMain->sku);
                if ($ops->design_number) $consolidated['design_nos']->push($ops->design_number);
                if ($ops->fabric_names) {
                    foreach (explode(',', $ops->fabric_names) as $n) $consolidated['fabrics']->push(trim($n));
                }
                if ($ops->colors) $consolidated['colors']->push($ops->colors->name);
                if ($ops->master_design_pattern) $consolidated['patterns']->push($ops->master_design_pattern->name);
                if ($ops->master_product_fitting) $consolidated['fittings']->push($ops->master_product_fitting->name);
                if ($ops->orderMain?->customer) $consolidated['customers']->push($ops->orderMain->customer->name);
            }
        }

        $data['summary'] = [
            'lot_no' => $consolidated['lot_nos']->unique()->implode(', '),
            'order_no' => $consolidated['order_nos']->unique()->implode(', '),
            'design' => $consolidated['design_nos']->unique()->implode(', '),
            'fabric' => $consolidated['fabrics']->unique()->implode(', '),
            'color' => $consolidated['colors']->unique()->implode(', '),
            'pattern' => $consolidated['patterns']->unique()->implode(', '),
            'fitting' => $consolidated['fittings']->unique()->implode(', '),
            'customer' => $consolidated['customers']->unique()->implode(', '),
            'production_date' => $consolidated['production_dates']->unique()->map(fn($d) => getformatDateTime($d))->implode(' | ')
        ];

        $orderSet = $slip->orderProductSet;
        if (!$orderSet && $data['lots']->isNotEmpty()) $orderSet = $data['lots']->first()->orderProductSet;
        if (!$orderSet && $data['printings']->isNotEmpty()) $orderSet = $data['printings']->first()->orderProduct?->orderProductSet;
        if (!$orderSet && $data['stage_transactions']->isNotEmpty()) $orderSet = $data['stage_transactions']->first()->orderProduct?->orderProductSet;
        $data['orderProductSet'] = $orderSet;

        $all_sizes_collector = collect();
        foreach ($data['rolls'] as $roll) { foreach ($roll->fabricRollAssigningsDetail as $det) $all_sizes_collector->push($det->size); }
        foreach ($data['printings'] as $pr) { foreach ($pr->details as $det) $all_sizes_collector->push($det->size); }
        foreach ($data['stage_transactions'] as $st) { foreach ($st->details as $det) $all_sizes_collector->push($det->size); }
        $data['all_sizes'] = $all_sizes_collector->unique()->filter()->values();

        $sizes = [];
        $pcs_in_set = '-';
        if ($orderSet && $orderSet->size_measurement) {
            $sizes = explode(',', $orderSet->size_measurement->size_group);
            $pcs_in_set = count($sizes);
        }
        $data['pcs_in_set'] = $pcs_in_set;
        $data['size_set'] = count($sizes) > 0 ? min($sizes) . '-' . max($sizes) : '-';

        return $data;
    }




    public function finalize($id)
    {
        $slip = ProductionSlipDigitization::findOrFail($id);
        $slip->status = 1;
        $slip->save();

        return redirect()->back()->with('success', 'Slip marked as Finalized.');
    }

    public function destroy($id)
    {
        $slip = ProductionSlipDigitization::findOrFail($id);

        // Check for any associated sessions/transactions
        $hasSessions = $slip->orderLots()->exists() ||
            $slip->orderStageTransaction()->exists() ||
            $slip->orderPrintingStageTransaction()->exists() ||
            $slip->orderPrintingToStichingTransaction()->exists() ||
            $slip->fabricRollAssignings()->exists() ||
            $slip->packingMain()->exists();

        if ($hasSessions) {
            return redirect()->back()->with('error', 'Cannot delete slip. It has associated sessions or transactions. Please delete the sessions first.');
        }

        $slip->delete();
        return redirect()->back()->with('success', 'Slip deleted successfully.');
    }

    public function downloadOutflowReceipt($id)
    {
        $outflow = \App\Models\ProductionOutflowInventory::with([
            'product',
            'color',
            'size',
            'responsibleStage',
            'responsibleUnit'
        ])->findOrFail($id);

        $general_setting = \App\Models\GeneralSettings::first();

        $pdf = Pdf::loadView('admin.uploaded_slips.outflow_receipt', [
            'outflow' => $outflow,
            'general_setting' => $general_setting
        ]);

        return $pdf->stream('Receipt-TXN-' . $id . '.pdf');
    }

    public function corporateExcel(Request $request, $id)
    {
        $packing = \App\Models\PackingMain::with(['cartons.items.detail', 'cartons.boxes', 'order'])->findOrFail($id);
        $slip = \App\Models\ProductionSlipDigitization::with('orderProductSet.colors')->find($packing->slip_id);
        $orderProductSet = $slip ? $slip->orderProductSet : null;

        $weights = $request->input('weights', []);
        $po_no = $request->input('po_no');
        $v_cd = $request->input('v_cd');
        $v_nm = $request->input('v_nm');
        $cont_no = $request->input('cont_no');
        $bora_desc = $request->input('bora_desc');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // 1. Headers (Rows 1-2 based on density)
        $headers = [
            'A1' => 'PO NO.',
            'B1' => 'V CD',
            'C1' => 'V NM',
            'D1' => 'CONT NO.',
            'E1' => 'BORA/HU NO.',
            'F1' => 'BORA DESC',
            'G1' => 'BORA/HU WT-V2',
            'H1' => 'COLOUR',
            'I1' => 'SIZE',
            'J1' => 'MC BORA QTY',
            'K1' => 'MC BORA VAL',
            'L1' => 'MC PO QTY',
            'M1' => 'MC PO VAL',
            'N1' => 'VAR ART NO.',
            'O1' => 'VAR ART DESC',
            'P1' => 'VEND DZN NO.',
            'Q1' => 'RATE',
            'R1' => 'MRP',
            'S1' => 'ART BORA QTY',
            'T1' => 'ART BORA VAL',
            'U1' => 'ART PO QTY',
            'V1' => 'ART PO VAL'
        ];
        foreach ($headers as $cell => $val) {
            $sheet->setCellValue($cell, $val);
            $sheet->getStyle($cell)->getFont()->setBold(true);
            $sheet->getStyle($cell)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
        }

        // 2. Prepare Data (Carton-Centric: One Row Per Carton)
        $carton_rows = [];
        foreach ($packing->cartons as $carton) {
            $weight = $weights[$carton->id] ?? 0;
            $items = $carton->items;

            // Collect unique sizes for this carton
            $sizes_in_carton = $items->map(function ($i) {
                return $i->detail ? $i->detail->size : ($i->size ? $i->size->name : 'N/A');
            })->unique()->implode(', ');

            $total_qty = $items->sum('quantity');
            $total_val = 0;
            $unit_price = 0;
            $unit_mrp = 0;

            foreach ($items as $i) {
                $p = $i->selling_price ?? ($orderProductSet->unit_price ?? 0);
                $m = $i->mrp ?? ($orderProductSet->mrp ?? 0);
                $total_val += ($i->quantity * $p);
                $unit_price = $p; // Baseline for the row
                $unit_mrp = $m;
            }

            $carton_rows[] = [
                'carton_no' => $carton->carton_no,
                'weight' => $weight,
                'sizes' => $sizes_in_carton,
                'qty' => $total_qty,
                'val' => $total_val,
                'price' => $unit_price,
                'mrp' => $unit_mrp,
            ];
        }

        $row = 2;
        $size_groups = [];
        $currentRow = 2;

        foreach ($carton_rows as $data) {
            $art_no = $orderProductSet->design_number ?? 'N/A';
            $color = $orderProductSet->colors?->name ?? 'N/A';

            $sheet->setCellValue('A' . $currentRow, $po_no);
            $sheet->setCellValue('B' . $currentRow, $v_cd);
            $sheet->setCellValue('C' . $currentRow, $v_nm);
            $sheet->setCellValue('D' . $currentRow, $cont_no);
            $sheet->setCellValue('E' . $currentRow, $data['carton_no']);
            $sheet->setCellValue('F' . $currentRow, $bora_desc);
            $sheet->setCellValue('G' . $currentRow, $data['weight']);
            $sheet->setCellValue('H' . $currentRow, $color);
            $sheet->setCellValue('I' . $currentRow, $data['sizes']);
            $sheet->getStyle('I' . $currentRow)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setRGB('F4C7C3');

            $sheet->setCellValue('J' . $currentRow, $data['qty']);
            $sheet->setCellValue('K' . $currentRow, $data['val']);
            $sheet->setCellValue('N' . $currentRow, $art_no);
            $sheet->setCellValue('O' . $currentRow, $bora_desc);
            $sheet->setCellValue('Q' . $currentRow, $data['price']);
            $sheet->setCellValue('R' . $currentRow, $data['mrp']);
            $sheet->setCellValue('S' . $currentRow, $data['qty']);
            $sheet->setCellValue('T' . $currentRow, $data['val']);

            // Grouping for merging by the specific size combination
            $groupKey = $data['sizes'];
            if (!isset($size_groups[$groupKey])) {
                $size_groups[$groupKey] = ['start' => $currentRow, 'qty' => 0, 'val' => 0];
            }
            $size_groups[$groupKey]['end'] = $currentRow;
            $size_groups[$groupKey]['qty'] += $data['qty'];
            $size_groups[$groupKey]['val'] += $data['val'];

            $currentRow++;
        }

        // 3. Merging for PO QTY and PO VAL Columns
        foreach ($size_groups as $group) {
            if ($group['start'] != $group['end']) {
                $sheet->mergeCells("L{$group['start']}:L{$group['end']}");
                $sheet->mergeCells("M{$group['start']}:M{$group['end']}");
                $sheet->mergeCells("U{$group['start']}:U{$group['end']}");
                $sheet->mergeCells("V{$group['start']}:V{$group['end']}");
            }

            $sheet->setCellValue("L{$group['start']}", $group['qty']);
            $sheet->setCellValue("M{$group['start']}", $group['val']);
            $sheet->setCellValue("U{$group['start']}", $group['qty']);
            $sheet->setCellValue("V{$group['start']}", $group['val']);

            // Vertical center
            foreach (['L', 'M', 'U', 'V'] as $col) {
                $sheet->getStyle("{$col}{$group['start']}")->getAlignment()->setVertical(\PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER);
                $sheet->getStyle("{$col}{$group['start']}")->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);
            }
        }

        // 4. Grand Total Row
        $total_qty = array_sum(array_column($size_groups, 'qty'));
        $total_val = array_sum(array_column($size_groups, 'val'));

        $sheet->setCellValue('J' . $currentRow, $total_qty);
        $sheet->setCellValue('K' . $currentRow, $total_val);
        $sheet->setCellValue('L' . $currentRow, $total_qty);
        $sheet->setCellValue('M' . $currentRow, $total_val);
        $sheet->setCellValue('S' . $currentRow, $total_qty);
        $sheet->setCellValue('T' . $currentRow, $total_val);
        $sheet->setCellValue('U' . $currentRow, $total_qty);
        $sheet->setCellValue('V' . $currentRow, $total_val);
        $sheet->getStyle("A{$currentRow}:V{$currentRow}")->getFont()->setBold(true);

        // Styling: Auto-size and Borders
        foreach (range('A', 'V') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        $sheet->getStyle("A1:V{$currentRow}")->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);

        $fileName = 'Corporate-Packing-' . $id . '-' . date('His') . '.xlsx';
        $filePath = storage_path('app/public/' . $fileName);
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        return response()->download($filePath)->deleteFileAfterSend(true);
    }


    public function deleteSession($type, $id)
    {
        $result = deleteProductionSession($type, $id);

        if ($result['status'] === 'success') {
            return redirect()->back()->with('success', $result['message']);
        } else {
            return redirect()->back()->with('error', $result['message']);
        }
    }
}
