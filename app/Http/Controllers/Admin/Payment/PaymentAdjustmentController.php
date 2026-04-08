<?php

namespace App\Http\Controllers\Admin\Payment;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use App\Models\PaymentAdjustment;
use App\Models\BankAccount;
use App\Models\CashPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class PaymentAdjustmentController extends Controller
{
    public function index()
    {
        $adjustments = PaymentAdjustment::with('master')->latest()->get();
        // Group by batch_id, if null use unique key
        $grouped = $adjustments->groupBy(function($item) {
            return $item->batch_id ?? 'unique_' . $item->id;
        });
        return view('admin.payment.adjustment.index', compact('grouped'));
    }

    public function show($batchId)
    {
        if (str_starts_with($batchId, 'unique_')) {
            $id = str_replace('unique_', '', $batchId);
            $adjustments = PaymentAdjustment::where('id', $id)->get();
        } else {
            $adjustments = PaymentAdjustment::where('batch_id', $batchId)->get();
        }

        if ($adjustments->isEmpty()) {
            abort(404);
        }

        return view('admin.payment.adjustment.show', compact('adjustments', 'batchId'));
    }

    public function create()
    {
        $masters = AdjustmentMaster::where('status', 1)->get();
        $domesticMaster = AdjustmentMaster::where('name', 'Customer')->where('status', 1)->first();
        $vendorMaster = AdjustmentMaster::where('name', 'Vendor')->where('status', 1)->first();
        $shipmentMaster = $vendorMaster;
        
        return view('admin.payment.adjustment.create', compact('masters', 'vendorMaster', 'shipmentMaster', 'domesticMaster'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'adjustment_master_id' => 'required|array',
            'ref_id' => 'required|array',
            'type' => 'required',
            'payment_mode' => 'required',
            'payment_account_id' => 'required',
            'amount' => 'required|array',
            'date' => 'required|date',
            'total_expected_amount' => 'required|numeric',
        ]);

        $totalExpected = (float) $request->total_expected_amount;
        $totalActual = array_sum($request->amount);

        if (abs($totalExpected - $totalActual) > 0.01) {
            return back()->withErrors(['total_expected_amount' => 'Sum (' . number_format($totalActual, 2) . ') != Expected (' . number_format($totalExpected, 2) . ')'])->withInput();
        }

        $batchId = 'BAT-' . strtoupper(uniqid());
        $type = $request->type;
        $mode = $request->payment_mode;
        $accountId = $request->payment_account_id;

        foreach ($request->adjustment_master_id as $key => $masterId) {
            $rawRefIds = $request->ref_id[$key];
            $amountToDistribute = floatval($request->amount[$key]);
            $remarks = $request->remarks[$key] ?? '';
            
            $master = AdjustmentMaster::find($masterId);
            if ($master && class_exists($master->model_name)) {
                $model = $master->model_name;
                $refIds = explode(',', $rawRefIds);
                
                foreach ($refIds as $refId) {
                    $refId = trim($refId);
                    if (!$refId) continue;
                    if ($amountToDistribute <= 0 && count($refIds) > 1) break;

                    $actualModel = $model;
                    $actualId = $refId;
                    
                    // Handle Prefixed IDs for Corporate/Domestic Dispatches
                    if ($model == 'App\Models\AgentOrder' || $model == 'App\Models\MasterCustomer') {
                        if (str_starts_with($refId, 'OD:')) {
                            $actualModel = 'App\Models\OrderDispatch';
                            $actualId = substr($refId, 3);
                        } elseif (str_starts_with($refId, 'AOD:')) {
                            $actualModel = 'App\Models\AgentOrderDispatch';
                            $actualId = substr($refId, 4);
                        } elseif (str_starts_with($refId, 'OR:')) {
                            $actualModel = 'App\Models\OrderMain';
                            $actualId = substr($refId, 3);
                        }
                    }

                    $item = $actualModel::find($actualId);
                    if ($item) {
                        $currentAmount = $amountToDistribute;
                        
                        // Handle Distribution (Waterfall) - only if multiple selected
                        if (($model == 'App\Models\FabricReceipt' || $model == 'App\Models\AgentOrder' || $actualModel == 'App\Models\OrderDispatch' || $actualModel == 'App\Models\AgentOrderDispatch') && count($refIds) > 1) {
                            $balance = floatval($item->balance_amount ?? 0);
                            if ($balance <= 0) continue;
                            $currentAmount = min($amountToDistribute, $balance);
                        }
                        
                        $amountToDistribute -= $currentAmount;

                        // Create Adjustment Record
                        PaymentAdjustment::create([
                            'batch_id' => $batchId,
                            'adjustment_master_id' => $masterId,
                            'ref_id' => $refId, // Store prefixed ID as-is for tracking
                            'type' => $type,
                            'payment_mode' => $mode,
                            'payment_account_id' => $accountId,
                            'amount' => $currentAmount,
                            'date' => $request->date,
                            'remarks' => '[Dist] ' . $remarks
                        ]);

                        // Financial Logic
                        if ($model == 'App\Models\FabricReceipt') {
                            // No need to manual save balance, it's calculated from Payment records
                            \App\Models\Payment::create([
                                'payment_category' => 'fabric_shipment',
                                'payment_type' => ($type == 'debit' ? 'paid' : 'adjustment'),
                                'party_type' => \App\Models\Vendor::class,
                                'party_id' => $item->vendor_id,
                                'paymentable_type' => \App\Models\FabricReceipt::class,
                                'paymentable_id' => $item->id,
                                'amount' => $currentAmount,
                                'payment_date' => $request->date,
                                'payment_mode' => 'Adjustment',
                                'remarks' => '[Dist-Adj] ' . $remarks,
                                'created_by' => Auth::id(),
                            ]);

                            // Update Vendor Balance (Deduct if we're "paying" via adjustment, or Increase if they gave us more stuff)
                            $vendor = \App\Models\Vendor::find($item->vendor_id);
                            if ($vendor) {
                                if ($type == 'debit') $vendor->balance -= $currentAmount;
                                else $vendor->balance += $currentAmount;
                                $vendor->save();
                            }
                        } elseif ($model == 'App\Models\Vendor') {
                             if ($type == 'debit') $item->balance -= $currentAmount;
                             else $item->balance += $currentAmount;
                             $item->save();
                        } elseif ($model == 'App\Models\Employee') {
                            \App\Models\Payment::create([
                                'payment_category' => 'employee_salary',
                                'payment_type' => 'paid',
                                'party_type' => \App\Models\Employee::class,
                                'party_id' => $item->id,
                                'paymentable_type' => \App\Models\Employee::class,
                                'paymentable_id' => $item->id,
                                'amount' => $currentAmount,
                                'payment_date' => $request->date,
                                'payment_mode' => 'Adjustment',
                                'remarks' => '[Salary-Adj] ' . $remarks,
                                'created_by' => Auth::id(),
                            ]);
                        } elseif ($model == 'App\Models\AgentOrder' || $actualModel == 'App\Models\OrderDispatch' || $actualModel == 'App\Models\OrderMain' || $actualModel == 'App\Models\AgentOrderDispatch') {
                            $category = ($actualModel == 'App\Models\AgentOrder' || $actualModel == 'App\Models\AgentOrderDispatch') ? 'domestic_order' : 'corporate_order';
                            $prefix = ($actualModel == 'App\Models\AgentOrder' || $actualModel == 'App\Models\AgentOrderDispatch') ? 'Customer' : 'Corp';
                            
                            $party_id = ($actualModel == 'App\Models\AgentOrder' || $actualModel == 'App\Models\AgentOrderDispatch') 
                                ? ($item->master_customer_id ?? $item->customer_id) 
                                : ($actualModel == 'App\Models\OrderDispatch' ? $item->customer_id : $item->master_customer_id);

                            \App\Models\Payment::create([
                                'payment_category' => $category,
                                'payment_type' => 'paid',
                                'party_type' => \App\Models\MasterCustomer::class,
                                'party_id' => $party_id,
                                'paymentable_type' => $actualModel,
                                'paymentable_id' => $actualId,
                                'amount' => $currentAmount,
                                'payment_date' => $request->date,
                                'payment_mode' => 'Adjustment',
                                'remarks' => '[' . $prefix . '-Adj] ' . $remarks,
                                'created_by' => Auth::id(),
                            ]);

                            // Update MasterCustomer Balance
                            $customer = \App\Models\MasterCustomer::find($party_id);
                            if ($customer) {
                                if ($type == 'credit') $customer->balance += $currentAmount;
                                else $customer->balance -= $currentAmount;
                                $customer->save();
                            }
                        } elseif ($model == 'App\Models\MasterCustomer') {
                            if ($type == 'credit') $item->balance += $currentAmount;
                            else $item->balance -= $currentAmount;
                            $item->save();
                        } else {
                            // Update Master Item Balance (regular flow)
                            if (isset($item->balance)) {
                                if ($type == 'debit') $item->balance -= $currentAmount;
                                else $item->balance += $currentAmount;
                                $item->save();
                            } elseif (isset($item->amount)) {
                                if ($type == 'debit') $item->amount -= $currentAmount;
                                else $item->amount += $currentAmount;
                                $item->save();
                            }
                        }
                    }
                }
            }
        }

        // Update Global Account Balance (Single aggregate update)
        if ($mode == 'bank' && $accountId) {
            $account = BankAccount::find($accountId);
        } elseif ($accountId) {
            $account = CashPayment::find($accountId);
        }

        if ($account) {
            if ($type == 'credit') $account->balance += $totalActual;
            else $account->balance -= $totalActual;
            $account->save();
        }

        return redirect()->route('admin.payment.adjustment.index')->with('success', 'Multiple payment adjustments recorded and distributed.');
    }

    public function getSubMasters(Request $request)
    {
        $masterId = $request->master_id;
        $master = AdjustmentMaster::find($masterId);
        
        if (!$master) {
            return response()->json([]);
        }

        $modelName = $master->model_name;
        if (class_exists($modelName)) {
            if ($modelName == 'App\Models\FabricReceipt') {
                $vendors = \App\Models\Vendor::where('status', 1)->get()->map(function ($v) {
                    return ['id' => $v->id, 'name' => $v->name, 'balance' => 0];
                });
                return response()->json($vendors);
            }

            if ($modelName == 'App\Models\AgentOrder') {
                $customers = \App\Models\MasterCustomer::with('agent')
                    ->whereIn('type', ['domestic', 'corporate'])
                    ->get()
                    ->map(function ($c) {
                        $agentName = $c->agent ? $c->agent->name : 'Direct';
                        $prefix = ($c->type == 'corporate') ? '[Corp] ' : '';
                        return ['id' => $c->id, 'name' => $prefix . $c->name . ' (Agent: ' . $agentName . ')', 'balance' => 0];
                    });
                return response()->json($customers);
            }

            $data = $modelName::where('status', 1)->get()->map(function ($item) use ($modelName) {
                $name = $item->name;
                if ($modelName == 'App\Models\BankAccount') {
                    $name = $item->bank_name . ' (' . $item->account_number . ')';
                }
                return ['id' => $item->id, 'name' => $name, 'balance' => $item->balance ?? $item->amount ?? 0];
            });
            return response()->json($data);
        }

        return response()->json([]);
    }

    public function getAccounts(Request $request)
    {
        $mode = $request->mode;
        
        if ($mode == 'bank') {
            $data = BankAccount::where('status', 1)->get();
            $formatted = $data->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->bank_name . ' (' . $item->account_number . ') - Bal: ' . number_format($item->balance, 2),
                    'balance' => $item->balance
                ];
            });
            return response()->json($formatted);
        } else {
            $data = CashPayment::where('status', 1)->get()->map(function($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->name . ' - Bal: ' . number_format($item->balance, 2),
                    'balance' => $item->balance
                ];
            });
            return response()->json($data);
        }
    }

    public function getVendorShipments(Request $request)
    {
        $id = $request->vendor_id; // Reuse generic ID field
        $masterId = $request->master_id;
        $master = AdjustmentMaster::find($masterId);

        if (!$master) return response()->json([]);

        if ($master->model_name == 'App\Models\AgentOrder') {
            $customer = \App\Models\MasterCustomer::find($id);
            if (!$customer) return response()->json([]);

            if ($customer->type == 'domestic') {
                $dispatches = \App\Models\AgentOrderDispatch::where('master_customer_id', $id)
                    ->get()
                    ->filter(function ($d) {
                        return $d->balance_amount > 0;
                    })
                    ->map(function($d) {
                        return [
                            'id' => 'AOD:' . $d->id,
                            'shipment_no' => 'DIS-#' . $d->id,
                            'balance' => $d->balance_amount
                        ];
                    })
                    ->values();
                return response()->json($dispatches);
            } else {
                // Corporate Logic - Only show Dispatches with balance
                $dispatches = \App\Models\OrderDispatch::where('customer_id', $id)
                    ->where('is_paid', 0)
                    ->whereHas('orderMain', function ($q) {
                        $q->where('order_type', 'corporate');
                    })
                    ->get()
                    ->filter(function ($d) { return $d->balance_amount > 0; })
                    ->map(function ($d) {
                        return [
                            'id' => 'OD:' . $d->id,
                            'shipment_no' => 'DIS-#' . $d->sku,
                            'balance' => $d->balance_amount
                        ];
                    });
                
                return response()->json($dispatches->values());
            }
        }

        $shipments = \App\Models\FabricReceipt::where('vendor_id', $id)
            ->get()
            ->filter(function ($receipt) {
                return $receipt->balance_amount > 0;
            })
            ->map(function($receipt) {
                return [
                    'id' => $receipt->id,
                    'shipment_no' => $receipt->shipment_id ?? 'N/A',
                    'balance' => $receipt->balance_amount
                ];
            })
            ->values();
        
        return response()->json($shipments);
    }
}
