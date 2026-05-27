<?php

namespace App\Http\DataTable\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Models\WashingVoucher;
use Yajra\DataTables\Facades\DataTables;

class WashingVoucherDataTable
{
    public function indexList($request)
    {
        $query = WashingVoucher::with(['washingMaster', 'items.orderLot']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->get('search')['value']) {
                    $searchValue = $request->get('search')['value'];
                    $query->whereHas('washingMaster', function($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhere('voucher_number', 'like', "%{$searchValue}%");
                }
            })
            ->editColumn('voucher_date', function ($row) {
                return date('d M Y', strtotime($row->voucher_date));
            })
            ->editColumn('total_amount', function ($row) {
                return '₹ ' . number_format($row->total_amount, 2);
            })
            ->addColumn('lot_number', function ($row) {
                $lots = $row->items->filter(function($item) { return $item->orderLot; })->map(function($item) { return $item->orderLot->lot_no; })->unique();
                return $lots->count() > 0 ? $lots->implode(', ') : 'N/A';
            })
            ->addColumn('document', function ($row) {
                if ($row->document) {
                    return '<a href="' . asset($row->document) . '" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-file-download"></i> View</a>';
                }
                return 'No Document';
            })
            ->addColumn('action', function ($row) {
                return '
                <a href="' . route('admin.payment.voucher.washing.edit', ['id' => $row->id]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.payment.voucher.washing.delete', ['id' => $row->id]) . '" class="ml-2" data-toggle="tooltip" data-placement="top" title="Delete" onclick="return confirm(\'Are you sure you want to delete this voucher?\')"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'document'])
            ->make(true);
    }
}
