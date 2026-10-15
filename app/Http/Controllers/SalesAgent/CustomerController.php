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
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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
            'name' => 'required_if:subtype,direct|nullable|string|max:255',
            'phone' => 'required_if:subtype,direct|nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'shop_name' => 'required_if:subtype,agent|nullable|string|max:255',
            'shop_phone' => 'required_if:subtype,agent|nullable|string|max:20',
            'shop_email' => 'nullable|email|max:255',
            'sales_agent_id' => ['required_if:subtype,agent', 'nullable', Rule::exists('sales_agents', 'id')->where('status', 1)],
            'balance' => 'nullable|numeric|min:0',
            'balance_type' => 'nullable|in:Credit,Debit',
            'payment_term_days' => 'nullable|integer|min:0|max:3650',
            'brand_discounts' => 'nullable|array',
            'brand_discounts.*' => 'nullable|numeric|min:0|max:100',
        ], [
            'brand_discounts.*.max' => 'A brand discount cannot be more than 100%.',
            'brand_discounts.*.min' => 'A brand discount cannot be less than 0%.',
            'sales_agent_id.exists' => 'Please choose an active sales agent.',
        ]);

        $isShop = $request->subtype === 'agent';

        DB::beginTransaction();
        try {
            $customer = new MasterCustomer;
            $customer->type = 'domestic';
            $customer->subtype = $request->subtype;
            $customer->status = 1;
            $customer->payment_term_days = $request->payment_term_days ?? 120;
            $customer->balance = 0;
            if ($isShop) {
                // a SHOP under an existing agent
                $customer->name = $request->shop_name;
                $customer->phone = $request->shop_phone;
                $customer->email = $request->shop_email;
                $customer->gst_number = $request->shop_gst_number;
                $customer->address = $request->shop_address;
                $customer->parent_id = $request->sales_agent_id; // For hierarchy
                $customer->sales_agent_id = $request->sales_agent_id; // For compatibility/tracking
            } else {
                $customer->name = $request->name;
                $customer->phone = $request->phone;
                $customer->email = $request->email;
                $customer->gst_number = $request->gst_number;
                $customer->address = $request->address;
            }
            $customer->save();

            // the one opening balance row; the running balance starts from it
            MasterOpeningBalance::applyOpeningChange($customer, 'customer', $request->balance ?? 0, $request->balance_type ?? 'Credit');
            $customer->save();

            if (!$isShop && is_array($request->brand_discounts)) {
                foreach ($request->brand_discounts as $brand_id => $discount) {
                    if ($discount !== null && $discount !== '' && Brand::where('id', $brand_id)->exists()) {
                        CustomerBrandDiscount::create([
                            'customer_id' => $customer->id,
                            'brand_id' => $brand_id,
                            'discount_percentage' => $discount,
                        ]);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Could not save the customer: ' . $e->getMessage());
        }

        return redirect()->route('agent.dashboard')->with('success', $isShop
            ? 'Customer (Shop) created successfully!'
            : 'Customer (Direct) created successfully!');
    }
}
