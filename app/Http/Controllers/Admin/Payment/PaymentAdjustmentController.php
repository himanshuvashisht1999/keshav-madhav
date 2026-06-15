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
    public function index(Request $request)
    {
        $query = PaymentAdjustment::with('master');

        if ($request->filled('from_date')) {
            $query->whereDate('date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('date', '<=', $request->to_date);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('payment_account_id')) {
            $query->where('payment_account_id', $request->payment_account_id);
        }

        if ($request->filled('amount_from')) {
            $query->where('amount', '>=', $request->amount_from);
        }

        if ($request->filled('amount_to')) {
            $query->where('amount', '<=', $request->amount_to);
        }

        $adjustments = $query->orderBy('id', 'desc')->get();
        
        $totalDebit = $adjustments->where('type', 'debit')->sum('amount');
        $totalCredit = $adjustments->where('type', 'credit')->sum('amount');

        // Group by batch_id, if null use unique key
        $grouped = $adjustments->groupBy(function ($item) {
            return $item->batch_id ?? 'unique_' . $item->id;
        });

        $bankAccounts = BankAccount::where('status', 1)->get();
        $cashAccounts = CashPayment::where('status', 1)->get();

        return view('admin.payment.adjustment.index', compact('grouped', 'totalDebit', 'totalCredit', 'bankAccounts', 'cashAccounts'));
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
                    if (!$refId)
                        continue;

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

                            // If we have remaining money, give it to this item based on its balance
                            if ($amountToDistribute > 0 && $balance > 0) {
                                $currentAmount = min($amountToDistribute, $balance);
                            } else {
                                $currentAmount = 0;
                            }
                        }

                        $amountToDistribute -= $currentAmount;

                        // Create Adjustment Record (Always created, even if amount is 0)
                        PaymentAdjustment::create([
                            'batch_id' => $batchId,
                            'adjustment_master_id' => $masterId,
                            'ref_id' => $refId,
                            'type' => $type,
                            'payment_mode' => $mode,
                            'payment_account_id' => $accountId,
                            'amount' => $currentAmount,
                            'date' => $request->date,
                            'remarks' => $remarks
                        ]);

                        // Financial Logic: Only apply if amount distributed is > 0
                        if ($currentAmount > 0) {
                            if ($model == 'App\Models\Vendor') {
                                \App\Models\Payment::create([
                                    'payment_category' => 'fabric_shipment',
                                    'payment_type' => $type,
                                    'party_type' => \App\Models\Vendor::class,
                                    'party_id' => $item->id,
                                    'paymentable_type' => \App\Models\Vendor::class,
                                    'paymentable_id' => $item->id,
                                    'amount' => $currentAmount,
                                    'payment_date' => $request->date,
                                    'payment_mode' => 'Adjustment',
                                    'reference_id' => $batchId,
                                    'remarks' => '[Vendor-Adj] ' . $remarks,
                                    'created_by' => \Auth::id(),
                                ]);

                                if ($type == 'debit')
                                    $item->balance -= $currentAmount;
                                else
                                    $item->balance += $currentAmount;
                                $item->save();
                            } elseif ($model == 'App\Models\Employee') {
                                \App\Models\Payment::create([
                                    'payment_category' => 'employee_salary',
                                    'payment_type' => $type,
                                    'party_type' => \App\Models\Employee::class,
                                    'party_id' => $item->id,
                                    'paymentable_type' => \App\Models\Employee::class,
                                    'paymentable_id' => $item->id,
                                    'amount' => $currentAmount,
                                    'payment_date' => $request->date,
                                    'payment_mode' => 'Adjustment',
                                    'reference_id' => $batchId,
                                    'remarks' => '[Salary-Adj] ' . $remarks,
                                    'created_by' => Auth::id(),
                                ]);
                            } elseif ($model == 'App\Models\MasterCustomer') {
                                \App\Models\Payment::create([
                                    'payment_category' => 'domestic_order',
                                    'payment_type' => $type,
                                    'party_type' => \App\Models\MasterCustomer::class,
                                    'party_id' => $item->id,
                                    'paymentable_type' => \App\Models\MasterCustomer::class,
                                    'paymentable_id' => $item->id,
                                    'amount' => $currentAmount,
                                    'payment_date' => $request->date,
                                    'payment_mode' => 'Adjustment',
                                    'reference_id' => $batchId,
                                    'remarks' => '[Customer-Adj] ' . $remarks,
                                    'created_by' => Auth::id(),
                                ]);

                                if ($type == 'credit')
                                    $item->balance += $currentAmount;
                                else
                                    $item->balance -= $currentAmount;
                                $item->save();
                            } else {
                                // Update Master Item Balance (regular flow)
                                if (isset($item->balance) || isset($item->amount)) {
                                    $isSpecial = in_array($masterId, [1, 12, 19, 26, 27, 28, 29, 30]); // Committee, Bank, Hulayati, Machinery, Loan, Factory Head, Discount, Salary

                                    if (isset($item->balance)) {
                                        if ($type == 'debit') {
                                            $item->balance -= $currentAmount;
                                        } else {
                                            $item->balance += $currentAmount;
                                        }
                                    } elseif (isset($item->amount)) {
                                        if ($type == 'debit') {
                                            $item->amount -= $currentAmount;
                                        } else {
                                            $item->amount += $currentAmount;
                                        }
                                    }
                                    $item->save();
                                }

                                // Handle linked models (FabricReceipt -> Vendor, AgentOrder -> Customer)
                                if ($model == 'App\Models\FabricReceipt' && isset($item->vendor_id)) {
                                    $vendor = \App\Models\Vendor::find($item->vendor_id);
                                    if ($vendor) {
                                        if ($type == 'credit')
                                            $vendor->balance += $currentAmount;
                                        else
                                            $vendor->balance -= $currentAmount;
                                        $vendor->save();
                                    }
                                } elseif (($model == 'App\Models\AgentOrder' || $actualModel == 'App\Models\OrderDispatch' || $actualModel == 'App\Models\OrderMain' || $actualModel == 'App\Models\AgentOrderDispatch')) {
                                    $partyId = ($actualModel == 'App\Models\AgentOrder' || $actualModel == 'App\Models\AgentOrderDispatch')
                                        ? ($item->master_customer_id ?? $item->customer_id)
                                        : ($actualModel == 'App\Models\OrderDispatch' ? $item->customer_id : $item->master_customer_id);

                                    $customer = \App\Models\MasterCustomer::find($partyId);
                                    if ($customer) {
                                        if ($type == 'credit')
                                            $customer->balance += $currentAmount;
                                        else
                                            $customer->balance -= $currentAmount;
                                        $customer->save();
                                    }
                                }
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
            // For To Account, the type is reverse of the Party type
            if ($type == 'credit')
                $account->balance -= $totalActual;
            else
                $account->balance += $totalActual;
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

    public function getSubMastersAll(Request $request)
    {
        $masters = AdjustmentMaster::where('status', 1)->get();
        $allData = [];

        foreach ($masters as $master) {
            $modelName = $master->model_name;
            if (class_exists($modelName)) {
                $items = $modelName::where('status', 1)->get()->map(function ($item) use ($modelName, $master) {
                    $name = $item->name;
                    if ($modelName == 'App\Models\BankAccount') {
                        $name = $item->bank_name . ' (' . $item->account_number . ')';
                    }
                    return [
                        'id' => $item->id,
                        'name' => $name . ' [' . $master->name . ']',
                        'master_id' => $master->id,
                        'balance' => $item->balance ?? $item->amount ?? 0
                    ];
                })->toArray();
                $allData = array_merge($allData, $items);
            }
        }

        return response()->json($allData);
    }

    public function getAccounts(Request $request)
    {
        $mode = $request->mode;

        if ($mode == 'bank') {
            $data = BankAccount::where('status', 1)->get();
            $formatted = $data->map(function ($item) {
                return [
                    'id' => $item->id,
                    'name' => $item->bank_name . ' (' . $item->account_number . ') - Bal: ' . number_format($item->balance, 2),
                    'balance' => $item->balance
                ];
            });
            return response()->json($formatted);
        } else {
            $data = CashPayment::where('status', 1)->get()->map(function ($item) {
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

        if (!$master)
            return response()->json([]);

        if ($master->model_name == 'App\Models\AgentOrder') {
            $customer = \App\Models\MasterCustomer::find($id);
            if (!$customer)
                return response()->json([]);

            if ($customer->type == 'domestic') {
                $dispatches = \App\Models\AgentOrderDispatch::where('master_customer_id', $id)
                    ->get()
                    ->filter(function ($d) {
                        return $d->balance_amount > 0;
                    })
                    ->map(function ($d) {
                        return [
                            'id' => 'AOD:' . $d->id,
                            'shipment_no' => 'DIS-#' . $d->id,
                            'balance' => $d->balance_amount
                        ];
                    })
                    ->values();
                return response()->json($dispatches);
            } else {
                $batchId = $request->batch_id;
                $batchRefIds = [];
                if ($batchId) {
                    $batchRefIds = \App\Models\PaymentAdjustment::where('batch_id', $batchId)->pluck('ref_id')->toArray();
                }

                // Fetch Dispatches
                $dispatches = \App\Models\OrderDispatch::where('customer_id', $id)
                    ->whereHas('orderMain', function ($q) {
                        $q->where('order_type', 'corporate');
                    })
                    ->get()
                    ->filter(function ($d) use ($batchRefIds) {
                        return $d->balance_amount > 0 || in_array('OD:' . $d->id, $batchRefIds);
                    })
                    ->map(function ($d) use ($batchRefIds) {
                        $bal = $d->balance_amount;
                        if (in_array('OD:' . $d->id, $batchRefIds)) {
                            $adj = \App\Models\PaymentAdjustment::where('batch_id', request()->batch_id)->where('ref_id', 'OD:' . $d->id)->first();
                            if ($adj)
                                $bal += $adj->amount;
                        }
                        return [
                            'id' => 'OD:' . $d->id,
                            'shipment_no' => 'DIS-#' . ($d->sku ?? $d->id),
                            'balance' => $bal
                        ];
                    });

                // Fetch Orders (Directly)
                $orders = \App\Models\OrderMain::where('master_customer_id', $id)
                    ->where('is_paid', 0)
                    ->get()
                    ->filter(function ($o) use ($batchRefIds) {
                        return ($o->total_amount - $o->paid_amount) > 0 || in_array('OR:' . $o->id, $batchRefIds);
                    })
                    ->map(function ($o) use ($batchRefIds) {
                        $bal = $o->total_amount - $o->paid_amount;
                        if (in_array('OR:' . $o->id, $batchRefIds)) {
                            $adj = \App\Models\PaymentAdjustment::where('batch_id', request()->batch_id)->where('ref_id', 'OR:' . $o->id)->first();
                            if ($adj)
                                $bal += $adj->amount;
                        }
                        return [
                            'id' => 'OR:' . $o->id,
                            'shipment_no' => 'ORD-#' . ($o->sku ?? $o->id),
                            'balance' => $bal
                        ];
                    });

                // Fetch Agent Order Dispatches
                $agentDispatches = \App\Models\AgentOrderDispatch::where('master_customer_id', $id)
                    ->get()
                    ->filter(function ($ad) use ($batchRefIds) {
                        return $ad->balance_amount > 0 || in_array('AOD:' . $ad->id, $batchRefIds);
                    })
                    ->map(function ($ad) use ($batchRefIds) {
                        $bal = $ad->balance_amount;
                        if (in_array('AOD:' . $ad->id, $batchRefIds)) {
                            $adj = \App\Models\PaymentAdjustment::where('batch_id', request()->batch_id)->where('ref_id', 'AOD:' . $ad->id)->first();
                            if ($adj)
                                $bal += $adj->amount;
                        }
                        return [
                            'id' => 'AOD:' . $ad->id,
                            'shipment_no' => 'A-DIS-#' . ($ad->sku ?? $ad->id),
                            'balance' => $bal
                        ];
                    });

                $combined = collect([])
                    ->concat($dispatches)
                    ->concat($orders)
                    ->concat($agentDispatches);

                return response()->json($combined->values());
            }
        }

        $batchId = $request->batch_id;
        $batchShipmentIds = [];
        if ($batchId) {
            $batchShipmentIds = \App\Models\PaymentAdjustment::where('batch_id', $batchId)->pluck('ref_id')->toArray();
        }

        $shipments = \App\Models\FabricReceipt::where('vendor_id', $id)
            ->get()
            ->filter(function ($receipt) use ($batchShipmentIds) {
                return $receipt->balance_amount > 0 || in_array($receipt->id, $batchShipmentIds);
            })
            ->map(function ($receipt) use ($batchShipmentIds) {
                $bal = $receipt->balance_amount;
                // If it's in this batch, add back the amount for preview
                if ($batchShipmentIds && in_array($receipt->id, $batchShipmentIds)) {
                    $adj = \App\Models\PaymentAdjustment::where('ref_id', $receipt->id)->where('batch_id', request()->batch_id)->first();
                    if ($adj)
                        $bal += $adj->amount;
                }
                return [
                    'id' => $receipt->id,
                    'shipment_no' => $receipt->shipment_id ?? 'N/A',
                    'balance' => $bal
                ];
            })
            ->values();

        return response()->json($shipments);
    }

    public function edit($batchId)
    {
        $allAdjustments = PaymentAdjustment::where('batch_id', $batchId)->get();
        if ($allAdjustments->isEmpty()) {
            abort(404);
        }

        // Group adjustments by master type and parent (Vendor/Customer)
        $groupedAdjustments = $allAdjustments->groupBy(function ($adj) {
            $parent = $adj->parent_item;
            return $adj->adjustment_master_id . '_' . $parent['id'];
        })->map(function ($group) {
            $first = $group->first();
            return (object) [
                'adjustment_master_id' => $first->adjustment_master_id,
                'parent_id' => $first->parent_item['id'],
                'parent_name' => $first->parent_item['name'],
                'ref_id' => $group->pluck('ref_id')->implode(','),
                'amount' => $group->sum('amount'),
                'remarks' => $first->remarks, // Usually same for a batch but we take first
                'items' => $group->map(function ($g) {
                    $entity = $g->entity;
                    $balance = ($entity->balance_amount ?? $entity->balance ?? 0) + $g->amount;
                    return (object) [
                        'id' => $g->ref_id,
                        'name' => $g->entity_name,
                        'amount' => $g->amount,
                        'balance' => $balance
                    ];
                })
            ];
        });

        $masters = AdjustmentMaster::where('status', 1)->get();
        $domesticMaster = AdjustmentMaster::where('name', 'Customer')->where('status', 1)->first();
        $vendorMaster = AdjustmentMaster::where('name', 'Vendor')->where('status', 1)->first();
        $shipmentMaster = $vendorMaster;

        $first = $allAdjustments->first();
        return view('admin.payment.adjustment.edit', compact('groupedAdjustments', 'batchId', 'masters', 'vendorMaster', 'shipmentMaster', 'domesticMaster', 'first'));
    }

    public function update(Request $request, $batchId)
    {
        DB::beginTransaction();
        try {
            $this->reverseAdjustmentBatch($batchId);
            $this->store($request);
            DB::commit();
            return redirect()->route('admin.payment.adjustment.index')->with('success', 'Adjustment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating adjustment: ' . $e->getMessage())->withInput();
        }
    }

    public function delete($batchId)
    {
        DB::beginTransaction();
        try {
            $this->reverseAdjustmentBatch($batchId);
            DB::commit();
            return redirect()->route('admin.payment.adjustment.index')->with('success', 'Adjustment batch deleted and balances reversed.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error deleting adjustment: ' . $e->getMessage());
        }
    }

    private function reverseAdjustmentBatch($batchId)
    {
        if (str_starts_with($batchId, 'unique_')) {
            $id = str_replace('unique_', '', $batchId);
            $adjustments = PaymentAdjustment::where('id', $id)->get();
        } else {
            $adjustments = PaymentAdjustment::where('batch_id', $batchId)->get();
        }

        if ($adjustments->isEmpty())
            return false;

        $first = $adjustments->first();
        $type = $first->type;
        $mode = $first->payment_mode;
        $accountId = $first->payment_account_id;
        $totalBatchAmount = $adjustments->sum('amount');

        foreach ($adjustments as $adj) {
            $master = AdjustmentMaster::find($adj->adjustment_master_id);
            if ($master && class_exists($master->model_name)) {
                $model = $master->model_name;
                $refId = $adj->ref_id;
                $amount = $adj->amount;

                $actualModel = $model;
                $actualId = $refId;

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
                    if ($model == 'App\Models\FabricReceipt') {
                        $vendor = \App\Models\Vendor::find($item->vendor_id);
                        if ($vendor) {
                            if ($type == 'debit')
                                $vendor->balance += $amount;
                            else
                                $vendor->balance -= $amount;
                            $vendor->save();
                        }
                    } elseif ($model == 'App\Models\Vendor') {
                        if ($type == 'debit')
                            $item->balance += $amount;
                        else
                            $item->balance -= $amount;
                        $item->save();
                    } elseif ($model == 'App\Models\AgentOrder' || $actualModel == 'App\Models\OrderDispatch' || $actualModel == 'App\Models\OrderMain' || $actualModel == 'App\Models\AgentOrderDispatch') {
                        $partyId = ($actualModel == 'App\Models\AgentOrder' || $actualModel == 'App\Models\AgentOrderDispatch')
                            ? ($item->master_customer_id ?? $item->customer_id)
                            : ($actualModel == 'App\Models\OrderDispatch' ? $item->customer_id : $item->master_customer_id);

                        $customer = \App\Models\MasterCustomer::find($partyId);
                        if ($customer) {
                            if ($type == 'credit')
                                $customer->balance -= $amount;
                            else
                                $customer->balance += $amount;
                            $customer->save();
                        }
                    } elseif ($model == 'App\Models\MasterCustomer') {
                        if ($type == 'credit')
                            $item->balance -= $amount;
                        else
                            $item->balance += $amount;
                        $item->save();
                    } else {
                        if (isset($item->balance) || isset($item->amount)) {
                            $isSpecial = in_array($adj->adjustment_master_id, [1, 12, 19, 26, 27, 28, 29, 30]);

                            if (isset($item->balance)) {
                                if ($type == 'debit') {
                                    $item->balance += $amount; // Reverse of subtract
                                } else {
                                    $item->balance -= $amount; // Reverse of add
                                }
                            } elseif (isset($item->amount)) {
                                if ($type == 'debit') {
                                    $item->amount += $amount;
                                } else {
                                    $item->amount -= $amount;
                                }
                            }
                            $item->save();
                        }
                    }
                }
            }
            $adj->delete();
        }

        // Reverse Global Account Balance
        if ($mode == 'bank' && $accountId) {
            $account = BankAccount::find($accountId);
        } elseif ($accountId) {
            $account = CashPayment::find($accountId);
        }

        if ($account) {
            // Reversing the reverse: if type was credit, we added it (reverse of subtract)
            if ($type == 'credit')
                $account->balance += $totalBatchAmount;
            else
                $account->balance -= $totalBatchAmount;
            $account->save();
        }

        // Delete associated Payment records
        \App\Models\Payment::where('reference_id', $batchId)->delete();

        return true;
    }
}
