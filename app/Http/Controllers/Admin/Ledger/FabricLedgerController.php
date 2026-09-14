<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Fabric;
use App\Models\FabricReceiptDetail;
use App\Models\FabricRollAssigning;
use App\Models\AgentOrderFabricItem;
use App\Models\FabricReturnDetail;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use DB;

class FabricLedgerController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getFabricListData($request, true);
        $fabrics = $data['fabrics'];
        return view('admin.ledger.fabric.index', compact('fabrics'));
    }

    public function exportListPdf(Request $request)
    {
        $data = $this->getFabricListData($request, false);
        $search = $request->query('search');
        $pdf = \PDF::loadView('admin.ledger.fabric.list_pdf', [
            'fabrics' => $data['fabrics'],
            'search' => $search
        ])->setPaper('a4', 'portrait');

        return $pdf->download('Fabric_Ledger_List_' . date('Y-m-d_His') . '.pdf');
    }

    public function exportListExcel(Request $request)
    {
        $data = $this->getFabricListData($request, false);
        $fabrics = $data['fabrics'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fabric Ledger Summary');

        // Header Title
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'SNAPKID - Fabric Stock Ledger Summary');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->getColor()->setARGB('FF1E3C72');
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Date
        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', 'Generated on: ' . date('d M Y, h:i A'));
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Headers
        $headers = ['#', 'Fabric Name', 'Total Inward (Mtr)', 'Total Outward (Mtr)', 'Current Balance (Mtr)'];
        $cols = ['A', 'B', 'C', 'D', 'E'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:E4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $totIn = 0;
        $totOut = 0;
        $totBal = 0;

        foreach ($fabrics as $idx => $f) {
            $in = (float)$f->total_inward;
            $out = (float)$f->total_outward;
            $bal = (float)$f->current_balance;

            $totIn += $in;
            $totOut += $out;
            $totBal += $bal;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $f->name ?? '-');
            $sheet->setCellValue('C' . $row, $in);
            $sheet->setCellValue('D' . $row, $out);
            $sheet->setCellValue('E' . $row, $bal);
            $row++;
        }

        // Summary Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('C' . $row, $totIn);
        $sheet->setCellValue('D' . $row, $totOut);
        $sheet->setCellValue('E' . $row, $totBal);
        $sheet->getStyle('A' . $row . ':E' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':E' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle('C5:E' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'E') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Fabric_Ledger_List_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getFabricListData(Request $request, $paginate = true)
    {
        $search = $request->query('search');

        $query = Fabric::with(['fabric_vendor'])
            ->where('status', 1)
            ->when($search, function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
            });

        $fabrics = $paginate ? $query->paginate(15)->withQueryString() : $query->get();

        foreach ($fabrics as $fabric) {
            $fabric->total_inward = FabricReceiptDetail::where('fabric_id', $fabric->id)
                ->where('status', '>', 0)
                ->sum('meter');

            $fabric->current_balance = FabricReceiptDetail::where('fabric_id', $fabric->id)
                ->where('status', '>', 0)
                ->sum('remaining_quantity');

            $fabric->total_outward = $fabric->total_inward - $fabric->current_balance;
        }

        return compact('fabrics', 'search');
    }

    public function show(Request $request, $id)
    {
        $data = $this->getFabricLedgerData($request, $id);
        return view('admin.ledger.fabric.show', $data);
    }

    public function exportPdf(Request $request, $id)
    {
        $data = $this->getFabricLedgerData($request, $id);
        $pdf = \PDF::loadView('admin.ledger.fabric.pdf', $data)->setPaper('a4', 'portrait');
        $fileName = 'Fabric_Ledger_' . str_replace(' ', '_', $data['fabric']->name) . '_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($fileName);
    }

    public function exportExcel(Request $request, $id)
    {
        $data = $this->getFabricLedgerData($request, $id);
        $fabric = $data['fabric'];
        $transactions = $data['transactions'];
        $openingBalanceAmount = $data['openingBalanceAmount'];

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Fabric Ledger');

        // Header Title
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', 'SNAPKID - Fabric Stock Ledger: ' . ($fabric->name ?? 'Fabric'));
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
        $sheet->setCellValue('A2', 'Vendor: ' . ($fabric->fabric_vendor?->name ?? 'N/A') . ' | Period: ' . $periodText);
        $sheet->getStyle('A2')->getFont()->setSize(10)->getColor()->setARGB('FF666666');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = ['Date', 'Type', 'Party', 'Particulars', 'Inward (Mtr)', 'Outward (Mtr)', 'Balance (Mtr)'];
        $cols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($headers as $idx => $h) {
            $sheet->setCellValue($cols[$idx] . '4', $h);
        }

        $headerRange = 'A4:G4';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FF1E3C72');
        $sheet->getStyle($headerRange)->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $row = 5;
        $totalInward = 0;
        $totalOutward = 0;

        // Opening balance row
        $sheet->setCellValue('A' . $row, $data['startDate'] ? \Carbon\Carbon::parse($data['startDate'])->format('d M Y') : '-');
        $sheet->setCellValue('B' . $row, 'Opening');
        $sheet->setCellValue('C' . $row, '-');
        $sheet->setCellValue('D' . $row, 'Opening Stock B/F');
        $sheet->setCellValue('E' . $row, 0);
        $sheet->setCellValue('F' . $row, 0);
        $sheet->setCellValue('G' . $row, $openingBalanceAmount);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setItalic(true);
        $row++;

        foreach ($transactions as $tx) {
            $in = (float)($tx->inward ?? 0);
            $out = (float)($tx->outward ?? 0);
            $bal = (float)($tx->running_balance ?? 0);

            $totalInward += $in;
            $totalOutward += $out;

            $sheet->setCellValue('A' . $row, $tx->date ? \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A') : '-');
            $sheet->setCellValue('B' . $row, $tx->type ?? '-');
            $sheet->setCellValue('C' . $row, $tx->party ?? '-');
            $sheet->setCellValue('D' . $row, $tx->particulars ?? '-');
            $sheet->setCellValue('E' . $row, $in);
            $sheet->setCellValue('F' . $row, $out);
            $sheet->setCellValue('G' . $row, $bal);

            $row++;
        }

        // Total Row
        $sheet->setCellValue('A' . $row, 'Total');
        $sheet->setCellValue('E' . $row, $totalInward);
        $sheet->setCellValue('F' . $row, $totalOutward);
        $finalBal = end($transactions) ? end($transactions)->running_balance : $openingBalanceAmount;
        $sheet->setCellValue('G' . $row, $finalBal);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFont()->setBold(true);
        $sheet->getStyle('A' . $row . ':G' . $row)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');

        $sheet->getStyle('E5:G' . $row)->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Fabric_Ledger_' . str_replace(' ', '_', $fabric->name ?? 'Fabric') . '_' . date('Y-m-d_His') . '.xlsx';
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $tempPath = storage_path('app/public/' . $fileName);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getFabricLedgerData(Request $request, $id)
    {
        $fabric = Fabric::findOrFail($id);
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $vendorId = $request->query('vendor_id');
        $customerId = $request->query('customer_id');

        $vendors = Vendor::orderBy('name')->get();
        $customers = MasterCustomer::orderBy('name')->get();

        // 1. Fetch relevant Inward rolls (Status > 0)
        $receivedRollsQuery = FabricReceiptDetail::where('fabric_id', $id)
            ->where('status', '>', 0);

        $receivedRollIds = (clone $receivedRollsQuery)->pluck('id');
        $receivedRollNumbers = (clone $receivedRollsQuery)->pluck('roll_number');

        $inwards = FabricReceiptDetail::with(['fabric_receipt.vendor'])
            ->whereIn('id', $receivedRollIds)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($vendorId, function ($q) use ($vendorId) {
                $q->whereHas('fabric_receipt', fn($sq) => $sq->where('vendor_id', $vendorId));
            })
            ->get();

        // 2. Fetch recorded Outwards (Only for the received rolls)

        // A. Sales (Agent Orders)
        $salesOutwards = AgentOrderFabricItem::with(['order.party', 'roll'])
            ->whereIn('fabric_receipt_detail_id', $receivedRollIds)
            ->where('status', 'dispatched')
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($customerId, function ($q) use ($customerId) {
                $q->whereHas('order', fn($sq) => $sq->where('party_id', $customerId));
            })
            ->get();

        // B. Returns
        $returnsOutwards = FabricReturnDetail::with(['fabric_return.receipt.vendor', 'receipt_detail'])
            ->whereIn('fabric_receipt_detail_id', $receivedRollIds)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->when($vendorId, function ($q) use ($vendorId) {
                $q->whereHas('return.receipt', fn($sq) => $sq->where('vendor_id', $vendorId));
            })
            ->get();

        // C. Production (Cutting)
        $productionOutwards = FabricRollAssigning::with(['orderProductSet', 'stageMasterUnit'])
            ->whereIn('fabric_receipt_detail_id', $receivedRollIds->isEmpty() ? [0] : $receivedRollIds)
            ->when($startDate, fn($q) => $q->whereDate('created_at', '>=', $startDate))
            ->when($endDate, fn($q) => $q->whereDate('created_at', '<=', $endDate))
            ->get();

        // 3. Unify Transactions with Physical Attribution Logic
        $transactions = collect();

        // A. Add Grouped Inwards (Shipments)
        $groupedInwards = $inwards->groupBy('fabric_receipt_id');
        foreach ($groupedInwards as $receiptId => $rolls) {
            $first = $rolls->first();
            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Inward',
                'party' => $first->fabric_receipt?->vendor?->name ?? 'Direct Purchase',
                'particulars' => 'Receipt (Shipment: ' . ($first->fabric_receipt?->shipment_id ?? '-') . ')',
                'inward' => (float)$rolls->sum('meter'),
                'outward' => 0,
                'rolls' => $rolls->map(fn($r) => ['number' => $r->roll_number, 'meter' => $r->meter])->values()
            ]);
        }

        // B. Prepare Attribution Pool for Physical Usage
        // We use the difference between meter and remaining_quantity as the "True" outward meter
        $allInwardRolls = FabricReceiptDetail::where('fabric_id', $id)
            ->where('status', '>', 0)
            ->orderBy('created_at', 'asc')
            ->get();

        $attributionPool = [];
        foreach ($allInwardRolls as $roll) {
            $attributionPool[$roll->roll_number][] = (object)[
                'id' => $roll->id,
                'available_to_attribute' => (float)$roll->meter - (float)$roll->remaining_quantity
            ];
        }

        // Helper to attribute meter from the physical pool
        $attributeFromPool = function($rollNo, $requestedMeter) use (&$attributionPool) {
            if (!isset($attributionPool[$rollNo])) return 0;
            $attributed = 0;
            foreach ($attributionPool[$rollNo] as $poolItem) {
                if ($poolItem->available_to_attribute > 0.001) {
                    $take = min($requestedMeter - $attributed, $poolItem->available_to_attribute);
                    $attributed += $take;
                    $poolItem->available_to_attribute -= $take;
                    if ($attributed >= $requestedMeter) break;
                }
            }
            return $attributed;
        };

        // C. Add Grouped Sales (Attribute from physical usage)
        $groupedSales = $salesOutwards->groupBy('agent_order_id');
        foreach ($groupedSales as $orderId => $items) {
            $first = $items->first();
            $actualOutward = 0;
            foreach($items as $item) {
                $actualOutward += $attributeFromPool($item->roll?->roll_number, $item->meter);
            }

            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->order?->party?->name ?? 'Customer',
                'particulars' => 'Sale: Order ' . ($first->order?->sku ?? '-'),
                'inward' => 0,
                'outward' => $actualOutward,
                'rolls' => $items->map(fn($s) => ['number' => $s->roll?->roll_number ?? '-', 'meter' => $s->meter])->values()
            ]);
        }

        // D. Add Grouped Returns
        $groupedReturns = $returnsOutwards->groupBy('fabric_return_id');
        foreach ($groupedReturns as $returnId => $details) {
            $first = $details->first();
            $actualOutward = 0;
            foreach($details as $d) {
                $actualOutward += $attributeFromPool($d->receipt_detail?->roll_number, $d->return_meter);
            }

            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->fabric_return?->receipt?->vendor?->name ?? 'Vendor',
                'particulars' => 'Return: ' . ($first->fabric_return?->return_number ?? '-'),
                'inward' => 0,
                'outward' => $actualOutward,
                'rolls' => $details->map(fn($r) => ['number' => $r->receipt_detail?->roll_number ?? '-', 'meter' => $r->return_meter])->values()
            ]);
        }

        // E. Add Grouped Production
        $groupedProduction = $productionOutwards->groupBy(function($item) {
            return $item->order_no . '|' . $item->lot_no;
        });
        foreach ($groupedProduction as $key => $items) {
            $first = $items->first();
            $actualOutward = 0;
            foreach($items as $p) {
                $actualOutward += $attributeFromPool($p->roll_no, $p->meter);
            }

            $transactions->push((object)[
                'date' => $first->created_at,
                'type' => 'Outward',
                'party' => $first->stageMasterUnit?->name ?? 'Internal Unit',
                'particulars' => 'Production: Lot ' . ($first->lot_no ?? '-') . ' (Ord: ' . ($first->order_no ?? '-') . ')',
                'inward' => 0,
                'outward' => $actualOutward,
                'rolls' => $items->map(fn($p) => ['number' => $p->roll_no ?? '-', 'meter' => $p->meter])->values()
            ]);
        }

        // F. Add reconciliation for remaining physical usage NOT captured in records
        foreach ($attributionPool as $rollNo => $poolItems) {
            foreach ($poolItems as $item) {
                if ($item->available_to_attribute > 0.01) {
                    $roll = FabricReceiptDetail::find($item->id);
                    $transactions->push((object)[
                        'date' => $roll->updated_at,
                        'type' => 'Outward',
                        'party' => 'Internal Usage',
                        'particulars' => 'Unrecorded Physical Usage (Roll ' . $rollNo . ')',
                        'inward' => 0,
                        'outward' => $item->available_to_attribute,
                        'rolls' => [['number' => $rollNo, 'meter' => $item->available_to_attribute]]
                    ]);
                }
            }
        }

        // 4. Calculate Opening Balance (Brought Forward)
        $openingBalanceAmount = 0;
        if ($startDate) {
            $inwardBefore = FabricReceiptDetail::where('fabric_id', $id)->where('status', '>', 0)->whereDate('created_at', '<', $startDate)->sum('meter');
            $salesBefore = AgentOrderFabricItem::whereHas('roll', fn($q) => $q->where('fabric_id', $id))->where('status', 'dispatched')->whereDate('created_at', '<', $startDate)->sum('meter');
            $returnsBefore = FabricReturnDetail::whereHas('receipt_detail', fn($q) => $q->where('fabric_id', $id))->whereDate('created_at', '<', $startDate)->sum('return_meter');
            $productionBefore = FabricRollAssigning::whereIn('fabric_receipt_detail_id', $receivedRollIds->isEmpty() ? [0] : $receivedRollIds)->whereHas('orderProductSet', fn($q) => $q->whereRaw("FIND_IN_SET(?, fabric_id)", [$id]))->whereDate('created_at', '<', $startDate)->sum('meter');
            $openingBalanceAmount = (float)$inwardBefore - (float)$salesBefore - (float)$returnsBefore - (float)$productionBefore;
        }

        // 5. Final Sort and Balance Calculation
        $transactions = $transactions->sortBy('date')->values();
        
        $balance = $openingBalanceAmount;
        foreach ($transactions as $tx) {
            $balance += ($tx->inward - $tx->outward);
            $tx->running_balance = $balance;
        }

        return compact('fabric', 'transactions', 'startDate', 'endDate', 'vendors', 'customers', 'vendorId', 'customerId', 'openingBalanceAmount');
    }
}
