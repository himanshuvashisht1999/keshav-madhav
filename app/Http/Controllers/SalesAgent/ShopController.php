<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\MasterCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $query = MasterCustomer::where('sales_agent_id', Auth::guard('sales_agent')->id());

        if ($request->has('search') && $request->search != '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('phone', 'LIKE', "%{$search}%");
            });
        }

        $shops = $query->latest()->get();
        return view('sales_agent.shops.index', compact('shops'));
    }

    public function create()
    {
        return view('sales_agent.shops.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['sales_agent_id'] = Auth::guard('sales_agent')->id();
        $validated['status'] = 1;

        MasterCustomer::create($validated);

        return redirect()->route('agent.shops.index')->with('success', 'Shop added successfully');
    }

    public function edit($id)
    {
        $shop = MasterCustomer::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->firstOrFail();
        return view('sales_agent.shops.edit', compact('shop'));
    }

    public function update(Request $request, $id)
    {
        $shop = MasterCustomer::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        $shop->update($validated);

        return redirect()->route('agent.shops.index')->with('success', 'Shop updated successfully');
    }

    public function toggleStatus($id)
    {
        $shop = MasterCustomer::where('id', $id)
            ->where('sales_agent_id', Auth::guard('sales_agent')->id())
            ->firstOrFail();

        $shop->status = !$shop->status;
        $shop->save();

        $statusMsg = $shop->status ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Shop $statusMsg successfully");
    }
}
