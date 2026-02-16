<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Employee;
use Illuminate\Support\Facades\DB;

class SalaryPaymentController extends Controller
{
    public function create()
    {
        $employees = Employee::where('status', 1)->orderBy('name')->get();
        return view('admin.payment.salary.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        try {
            DB::beginTransaction();

            $employee = Employee::findOrFail($request->employee_id);

            $payment = new Payment();
            $payment->payment_category = 'salary';
            $payment->payment_type = 'paid';
            $payment->party_type = 'App\Models\Employee';
            $payment->party_id = $employee->id;
            $payment->paymentable_type = 'App\Models\Employee';
            $payment->paymentable_id = $employee->id;
            $payment->amount = $request->amount;
            $payment->payment_date = $request->payment_date;
            $payment->payment_mode = $request->payment_mode;
            $payment->reference_id = $request->reference_id;
            $payment->remarks = $request->remarks;
            $payment->created_by = auth()->id();
            $payment->save();

            DB::commit();

            return redirect()->back()->with('success', 'Salary Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
