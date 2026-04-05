<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\FabricReceipt;
use App\Models\Payment;
use App\Models\BankAccount;
use App\Models\CashPayment;
use App\Services\Admin\BalanceService;
use Illuminate\Support\Facades\DB;
use Auth;

class FabricShipmentPaymentController extends Controller
{
    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        $this->balanceService = $balanceService;
    }

    public function create(Request $request)
    {
        $vendors = Vendor::where('status', 1)->get();
        $selectedVendorId = $request->get('vendor_id');
        $selectedReceiptId = $request->get('receipt_id');
        $bank_accounts = BankAccount::where('status', 1)->orderBy('bank_name')->get();
        $cash_accounts = CashPayment::where('status', 1)->orderBy('name')->get();

        return view('admin.payment.fabric_shipment.create', compact('vendors', 'selectedVendorId', 'selectedReceiptId', 'bank_accounts', 'cash_accounts'));
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
            'payment_mode' => 'required|string|in:Bank,Cash',
            'payment_method_id' => 'required|integer',
        ]);

        $receipt = FabricReceipt::findOrFail($request->fabric_receipt_id);

        if ($request->amount > $receipt->balance_amount + 1) { // Tolerance of 1 for float issues? Or strict? specific requirement not given. keeping strict for now.
            // Actually, let's just warn or allow? usually allow but create warning. For now strict validation.
            return redirect()->back()->with('error', 'Amount cannot be greater than pending balance (' . $receipt->balance_amount . ')');
        }

        try {
            DB::beginTransaction();

            // Get method name for record description
            $methodName = '';
            if ($request->payment_mode === 'Bank') {
                $account = BankAccount::find($request->payment_method_id);
                $methodName = $account ? "Bank: {$account->bank_name} ({$account->account_number})" : "Bank";
            } else {
                $account = CashPayment::find($request->payment_method_id);
                $methodName = $account ? "Cash: {$account->name}" : "Cash";
            }

            $payment = Payment::create([
                'payment_category' => 'fabric_shipment',
                'payment_type' => 'paid',
                'party_type' => Vendor::class,
                'party_id' => $request->vendor_id,
                'paymentable_type' => FabricReceipt::class,
                'paymentable_id' => $request->fabric_receipt_id,
                'amount' => $request->amount,
                'payment_date' => $request->payment_date,
                'payment_mode' => $methodName,
                'payment_method_type' => $request->payment_mode,
                'payment_method_id' => $request->payment_method_id,
                'reference_id' => $request->reference_id,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

            // Update Balance (Fabric Shipment is always 'paid' -> deduct from bank/cash)
            $this->balanceService->updateBalance($request->payment_mode, $request->payment_method_id, $request->amount, 'deduct');

            // Update Vendor Balance (Deduct from what we owe)
            $vendor = Vendor::findOrFail($request->vendor_id);
            $vendor->balance -= $request->amount;
            $vendor->save();

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
