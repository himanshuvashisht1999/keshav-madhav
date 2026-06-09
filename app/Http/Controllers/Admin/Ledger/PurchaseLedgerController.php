<?php

namespace App\Http\Controllers\Admin\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseLedgerController extends Controller
{
    public function index(Request $request)
    {
        // Fabric Receipts (Invoices)
        $fabricsQuery = DB::table('fabric_receipts')
            ->join('vendors', 'fabric_receipts.vendor_id', '=', 'vendors.id')
            ->select(
                'fabric_receipts.id as ref_id',
                'fabric_receipts.bill_no as invoice_no',
                'vendors.name as vendor_name',
                DB::raw("CAST('Fabric' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'fabric_receipts.time as date',
                DB::raw('COALESCE(fabric_receipts.total_amount, 0) as grand_total')
            );

        // Item Receipts (Invoices) - from Domestic Inventory Purchases
        $itemsQuery = DB::table('domestic_inventory_purchases')
            ->join('vendors', 'domestic_inventory_purchases.vendor_id', '=', 'vendors.id')
            ->select(
                'domestic_inventory_purchases.id as ref_id',
                DB::raw('NULL as invoice_no'),
                'vendors.name as vendor_name',
                DB::raw("CAST('Product/Accessory' AS CHAR) COLLATE utf8mb4_unicode_ci as item_type"),
                'domestic_inventory_purchases.purchase_date as date',
                DB::raw('COALESCE(domestic_inventory_purchases.total_amount, 0) as grand_total')
            );

        if ($request->filled('from_date')) {
            $fabricsQuery->whereDate('fabric_receipts.time', '>=', $request->from_date);
            $itemsQuery->whereDate('domestic_inventory_purchases.purchase_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $fabricsQuery->whereDate('fabric_receipts.time', '<=', $request->to_date);
            $itemsQuery->whereDate('domestic_inventory_purchases.purchase_date', '<=', $request->to_date);
        }

        if ($request->filled('item_type')) {
            if ($request->item_type == 'Fabric') {
                $itemsQuery->whereRaw('1 = 0');
            } elseif ($request->item_type == 'Product/Accessory') {
                $fabricsQuery->whereRaw('1 = 0');
            }
        }

        if ($request->filled('vendor_id')) {
            $fabricsQuery->where('fabric_receipts.vendor_id', $request->vendor_id);
            $itemsQuery->where('domestic_inventory_purchases.vendor_id', $request->vendor_id);
        }

        $combinedQuery = $fabricsQuery->union($itemsQuery);

        $query = DB::table(DB::raw("({$combinedQuery->toSql()}) as combined_purchases"))
            ->mergeBindings($combinedQuery);

        $totalGrandTotal = clone $query;
        $totalGrandTotal = $totalGrandTotal->sum('grand_total');

        $purchases = $query->orderBy('date', 'desc')->paginate(25)->appends($request->all());

        $vendors = DB::table('vendors')->select('id', 'name')->get();

        return view('admin.ledger.purchase.index', compact('purchases', 'vendors', 'totalGrandTotal'));
    }
}
