<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\Vendor;
use App\Models\Item;
use App\Models\PurchaseAgent;
use App\Models\MasterOpeningBalance;
use App\Http\DataTable\Admin\Master\VendorDataTable as DataTable;

class VendorService {
    protected $datatable;
    protected $vendor;
    public function __construct(
        DataTable $datatable,
        Vendor $vendor
    ) {
        $this->datatable= $datatable;
        $this->vendor= $vendor;
    }

    public function index(Request $request){
        return true;
    }

    public function indexList(Request $request){
        return $this->datatable->indexList($request);
    }

    public function calculateVendorBalances($vendorIds, $startDate = null, $endDate = null)
    {
        $balances = [];

        // Swap dates if startDate is greater than endDate
        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        $opOpeningBalances = \App\Models\MasterOpeningBalance::where('master_type', 'vendor')
            ->whereIn('master_id', $vendorIds)
            ->where('financial_year', \App\Models\MasterOpeningBalance::openingKey())
            ->get()
            ->keyBy('master_id');
        $clOpeningBalances = $opOpeningBalances;

        // Initialize balances
        foreach ($vendorIds as $id) {
            $opInitial = 0;
            if (isset($opOpeningBalances[$id])) {
                $opInitial = (float) $opOpeningBalances[$id]->amount;
                if (strtolower(trim($opOpeningBalances[$id]->balance_type)) === 'debit') {
                    $opInitial = -$opInitial;
                }
            }

            $clInitial = 0;
            if (isset($clOpeningBalances[$id])) {
                $clInitial = (float) $clOpeningBalances[$id]->amount;
                if (strtolower(trim($clOpeningBalances[$id]->balance_type)) === 'debit') {
                    $clInitial = -$clInitial;
                }
            }

            $balances[$id] = [
                'op_initial' => $opInitial,
                'cl_initial' => $clInitial,
                'opening_debit' => 0,
                'opening_credit' => 0,
                'closing_debit' => 0,
                'closing_credit' => 0,
            ];
        }

        // Get vendor master ID for journal vouchers
        $jvMasterIds = \App\Models\AdjustmentMaster::where('name', 'Vendor')->orWhere('model_name', 'App\Models\Vendor')->pluck('id');

        // A. AgentOrderDispatch (Debit)
        $dispatches = \App\Models\AgentOrderDispatch::whereIn('master_vendor_id', $vendorIds)
            ->where('party_type', 'vendor')
            ->where('status', 'dispatched')
            ->get();
        foreach ($dispatches as $d) {
            $date = $d->dispatch_date;
            $amount = (float) $d->grand_total;
            $id = $d->master_vendor_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($startDate && $txDate < $startDate) {
                $balances[$id]['opening_debit'] += $amount;
            }
            if (!$endDate || $txDate <= $endDate) {
                $balances[$id]['closing_debit'] += $amount;
            }
        }

        // B. AgentOrderReturn (Credit)
        $returns = \App\Models\AgentOrderReturn::whereHas('dispatch', function($q) use ($vendorIds) {
            $q->whereIn('master_vendor_id', $vendorIds)->where('party_type', 'vendor');
        })->with('dispatch')->get();
        foreach ($returns as $r) {
            if (!$r->dispatch) continue;
            $date = $r->return_date;
            $amount = (float) $r->grand_total;
            $id = $r->dispatch->master_vendor_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($startDate && $txDate < $startDate) {
                $balances[$id]['opening_credit'] += $amount;
            }
            if (!$endDate || $txDate <= $endDate) {
                $balances[$id]['closing_credit'] += $amount;
            }
        }

        // C. Payments (Credit / Debit)
        $payments = \App\Models\Payment::whereIn('party_id', $vendorIds)
            ->where('party_type', \App\Models\Vendor::class)
            ->where(function($q) {
                $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
                  ->orWhereNull('paymentable_type');
            })
            ->get();
        foreach ($payments as $p) {
            $date = $p->payment_date;
            $amount = (float) $p->amount;
            $id = $p->party_id;
            
            $isCredit = in_array($p->payment_type, ['received', 'credit']);
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($isCredit) {
                if ($startDate && $txDate < $startDate) {
                    $balances[$id]['opening_credit'] += $amount;
                }
                if (!$endDate || $txDate <= $endDate) {
                    $balances[$id]['closing_credit'] += $amount;
                }
            } else {
                if ($startDate && $txDate < $startDate) {
                    $balances[$id]['opening_debit'] += $amount;
                }
                if (!$endDate || $txDate <= $endDate) {
                    $balances[$id]['closing_debit'] += $amount;
                }
            }
        }

        // D. DomesticInventoryPurchase (Credit)
        $purchases = \App\Models\DomesticInventoryPurchase::whereIn('vendor_id', $vendorIds)->get();
        foreach ($purchases as $ip) {
            $date = $ip->purchase_date ?: $ip->created_at;
            $amount = (float) $ip->total_amount;
            $id = $ip->vendor_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($startDate && $txDate < $startDate) {
                $balances[$id]['opening_credit'] += $amount;
            }
            if (!$endDate || $txDate <= $endDate) {
                $balances[$id]['closing_credit'] += $amount;
            }
        }

        // E. FabricReceipt (Credit)
        $receipts = \App\Models\FabricReceipt::whereIn('vendor_id', $vendorIds)->get();
        foreach ($receipts as $r) {
            $date = $r->time ?: $r->created_at;
            $amount = (float) $r->total_amount;
            $id = $r->vendor_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($startDate && $txDate < $startDate) {
                $balances[$id]['opening_credit'] += $amount;
            }
            if (!$endDate || $txDate <= $endDate) {
                $balances[$id]['closing_credit'] += $amount;
            }
        }

        // F. FabricReturn (Debit)
        $fabricReturns = \App\Models\FabricReturn::whereIn('vendor_id', $vendorIds)
            ->orWhereHas('receipt', function($q) use ($vendorIds) {
                $q->whereIn('vendor_id', $vendorIds);
            })->with('receipt')->get();
        foreach ($fabricReturns as $pr) {
            $date = $pr->date ?: $pr->created_at;
            $amount = (float) $pr->total_amount;
            $id = $pr->vendor_id ?: ($pr->receipt ? $pr->receipt->vendor_id : null);
            if (!$id || !isset($balances[$id])) continue;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($startDate && $txDate < $startDate) {
                $balances[$id]['opening_debit'] += $amount;
            }
            if (!$endDate || $txDate <= $endDate) {
                $balances[$id]['closing_debit'] += $amount;
            }
        }

        // G. JournalVouchers (Credit / Debit)
        if ($jvMasterIds->isNotEmpty()) {
            $vouchers = \App\Models\JournalVoucherItem::with('voucher')
                ->whereIn('master_type', $jvMasterIds)
                ->whereIn('master_id', $vendorIds)
                ->get();
            foreach ($vouchers as $v) {
                if (!$v->voucher) continue;
                $date = $v->voucher->date;
                $amount = (float) $v->amount;
                $id = $v->master_id;
                
                $isCredit = strtolower($v->type) === 'credit';
                $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
                if ($isCredit) {
                    if ($startDate && $txDate < $startDate) {
                        $balances[$id]['opening_credit'] += $amount;
                    }
                    if (!$endDate || $txDate <= $endDate) {
                        $balances[$id]['closing_credit'] += $amount;
                    }
                } else {
                    if ($startDate && $txDate < $startDate) {
                        $balances[$id]['opening_debit'] += $amount;
                    }
                    if (!$endDate || $txDate <= $endDate) {
                        $balances[$id]['closing_debit'] += $amount;
                    }
                }
            }
        }

        // Calculate final opening and closing balances
        $results = [];
        foreach ($vendorIds as $id) {
            $opBal = $balances[$id]['op_initial'] + ($balances[$id]['opening_credit'] - $balances[$id]['opening_debit']);
            $clBal = $balances[$id]['cl_initial'] + ($balances[$id]['closing_credit'] - $balances[$id]['closing_debit']);
            
            $results[$id] = [
                'opening_balance' => $opBal,
                'closing_balance' => $clBal,
            ];
        }

        return $results;
    }

