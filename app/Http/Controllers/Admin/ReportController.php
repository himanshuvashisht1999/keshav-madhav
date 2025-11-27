<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\{
    ReportService as Service,
    ProductOrderService,
    OrderStagesService,
    Master\ProductionGoodsService,
    Master\FabricService
};
use App\Models\{
    FabricReceipt,
    PurchaseOrder,
    Stock,
    Fabric,
    Order,
    OrderStageTransaction
};
use Illuminate\Support\Facades\DB;

// excel import use
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReportController extends Controller
{
    protected $service;
    public function __construct(
        Service $service, 
        ProductOrderService $productOrderService, 
        OrderStagesService $orderStagesService, 
        ProductionGoodsService $productionGoodsService, 
        FabricService $fabricService
    ) {
        $this->service = $service;
        $this->productOrderService = $productOrderService;
        $this->orderStagesService = $orderStagesService;
        $this->productionGoodsService = $productionGoodsService;
        $this->fabricService = $fabricService;
    }
    public function fabricReceipt()
    {
        $response['vendors'] = $this->service->vendors();
        return view('admin.reports.fabric_receipt',$response);
    }

    public function generateFabricReceiptExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Vendor');
        $sheet->setCellValue('C1', 'Truck Number');
        $sheet->setCellValue('D1', 'Date & Time');
        $sheet->setCellValue('E1', 'Packet');
        $sheet->setCellValue('F1', 'Received By');

        $filters = $request->all();

        // Base query
        $query = FabricReceipt::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        if (!empty($filters['truck_number'])) {
            $query->where('truck_number', 'like', '%' . $filters['truck_number'] . '%');
        }
        if (!empty($filters['time'])) {
            $query->whereDate('created_at', $filters['time']);
        }
        if (!empty($filters['roll'])) {
            $query->where('roll', 'like', '%' . $filters['roll'] . '%');
        }
        if (!empty($filters['received_by'])) {
            $query->where('received_by', 'like', '%' . $filters['received_by'] . '%');
        }
        $query->orderBy('id','desc');
        $query->where('status',1);

        // Filtered result
        $fabricReceipts = $query->get();

        if (!$fabricReceipts->isEmpty()) {
        
            $row = 2;
            foreach ($fabricReceipts as $fabricReceipt) {
                $sheet->setCellValue('A' . $row, $fabricReceipt['sku']);
                $sheet->setCellValue('B' . $row, $fabricReceipt['vendor']?->name);
                $sheet->setCellValue('C' . $row, $fabricReceipt['truck_number']);
                $sheet->setCellValue('D' . $row, getformatDateTime($fabricReceipt['time']));
                $sheet->setCellValue('E' . $row, $fabricReceipt['roll']);
                $sheet->setCellValue('F' . $row, $fabricReceipt['received_by']);
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/fabric_receipt_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
    public function fabricReceiptList(Request $request){
        return $this->service->fabricReceiptList($request);
    }

    public function purchaseOrder()
    {   
        $response['vendors'] = $this->service->vendors();
        return view('admin.reports.purchase_order', $response);
    }

    public function purchaseOrderList(Request $request)
    {
        return $this->service->purchaseOrderList($request);
    }

    public function generatePurchaseOrderExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Purchase Order Date');
        $sheet->setCellValue('C1', 'Vendor');
        $sheet->setCellValue('D1', 'Expected Delivery Date');

        $filters = $request->all();

        // Base query
        $query = purchaseOrder::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        
        if (!empty($filters['date'])) {
            $query->where('date', $filters('date'));
        }
    
        if (!empty($filters['delivery_date'])) {
            $query->where('delivery_date', $filters('delivery_date'));
        }
        $query->orderBy('id','desc');
        

        // Filtered result
        $purchaseOrders = $query->get();

        if (!$purchaseOrders->isEmpty()) {
            
            $row = 2;
            foreach ($purchaseOrders as $purchaseOrder) {
                
                $sheet->setCellValue('A' . $row, $purchaseOrder['sku']);
                $sheet->setCellValue('B' . $row, getformatDate($purchaseOrder['date']));
                $sheet->setCellValue('C' . $row, $purchaseOrder['vendor']?->name);
                $sheet->setCellValue('D' . $row, getformatDate($purchaseOrder['delivery_date']));
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/purchase_order_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function excelPurchaseOrderSingle(Request $request)
    {   
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Purchase Order Date');
        $sheet->setCellValue('C1', 'Vendor');
        $sheet->setCellValue('D1', 'Expected Delivery Date');

        $filters = $request->all();
        
        // Base query
        $query = purchaseOrder::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        
        if (!empty($filters['date'])) {
            $query->where('date', $filters('date'));
        }
    
        if (!empty($filters['delivery_date'])) {
            $query->where('delivery_date', $filters('delivery_date'));
        }
        $query->orderBy('id','desc');
        

        // Filtered result
        $purchaseOrders = $query->get();

        if (!$purchaseOrders->isEmpty()) {
            
            $row = 2;
            foreach ($purchaseOrders as $purchaseOrder) {
                
                $sheet->setCellValue('A' . $row, $purchaseOrder['sku']);
                $sheet->setCellValue('B' . $row, getformatDate($purchaseOrder['date']));
                $sheet->setCellValue('C' . $row, $purchaseOrder['vendor']?->name);
                $sheet->setCellValue('D' . $row, getformatDate($purchaseOrder['delivery_date']));
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/purchase_order_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function generateEachPurchaseOrderExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Purchase Order Date');
        $sheet->setCellValue('C1', 'Vendor');
        $sheet->setCellValue('D1', 'Expected Delivery Date');

        $filters = $request->all();

        // Base query
        $query = purchaseOrder::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }
        
        if (!empty($filters['date'])) {
            $query->where('date', $filters('date'));
        }
    
        if (!empty($filters['delivery_date'])) {
            $query->where('delivery_date', $filters('delivery_date'));
        }
        $query->orderBy('id','desc');
        

        // Filtered result
        $purchaseOrders = $query->get();

        if (!$purchaseOrders->isEmpty()) {
            
            $row = 2;
            foreach ($purchaseOrders as $purchaseOrder) {
                
                $sheet->setCellValue('A' . $row, $purchaseOrder['sku']);
                $sheet->setCellValue('B' . $row, getformatDate($purchaseOrder['date']));
                $sheet->setCellValue('C' . $row, $purchaseOrder['vendor']?->name);
                $sheet->setCellValue('D' . $row, getformatDate($purchaseOrder['delivery_date']));
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/purchase_order_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function fabricStock()
    {   
        $response['fabrics'] = $this->productionGoodsService->fabrics();
        return view('admin.reports.fabric_stock', $response);
    }

    public function fabricStockDetails(Request $request)
    {      
        $response['fabrics'] = $this->fabricService->getFabricById($request);
        return view('admin.reports.fabric_stock_details', $response);
    }

    public function fabricStockSku()
    {   
        $response['fabrics'] = $this->productionGoodsService->fabrics();
        return view('admin.reports.fabric_stock', $response);
    }
    
    public function fabricStockList(Request $request)
    {
        return $this->service->fabricStockList($request);
    }

    public function fabricStockSkuList(Request $request)
    {
        return $this->service->fabricStockSkuList($request);
    }
    public function generateFabricStockExcel(Request $request)
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
            $query->where('date', $filters('date'));
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

        $query->orderBy('id','desc');
        

        // Filtered result
        $stocks = $query->get();

        if (!$stocks->isEmpty()) {
        
            $row = 2;
            foreach ($stocks as $stock) {
                $sheet->setCellValue('A' . $row, $stock['sku']);
                $sheet->setCellValue('B' . $row, getformatDate($stock['date']));
                $sheet->setCellValue('C' . $row, $stock['meter']);
                $sheet->setCellValue('D' . $row, $stock['unique_number']);
                $sheet->setCellValue('E' . $row, $stock['batch_no']);
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/'. $stock['sku'] .'_stock_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

     public function generateFabricStockSkuExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Total Fabric (Meters)');

        $filters = $request->all();

        $query = Fabric::withSum('stocks as total_meter', 'meter');

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }
        
        $query->orderBy('id','desc');
        

        // Filtered result
        $stocks = $query->get();

        if (!$stocks->isEmpty()) {
        
            $row = 2;
            foreach ($stocks as $stock) {
                $sheet->setCellValue('A' . $row, $stock['sku']);
                $sheet->setCellValue('B' . $row, $stock['total_meter'] ?? 0);
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/stock_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }

    public function production()
    {
        // $response['products'] = $this->service->products();
        $response['customers'] = $this->productOrderService->customers();
        return view('admin.reports.production', $response);
    }

    public function productionList(Request $request)
    {
        return $this->service->productionList($request);
    }

    public function generateProductionExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'SKU');
        $sheet->setCellValue('B1', 'Customer');
        $sheet->setCellValue('C1', 'Order Date');
        $sheet->setCellValue('D1', 'Expected Delivery Date');
        $sheet->setCellValue('E1', 'Status');

        $filters = $request->all();

        // Base query
        $query = Order::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }
        
        if (!empty($filters['date'])) {
            $query->where('date', $filters['date']);
        }
        
        if (!empty($filters['master_customer_id'])) {
            $query->where('master_customer_id', $filters['master_customer_id']);
        }
        if (!empty($filters['created_at'])) {
            $query->where('created_at', 'like', "%{$filters['created_at']}%");
        }
        if (!empty($filters['expected_delivery_date'])) {
            $query->where('expected_delivery_date', 'like', "%{$filters['expected_delivery_date']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $query->orderBy('id','desc');
        

        // Filtered result
        $orders = $query->get();

        if (!$orders->isEmpty()) {
        

            $row = 2;
            foreach ($orders as $order) {
                $sheet->setCellValue('A' . $row, $order['sku']);
                $sheet->setCellValue('B' . $row, $order->customer?->name);
                $sheet->setCellValue('C' . $row, $order['created_at'] ? getformatDateTime($order['created_at']) : '-');
                $sheet->setCellValue('D' . $row, getformatDate($order['expected_delivery_date']));
                $statusText = '';
                if ($order['status'] == 1) {
                    $statusText = 'Not Issued';
                } elseif ($order['status'] == 3) {
                    $statusText = 'Completed';
                } else {
                    $statusText = 'In Progress';
                }   
                $sheet->setCellValue('E' . $row, $statusText);
                
                $row++;
            }
        }
        // Save file
        $filePath = storage_path('app/public/production_report_' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
    public function stages(Request $request)
    {
        $response['product_stage'] = $this->orderStagesService->product_stage();
        $response['stage_data'] = $this->orderStagesService->stage_data($request);
        return view('admin.reports.stages', $response);
    }
    
    public function stagesList()
    {
        return $this->service->stagesList($request);
    }

    public function generateStagesReportExcel(Request $request)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'Order No');
        $sheet->setCellValue('B1', 'Product SKU');
        $sheet->setCellValue('C1', 'From Stage');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->setCellValue('E1', 'Remain');
        $sheet->setCellValue('F1', 'Status');
        $sheet->setCellValue('G1', 'Received');
        $sheet->setCellValue('H1', 'Delivered');

        $filters = $request->all();

        // Base query
        $query = OrderStageTransaction::query();

        // Filter conditions 
        if (!empty($filters['sku'])) {
            $query->where('sku', 'like', '%' . $filters['sku'] . '%');
        }

        if (!empty($filters['order_product_id'])) {
            $query->whereHas('orderProduct', function ($q) use ($request) {
                $q->where('product_sku', 'like', '%' . $filters('order_product_id') . '%');
            });
        }
        if (!empty($filters['quantity'])) {
            $query->where('quantity', $filters('quantity'));
        }

        if (!empty($filters['remaining_quantity'])) {
            $query->where('remaining_quantity', $filters('remaining_quantity'));
        }
        if (!empty($filters['from_stage_id'])) {
            $query->where('from_stage_id', $filters('from_stage_id'));
        }
        if (!empty($filters['created_at'])) {
            $query->where('created_at', 'like', "%{$filters('created_at')}%");
        }
        if (!empty($filters['updated_at'])) {
            $query->where('updated_at', 'like', "%{$filters('updated_at')}%");
        }
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'in_progress') {
                $query->where('remaining_quantity', '>', 0);
            } elseif ($filters['status'] === 'completed') {
                $query->where('remaining_quantity', '=', 0);
            }
        }
        $query->where('to_stage_id',$filters['stage_id']);
        $query->orderBy('id','desc');
        

        // Filtered result
        $OrderProductStages = $query->get();

        if (!$OrderProductStages->isEmpty()) {
            $row = 2;
            foreach ($OrderProductStages as $order) {
                $sheet->setCellValue('A' . $row, $order['sku']);
                $sheet->setCellValue('B' . $row, $order->orderProduct?->product_sku);
                $from_stage = $order['from_stage_id'] == 0 ? 'Stock' : $order->from_stage?->name;
                $sheet->setCellValue('C' . $row, $from_stage);
                $sheet->setCellValue('D' . $row, $order['quantity']);
                $sheet->setCellValue('E' . $row, $order['remaining_quantity']);
                $statusText = $order['remaining_quantity'] > 0 ? 'In Progress' : 'Completed';   
                $sheet->setCellValue('F' . $row, $statusText);
                $sheet->setCellValue('G' . $row, $order['created_at'] ? getformatDateTime($order['created_at']) : '-');
                
                $delivered = $order['remaining_quantity'] == 0 ? getformatDateTime($order['updated_at']) : 'In Progress';
                $sheet->setCellValue('H' . $row, $delivered);
                $row++;
            }
        }
        // Save file
        $product_stage = $this->orderStagesService->stage_data($request);
        $filePath = storage_path('app/public/'. str_replace(' ', '-', $product_stage['name']) .'-stage-report-' . now()->format('Y-m-d_H-i-s') . '.xlsx');
        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);

        // Return file as download
        return response()->download($filePath)->deleteFileAfterSend(true);
    }
}
