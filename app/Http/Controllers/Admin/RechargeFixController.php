<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RechargeDataFixService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * /admin/recharge-fix : lists the adjustment lines booked to Telephone Expense > RECHARGE (really rickshaw expense)
 * and one button moves the ticked lines to General Expense > RIKSHA KHARCH (old values saved to storage/app/recharge-fix/).
 */
class RechargeFixController extends Controller
{
    public function show(RechargeDataFixService $fix)
    {
        try {
            $plan = $fix->run([], false);
        } catch (\Throwable $e) {
            return redirect()->route('admin.payment.adjustment.index')->with('error', 'Recharge repair page: ' . $e->getMessage());
        }
        $runs = collect(Storage::disk('local')->files('recharge-fix'))->sort()->reverse()->values();

        return view('admin.recharge_fix.show', compact('plan', 'runs'));
    }

    public function run(Request $request, RechargeDataFixService $fix)
    {
        try {
            $result = $fix->run((array) $request->input('ids', []), true);
        } catch (\Throwable $e) {
            return redirect()->route('admin.recharge-fix.show')->with('fix_error', 'Nothing was changed: ' . $e->getMessage());
        }

        return redirect()->route('admin.recharge-fix.show')->with('fix_result', [
            'moved' => $result['moved'],
            'amount' => $result['amount'],
            'log_file' => $result['log_file'],
        ]);
    }
}
