<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\{
    ReportService as Service
};

class ReportController extends Controller
{
    protected $service;
    public function __construct(
        Service $service, 
    ) {
        $this->service = $service;
    }

    public function salesOrder(Request $request)
    {
        $response['data'] = $this->service->salesOrder($request);
        return view('admin.report.sales_order',$response);
    }
    public function stock(Request $request)
    {
        $response['data'] = $this->service->stock($request);
        $response['warehouses'] = $this->service->warehouses();
        $response['fabrics'] = $this->service->fabrics();
        $response['filters'] = $request->all();
        return view('admin.report.stock',$response);
    }
    public function fabricRollDetails(Request $request)
    {
        return $this->service->fabricRollDetails(
            $request->fabric_sku,
            $request->warehouse_id
        );
    }

    public function purchaseOrder(Request $request)
    {
        $response['data'] = $this->service->purchaseOrder($request);
        $response['fabrics'] = $this->service->fabrics();
        $response['filters'] = $request->all();
        return view('admin.report.purchase_order',$response);
    }
    public function purchaseOrderItemDetails(Request $request)
    {
        return $this->service->purchaseOrderItemReceipts(
            $request->purchase_order_item_id
        );
    }

    public function orderTrackingSystem(Request $request)
    {
        $response['data'] = $this->service->orderTrackingSystem($request);
        return view('admin.report.order_tracking',$response);
    }

    public function lotTrackingDetails(Request $request)
    {
        $lot_no = $request->lot_no;

        $tracking = $this->service->lotTrackingDetails($request);

        $currentStage = $tracking->last();
        return response()->json([
            'current_stage' => $currentStage?->stage_name,
            'data' => $tracking
        ]);
    }

    public function dispatchOrder(Request $request)
    {
        $response['data'] = $this->service->dispatchOrder($request);
        return view('admin.report.dispatch_order',$response);
    }

}
