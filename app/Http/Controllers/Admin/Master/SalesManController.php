<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\SalesMan;
use Illuminate\Http\Request;

class SalesManController extends Controller
{
    public function index(Request $request)
    {
        $query = SalesMan::query();
        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('phone', 'like', '%' . $request->search . '%');
        }

        $salesMen = $query->latest()->paginate(20);
        return view('admin.master.sales_man.index', compact('salesMen'));
    }

    public function allSalesMen()
    {
        $sales_men = SalesMan::select('id', 'name')->where('status', 1)->get();
        return response()->json($sales_men);
    }

    public function create()
    {
        return view('admin.master.sales_man.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        SalesMan::create($request->all());

        return redirect()->route('admin.master.sales-man.index')
                         ->with('success', 'Sales man created successfully.');
    }

    public function edit(SalesMan $salesMan)
    {
        return view('admin.master.sales_man.edit', compact('salesMan'));
    }

    public function update(Request $request, SalesMan $salesMan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'status' => 'required|boolean',
        ]);

        $salesMan->update($request->all());

        return redirect()->route('admin.master.sales-man.index')
                         ->with('success', 'Sales man updated successfully.');
    }

    public function destroy(SalesMan $salesMan)
    {
        $salesMan->delete();
        return redirect()->route('admin.master.sales-man.index')
                         ->with('success', 'Sales man deleted successfully.');
    }
}
