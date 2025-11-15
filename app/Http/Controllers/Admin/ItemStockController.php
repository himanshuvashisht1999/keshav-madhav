<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\ItemStockService as Service;
use App\Services\Admin\Master\ItemAttributeValueService;
use App\Services\Admin\Master\ProductionGoodsService;
use Illuminate\Support\Facades\Crypt;
use App\Models\ItemStock;
use Illuminate\Support\Facades\DB;
// excel import use
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

use Auth;
use PDF;

class ItemStockController extends Controller
{
    protected $service;
    public function __construct(Service $service, ProductionGoodsService $productionGoodsService, ItemAttributeValueService $itemAttributeValueService)
    {
        $this->service = $service;
        $this->productionGoodsService = $productionGoodsService;
        $this->itemAttributeValueService = $itemAttributeValueService;
    }
    public function index(Request $request)
    {   
        if($request->has('id') && !empty($request->id)){
            $response['items'] = $this->itemAttributeValueService->getItemAttributeValueById($request);
        } else {
            $response['items'] = $this->itemAttributeValueService->getItemAttributeValueBySku($request);
        }
        return view('admin.item_stock.index', $response);
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }
    public function view(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.item_stock.view', $response);
    }
    public function detail(Request $request)
    {
        $response['data'] = $this->service->view($request);
        return view('admin.item_stock.detail', $response);
    }
    
    public function itemIndex()
    {
        $response['items'] = $this->productionGoodsService->items();
        return view('admin.item_stock.item_index', $response);
    }

    public function itemIndexList(Request $request){
        return $this->service->itemIndexList($request);
    }

    public function generateStockReportPDF(Request $request)
    {
       
        $filters = $request->all();

        // Base query
        $query = ItemStock::query();

        // Filter conditions
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['quantity'])) {
            $query->where('quantity', 'like', '%' . $filters['quantity'] . '%');
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
        $html = '<h2 style="text-align:center;">Manage Item Stock</h2>';
        $html .= '<table border="1" cellspacing="0" cellpadding="6" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>SKU</th>
                            <th>Date</th>
                            <th>Quantity</th>
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
                        <td>'.getformatDate($stock['date']).'</td>
                        <td>'.$stock['quantity'].'</td>
                        <td>'.$stock['unique_number'].'</td>
                        <td>'.$stock['batch_no'].'</td>
                    </tr>';
        }
        $html .= '</tbody></table>';

        // PDF generate 
        $pdf = PDF::loadHTML($html);

        
        $fileName = 'Item_Stock_report_' . now()->format('Y-m-d_H-i-s') . '.pdf';

        // Download
        return $pdf->download($fileName);
    }
     

    public function generateStockReportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Date');
        $sheet->setCellValue('C1', 'Quantity');
        $sheet->setCellValue('D1', 'Unique No');
        $sheet->setCellValue('E1', 'Batch No');

         $filters = $request->all();

        // Base query
        $query = ItemStock::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['date'])) {
            $query->whereDate('date', $filters['date']);
        }

        if (!empty($filters['quantity'])) {
            $query->where('quantity', 'like', '%' . $filters['quantity'] . '%');
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
            $sheet->setCellValue('B' . $row, getformatDate($stock['date']));
            $sheet->setCellValue('C' . $row, $stock['quantity']);
            $sheet->setCellValue('D' . $row, $stock['unique_number']);
            $sheet->setCellValue('E' . $row, $stock['batch_no']);
            $row++;
        }

        // Save file
        $filePath = storage_path('app/public/item_stock_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function generateItemQuantityReportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Total Quantity');

        $stocks = ItemStock::select('sku', DB::raw('SUM(quantity) as total_quantity'))
            ->groupBy('sku')
            ->get();
            
        if ($stocks->isEmpty()) {
            return response()->json(['error' => 'No records found for given filters.'], 404);
        }

        $row = 2;
        foreach ($stocks as $stock) {
            $sheet->setCellValue('A' . $row, $stock['sku']);
            $sheet->setCellValue('B' . $row, $stock['total_quantity']);
            $row++;
        }

        // Save file
        $filePath = storage_path('app/public/item_stock_quantity_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
