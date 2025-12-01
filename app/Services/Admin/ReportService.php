<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Http\DataTable\Admin\ReportDataTable as DataTable;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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

    public function generateProductionExcelSingle(Request $request){
        $order_main_id = $request->id;
        $order_data = OrderMain::with('order_products.product_details')->where('id',$order_main_id)->first();

        dd($order_data);

    }


}