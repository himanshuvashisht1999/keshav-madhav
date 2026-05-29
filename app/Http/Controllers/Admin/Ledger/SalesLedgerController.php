<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SalesLedgerController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\AgentOrderDispatch::with(['shop', 'vendor', 'agent'])
            ->latest('dispatch_date');

        if ($request->filled('party_id')) {
            $query->where('master_customer_id', $request->party_id);
        }

        if ($request->filled('vendor_id')) {
            $query->where('master_vendor_id', $request->vendor_id);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('dispatch_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('dispatch_date', '<=', $request->to_date);
        }

        if ($request->filled('item_type')) {
            $dispatchType = $request->item_type == 'Product' ? 'item' : 'fabric';
            $query->whereHas('orders', function ($q) use ($dispatchType) {
                $q->where(function ($sub) use ($dispatchType) {
                    $sub->where('sale_type', $dispatchType)
                        ->orWhere('order_type', $dispatchType);
                });
            });
        }

        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');

        $sales = $query->paginate(25)->appends($request->all());
        
        $parties = DB::table('master_customers')->select('id', 'name')->get();
        $vendors = DB::table('vendors')->select('id', 'name')->get();

        return view('admin.ledger.sales.index', compact('sales', 'parties', 'vendors', 'totalGrandTotal'));
    }
}
