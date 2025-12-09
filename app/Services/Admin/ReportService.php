<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Http\DataTable\Admin\ReportDataTable as DataTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use App\Models\OrderMain;
use App\Models\Package;

use App\Exports\ProductionDetailExport;
use Maatwebsite\Excel\Facades\Excel;
class ReportService {
    public function __construct(
        DataTable $datatable, 
    ) {
        $this->datatable= $datatable;
    }

    public function fabricReceiptList(Request $request){
        return $this->datatable->fabricReceiptList($request);
    }

    public function itemReceiptList(Request $request){
        return $this->datatable->itemReceiptList($request);
    }

    public function purchaseOrderList(Request $request){
        return $this->datatable->purchaseOrderList($request);
    }

    public function itemPurchaseOrderList(Request $request){
        return $this->datatable->itemPurchaseOrderList($request);
    }
    public function itemStockSkuList(Request $request){
        return $this->datatable->itemStockSkuList($request);
    }
    public function itemStockList(Request $request){
        return $this->datatable->itemStockList($request);
    }
    public function fabricStockList(Request $request){
        return $this->datatable->fabricStockList($request);
    }

    public function fabricStockSkuList(Request $request){
        return $this->datatable->fabricStockSkuList($request);
    }
    public function productionList(Request $request){
        return $this->datatable->productionList($request);
    }
    
    public function stagesList(Request $request){
        return $this->datatable->stagesList($request);
    }
    public function vendors(){
        $data = Vendor::where('status',1)->get();
        return $data;
    }

    public function generateProductionExcelSingle(Request $request)
    {
        $order_main_id = $request->id;

        $orderMain = OrderMain::with([
            'customer',
            'orders.products.product_details.product_detail_stocks',
            'orders.products.order_stages.stage',
            'orders.products.order_stage_trnsactions',
            'packages.package_boxes.package_boxes_items',
        ])->findOrFail($order_main_id);

        $fileName = 'production-detail-' . ($orderMain->sku ?? $orderMain->id) . '.xls';

        // Render Blade view into HTML
        $html = view('admin.reports.production_detail_excel', compact('orderMain'))->render();

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    public function productionDetail(Request $request){
        $data = OrderMain::with([
            'customer',
            'orders.products.product_details.product_detail_stocks',
            'orders.products.order_stages.stage',
            'orders.products.order_stage_trnsactions',
            'package.package_boxes.package_boxes_items',
        ])->findOrFail($request->id);

        return $data;
        
    }


}