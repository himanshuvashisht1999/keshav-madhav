<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\FabricReturn;
use App\Models\FabricReturnDetail;
use App\Models\FabricReceipt;
use App\Models\FabricReceiptDetail;
use App\Models\Vendor;
use App\Http\DataTable\Admin\FabricReturnDataTable;
use Illuminate\Support\Facades\DB;
use PDF;

class FabricReturnController extends Controller
{
    protected $dataTable;

    public function __construct(FabricReturnDataTable $dataTable)
    {
        $this->dataTable = $dataTable;
    }

    public function index(Request $request)
    {
        $vendors = Vendor::orderBy('name', 'asc')->get();
        return view('admin.fabric_return.index', compact('vendors'));
    }

    public function indexList(Request $request)
    {
        return $this->dataTable->indexList($request);
    }

    public function create(Request $request)
    {
        $vendors = Vendor::orderBy('name', 'asc')->get();
        $selectedVendorId = $request->get('vendor_id');
        return view('admin.fabric_return.create', compact('vendors', 'selectedVendorId'));
    }

    public function getVendorAvailableRolls($vendorId)
    {
        try {
            $vendor = Vendor::find($vendorId);
            if (!$vendor) {
                return response()->json(['success' => false, 'message' => 'Vendor not found.'], 404);
            }

            $rolls = FabricReceiptDetail::with([
                'fabric_receipt:id,sku,shipment_id,bill_no,time,vendor_id,master_fabric_warehouse_id',
                'fabric:id,name,sku',
                'master_fabric_warehouse:id,cutting_master_name'
            ])
            ->whereHas('fabric_receipt', function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId);
            })
            ->where('remaining_quantity', '>', 0)
            ->where('status', '!=', 2)
            ->orderBy('fabric_receipt_id', 'desc')
            ->orderBy('id', 'asc')
            ->get();

            $formatted = $rolls->map(function ($roll) {
                $receipt = $roll->fabric_receipt;
                $whName = $roll->master_fabric_warehouse->cutting_master_name
                    ?? ($receipt->master_fabric_warehouse->cutting_master_name ?? 'N/A');

                return [
                    'id' => $roll->id,
                    'roll_number' => $roll->roll_number ?: ('#' . $roll->id),
                    'fabric_id' => $roll->fabric_id,
                    'fabric_name' => $roll->fabric->name ?? 'N/A',
                    'fabric_sku' => $roll->fabric->sku ?? ($roll->fabric_sku ?? ''),
                    'receipt_id' => $roll->fabric_receipt_id,
                    'shipment_sku' => $receipt->shipment_id ?: ($receipt->sku ?: 'N/A'),
                    'bill_no' => $receipt->bill_no ?: '-',
                    'receipt_date' => $receipt->time ? getformatDate($receipt->time) : '-',
                    'warehouse' => $whName,
                    'total_meter' => (float) $roll->meter,
                    'remaining_quantity' => (float) $roll->remaining_quantity,
                    'price_per_meter' => (float) $roll->price_per_meter,
                    'amount' => (float) ($roll->remaining_quantity * $roll->price_per_meter),
                ];
            });

            return response()->json([
                'success' => true,
                'vendor' => [
                    'id' => $vendor->id,
                    'name' => $vendor->name,
                    'phone' => $vendor->phone,
                    'email' => $vendor->email,
                    'address' => $vendor->address,
                    'balance' => (float) $vendor->balance
                ],
                'rolls' => $formatted,
                'total_rolls' => $rolls->count(),
                'total_meters' => (float) $rolls->sum('remaining_quantity')
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'vendor_id' => 'required|exists:vendors,id',
            'date' => 'required|date',
            'returns' => 'required|array',
        ]);

        // Ensure at least one roll is chosen with return meter > 0
        $validRollSelected = false;
        if (is_array($request->returns)) {
            foreach ($request->returns as $rollData) {
                if (!empty($rollData['selected']) && (float)($rollData['return_meter'] ?? 0) > 0) {
                    $validRollSelected = true;
                    break;
                }
            }
        }

        if (!$validRollSelected) {
            return redirect()->back()->withInput()->with('error', 'Please select at least one roll with return meter greater than 0.');
        }

