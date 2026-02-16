<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class OtherPaymentController extends Controller
{
    public function create()
    {
        $employees = Employee::where('status', 1)->orderBy('name')->get();
        return view('admin.payment.other.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'payee_name' => 'required_without:employee_id|nullable|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
            'payment_type' => 'required|in:received,paid',
        ]);

        try {
            DB::beginTransaction();

            $payment = new Payment();
            $payment->payment_category = 'other';
            $payment->payment_type = $request->payment_type;
            $payment->amount = $request->amount;
            $payment->payment_date = $request->payment_date;
            $payment->payment_mode = $request->payment_mode;
            $payment->reference_id = $request->reference_id;
            $payment->remarks = $request->remarks;
            $payment->created_by = auth()->id();

            if ($request->employee_id) {
                // If Employee selected
                $employee = Employee::findOrFail($request->employee_id);
                $payment->party_type = 'App\Models\Employee';
                $payment->party_id = $employee->id;
                $payment->payee_name = $employee->name; // Optional: store name redundantly or leave null
            } else {
                // If Manual Payee Name
                $payment->payee_name = $request->payee_name;
            }

            $payment->save();

            DB::commit();

            return redirect()->back()->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
