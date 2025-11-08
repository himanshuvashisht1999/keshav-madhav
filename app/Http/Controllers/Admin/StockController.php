<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\StockService as Service;
use Illuminate\Support\Facades\Crypt;
use App\Models\Stock;
use Illuminate\Support\Facades\DB;
// excel import use
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Auth;
use PDF;

class StockController extends Controller
{
    protected $service;
    public function __construct(Service $service)
    {
        $this->service = $service;
    }
    public function index()
    {
        return view('admin.stock.index');
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function view(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.stock.view', $response);
    }
    public function detail(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.stock.detail', $response);
    }
    public function generateStockReportPDF(Request $request)
    {
       
        $filters = $request->all();

        // Base query
        $query = Stock::query();

        // Filter conditions
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['meter'])) {
            $query->where('meter', 'like', '%' . $filters['meter'] . '%');
        }

        if (!empty($filters['unique_number'])) {
            $query->where('unique_number', 'like', '%' . $filters['unique_number'] . '%');
        }

        if (!empty($filters['batch_no'])) {
            $query->where('batch_no', 'like', '%' . $filters['batch_no'] . '%');
        }

        // Filtered result
        $stocks = $query->get();

        if ($stocks->isEmpty()) {
            return response()->json(['error' => 'No records found for given filters.'], 404);
        }
        // 2️⃣ Array ko HTML string 
        $html = '<h2 style="text-align:center;">Manage Fabric Stock</h2>';
        $html .= '<table border="1" cellspacing="0" cellpadding="6" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>SKU</th>
                            <th>Date</th>
                            <th>Meter</th>
                            <th>Unique No</th>
                            <th>Batch No</th>
                        </tr>
                    </thead>
                    <tbody>';
        $sno = 0;
        foreach ($stocks as $stock) {
            $sno++;
            $html .= '<tr>
                        <td>'.$sno.'</td>
                        <td>'.$stock['sku'].'</td>
                        <td>'.$stock['date'].'</td>
                        <td>'.$stock['meter'].'</td>
                        <td>'.$stock['unique_number'].'</td>
                        <td>'.$stock['batch_no'].'</td>
                    </tr>';
        }
        $html .= '</tbody></table>';

        // PDF generate 
        $pdf = PDF::loadHTML($html);

        
        $fileName = 'Stock_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        // Download
        return $pdf->download($fileName);
    }
    

    public function generateStockReportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Meter');
        $sheet->setCellValue('D1', 'Unique No');
        $sheet->setCellValue('E1', 'Batch No');

         $filters = $request->all();

        // Base query
        $query = Stock::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['meter'])) {
            $query->where('meter', 'like', '%' . $filters['meter'] . '%');
        }

        if (!empty($filters['unique_number'])) {
            $query->where('unique_number', 'like', '%' . $filters['unique_number'] . '%');
        }

        if (!empty($filters['batch_no'])) {
            $query->where('batch_no', 'like', '%' . $filters['batch_no'] . '%');
        }

        // Filtered result
        $stocks = $query->get();

        if ($stocks->isEmpty()) {
            return response()->json(['error' => 'No records found for given filters.'], 404);
        }

        $row = 2;
        foreach ($stocks as $stock) {
            $sheet->setCellValue('A' . $row, $stock['sku']);
            $sheet->setCellValue('B' . $row, $stock['date']);
            $sheet->setCellValue('C' . $row, $stock['meter']);
            $sheet->setCellValue('D' . $row, $stock['unique_number']);
            $sheet->setCellValue('E' . $row, $stock['batch_no']);
            $row++;
        }

        // Save file
        $filePath = storage_path('app/public/stock_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function generateFebricQuantityReportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Total Meter');

        $stocks = Stock::select('sku', DB::raw('SUM(meter) as total_meter'))
            ->groupBy('sku')
            ->get();
            
        if ($stocks->isEmpty()) {
            return response()->json(['error' => 'No records found for given filters.'], 404);
        }

        $row = 2;
        foreach ($stocks as $stock) {
            $sheet->setCellValue('A' . $row, $stock['sku']);
            $sheet->setCellValue('B' . $row, $stock['total_meter']);
            $row++;
        }

        // Save file
        $filePath = storage_path('app/public/stock_quantity_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
