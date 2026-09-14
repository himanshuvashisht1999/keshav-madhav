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

        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');

        $purchases = $query->orderBy('date', 'desc')->paginate(25)->appends($request->all());

        $vendors = DB::table('vendors')->select('id', 'name')->orderBy('name')->get();

        return view('admin.ledger.purchase.index', compact('purchases', 'vendors', 'totalGrandTotal'));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getPurchasesQuery($request);
        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');
        $purchases = $query->orderBy('date', 'desc')->get();

        $selectedVendor = null;
        if ($request->filled('vendor_id')) {
            $selectedVendor = DB::table('vendors')->where('id', $request->vendor_id)->value('name');
        }

        $pdf = Pdf::loadView('admin.ledger.purchase.pdf', [
            'purchases' => $purchases,
            'totalGrandTotal' => $totalGrandTotal,
            'filters' => $request->all(),
            'selectedVendor' => $selectedVendor,
        ])->setPaper('a4', 'portrait');

        $filename = 'Purchase_Ledger_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getPurchasesQuery($request);
        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');
        $purchases = $query->orderBy('date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Purchase Ledger');

        // Header metadata
        $sheet->setCellValue('A1', 'Purchase Ledger Report');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Generated: ' . date('d-m-Y H:i A') . ' | Total Grand Total: ₹' . number_format($totalGrandTotal, 2));
        $sheet->mergeCells('A2:F2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = ['S.No.', 'Date', 'Bill No.', 'Vendor Name', 'Receipt Type', 'Grand Total'];
        $sheet->fromArray($headers, NULL, 'A4');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3C72']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A4:F4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        $rowNumber = 5;
        $sno = 1;
        foreach ($purchases as $item) {
            $sheet->setCellValue('A' . $rowNumber, $sno++);
            $sheet->setCellValue('B' . $rowNumber, $item->date ? date('d-m-Y', strtotime($item->date)) : 'N/A');
            $sheet->setCellValue('C' . $rowNumber, $item->invoice_no ?? 'N/A');
            $sheet->setCellValue('D' . $rowNumber, $item->vendor_name ?? 'N/A');
            $sheet->setCellValue('E' . $rowNumber, $item->item_type ?? 'N/A');
            $sheet->setCellValue('F' . $rowNumber, (float) $item->grand_total);
            $sheet->getStyle('F' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
            $rowNumber++;
        }

        // Total Row
        $sheet->setCellValue('A' . $rowNumber, 'TOTAL');
        $sheet->mergeCells("A{$rowNumber}:E{$rowNumber}");
        $sheet->setCellValue('F' . $rowNumber, (float) $totalGrandTotal);
        $sheet->getStyle('F' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A{$rowNumber}:F{$rowNumber}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ]);

        foreach (range('A', 'F') as $col) {
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
        // Fabric Receipts (Invoices)
        $fabricsQuery = DB::table('fabric_receipts')
            ->join('vendors', 'fabric_receipts.vendor_id', '=', 'vendors.id')
            ->select(
                'fabric_receipts.id as ref_id',
                'fabric_receipts.bill_no as invoice_no',
                'vendors.name as vendor_name',
                DB::raw("CAST('Fabric' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'fabric_receipts.time as date',
                DB::raw('COALESCE(fabric_receipts.total_amount, 0) as grand_total')
            );

        // Item Receipts (Invoices) - from Domestic Inventory Purchases
        $itemsQuery = DB::table('domestic_inventory_purchases')
            ->join('vendors', 'domestic_inventory_purchases.vendor_id', '=', 'vendors.id')
            ->select(
                'domestic_inventory_purchases.id as ref_id',
                DB::raw('NULL as invoice_no'),
                'vendors.name as vendor_name',
                DB::raw("CAST('Product/Accessory' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'domestic_inventory_purchases.purchase_date as date',
                DB::raw('COALESCE(domestic_inventory_purchases.total_amount, 0) as grand_total')
            );

        if ($request->filled('bill_no')) {
            $billNo = trim($request->bill_no);
            $fabricsQuery->where('fabric_receipts.bill_no', 'like', "%{$billNo}%");
            $itemsQuery->whereRaw('1 = 0');
        }

        if ($request->filled('from_date')) {
            $fabricsQuery->whereDate('fabric_receipts.time', '>=', $request->from_date);
            $itemsQuery->whereDate('domestic_inventory_purchases.purchase_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $fabricsQuery->whereDate('fabric_receipts.time', '<=', $request->to_date);
            $itemsQuery->whereDate('domestic_inventory_purchases.purchase_date', '<=', $request->to_date);
        }

        if ($request->filled('item_type')) {
            if ($request->item_type == 'Fabric') {
                $itemsQuery->whereRaw('1 = 0');
            } elseif ($request->item_type == 'Product/Accessory') {
                $fabricsQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('vendor_id')) {
            $fabricsQuery->where('fabric_receipts.vendor_id', $request->vendor_id);
            $itemsQuery->where('domestic_inventory_purchases.vendor_id', $request->vendor_id);
        }

        $combinedQuery = $fabricsQuery->union($itemsQuery);

        return DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_purchases"))
            ->mergeBindings($combinedQuery);
    }
}
