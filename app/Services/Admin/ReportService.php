<?php

namespace App\Services\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Http\DataTable\Admin\ReportDataTable as DataTable;


class ReportService {
    public function __construct(
        DataTable $datatable,
    ) {
        $this->datatable= $datatable;
    }

    public function fabricReceiptList(Request $request){
        return $this->datatable->fabricReceiptList($request);
    }

    public function purchaseOrderList(Request $request){
        return $this->datatable->purchaseOrderList($request);
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
    
    public function stagesList(){
        return $this->datatable->stagesList($request);
    }
    public function vendors(){
        $data = Vendor::where('status',1)->get();
        return $data;
    }


}