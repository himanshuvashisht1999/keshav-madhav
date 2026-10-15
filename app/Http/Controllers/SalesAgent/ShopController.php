<?php

namespace App\Http\Controllers\SalesAgent;

use App\Http\Controllers\Controller;
use App\Models\MasterCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShopController extends Controller
{
    /** The agent's own shops; deleted shops (status 3) are never shown or changed here. */
    private function myShops()
    {
        return MasterCustomer::where('sales_agent_id', Auth::guard('sales_agent')->id())->where('status', '!=', 3);
    }

    /** Shop details an agent may enter. The balance is not one of them: it comes from the office's ledger. */
    private function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'contact_person' => 'nullable|string|max:255',
            'gst_number' => 'nullable|string|max:50',
        ];
    }

    public function index(Request $request)
    {
        $query = $this->myShops();

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
        $validated = $request->validate($this->rules());

        $validated['sales_agent_id'] = Auth::guard('sales_agent')->id();
        $validated['status'] = 1;
        $validated['type'] = 'domestic';
        $validated['balance'] = 0;

        MasterCustomer::create($validated);

        return redirect()->route('agent.shops.index')->with('success', 'Shop added successfully');
    }

    public function edit($id)
    {
        $shop = $this->myShops()->where('id', $id)->firstOrFail();
        return view('sales_agent.shops.edit', compact('shop'));
    }

    public function update(Request $request, $id)
    {
        $shop = $this->myShops()->where('id', $id)->firstOrFail();

        $shop->update($request->validate($this->rules()));

        return redirect()->route('agent.shops.index')->with('success', 'Shop updated successfully');
    }

    public function toggleStatus($id)
    {
        $shop = $this->myShops()->where('id', $id)->firstOrFail();

        // active <-> inactive only
        $shop->status = (int) $shop->status === 1 ? 0 : 1;
        $shop->save();

        $statusMsg = $shop->status ? 'activated' : 'deactivated';
        return redirect()->back()->with('success', "Shop $statusMsg successfully");
    }
}
