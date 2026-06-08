<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\MasterCustomer;
use App\Models\MasterOpeningBalance;
use App\Models\SalesAgent;
use App\Models\Brand;
use App\Models\CustomerBrandDiscount;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerController extends Controller
{
    public function create()
    {
        $agent = Auth::guard('sales_agent')->user();
        if (!$agent->is_master_agent) {
            abort(403, 'Unauthorized action.');
        }

        $items = [
            'agents' => SalesAgent::where('status', 1)->get(),
            'brands' => Brand::where('status', 1)->get(),
        ];

        return view('sales_agent.customers.create', compact('items'));
    }

    public function store(Request $request)
    {
        $agent = Auth::guard('sales_agent')->user();
        if (!$agent->is_master_agent) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'subtype' => 'required|in:direct,agent',
            'name' => 'required_if:subtype,direct|max:255',
            'phone' => 'required_if:subtype,direct|max:20',
            'shop_name' => 'required_if:subtype,agent|max:255',
            'shop_phone' => 'required_if:subtype,agent|max:20',
            'sales_agent_id' => 'required_if:subtype,agent',
        ]);

        $type = 'domestic';
        $subtype = $request->subtype;

        // If Agent subtype is selected, we are creating a SHOP under an existing Agent
        if ($subtype == 'agent') {
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

            return redirect()->route('agent.dashboard')->with('success', 'Customer (Shop) created successfully!');
        }

        // Standard logic for Domestic Direct
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
        $save_data->subtype = $subtype;
        $save_data->payment_term_days = $request->payment_term_days ?? 120;

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

        if ($request->has('brand_discounts')) {
            foreach ($request->brand_discounts as $brand_id => $discount) {
                if ($discount !== null && $discount !== '') {
                    CustomerBrandDiscount::create([
                        'customer_id' => $save_data->id,
                        'brand_id' => $brand_id,
                        'discount_percentage' => $discount,
                    ]);
                }
            }
        }

        return redirect()->route('agent.dashboard')->with('success', 'Customer (Direct) created successfully!');
    }
}
