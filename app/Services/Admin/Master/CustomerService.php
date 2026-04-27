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