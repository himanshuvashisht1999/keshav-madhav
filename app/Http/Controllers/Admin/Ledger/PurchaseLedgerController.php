<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->getPurchasesQuery($request);
        $sortDate = strtolower($request->get('sort_date', 'desc')) === 'asc' ? 'asc' : 'desc';

        $totalPurchaseGrandTotal = (float)(clone $query)->where('entry_type', 'purchase')->sum('grand_total');
        $totalReturnGrandTotal = (float)(clone $query)->where('entry_type', 'return')->sum('grand_total');
        $netGrandTotal = $totalPurchaseGrandTotal - $totalReturnGrandTotal;
        $totalGrandTotal = $netGrandTotal;

        $purchases = $query->orderBy('date', $sortDate)->orderBy('ref_id', $sortDate)->paginate(25)->appends($request->all());

        $pagePurchaseTotal = (float)$purchases->where('entry_type', 'purchase')->sum('grand_total');
        $pageReturnTotal = (float)$purchases->where('entry_type', 'return')->sum('grand_total');
        $pageSubTotal = $pagePurchaseTotal - $pageReturnTotal;
        $totalPurchasesCount = $purchases->total();

        $vendors = DB::table('vendors')->select('id', 'name')->orderBy('name')->get();
        $purchaseAgents = DB::table('purchase_agents')->select('id', 'name')->where('status', 1)->orderBy('name')->get();

        return view('admin.ledger.purchase.index', compact(
            'purchases',
            'vendors',
            'purchaseAgents',
            'totalGrandTotal',
            'totalPurchaseGrandTotal',
            'totalReturnGrandTotal',
            'netGrandTotal',
            'pageSubTotal',
            'totalPurchasesCount'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getPurchasesQuery($request);
        $sortDate = strtolower($request->get('sort_date', 'desc')) === 'asc' ? 'asc' : 'desc';

        $totalPurchaseGrandTotal = (float)(clone $query)->where('entry_type', 'purchase')->sum('grand_total');
        $totalReturnGrandTotal = (float)(clone $query)->where('entry_type', 'return')->sum('grand_total');
        $netGrandTotal = $totalPurchaseGrandTotal - $totalReturnGrandTotal;
        $totalGrandTotal = $netGrandTotal;

        $purchases = $query->orderBy('date', $sortDate)->orderBy('ref_id', $sortDate)->get();

        $selectedVendor = null;
        if ($request->filled('vendor_id')) {
            $selectedVendor = DB::table('vendors')->where('id', $request->vendor_id)->value('name');
        }

        $selectedPurchaseAgent = null;
        if ($request->filled('purchase_agent_id')) {
            if ($request->purchase_agent_id === 'direct') {
                $selectedPurchaseAgent = 'Direct (No Agent)';
            } else {
                $selectedPurchaseAgent = DB::table('purchase_agents')->where('id', $request->purchase_agent_id)->value('name');
            }
        }

        $pdf = Pdf::loadView('admin.ledger.purchase.pdf', [
            'purchases' => $purchases,
            'totalGrandTotal' => $totalGrandTotal,
            'totalPurchaseGrandTotal' => $totalPurchaseGrandTotal,
            'totalReturnGrandTotal' => $totalReturnGrandTotal,
            'netGrandTotal' => $netGrandTotal,
            'filters' => $request->all(),
            'selectedVendor' => $selectedVendor,
            'selectedPurchaseAgent' => $selectedPurchaseAgent,
        ])->setPaper('a4', 'portrait');

        $filename = 'Purchase_Ledger_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getPurchasesQuery($request);
        $sortDate = strtolower($request->get('sort_date', 'desc')) === 'asc' ? 'asc' : 'desc';

        $totalPurchaseGrandTotal = (float)(clone $query)->where('entry_type', 'purchase')->sum('grand_total');
        $totalReturnGrandTotal = (float)(clone $query)->where('entry_type', 'return')->sum('grand_total');
        $netGrandTotal = $totalPurchaseGrandTotal - $totalReturnGrandTotal;
        $totalGrandTotal = $netGrandTotal;

        $purchases = $query->orderBy('date', $sortDate)->orderBy('ref_id', $sortDate)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Purchase Ledger');

        // Header metadata
        $sheet->setCellValue('A1', 'Purchase Ledger Report');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $reportTypeLabel = 'Both (Purchase + Return)';
        if ($request->get('report_type') === 'purchase') {
            $reportTypeLabel = 'Purchase Only';
        } elseif ($request->get('report_type') === 'return') {
            $reportTypeLabel = 'Purchase Return Only';
        }

        $sheet->setCellValue('A2', 'Generated: ' . date('d-m-Y H:i A') . ' | Report Type: ' . $reportTypeLabel . ' | Date Order: ' . strtoupper($sortDate) . ' | Net Total: ₹' . number_format($netGrandTotal, 2));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = ['S.No.', 'Type', 'Date', 'Bill / Return No.', 'Vendor Name', 'Purchase Agent', 'Receipt Type', 'Grand Total'];
        $sheet->fromArray($headers, NULL, 'A4');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3C72']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A4:H4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        $rowNumber = 5;
        $sno = 1;
        foreach ($purchases as $item) {
            $sheet->setCellValue('A' . $rowNumber, $sno++);
            $sheet->setCellValue('B' . $rowNumber, ($item->entry_type ?? 'purchase') === 'return' ? 'Purchase Return' : 'Purchase');
            $sheet->setCellValue('C' . $rowNumber, $item->date ? date('d-m-Y', strtotime($item->date)) : 'N/A');
            $sheet->setCellValue('D' . $rowNumber, $item->invoice_no ?? 'N/A');
            $sheet->setCellValue('E' . $rowNumber, $item->vendor_name ?? 'N/A');
            $sheet->setCellValue('F' . $rowNumber, $item->purchase_agent_name ?? 'Direct');
            $sheet->setCellValue('G' . $rowNumber, $item->item_type ?? 'N/A');
            
            $amountVal = (float)$item->grand_total;
            if (($item->entry_type ?? 'purchase') === 'return') {
                $amountVal = -$amountVal;
            }
            $sheet->setCellValue('H' . $rowNumber, $amountVal);
            $sheet->getStyle('H' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
            $rowNumber++;
        }

        // Total Row
        $sheet->setCellValue('A' . $rowNumber, 'NET TOTAL');
        $sheet->mergeCells("A{$rowNumber}:G{$rowNumber}");
        $sheet->setCellValue('H' . $rowNumber, (float) $netGrandTotal);
        $sheet->getStyle('H' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A{$rowNumber}:H{$rowNumber}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ]);

        foreach (range('A', 'H') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Purchase_Ledger_' . date('Y-m-d_His') . '.xlsx';
        $tempDir = storage_path('app/public');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $tempPath = $tempDir . '/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getPurchasesQuery(Request $request)
    {
        // 1. Fabric Receipts (Invoices)
        $fabricsQuery = DB::table('fabric_receipts')
            ->join('vendors', 'fabric_receipts.vendor_id', '=', 'vendors.id')
            ->leftJoin('purchase_agents', 'vendors.purchase_agent_id', '=', 'purchase_agents.id')
            ->select(
                'fabric_receipts.id as ref_id',
                'fabric_receipts.bill_no as invoice_no',
                'vendors.name as vendor_name',
                DB::raw("CAST(purchase_agents.name AS CHAR) COLLATE utf8mb4_unicode_ci as purchase_agent_name"),
                'vendors.purchase_agent_id',
                DB::raw("CAST('Fabric' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'fabric_receipts.time as date',
                DB::raw('COALESCE(fabric_receipts.total_amount, 0) as grand_total'),
                DB::raw("CAST('purchase' AS CHAR) COLLATE utf8mb4_unicode_ci as entry_type")
            );

        // 2. Item Receipts (Invoices) - from Domestic Inventory Purchases
        $itemsQuery = DB::table('domestic_inventory_purchases')
            ->join('vendors', 'domestic_inventory_purchases.vendor_id', '=', 'vendors.id')
            ->leftJoin('purchase_agents', 'vendors.purchase_agent_id', '=', 'purchase_agents.id')
            ->select(
                'domestic_inventory_purchases.id as ref_id',
                DB::raw("CAST(domestic_inventory_purchases.bill_no AS CHAR) COLLATE utf8mb4_unicode_ci as invoice_no"),
                'vendors.name as vendor_name',
                DB::raw("CAST(purchase_agents.name AS CHAR) COLLATE utf8mb4_unicode_ci as purchase_agent_name"),
                'vendors.purchase_agent_id',
                DB::raw("CAST('Product/Accessory' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'domestic_inventory_purchases.purchase_date as date',
                DB::raw('COALESCE(domestic_inventory_purchases.total_amount, 0) as grand_total'),
                DB::raw("CAST('purchase' AS CHAR) COLLATE utf8mb4_unicode_ci as entry_type")
            );

        // 3. Fabric Returns (Purchase Returns to Vendors)
        $returnsQuery = DB::table('fabric_returns')
            ->join('vendors', 'fabric_returns.vendor_id', '=', 'vendors.id')
            ->leftJoin('purchase_agents', 'vendors.purchase_agent_id', '=', 'purchase_agents.id')
            ->leftJoin('fabric_receipts', 'fabric_returns.fabric_receipt_id', '=', 'fabric_receipts.id')
            ->select(
                'fabric_returns.id as ref_id',
                DB::raw("CAST(COALESCE(fabric_returns.return_number, fabric_receipts.bill_no, CONCAT('#FR-', LPAD(fabric_returns.id, 5, '0'))) AS CHAR) COLLATE utf8mb4_unicode_ci as invoice_no"),
                'vendors.name as vendor_name',
                DB::raw("CAST(purchase_agents.name AS CHAR) COLLATE utf8mb4_unicode_ci as purchase_agent_name"),
                'vendors.purchase_agent_id',
                DB::raw("CAST('Fabric Return' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'fabric_returns.date as date',
                DB::raw('COALESCE(fabric_returns.total_amount, 0) as grand_total'),
                DB::raw("CAST('return' AS CHAR) COLLATE utf8mb4_unicode_ci as entry_type")
            );

        if ($request->filled('bill_no')) {
            $billNo = trim($request->bill_no);
            $fabricsQuery->where('fabric_receipts.bill_no', 'like', "%{$billNo}%");
            $itemsQuery->where('domestic_inventory_purchases.bill_no', 'like', "%{$billNo}%");
            $returnsQuery->where(function ($q) use ($billNo) {
                $q->where('fabric_returns.return_number', 'like', "%{$billNo}%")
                  ->orWhere('fabric_receipts.bill_no', 'like', "%{$billNo}%");
            });
        }

        if ($request->filled('from_date')) {
            $fabricsQuery->whereDate('fabric_receipts.time', '>=', $request->from_date);
            $itemsQuery->whereDate('domestic_inventory_purchases.purchase_date', '>=', $request->from_date);
            $returnsQuery->whereDate('fabric_returns.date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $fabricsQuery->whereDate('fabric_receipts.time', '<=', $request->to_date);
            $itemsQuery->whereDate('domestic_inventory_purchases.purchase_date', '<=', $request->to_date);
            $returnsQuery->whereDate('fabric_returns.date', '<=', $request->to_date);
        }

        if ($request->filled('item_type')) {
            if ($request->item_type == 'Fabric') {
                $itemsQuery->whereRaw('1 = 0');
            } elseif ($request->item_type == 'Product/Accessory') {
                $fabricsQuery->whereRaw('1 = 0');
                $returnsQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('vendor_id')) {
            $fabricsQuery->where('fabric_receipts.vendor_id', $request->vendor_id);
            $itemsQuery->where('domestic_inventory_purchases.vendor_id', $request->vendor_id);
            $returnsQuery->where('fabric_returns.vendor_id', $request->vendor_id);
        }

        if ($request->filled('purchase_agent_id')) {
            if ($request->purchase_agent_id === 'direct') {
                $agentDirectFilter = function ($q) {
                    $q->whereNull('vendors.purchase_agent_id')
                      ->orWhere('vendors.purchase_agent_id', 0);
                };
                $fabricsQuery->where($agentDirectFilter);
                $itemsQuery->where($agentDirectFilter);
                $returnsQuery->where($agentDirectFilter);
            } else {
                $fabricsQuery->where('vendors.purchase_agent_id', $request->purchase_agent_id);
                $itemsQuery->where('vendors.purchase_agent_id', $request->purchase_agent_id);
                $returnsQuery->where('vendors.purchase_agent_id', $request->purchase_agent_id);
            }
        }

        // Filter by Report Type: purchase (Purchase Only), return (Purchase Return Only), both (Both)
        $reportType = $request->get('report_type', 'both');
        if ($reportType === 'purchase') {
            $combinedQuery = $fabricsQuery->union($itemsQuery);
        } elseif ($reportType === 'return') {
            $combinedQuery = $returnsQuery;
        } else {
            $combinedQuery = $fabricsQuery->union($itemsQuery)->union($returnsQuery);
        }

        return DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_purchases"))
            ->mergeBindings($combinedQuery);
    }
}
