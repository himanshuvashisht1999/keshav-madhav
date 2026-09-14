<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Barryvdh\DomPDF\Facade\Pdf;

class SalesLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = $this->getSalesQuery($request);

        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');

        $sales = $query->orderBy('dispatch_date', 'desc')->paginate(25)->appends($request->all());

        $parties = DB::table('master_customers')->select('id', 'name')->orderBy('name')->get();
        $vendors = DB::table('vendors')->select('id', 'name')->orderBy('name')->get();

        return view('admin.ledger.sales.index', compact('sales', 'parties', 'vendors', 'totalGrandTotal'));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getSalesQuery($request);
        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');
        $sales = $query->orderBy('dispatch_date', 'desc')->get();

        $selectedParty = null;
        if ($request->filled('party_id')) {
            $selectedParty = DB::table('master_customers')->where('id', $request->party_id)->value('name');
        }

        $pdf = Pdf::loadView('admin.ledger.sales.pdf', [
            'sales' => $sales,
            'totalGrandTotal' => $totalGrandTotal,
            'filters' => $request->all(),
            'selectedParty' => $selectedParty,
        ])->setPaper('a4', 'portrait');

        $filename = 'Sales_Ledger_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getSalesQuery($request);
        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');
        $sales = $query->orderBy('dispatch_date', 'desc')->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Ledger');

        // Header metadata
        $sheet->setCellValue('A1', 'Sales Ledger Report');
        $sheet->mergeCells('A1:G1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $sheet->setCellValue('A2', 'Generated: ' . date('d-m-Y H:i A') . ' | Total Grand Total: ₹' . number_format($totalGrandTotal, 2));
        $sheet->mergeCells('A2:G2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = ['S.No.', 'Dispatch ID', 'Bill No.', 'Party Name', 'Agent / Type', 'Grand Total', 'Date'];
        $sheet->fromArray($headers, NULL, 'A4');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '1E3C72']
            ],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]
        ];
        $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);
        $sheet->getRowDimension(4)->setRowHeight(25);

        $rowNumber = 5;
        $sno = 1;
        foreach ($sales as $dispatch) {
            $sheet->setCellValue('A' . $rowNumber, $sno++);
            $sheet->setCellValue('B' . $rowNumber, $dispatch->dispatch_no ?? 'N/A');
            $sheet->setCellValue('C' . $rowNumber, $dispatch->bill_no ?? 'N/A');
            $partyLabel = $dispatch->party_name ?? 'N/A';
            if ($dispatch->party_type === 'vendor') {
                $partyLabel .= ' (Vendor)';
            } elseif ($dispatch->source_type === 'corporate') {
                $partyLabel .= ' (Corporate)';
            }
            $sheet->setCellValue('D' . $rowNumber, $partyLabel);
            $sheet->setCellValue('E' . $rowNumber, $dispatch->source_type === 'corporate' ? 'Corporate' : ($dispatch->agent_name ?? 'Direct'));
            $sheet->setCellValue('F' . $rowNumber, (float) $dispatch->grand_total);
            $sheet->getStyle('F' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('G' . $rowNumber, $dispatch->dispatch_date ? date('d-m-Y', strtotime($dispatch->dispatch_date)) : 'N/A');
            $rowNumber++;
        }

        // Total Row
        $sheet->setCellValue('A' . $rowNumber, 'TOTAL');
        $sheet->mergeCells("A{$rowNumber}:E{$rowNumber}");
        $sheet->setCellValue('F' . $rowNumber, (float) $totalGrandTotal);
        $sheet->getStyle('F' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
        $sheet->getStyle("A{$rowNumber}:G{$rowNumber}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'E2E8F0']
            ]
        ]);

        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $fileName = 'Sales_Ledger_' . date('Y-m-d_His') . '.xlsx';
        $tempDir = storage_path('app/public');
        if (!file_exists($tempDir)) {
            mkdir($tempDir, 0777, true);
        }
        $tempPath = $tempDir . '/' . $fileName;

        $writer = new Xlsx($spreadsheet);
        $writer->save($tempPath);

        return response()->download($tempPath, $fileName)->deleteFileAfterSend(true);
    }

    private function getSalesQuery(Request $request)
    {
        // 1. Agent Order Dispatches Query
        $q1 = DB::table('agent_order_dispatches')
            ->leftJoin('master_customers', 'agent_order_dispatches.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_order_dispatches.master_vendor_id', '=', 'vendors.id')
            ->leftJoin('sales_agents', 'agent_order_dispatches.sales_agent_id', '=', 'sales_agents.id')
            ->select(
                'agent_order_dispatches.id',
                DB::raw("CAST('agent' AS CHAR) COLLATE utf8mb4_unicode_ci as source_type"),
                DB::raw("CONCAT('#DSP-', LPAD(agent_order_dispatches.id, 5, '0')) as dispatch_no"),
                DB::raw("CAST(agent_order_dispatches.bill_no AS CHAR) COLLATE utf8mb4_unicode_ci as bill_no"),
                DB::raw("CAST(agent_order_dispatches.party_type AS CHAR) COLLATE utf8mb4_unicode_ci as party_type"),
                DB::raw("CAST(CASE WHEN agent_order_dispatches.party_type = 'vendor' THEN vendors.name ELSE master_customers.name END AS CHAR) COLLATE utf8mb4_unicode_ci as party_name"),
                DB::raw("CAST(sales_agents.name AS CHAR) COLLATE utf8mb4_unicode_ci as agent_name"),
                DB::raw("COALESCE(agent_order_dispatches.grand_total, 0) as grand_total"),
                'agent_order_dispatches.dispatch_date',
                DB::raw("CAST(agent_order_dispatches.remark AS CHAR) COLLATE utf8mb4_unicode_ci as remark"),
                'agent_order_dispatches.master_customer_id as customer_id',
                'agent_order_dispatches.master_vendor_id as vendor_id'
            );

        // 2. Corporate Order Dispatches Query
        $q2 = DB::table('order_dispatch')
            ->leftJoin('master_customers', 'order_dispatch.customer_id', '=', 'master_customers.id')
            ->leftJoin('sales_agents', 'master_customers.sales_agent_id', '=', 'sales_agents.id')
            ->select(
                'order_dispatch.id',
                DB::raw("CAST('corporate' AS CHAR) COLLATE utf8mb4_unicode_ci as source_type"),
                DB::raw("COALESCE(order_dispatch.sku, CONCAT('#OD-', LPAD(order_dispatch.id, 5, '0'))) as dispatch_no"),
                DB::raw("CAST(order_dispatch.bill_number AS CHAR) COLLATE utf8mb4_unicode_ci as bill_no"),
                DB::raw("CAST('customer' AS CHAR) COLLATE utf8mb4_unicode_ci as party_type"),
                DB::raw("CAST(master_customers.name AS CHAR) COLLATE utf8mb4_unicode_ci as party_name"),
                DB::raw("CAST(COALESCE(sales_agents.name, 'Corporate') AS CHAR) COLLATE utf8mb4_unicode_ci as agent_name"),
                DB::raw("COALESCE(order_dispatch.total_amount, 0) as grand_total"),
                'order_dispatch.dispatch_date',
                DB::raw("CAST(order_dispatch.remark AS CHAR) COLLATE utf8mb4_unicode_ci as remark"),
                'order_dispatch.customer_id as customer_id',
                DB::raw("NULL as vendor_id")
            );

        // Filter by Customer / Party
        if ($request->filled('party_id')) {
            $q1->where('agent_order_dispatches.master_customer_id', $request->party_id);
            $q2->where('order_dispatch.customer_id', $request->party_id);
        }

        // Filter by Vendor
        if ($request->filled('vendor_id')) {
            $q1->where('agent_order_dispatches.master_vendor_id', $request->vendor_id);
            $q2->whereRaw('1 = 0'); // Corporate orders are for customers, not vendors
        }

        // Filter by Bill No.
        if ($request->filled('bill_no')) {
            $billNo = trim($request->bill_no);
            $q1->where('agent_order_dispatches.bill_no', 'like', "%{$billNo}%");
            $q2->where('order_dispatch.bill_number', 'like', "%{$billNo}%");
        }

        // Filter by Date
        if ($request->filled('from_date')) {
            $q1->whereDate('agent_order_dispatches.dispatch_date', '>=', $request->from_date);
            $q2->whereDate('order_dispatch.dispatch_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $q1->whereDate('agent_order_dispatches.dispatch_date', '<=', $request->to_date);
            $q2->whereDate('order_dispatch.dispatch_date', '<=', $request->to_date);
        }

        // Filter by Item / Dispatch Type
        if ($request->filled('item_type')) {
            if ($request->item_type === 'Corporate') {
                $q1->whereRaw('1 = 0');
            } elseif ($request->item_type === 'Fabric') {
                $q2->whereRaw('1 = 0');
                $q1->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('agent_order_dispatch_items')
                        ->join('agent_orders', 'agent_order_dispatch_items.agent_order_id', '=', 'agent_orders.id')
                        ->whereColumn('agent_order_dispatch_items.agent_order_dispatch_id', 'agent_order_dispatches.id')
                        ->where(function ($w) {
                            $w->where('agent_orders.sale_type', 'fabric')
                              ->orWhere('agent_orders.order_type', 'fabric');
                        });
                });
            } elseif ($request->item_type === 'Product') {
                $q1->whereExists(function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('agent_order_dispatch_items')
                        ->join('agent_orders', 'agent_order_dispatch_items.agent_order_id', '=', 'agent_orders.id')
                        ->whereColumn('agent_order_dispatch_items.agent_order_dispatch_id', 'agent_order_dispatches.id')
                        ->where(function ($w) {
                            $w->where('agent_orders.sale_type', 'item')
                              ->orWhere('agent_orders.order_type', 'item');
                        });
                });
            }
        }

        $combined = $q1->unionAll($q2);
        return DB::table(DB::raw("({$combined->toSql()}) as combined_sales"))
            ->mergeBindings($combined);
    }
}
