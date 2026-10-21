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

        $sortDate = strtolower($request->get('sort_date', 'desc')) === 'asc' ? 'asc' : 'desc';

        $totalSalesGrandTotal = (float)(clone $query)->where('entry_type', 'sale')->sum('grand_total');
        $totalReturnGrandTotal = (float)(clone $query)->where('entry_type', 'return')->sum('grand_total');
        $netGrandTotal = $totalSalesGrandTotal - $totalReturnGrandTotal;
        $totalGrandTotal = $netGrandTotal;

        $sales = $query->orderBy('dispatch_date', $sortDate)->orderBy('id', $sortDate)->paginate(25)->appends($request->all());

        $pageSalesTotal = (float)$sales->where('entry_type', 'sale')->sum('grand_total');
        $pageReturnTotal = (float)$sales->where('entry_type', 'return')->sum('grand_total');
        $pageSubTotal = $pageSalesTotal - $pageReturnTotal;
        $totalSalesCount = $sales->total();

        $parties = DB::table('master_customers')->select('id', 'name')->orderBy('name')->get();
        $vendors = DB::table('vendors')->select('id', 'name')->orderBy('name')->get();

        return view('admin.ledger.sales.index', compact(
            'sales',
            'parties',
            'vendors',
            'totalGrandTotal',
            'totalSalesGrandTotal',
            'totalReturnGrandTotal',
            'netGrandTotal',
            'pageSubTotal',
            'totalSalesCount'
        ));
    }

    public function exportPdf(Request $request)
    {
        $query = $this->getSalesQuery($request);
        $sortDate = strtolower($request->get('sort_date', 'desc')) === 'asc' ? 'asc' : 'desc';

        $totalSalesGrandTotal = (float)(clone $query)->where('entry_type', 'sale')->sum('grand_total');
        $totalReturnGrandTotal = (float)(clone $query)->where('entry_type', 'return')->sum('grand_total');
        $netGrandTotal = $totalSalesGrandTotal - $totalReturnGrandTotal;
        $totalGrandTotal = $netGrandTotal;

        $sales = $query->orderBy('dispatch_date', $sortDate)->orderBy('id', $sortDate)->get();

        $selectedParty = null;
        if ($request->filled('party_id')) {
            $selectedParty = DB::table('master_customers')->where('id', $request->party_id)->value('name');
        }

        $pdf = Pdf::loadView('admin.ledger.sales.pdf', [
            'sales' => $sales,
            'totalGrandTotal' => $totalGrandTotal,
            'totalSalesGrandTotal' => $totalSalesGrandTotal,
            'totalReturnGrandTotal' => $totalReturnGrandTotal,
            'netGrandTotal' => $netGrandTotal,
            'filters' => $request->all(),
            'selectedParty' => $selectedParty,
        ])->setPaper('a4', 'portrait');

        $filename = 'Sales_Ledger_' . date('Y-m-d_His') . '.pdf';
        return $pdf->download($filename);
    }

    public function exportExcel(Request $request)
    {
        $query = $this->getSalesQuery($request);
        $sortDate = strtolower($request->get('sort_date', 'desc')) === 'asc' ? 'asc' : 'desc';

        $totalSalesGrandTotal = (float)(clone $query)->where('entry_type', 'sale')->sum('grand_total');
        $totalReturnGrandTotal = (float)(clone $query)->where('entry_type', 'return')->sum('grand_total');
        $netGrandTotal = $totalSalesGrandTotal - $totalReturnGrandTotal;
        $totalGrandTotal = $netGrandTotal;

        $sales = $query->orderBy('dispatch_date', $sortDate)->orderBy('id', $sortDate)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Sales Ledger');

        // Header metadata
        $sheet->setCellValue('A1', 'Sales Ledger Report');
        $sheet->mergeCells('A1:H1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        $reportTypeLabel = 'Both (Sales + Return)';
        if ($request->get('report_type') === 'sales') {
            $reportTypeLabel = 'Sales Only';
        } elseif ($request->get('report_type') === 'return') {
            $reportTypeLabel = 'Sales Return Only';
        }

        $sheet->setCellValue('A2', 'Generated: ' . date('d-m-Y H:i A') . ' | Report Type: ' . $reportTypeLabel . ' | Date Order: ' . strtoupper($sortDate) . ' | Net Total: ₹' . number_format($netGrandTotal, 2));
        $sheet->mergeCells('A2:H2');
        $sheet->getStyle('A2')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

        // Table Headers
        $headers = ['S.No.', 'Type', 'Dispatch / Return ID', 'Bill No.', 'Party Name', 'Agent / Type', 'Grand Total', 'Date'];
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
        foreach ($sales as $dispatch) {
            $sheet->setCellValue('A' . $rowNumber, $sno++);
            $sheet->setCellValue('B' . $rowNumber, ($dispatch->entry_type ?? 'sale') === 'return' ? 'Sale Return' : 'Sale');
            $sheet->setCellValue('C' . $rowNumber, $dispatch->dispatch_no ?? 'N/A');
            $sheet->setCellValue('D' . $rowNumber, $dispatch->bill_no ?? 'N/A');
            $partyLabel = $dispatch->party_name ?? 'N/A';
            if ($dispatch->party_type === 'vendor') {
                $partyLabel .= ' (Vendor)';
            } elseif ($dispatch->source_type === 'corporate') {
                $partyLabel .= ' (Corporate)';
            }
            $sheet->setCellValue('E' . $rowNumber, $partyLabel);
            $sheet->setCellValue('F' . $rowNumber, $dispatch->source_type === 'corporate' ? 'Corporate' : ($dispatch->agent_name ? $dispatch->agent_name . ' (Agent)' : 'Domestic (Direct)'));
            
            $amountVal = (float)$dispatch->grand_total;
            if (($dispatch->entry_type ?? 'sale') === 'return') {
                $amountVal = -$amountVal;
            }
            $sheet->setCellValue('G' . $rowNumber, $amountVal);
            $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
            $sheet->setCellValue('H' . $rowNumber, $dispatch->dispatch_date ? date('d-m-Y', strtotime($dispatch->dispatch_date)) : 'N/A');
            $rowNumber++;
        }

        // Total Row
        $sheet->setCellValue('A' . $rowNumber, 'NET TOTAL');
        $sheet->mergeCells("A{$rowNumber}:F{$rowNumber}");
        $sheet->setCellValue('G' . $rowNumber, (float) $netGrandTotal);
        $sheet->getStyle('G' . $rowNumber)->getNumberFormat()->setFormatCode('#,##0.00');
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
                DB::raw("CAST('sale' AS CHAR) COLLATE utf8mb4_unicode_ci as entry_type"),
                DB::raw("CONCAT('#DSP-', LPAD(agent_order_dispatches.id, 5, '0')) as dispatch_no"),
                DB::raw("CAST(agent_order_dispatches.bill_no AS CHAR) COLLATE utf8mb4_unicode_ci as bill_no"),
                DB::raw("CAST(agent_order_dispatches.party_type AS CHAR) COLLATE utf8mb4_unicode_ci as party_type"),
                DB::raw("CAST(CASE WHEN agent_order_dispatches.party_type = 'vendor' THEN vendors.name ELSE master_customers.name END AS CHAR) COLLATE utf8mb4_unicode_ci as party_name"),
                DB::raw("CAST(sales_agents.name AS CHAR) COLLATE utf8mb4_unicode_ci as agent_name"),
                DB::raw("COALESCE(agent_order_dispatches.grand_total, 0) as grand_total"),
                'agent_order_dispatches.dispatch_date',
                DB::raw("CAST(agent_order_dispatches.remark AS CHAR) COLLATE utf8mb4_unicode_ci as remark"),
                'agent_order_dispatches.master_customer_id as customer_id',
                'agent_order_dispatches.master_vendor_id as vendor_id',
                DB::raw("CAST(master_customers.type AS CHAR) COLLATE utf8mb4_unicode_ci as customer_type"),
                DB::raw("CAST(master_customers.subtype AS CHAR) COLLATE utf8mb4_unicode_ci as customer_subtype")
            );

        // 2. Corporate Order Dispatches Query
        $q2 = DB::table('order_dispatch')
            ->leftJoin('master_customers', 'order_dispatch.customer_id', '=', 'master_customers.id')
            ->leftJoin('sales_agents', 'master_customers.sales_agent_id', '=', 'sales_agents.id')
            ->select(
                'order_dispatch.id',
                DB::raw("CAST('corporate' AS CHAR) COLLATE utf8mb4_unicode_ci as source_type"),
                DB::raw("CAST('sale' AS CHAR) COLLATE utf8mb4_unicode_ci as entry_type"),
                DB::raw("COALESCE(order_dispatch.sku, CONCAT('#OD-', LPAD(order_dispatch.id, 5, '0'))) as dispatch_no"),
                DB::raw("CAST(order_dispatch.bill_number AS CHAR) COLLATE utf8mb4_unicode_ci as bill_no"),
                DB::raw("CAST('customer' AS CHAR) COLLATE utf8mb4_unicode_ci as party_type"),
                DB::raw("CAST(master_customers.name AS CHAR) COLLATE utf8mb4_unicode_ci as party_name"),
                DB::raw("CAST(COALESCE(sales_agents.name, 'Corporate') AS CHAR) COLLATE utf8mb4_unicode_ci as agent_name"),
                DB::raw("COALESCE(order_dispatch.total_amount, 0) as grand_total"),
                'order_dispatch.dispatch_date',
                DB::raw("CAST(order_dispatch.remark AS CHAR) COLLATE utf8mb4_unicode_ci as remark"),
                'order_dispatch.customer_id as customer_id',
                DB::raw("NULL as vendor_id"),
                DB::raw("CAST(COALESCE(master_customers.type, 'corporate') AS CHAR) COLLATE utf8mb4_unicode_ci as customer_type"),
                DB::raw("CAST(master_customers.subtype AS CHAR) COLLATE utf8mb4_unicode_ci as customer_subtype")
            );

        // 3. Agent Order Returns Query (Sales Returns)
        $q3 = DB::table('agent_order_returns')
            ->join('agent_order_dispatches', 'agent_order_returns.agent_order_dispatch_id', '=', 'agent_order_dispatches.id')
            ->leftJoin('master_customers', 'agent_order_dispatches.master_customer_id', '=', 'master_customers.id')
            ->leftJoin('vendors', 'agent_order_dispatches.master_vendor_id', '=', 'vendors.id')
            ->leftJoin('sales_agents', 'agent_order_dispatches.sales_agent_id', '=', 'sales_agents.id')
            ->select(
                'agent_order_returns.id',
                DB::raw("CAST('agent_return' AS CHAR) COLLATE utf8mb4_unicode_ci as source_type"),
                DB::raw("CAST('return' AS CHAR) COLLATE utf8mb4_unicode_ci as entry_type"),
                DB::raw("CONCAT('#RTN-', LPAD(agent_order_returns.id, 5, '0')) as dispatch_no"),
                DB::raw("CAST(agent_order_dispatches.bill_no AS CHAR) COLLATE utf8mb4_unicode_ci as bill_no"),
                DB::raw("CAST(agent_order_dispatches.party_type AS CHAR) COLLATE utf8mb4_unicode_ci as party_type"),
                DB::raw("CAST(CASE WHEN agent_order_dispatches.party_type = 'vendor' THEN vendors.name ELSE master_customers.name END AS CHAR) COLLATE utf8mb4_unicode_ci as party_name"),
                DB::raw("CAST(sales_agents.name AS CHAR) COLLATE utf8mb4_unicode_ci as agent_name"),
                DB::raw("COALESCE(agent_order_returns.grand_total, 0) as grand_total"),
                'agent_order_returns.return_date as dispatch_date',
                DB::raw("CAST(agent_order_returns.remark AS CHAR) COLLATE utf8mb4_unicode_ci as remark"),
                'agent_order_dispatches.master_customer_id as customer_id',
                'agent_order_dispatches.master_vendor_id as vendor_id',
                DB::raw("CAST(master_customers.type AS CHAR) COLLATE utf8mb4_unicode_ci as customer_type"),
                DB::raw("CAST(master_customers.subtype AS CHAR) COLLATE utf8mb4_unicode_ci as customer_subtype")
            );

        // Filter by Customer / Party
        if ($request->filled('party_id')) {
            $q1->where('agent_order_dispatches.master_customer_id', $request->party_id);
            $q2->where('order_dispatch.customer_id', $request->party_id);
            $q3->where('agent_order_dispatches.master_customer_id', $request->party_id);
        }

        // Filter by Vendor
        if ($request->filled('vendor_id')) {
            $q1->where('agent_order_dispatches.master_vendor_id', $request->vendor_id);
            $q2->whereRaw('1 = 0'); // Corporate orders are for customers, not vendors
            $q3->where('agent_order_dispatches.master_vendor_id', $request->vendor_id);
        }

        // Filter by Customer Type (Corporate, Domestic, Direct, Agent)
        if ($request->filled('type')) {
            $type = $request->type;
            if ($type === 'corporate') {
                $q1->where('master_customers.type', 'corporate');
                $q2->where(function ($q) {
                    $q->where('master_customers.type', 'corporate')
                      ->orWhereNull('master_customers.type');
                });
                $q3->where('master_customers.type', 'corporate');
            } elseif ($type === 'domestic') {
                $q1->where('master_customers.type', 'domestic');
                $q2->where('master_customers.type', 'domestic');
                $q3->where('master_customers.type', 'domestic');
            } elseif ($type === 'direct') {
                $q1->where('master_customers.type', 'domestic')
                   ->where(function ($q) {
                       $q->where('master_customers.subtype', 'direct')
                         ->orWhere(function ($sub) {
                             $sub->whereNull('master_customers.subtype')
                                 ->whereNull('master_customers.sales_agent_id')
                                 ->whereNull('agent_order_dispatches.sales_agent_id');
                         });
                   });
                $q2->where('master_customers.type', 'domestic')
                   ->where('master_customers.subtype', 'direct');
                $q3->where('master_customers.type', 'domestic')
                   ->where(function ($q) {
                       $q->where('master_customers.subtype', 'direct')
                         ->orWhere(function ($sub) {
                             $sub->whereNull('master_customers.subtype')
                                 ->whereNull('master_customers.sales_agent_id')
                                 ->whereNull('agent_order_dispatches.sales_agent_id');
                         });
                   });
            } elseif ($type === 'agent') {
                $q1->where('master_customers.type', 'domestic')
                   ->where(function ($q) {
                       $q->where('master_customers.subtype', 'agent')
                         ->orWhereNotNull('master_customers.sales_agent_id')
                         ->orWhereNotNull('agent_order_dispatches.sales_agent_id');
                   });
                $q2->where('master_customers.type', 'domestic')
                   ->where('master_customers.subtype', 'agent');
                $q3->where('master_customers.type', 'domestic')
                   ->where(function ($q) {
                       $q->where('master_customers.subtype', 'agent')
                         ->orWhereNotNull('master_customers.sales_agent_id')
                         ->orWhereNotNull('agent_order_dispatches.sales_agent_id');
                   });
            }
        }

        // Filter by Bill No.
        if ($request->filled('bill_no')) {
            $billNo = trim($request->bill_no);
            $q1->where('agent_order_dispatches.bill_no', 'like', "%{$billNo}%");
            $q2->where('order_dispatch.bill_number', 'like', "%{$billNo}%");
            $q3->where('agent_order_dispatches.bill_no', 'like', "%{$billNo}%");
        }

        // Filter by Date
        if ($request->filled('from_date')) {
            $q1->whereDate('agent_order_dispatches.dispatch_date', '>=', $request->from_date);
            $q2->whereDate('order_dispatch.dispatch_date', '>=', $request->from_date);
            $q3->whereDate('agent_order_returns.return_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $q1->whereDate('agent_order_dispatches.dispatch_date', '<=', $request->to_date);
            $q2->whereDate('order_dispatch.dispatch_date', '<=', $request->to_date);
            $q3->whereDate('agent_order_returns.return_date', '<=', $request->to_date);
        }

        // Filter by Item / Dispatch Type
        if ($request->filled('item_type')) {
            if ($request->item_type === 'Corporate') {
                $q1->whereRaw('1 = 0');
                $q3->whereRaw('1 = 0');
            } elseif ($request->item_type === 'Fabric') {
                $q2->whereRaw('1 = 0');
                $fabricFilter = function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('agent_order_dispatch_items')
                        ->join('agent_orders', 'agent_order_dispatch_items.agent_order_id', '=', 'agent_orders.id')
                        ->whereColumn('agent_order_dispatch_items.agent_order_dispatch_id', 'agent_order_dispatches.id')
                        ->where(function ($w) {
                            $w->where('agent_orders.sale_type', 'fabric')
                              ->orWhere('agent_orders.order_type', 'fabric');
                        });
                };
                $q1->whereExists($fabricFilter);
                $q3->whereExists($fabricFilter);
            } elseif ($request->item_type === 'Product') {
                $productFilter = function ($sub) {
                    $sub->select(DB::raw(1))
                        ->from('agent_order_dispatch_items')
                        ->join('agent_orders', 'agent_order_dispatch_items.agent_order_id', '=', 'agent_orders.id')
                        ->whereColumn('agent_order_dispatch_items.agent_order_dispatch_id', 'agent_order_dispatches.id')
                        ->where(function ($w) {
                            $w->where('agent_orders.sale_type', 'item')
                              ->orWhere('agent_orders.order_type', 'item');
                        });
                };
                $q1->whereExists($productFilter);
                $q3->whereExists($productFilter);
            }
        }

        // Filter by Report Type: sales (Sales Only), return (Sales Return Only), both (Both)
        $reportType = $request->get('report_type', 'both');
        if ($reportType === 'sales') {
            $combined = $q1->unionAll($q2);
        } elseif ($reportType === 'return') {
            $combined = $q3;
        } else {
            $combined = $q1->unionAll($q2)->unionAll($q3);
        }

        return DB::table(DB::raw("({$combined->toSql()}) as combined_sales"))
            ->mergeBindings($combined);
    }
}
