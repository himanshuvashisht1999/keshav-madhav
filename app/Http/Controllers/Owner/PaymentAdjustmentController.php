<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\PaymentAdjustment;
use Illuminate\Http\Request;

class PaymentAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = PaymentAdjustment::with(['master'])->latest()->get();
        
        // Group by batch_id, if null use unique key
        $grouped = $adjustments->groupBy(function($item) {
            return $item->batch_id ?? 'unique_' . $item->id;
        });

        return view('owner.reports.payment_adjustments', compact('grouped'));
    }

    public function show($batchId)
    {
        if (str_starts_with($batchId, 'unique_')) {
            $id = str_replace('unique_', '', $batchId);
            $adjustments = PaymentAdjustment::with(['master'])->where('id', $id)->get();
        } else {
            $adjustments = PaymentAdjustment::with(['master'])->where('batch_id', $batchId)->get();
        }

        if ($adjustments->isEmpty()) {
            abort(404);
        }

        return view('owner.reports.payment_adjustment_details', compact('adjustments', 'batchId'));
    }
}
