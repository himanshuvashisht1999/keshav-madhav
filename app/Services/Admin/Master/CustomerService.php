<?php

namespace App\Services\Admin\Master;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Auth;
use App\Models\MasterCustomer;
use App\Models\Item;
use App\Models\MasterOpeningBalance;
use App\Http\DataTable\Admin\Master\CustomerDataTable as DataTable;

class CustomerService
{
    protected $datatable;
    protected $customer;
    public function __construct(
        DataTable $datatable,
        MasterCustomer $customer
    ) {
        $this->datatable = $datatable;
        $this->customer = $customer;
    }

    public function index(Request $request)
    {
        return true;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function calculateCustomerBalances($customerIds, $startDate = null, $endDate = null)
    {
        $balances = [];

        // Swap dates if startDate is greater than endDate
        if ($startDate && $endDate && $startDate > $endDate) {
            $temp = $startDate;
            $startDate = $endDate;
            $endDate = $temp;
        }

        // Determine financial year for opening balance (from startDate, fallback to endDate, fallback to current year)
        $opRefDate = $startDate ?: $endDate;
        $opFinancialYear = \App\Models\MasterOpeningBalance::getFinancialYearForDate($opRefDate);

        // Determine financial year for closing balance (from endDate, fallback to startDate, fallback to current year)
        $clRefDate = $endDate ?: $startDate;
        $clFinancialYear = \App\Models\MasterOpeningBalance::getFinancialYearForDate($clRefDate);
        
        $opStartYear = explode('-', $opFinancialYear)[0];
        $opFyStartDate = $opStartYear . '-04-01';

        $clStartYear = explode('-', $clFinancialYear)[0];
        $clFyStartDate = $clStartYear . '-04-01';

        $queryStartDate = min($opFyStartDate, $clFyStartDate);

        // 1. Get initial opening balances of the opening financial year
        $opOpeningBalances = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
            ->whereIn('master_id', $customerIds)
            ->where('financial_year', $opFinancialYear)
            ->get()
            ->keyBy('master_id');

        // 2. Get initial opening balances of the closing financial year
        $clOpeningBalances = \App\Models\MasterOpeningBalance::where('master_type', 'customer')
            ->whereIn('master_id', $customerIds)
            ->where('financial_year', $clFinancialYear)
            ->get()
            ->keyBy('master_id');

        // Initialize balances
        foreach ($customerIds as $id) {
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

        // Get customer master ID for journal vouchers
        $jvMasterIds = \App\Models\AdjustmentMaster::where('model_name', 'App\Models\MasterCustomer')->pluck('id');

        // A. AgentOrderDispatch (Debit)
        $dispatches = \App\Models\AgentOrderDispatch::whereIn('master_customer_id', $customerIds)
            ->where('party_type', 'customer')
            ->where('status', 'dispatched')
            ->whereDate('dispatch_date', '>=', $queryStartDate)
            ->get();
        foreach ($dispatches as $d) {
            $date = $d->dispatch_date;
            $amount = (float) $d->grand_total;
            $id = $d->master_customer_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                $balances[$id]['opening_debit'] += $amount;
            }
            if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                $balances[$id]['closing_debit'] += $amount;
            }
        }

        // B. OrderDispatch (Debit)
        $orderDispatches = \App\Models\OrderDispatch::whereIn('customer_id', $customerIds)
            ->whereDate('dispatch_date', '>=', $queryStartDate)
            ->get();
        foreach ($orderDispatches as $od) {
            $date = $od->dispatch_date;
            $amount = (float) $od->total_amount;
            $id = $od->customer_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                $balances[$id]['opening_debit'] += $amount;
            }
            if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                $balances[$id]['closing_debit'] += $amount;
            }
        }

        // C. AgentOrderReturn (Credit)
        $returns = \App\Models\AgentOrderReturn::whereHas('dispatch', function($q) use ($customerIds) {
            $q->whereIn('master_customer_id', $customerIds)->where('party_type', 'customer');
        })->with('dispatch')
          ->whereDate('return_date', '>=', $queryStartDate)
          ->get();
        foreach ($returns as $r) {
            if (!$r->dispatch) continue;
            $date = $r->return_date;
            $amount = (float) $r->grand_total;
            $id = $r->dispatch->master_customer_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                $balances[$id]['opening_credit'] += $amount;
            }
            if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                $balances[$id]['closing_credit'] += $amount;
            }
        }

        // D. Payments (Credit / Debit)
        $payments = \App\Models\Payment::whereIn('party_id', $customerIds)
            ->where('party_type', 'App\Models\MasterCustomer')
            ->where(function($q) {
                $q->where('paymentable_type', '!=', \App\Models\JournalVoucher::class)
                  ->orWhereNull('paymentable_type');
            })
            ->whereDate('payment_date', '>=', $queryStartDate)
            ->get();
        foreach ($payments as $p) {
            $date = $p->payment_date;
            $amount = (float) $p->amount;
            $id = $p->party_id;
            
            $isCredit = in_array($p->payment_type, ['received', 'credit']);
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($isCredit) {
                if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                    $balances[$id]['opening_credit'] += $amount;
                }
                if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                    $balances[$id]['closing_credit'] += $amount;
                }
            } else {
                if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                    $balances[$id]['opening_debit'] += $amount;
                }
                if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                    $balances[$id]['closing_debit'] += $amount;
                }
            }
        }

        // E. DomesticInventoryPurchase (Credit)
        $purchases = \App\Models\DomesticInventoryPurchase::whereIn('customer_id', $customerIds)
            ->whereDate('created_at', '>=', $queryStartDate)
            ->get();
        foreach ($purchases as $ip) {
            $date = $ip->created_at;
            $amount = (float) $ip->total_amount;
            $id = $ip->customer_id;
            
            $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
            if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                $balances[$id]['opening_credit'] += $amount;
            }
            if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                $balances[$id]['closing_credit'] += $amount;
            }
        }

        // F. JournalVouchers (Credit / Debit)
        if ($jvMasterIds->isNotEmpty()) {
            $vouchers = \App\Models\JournalVoucherItem::with('voucher')
                ->whereIn('master_type', $jvMasterIds)
                ->whereIn('master_id', $customerIds)
                ->whereHas('voucher', function($q) use ($queryStartDate) {
                    $q->whereDate('date', '>=', $queryStartDate);
                })
                ->get();
            foreach ($vouchers as $v) {
                if (!$v->voucher) continue;
                $date = $v->voucher->date;
                $amount = (float) $v->amount;
                $id = $v->master_id;
                
                $isCredit = strtolower($v->type) === 'credit';
                $txDate = \Carbon\Carbon::parse($date)->format('Y-m-d');
                if ($isCredit) {
                    if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                        $balances[$id]['opening_credit'] += $amount;
                    }
                    if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                        $balances[$id]['closing_credit'] += $amount;
                    }
                } else {
                    if ($txDate >= $opFyStartDate && (!$startDate || $txDate < $startDate)) {
                        $balances[$id]['opening_debit'] += $amount;
                    }
                    if ($txDate >= $clFyStartDate && (!$endDate || $txDate <= $endDate)) {
                        $balances[$id]['closing_debit'] += $amount;
                    }
                }
            }
        }

        // Calculate final opening and closing balances
        $results = [];
        foreach ($customerIds as $id) {
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
        $query = MasterCustomer::with(['currentOpeningBalance', 'agent'])->where('status', '!=', 3);

        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', "%{$request->get('name')}%");
        }
        if ($request->has('phone') && !empty($request->phone)) {
            $query->where('phone', 'like', "%{$request->get('phone')}%");
        }
        if ($request->has('agent_ids') && !empty($request->agent_ids)) {
            $agentIds = is_array($request->agent_ids) ? $request->agent_ids : explode(',', $request->agent_ids);
            $agentIds = array_filter($agentIds);
            if (!empty($agentIds)) {
                $query->whereIn('sales_agent_id', $agentIds);
            }
        } elseif ($request->has('agent_name') && !empty($request->agent_name)) {
            $query->whereHas('agent', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->get('agent_name')}%");
            });
        }
        if ($request->has('status') && $request->filled('status')) {
            $query->where('status', $request->get('status'));
        }
        if ($request->has('type') && $request->filled('type')) {
            $type = $request->get('type');
            if ($type == 'direct') {
                $query->where('subtype', 'direct');
            } else {
                $query->where('type', $type);
            }
        }

        $customers = $query->orderBy('id', 'asc')->get();
        
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');
        $hasDateFilter = !empty($startDate) || !empty($endDate);
        
        $calculatedBalances = [];
        if ($hasDateFilter) {
            $customerIds = $customers->pluck('id')->toArray();
            $calculatedBalances = $this->calculateCustomerBalances($customerIds, $startDate, $endDate);
        }

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.master.customer.pdf', compact('customers', 'startDate', 'endDate', 'hasDateFilter', 'calculatedBalances'));
        return $pdf->download('customers.pdf');
    }

    public function store(Request $request)
    {
        $type = $request->type ?? 'corporate';
        $subtype = $request->subtype;

        // If Agent subtype is selected, we are essentially creating a SHOP under an existing Agent
        if ($type == 'domestic' && $subtype == 'agent') {
            $shop = new MasterCustomer;
            $shop->name = $request->shop_name;
            $shop->phone = $request->shop_phone;
            $shop->email = $request->shop_email;
            $shop->address = $request->shop_address;
            $shop->type = 'domestic';
            $shop->subtype = 'agent';
            $shop->parent_id = $request->sales_agent_id; // For hierarchy
            $shop->sales_agent_id = $request->sales_agent_id; // For compatibility/tracking
            $balance = $request->balance ?? 0;
            if ($request->balance_type == 'Debit') {
                $balance = -abs($balance);
            } else {
                $balance = abs($balance);
            }
            $shop->balance = $balance;
            $shop->status = 1;
            $shop->payment_term_days = $request->payment_term_days ?? 120;
            $shop->save();

            MasterOpeningBalance::updateOrCreate(
                [
                    'master_type' => 'customer',
                    'master_id' => $shop->id,
                    'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
                ],
                [
                    'amount' => abs($request->balance ?? 0),
                    'balance_type' => $request->balance_type ?? 'Credit',
                ]
            );

            return true;
        }

        // Standard logic for Corporate or Domestic Direct
        $save_data = new MasterCustomer;
        $save_data->name = $request->name;
        $save_data->phone = $request->phone;
        $save_data->email = $request->email;
        $save_data->address = $request->address;
        $balance = $request->balance ?? 0;
        if ($request->balance_type == 'Debit') {
            $balance = -abs($balance);
        } else {
            $balance = abs($balance);
        }
        $save_data->balance = $balance;
        $save_data->status = $request->status ?? 1;
        $save_data->type = $type;
        $save_data->payment_term_days = $request->payment_term_days ?? 120;

        if ($type == 'domestic') {
            $save_data->subtype = $subtype;
            if ($subtype == 'direct') {
                // $save_data->discount_percentage = $request->discount_percentage ?? 0;
            }
        }

        $save_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'customer',
                'master_id' => $save_data->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->balance_type ?? 'Credit',
            ]
        );

        if ($type == 'domestic' && $subtype == 'direct' && $request->has('brand_discounts')) {
            foreach ($request->brand_discounts as $brand_id => $discount) {
                if ($discount !== null && $discount !== '') {
                    \App\Models\CustomerBrandDiscount::create([
                        'customer_id' => $save_data->id,
                        'brand_id' => $brand_id,
                        'discount_percentage' => $discount,
                    ]);
                }
            }
        }
        return true;
    }

    public function edit(Request $request)
    {
        $data = $this->customer->with(['shops', 'brandDiscounts', 'currentOpeningBalance'])->where('id', $request->id)->first();
        return $data;
    }

    public function update(Request $request)
    {
        $update_data = $this->customer->find($request->id);
        $type = $request->type ?? 'corporate';
        $subtype = $request->subtype;

        // General update for Name, Phone, etc. (unless hidden by UI logic which we handle below)
        if (!($type == 'domestic' && $subtype == 'agent' && !$update_data->parent_id)) {
            $update_data->name = $request->name ?: $update_data->name;
            $update_data->phone = $request->phone ?: $update_data->phone;
            $update_data->email = $request->email ?: $update_data->email;
            $update_data->address = $request->address ?: $update_data->address;
            $balance = $request->balance ?? $update_data->balance;
            if ($request->balance_type == 'Debit') {
                $balance = -abs($balance);
            } else {
                $balance = abs($balance);
            }
            $update_data->balance = $balance;
        } else {
            // If it's the Parent Agent being updated, we don't change its name/phone from the shop fields
        }

        $update_data->status = $request->status;
        $update_data->type = $type;
        $update_data->payment_term_days = $request->payment_term_days ?? $update_data->payment_term_days;

        if ($type == 'domestic') {
            $update_data->subtype = $subtype;
            if ($subtype == 'direct') {
                // $update_data->discount_percentage = $request->discount_percentage ?? 0;
                $update_data->parent_id = null;
                $update_data->sales_agent_id = null;
            } elseif ($subtype == 'agent') {
                // If this itself is becoming a shop/agent linked to another
                if ($request->sales_agent_id) {
                    $update_data->parent_id = $request->sales_agent_id;
                    $update_data->sales_agent_id = $request->sales_agent_id;
                    // Update shop details for this record since it's now acting as a shop
                    $update_data->name = $request->shop_name ?: $update_data->name;
                    $update_data->phone = $request->shop_phone ?: $update_data->phone;
                    $update_data->email = $request->shop_email ?: $update_data->email;
                    $update_data->address = $request->shop_address ?: $update_data->address;
                }
            }
        } else {
            // Corporate
            $update_data->subtype = null;
            $update_data->parent_id = null;
            $update_data->sales_agent_id = null;
        }

        $update_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'customer',
                'master_id' => $update_data->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->balance_type ?? 'Credit',
            ]
        );

        // If it was an existing Agent (no parent_id) and we are adding/updating a SUB-SHOP
        if ($type == 'domestic' && $subtype == 'agent' && !empty($request->shop_name) && !$update_data->parent_id) {
            $shop = $this->customer->where('parent_id', $update_data->id)->first() ?: new MasterCustomer;
            $shop->name = $request->shop_name;
            $shop->phone = $request->shop_phone;
            $shop->email = $request->shop_email;
            $shop->address = $request->shop_address;
            $shop->type = 'domestic';
            $shop->subtype = 'agent';
            $shop->parent_id = $update_data->id;
            $shop->sales_agent_id = $update_data->id;
            $balance = $request->balance ?? $shop->balance;
            if ($request->balance_type == 'Debit') {
                $balance = -abs($balance);
            } else {
                $balance = abs($balance);
            }
            $shop->balance = $balance;
            $shop->status = 1;
            $shop->payment_term_days = $request->payment_term_days ?? 120;
            $shop->save();

            MasterOpeningBalance::updateOrCreate(
                [
                    'master_type' => 'customer',
                    'master_id' => $shop->id,
                    'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
                ],
                [
                    'amount' => abs($request->balance ?? 0),
                    'balance_type' => $request->balance_type ?? 'Credit',
                ]
            );
        }

        if ($type == 'domestic' && $subtype == 'direct' && $request->has('brand_discounts')) {
            \App\Models\CustomerBrandDiscount::where('customer_id', $update_data->id)->delete();
            foreach ($request->brand_discounts as $brand_id => $discount) {
                if ($discount !== null && $discount !== '') {
                    \App\Models\CustomerBrandDiscount::create([
                        'customer_id' => $update_data->id,
                        'brand_id' => $brand_id,
                        'discount_percentage' => $discount,
                    ]);
                }
            }
        }

        return true;
    }

    public function delete(Request $request)
    {
        $data = $this->customer->where('id', $request->id)->update([
            'status' => 3,
        ]);
        return $data;
    }

    public function items()
    {
        $res['items'] = Item::where('status', 1)->get();
        $res['agents'] = \App\Models\SalesAgent::where('status', 1)->get();
        $res['brands'] = \App\Models\Brand::where('status', 1)->get();
        return $res;
    }

}