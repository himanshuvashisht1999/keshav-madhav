<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\FabricReceipt;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Auth;

class FabricShipmentPaymentController extends Controller
{
    public function create(Request $request)
    {
        $vendors = Vendor::where('status', 1)->get();
        $selectedVendorId = $request->get('vendor_id');
        $selectedReceiptId = $request->get('receipt_id');

        return view('admin.payment.fabric_shipment.create', compact('vendors', 'selectedVendorId', 'selectedReceiptId'));
    }

    public function getShipments(Request $request)
    {
        $vendorId = $request->vendor_id;
        // Fetch shipments for the vendor that have a remaining balance
        $shipments = FabricReceipt::where('vendor_id', $vendorId)
            ->get()
            ->filter(function ($receipt) {
                return $receipt->balance_amount > 0;
            })
            ->values(); // Reset keys for JSON

        // Return JSON to be handled by frontend
        return response()->json([
            'status' => 'success',
            'shipments' => $shipments
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'fabric_receipt_id' => 'required|exists:fabric_receipts,id',
            'amount' => 'required|numeric|min:1',
            'payment_date' => 'required|date',
            'payment_mode' => 'required|string',
        ]);

        $receipt = FabricReceipt::findOrFail($request->fabric_receipt_id);

        if ($request->amount > $receipt->balance_amount + 1) { // Tolerance of 1 for float issues? Or strict? specific requirement not given. keeping strict for now.
            // Actually, let's just warn or allow? usually allow but create warning. For now strict validation.
            return redirect()->back()->with('error', 'Amount cannot be greater than pending balance (' . $receipt->balance_amount . ')');
        }

        try {
            DB::beginTransaction();

            $payment = Payment::create([
                'payment_category' => 'fabric_shipment',
                'payment_type' => 'paid',
                'party_type' => Vendor::class,
                'party_id' => $request->vendor_id,
                'paymentable_type' => FabricReceipt::class,
                'paymentable_id' => $request->fabric_receipt_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_mode' => $request->payment_mode,
                'reference_id' => $request->reference_id,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

            // Handle Image Upload if any
            if ($request->hasFile('image')) {
                $imageName = time() . '.' . $request->image->extension();
                $request->image->move(public_path('assets/payment-images'), $imageName);
                $payment->image = $imageName;
                $payment->save();
            }

            DB::commit();

            return redirect()->back()->with('success', 'Payment recorded successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Something went wrong: ' . $e->getMessage());
        }
    }
}
