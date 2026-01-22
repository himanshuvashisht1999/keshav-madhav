<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionSlipDigitization;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\MasterProductStage;
use App\Models\StageMasterUnit;
use App\Models\OrderLot;
use App\Models\FabricRollAssigning;
use App\Models\OrderStageTransaction;
use App\Models\OrderPrintingStageTransaction;
use App\Models\OrderPrintingStageTransactionDetail;
use App\Models\OrderPrintingToStichingTransaction;
use App\Models\OrderPrintingToStichingTransactionDetail;

class UploadedSlipsController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionSlipDigitization::orderBy('id', 'desc')->where('status', '!=', 3);

        if ($request->filled('from_stage_id')) {
            $query->where('from_stage_id', $request->from_stage_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('stage_master_unit_id')) {
            $query->where('stage_master_unit_id', $request->stage_master_unit_id);
        }

        $slips = $query->paginate(20);
        $stages = MasterProductStage::where('status', 1)->get();
        $units = StageMasterUnit::where('status', 1)->get();

        return view('admin.uploaded_slips.index', compact('slips', 'stages', 'units'));
    }

    public function show($slipId)
    {
        $data = $this->getSlipData($slipId);
        return view('admin.uploaded_slips.show', $data);
    }
    public function download($slipId)
    {
        $data = $this->getSlipData($slipId);
        $pdf = Pdf::loadView('admin.uploaded_slips.pdf', $data)
        ->setPaper('A4', 'portrait');

        return $pdf->download('Production_Slip_'.$slipId.'.pdf');
    }

    public function getSlipData($slipId){
        $slip = ProductionSlipDigitization::with([
            'fromStage',
            'getUnitMaster.masterFabricWarehouse'
        ])->findOrFail($slipId);

        $data = [
            'slip' => $slip,
            'lot' => null,
            'rolls' => collect(),
            'printing' => null,
            'printing_sizes' => collect(),
        ];

        /* -----------------------------
        * 2️⃣ SWITCH BY SAVE TYPE
        * ----------------------------- */
        switch ($slip->save_type) {

            /* =====================================================
            * 🟢 TYPE 1 → LOT / ROLLS ALLOT
            * ===================================================== */
            case 1:

                // One slip = one lot
                $lot = OrderLot::where('production_slip_digitization_id', $slip->id)
                    ->with([
                        'orderMain',
                        'orderProductSet.fabric',
                        'orderProductSet.colors',
                        'orderProductSet.master_design_pattern',
                        'orderProductSet.master_product_fitting',
                    ])
                    ->first();

                if ($lot) {
                    $rolls = FabricRollAssigning::where(
                            'production_slip_digitization_id',
                            $slip->id
                        )
                        ->with([
                            'fabricRollAssigningsDetail',
                            'stageMasterUnit.masterFabricWarehouse'
                        ])
                        ->get();

                    $data['lot'] = $lot;
                    $data['rolls'] = $rolls;
                }
                break;

            /* =====================================================
            * 🔵 TYPE 2 → PRINTING
            * ===================================================== */
            case 2:

                $printing = OrderPrintingStageTransaction::where(
                        'production_slip_digitization_id',
                        $slip->id
                    )
                    ->with('from_stage', 'to_stage')
                    ->first();

                if ($printing) {
                    $printingSizes = OrderPrintingStageTransactionDetail::where(
                            'order_printing_stage_transaction_id',
                            $printing->id
                        )->get();

                    $data['printing'] = $printing;
                    $data['printing_sizes'] = $printingSizes;
                }
                break;

            /* =====================================================
            * 🟠 TYPE 3 → OTHER (STITCHING / HAND SLIP)
            * ===================================================== */
            case 3:
                if($slip->from_stage_id == 1){
                    $stageTransaction = OrderPrintingToStichingTransaction::where(
                            'production_slip_digitization_id',
                            $slip->id
                        )
                        ->with('from_stage', 'to_stage')
                        ->first();

                    if ($stageTransaction) {
                        $stageSizes = OrderPrintingToStichingTransactionDetail::where(
                                'order_printing_to_stiching_transaction_id',
                                $stageTransaction->id
                            )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_sizes'] = $stageSizes;
                    }
                }else{
                    
                
                    $stageTransaction = OrderStageTransaction::where(
                            'production_slip_digitization_id',
                            $slip->id
                        )
                        ->with('from_stage', 'to_stage')
                        ->first();

                    if ($stageTransaction) {
                        $stageSizes = \App\Models\OrderStageTransactionDetail::where(
                                'order_stage_transaction_id',
                                $stageTransaction->id
                            )->get();

                        $data['stage_transaction'] = $stageTransaction;
                        $data['stage_sizes'] = $stageSizes;
                    }
                }
                break;
        }
        return $data;
    }




    public function destroy($id)
    {
        $slip = ProductionSlipDigitization::findOrFail($id);
        
        // Optional: Delete physical file if needed
        // if(file_exists(public_path($slip->slip_file))) {
        //     unlink(public_path($slip->slip_file));
        // }

        $slip->delete();

        return redirect()->back()->with('success', 'Slip deleted successfully.');
    }
}
