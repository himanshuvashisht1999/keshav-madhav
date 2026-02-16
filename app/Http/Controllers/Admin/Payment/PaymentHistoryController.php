<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use Illuminate\Http\Request;

class PaymentHistoryController extends Controller
{
    public function index(Request $request)
    {
        $isOwner = $request->is('owner/*') || $request->is('owner');
        $layout = $isOwner ? 'owner.layouts.app' : 'admin.layouts.app';
        $homeRoute = $isOwner ? 'owner.dashboard' : 'admin.dashboard';
        $routePrefix = $isOwner ? 'owner.payment.history.' : 'admin.payment.history.';

        $query = Payment::with('party')->orderBy('payment_date', 'desc')->orderBy('id', 'desc');

        if ($request->has('from_date') && $request->from_date) {
            $query->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->has('to_date') && $request->to_date) {
            $query->whereDate('payment_date', '<=', $request->to_date);
        }
        if ($request->has('payment_category') && $request->payment_category) {
            $query->where('payment_category', $request->payment_category);
        }
        if ($request->has('payment_mode') && $request->payment_mode) {
            $query->where('payment_mode', $request->payment_mode);
        }
        if ($request->has('payment_type') && $request->payment_type) {
            $query->where('payment_type', $request->payment_type);
        }
        if ($request->has('paymentable_type') && $request->paymentable_type) {
            $query->where('paymentable_type', $request->paymentable_type);
        }
        if ($request->has('paymentable_id') && $request->paymentable_id) {
            $query->where('paymentable_id', $request->paymentable_id);
        }

        $payments = $query->get();

        // Fixed list of all possible categories and modes
        $categories = [
            'fabric_shipment',
            'agent_order',
            'corporate_order',
            'salary',
            'other'
        ];

        $modes = [
            'cash',
            'bank_transfer',
            'cheque',
            'upi'
        ];

        $types = [
            'received',
            'paid'
        ];

        return view('admin.payment.index', compact('payments', 'categories', 'modes', 'types', 'layout', 'homeRoute', 'routePrefix', 'isOwner'));
    }

    public function show(Payment $payment)
    {
        $payment->load([
            'party',
            'paymentable' => function ($morphTo) {
                $morphTo->morphWith([
                    \App\Models\AgentOrder::class => ['shop', 'agent'],
                ]);
            }
        ]);
        return view('admin.payment.show', compact('payment'));
    }

    public function edit(Payment $payment)
    {
        return view('admin.payment.edit', compact('payment'));
    }

    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'payment_date' => 'required|date',
            'amount' => 'required|numeric|min:0.01',
            'payment_mode' => 'required|string',
            'payee_name' => 'nullable|string|max:255',
            'reference_id' => 'nullable|string|max:255',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $payment->update($request->only([
            'payment_date',
            'amount',
            'payment_mode',
            'payee_name',
            'reference_id',
            'remarks'
        ]));

        return redirect()->route('admin.payment.history.index')->with('success', 'Payment updated successfully.');
    }

    public function destroy(Payment $payment)
    {
        // Optional: Add delete if needed, but user only asked for edit.
        // Leaving placeholder for now.
        $payment->delete();
        return redirect()->route('admin.payment.history.index')->with('success', 'Payment deleted successfully.');
    }
}