        DB::beginTransaction();
        try {
            $vendor = Vendor::findOrFail($request->vendor_id);

            $return = new FabricReturn();
            $return->vendor_id = $vendor->id;
            $return->date = $request->date ?? date('Y-m-d');
            $return->remarks = $request->remarks;
            $return->return_number = 'RET-' . date('ymd') . '-' . rand(1000, 9999);
            $return->gst_percentage = (float) ($request->gst_percentage ?? 0);
            $return->discount = (float) ($request->discount ?? 0);
            $return->other_charges = (float) ($request->other_charges ?? 0);
            $return->sub_total = 0;
            $return->gst_amount = 0;
            $return->total_amount = 0;
            $return->save();

            $subTotal = 0;
            $primaryReceiptId = null;

            foreach ($request->returns as $detailId => $returnData) {
                if (empty($returnData['selected']) || (float)($returnData['return_meter'] ?? 0) <= 0) {
                    continue;
                }

                $detail = FabricReceiptDetail::find($detailId);
                if (!$detail) {
                    continue;
                }

                if (!$primaryReceiptId) {
                    $primaryReceiptId = $detail->fabric_receipt_id;
                }

                $returnMeter = (float) $returnData['return_meter'];
                $pricePerMeter = (float) ($returnData['price_per_meter'] ?? $detail->price_per_meter);

                if ($returnMeter > (float)$detail->remaining_quantity) {
                    throw new \Exception("Return quantity ({$returnMeter} m) for roll #{$detail->roll_number} exceeds remaining quantity ({$detail->remaining_quantity} m).");
                }

                $lineTotal = $returnMeter * $pricePerMeter;
                $subTotal += $lineTotal;

                $returnDetail = new FabricReturnDetail();
                $returnDetail->fabric_return_id = $return->id;
                $returnDetail->fabric_receipt_detail_id = $detail->id;
                $returnDetail->fabric_id = $detail->fabric_id;
                $returnDetail->return_meter = $returnMeter;
                $returnDetail->price_per_meter = $pricePerMeter;
                $returnDetail->save();

                // Deduct remaining quantity on the roll
                $detail->remaining_quantity -= $returnMeter;
                if ($detail->remaining_quantity <= 0) {
                    $detail->status = 2; // Marked as Returned
                }
                $detail->save();
            }

            $gstAmount = round(($subTotal * $return->gst_percentage) / 100, 2);
            $grandTotal = round($subTotal + $gstAmount + $return->other_charges - $return->discount, 2);

            $return->fabric_receipt_id = $primaryReceiptId;
            $return->sub_total = $subTotal;
            $return->gst_amount = $gstAmount;
            $return->total_amount = $grandTotal;
            $return->save();

            // Deduct from vendor balance
            $vendor->balance -= $grandTotal;
            $vendor->save();

            DB::commit();

            return redirect()->route('admin.fabric_return.view', $return->id)
                ->with('success', "Fabric Return voucher #{$return->return_number} generated successfully from multiple shipments.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }
    }

    public function view($id)
    {
        $return = FabricReturn::with([
            'vendor',
            'receipt.vendor',
            'details.fabric',
            'details.receipt_detail.fabric_receipt',
            'details.receipt_detail.master_fabric_warehouse'
        ])->findOrFail($id);

        return view('admin.fabric_return.view', compact('return'));
    }

    public function downloadReport($id)
    {
        $return = FabricReturn::with([
            'vendor',
            'receipt.vendor',
            'details.fabric',
            'details.receipt_detail.fabric_receipt',
            'details.receipt_detail.master_fabric_warehouse'
        ])->findOrFail($id);

        $pdf = PDF::loadView('admin.fabric_return.report_pdf', compact('return'));
        $fileName = 'Fabric_Return_' . str_replace(['/', ' ', ':'], '_', $return->return_number) . '.pdf';
        return $pdf->download($fileName);
    }

    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $return = FabricReturn::with('details.receipt_detail')->findOrFail($id);

            // Revert Roll Quantities
            foreach ($return->details as $detail) {
                if ($detail->receipt_detail) {
                    $detail->receipt_detail->remaining_quantity += $detail->return_meter;
                    if ($detail->receipt_detail->status == 2) {
                        $detail->receipt_detail->status = 1;
                    }
                    $detail->receipt_detail->save();
                }
                $detail->delete();
            }

            // Revert Vendor Balance
            $vendorId = $return->vendor_id ?? ($return->receipt ? $return->receipt->vendor_id : null);
            if ($vendorId) {
                $vendor = Vendor::find($vendorId);
                if ($vendor) {
                    $vendor->balance += $return->total_amount;
                    $vendor->save();
                }
            }

            $returnNo = $return->return_number;
            $return->delete();

            DB::commit();
            return redirect()->route('admin.fabric_return.index')
                ->with('success', "Fabric return {$returnNo} deleted successfully and roll quantities restored.");
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Error deleting return: ' . $e->getMessage());
        }
    }
}