    public function downloadPdf(Request $request)
    {
        $query = Vendor::with('currentOpeningBalance')->where('status', '!=', 3);

        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', "%{$request->get('name')}%");
        }
        if ($request->has('phone') && !empty($request->phone)) {
            $query->where('phone', 'like', "%{$request->get('phone')}%");
        }
        if ($request->has('email') && !empty($request->email)) {
            $query->where('email', 'like', "%{$request->get('email')}%");
        }
        if ($request->has('status') && $request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $hasDateFilter = !empty($startDate) || !empty($endDate);

        $vendors = $query->orderBy('id', 'asc')->get();

        $vendorIds = $vendors->pluck('id')->toArray();
        $calculatedBalances = [];
        if (!empty($vendorIds)) {
            $calculatedBalances = $this->calculateVendorBalances($vendorIds, $hasDateFilter ? $startDate : null, $hasDateFilter ? $endDate : null);
        }

        if ($request->filled('balance_type')) {
            $bType = strtolower($request->get('balance_type'));
            $vendors = $vendors->filter(function($v) use ($bType, $calculatedBalances) {
                $bal = isset($calculatedBalances[$v->id]) ? $calculatedBalances[$v->id]['closing_balance'] : $v->balance;
                return $bType === 'credit' ? $bal >= 0 : $bal < 0;
            });
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.master.vendor.pdf', compact('vendors', 'startDate', 'endDate', 'hasDateFilter', 'calculatedBalances'));
        return $pdf->download('vendors.pdf');
    }

    public function downloadExcel(Request $request)
    {
        $query = Vendor::with('currentOpeningBalance')->where('status', '!=', 3);

        $name = $request->get('name');
        $phone = $request->get('phone');
        $email = $request->get('email');
        $status = $request->get('status');
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $balanceType = $request->get('balance_type');
        $hasDateFilter = !empty($startDate) || !empty($endDate);

        if (!empty($name)) {
            $query->where('name', 'like', '%' . $name . '%');
        }
        if (!empty($phone)) {
            $query->where('phone', 'like', '%' . $phone . '%');
        }
        if (!empty($email)) {
            $query->where('email', 'like', '%' . $email . '%');
        }
        if ($status !== null && $status !== '') {
            $query->where('status', $status);
        }

        $vendors = $query->orderBy('id', 'asc')->get();

        $vendorIds = $vendors->pluck('id')->toArray();
        $calculatedBalances = [];
        if (!empty($vendorIds)) {
            $calculatedBalances = $this->calculateVendorBalances($vendorIds, $hasDateFilter ? $startDate : null, $hasDateFilter ? $endDate : null);
        }

        if (!empty($balanceType)) {
            $bType = strtolower($balanceType);
            $vendors = $vendors->filter(function($v) use ($bType, $calculatedBalances) {
                $bal = isset($calculatedBalances[$v->id]) ? $calculatedBalances[$v->id]['closing_balance'] : $v->balance;
                return $bType === 'credit' ? $bal >= 0 : $bal < 0;
            });
        }

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=vendors.csv",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['ID', 'Name', 'Phone', 'Email', 'Opening Balance', 'Balance', 'Status'];

        $callback = function() use($vendors, $columns, $calculatedBalances, $hasDateFilter) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($vendors as $vendor) {
                $openingBalance = '0.00';
                $balance = '0.00';
                
                if ($hasDateFilter && isset($calculatedBalances[$vendor->id])) {
                    $opBal = $calculatedBalances[$vendor->id]['opening_balance'];
                    $label = ($opBal >= 0) ? 'Cr' : 'Dr';
                    $openingBalance = number_format(abs($opBal), 2) . ' (' . $label . ')';
                } else if ($vendor->currentOpeningBalance) {
                    $opBal = $vendor->currentOpeningBalance->amount;
                    $label = $vendor->currentOpeningBalance->balance_type;
                    $openingBalance = number_format($opBal, 2) . ' (' . $label . ')';
                }
                
                if (isset($calculatedBalances[$vendor->id])) {
                    $bal = $calculatedBalances[$vendor->id]['closing_balance'];
                    $label = ($bal >= 0) ? 'Cr' : 'Dr';
                    $balance = number_format(abs($bal), 2) . ' (' . $label . ')';
                } else {
                    $bal = $vendor->balance;
                    $label = ($bal >= 0) ? 'Cr' : 'Dr';
                    $balance = number_format(abs($bal), 2) . ' (' . $label . ')';
                }

                $status = '';
                if ($vendor->status == 1) $status = 'Active';
                elseif ($vendor->status == 0) $status = 'Inactive';
                else $status = 'Deleted';

                fputcsv($file, [
                    $vendor->id,
                    $vendor->name,
                    $vendor->phone,
                    $vendor->email,
                    $openingBalance,
                    $balance,
                    $status
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function viewDetails(Request $request)
    {
        $data = $this->vendor->with([
            'fabrics',
            'purchaseAgent',
            'currentOpeningBalance',
        ])->where('id', $request->id)->firstOrFail();

        $calc = $this->calculateVendorBalances([$data->id], null, null);
        $data->calculated_closing_balance = $calc[$data->id]['closing_balance'] ?? $data->balance;
        $data->calculated_opening_balance = $calc[$data->id]['opening_balance'] ?? ($data->currentOpeningBalance ? $data->currentOpeningBalance->amount : 0);

        // Fetch recent records for vendor
        $data->recent_receipts = \App\Models\FabricReceipt::where('vendor_id', $data->id)->latest()->take(5)->get();
        $data->recent_returns = \App\Models\FabricReturn::where('vendor_id', $data->id)->latest()->take(5)->get();
        $data->recent_dispatches = \App\Models\AgentOrderDispatch::where('master_vendor_id', $data->id)->where('party_type', 'vendor')->latest()->take(5)->get();
        $data->recent_payments = \App\Models\Payment::where('party_id', $data->id)->where('party_type', \App\Models\Vendor::class)->latest()->take(5)->get();

        return $data;
    }

    public function store(Request $request){
        $save_data = new Vendor;
        $save_data->name = $request->name;
        $save_data->items = serialize($request->items);
        $save_data->phone = $request->phone;
        $save_data->email = $request->email;
        $save_data->address = $request->address;
        $save_data->purchase_agent_id = $request->purchase_agent_id;
        
        $balance = $request->balance ?? 0;
        if ($request->type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $save_data->balance = $balance;
        
        $save_data->sku = NULL;
        $save_data->status = $request->status;
        $save_data->description = $request->description;
        $save_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'vendor',
                'master_id' => $save_data->id,
                'financial_year' => MasterOpeningBalance::openingKey(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->type ?? 'Credit',
            ]
        );

        return true;
    }

    public function edit(Request $request){
        $data = $this->vendor->with('currentOpeningBalance')->where('id',$request->id)->first();
        return $data;
    }
    public function update(Request $request){
        $update_data = $this->vendor->find($request->id);
        
        $update_data->name = $request->name;
        $update_data->items = serialize($request->items);
        $update_data->phone = $request->phone;
        $update_data->email = $request->email;
        $update_data->address = $request->address;
        $update_data->purchase_agent_id = $request->purchase_agent_id;
        $update_data->sku = NULL;
        $update_data->status = $request->status;
        $update_data->description = $request->description;

        // Editing the opening balance moves the running balance by the difference only,
        // so purchases and payments already applied to this vendor are never wiped out.
        \Illuminate\Support\Facades\DB::transaction(function () use ($update_data, $request) {
            MasterOpeningBalance::applyOpeningChange($update_data, 'vendor', $request->has('balance') ? ($request->balance ?? 0) : null, $request->type ?? 'Credit');
            $update_data->save();
        });

        return true;
    }

    public function delete(Request $request){
        $data = $this->vendor->where('id',$request->id)->update([
            'status' => 3,
        ]);
        return $data;
    }

    public function items(){
        $data = Item::where('status',1)->get();
        return $data;
    }

    public function purchaseAgents(){
        $data = PurchaseAgent::where('status',1)->get();
        return $data;
    }

}