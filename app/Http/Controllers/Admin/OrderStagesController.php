<?php
namespace App\Http\Controllers\Admin;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Admin\OrderStagesService as Service;
use App\Requests\Admin\OrderStagesStoreRequest;
use App\Requests\Admin\OrderStagesUpdateRequest;
use Illuminate\Support\Facades\Crypt;
use App\Models\OrderStageTransaction;
use App\Models\OrderProduct;
use App\Models\OrderProductStage;
use PDF;
use Auth;

class OrderStagesController extends Controller { 
    protected $service;
    public function __construct(Service $service) {
        $this->service = $service;
    }
    public function index(Request $request){
        $response['product_stage'] = $this->service->product_stage();
        $response['stage_data'] = $this->service->stage_data($request);
        return view('admin.order_stages.index',$response);
    }
    public function indexList(Request $request){
        return $this->service->indexList($request);
    }

    public function downLoadReceipt(Request $request)
    {
        $transaction = OrderStageTransaction::with(['from_stage', 'to_stage', 'orderProduct.order'])->findOrFail($request->order_transaction_id);

        $orderProduct = $transaction->orderProduct;
        $order = $orderProduct->order;

        $data = [
            'transaction' => $transaction,
            'order' => $order,
            'orderProduct' => $orderProduct,
        ];
        $safeSku = str_replace(['/', '\\'], '_', $transaction->sku);

        $pdf = \PDF::loadView('admin.order_stages.stage_transfer_slip', $data)->setPaper('A4');

        return $pdf->download('StageTransferSlip_'.$safeSku.'.pdf');
    }

    public function getSubStages($order_product_id,$from_stage_id){
        $data               =   $this->service->getSubStages($order_product_id,$from_stage_id);
        $items_details      =   $this->service->getItemDetails($order_product_id);
        $nextProductStage   =   $this->service->nextProductStage($order_product_id,$from_stage_id);
        $nextProductStage   =   $this->service->nextProductStage($order_product_id,$from_stage_id);
        $lot_no             =   $this->service->getLotNo();
        return response()->json(['status' => true, 'data' => $data, 'items_details' => $items_details, 'next_product_stage' => $nextProductStage, 'lot_no' => $lot_no]);
    }
    

}