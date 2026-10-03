<?php

namespace App\Http\DataTable\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Models\ConsumableVoucher;
use Yajra\DataTables\Facades\DataTables;

class ConsumableVoucherDataTable
{
    public function indexList($request)
    {
        $query = ConsumableVoucher::with('consumableGood');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('consumable_good_id') && !empty($request->consumable_good_id)) {
                    $query->where('consumable_good_id', $request->consumable_good_id);
                }

                if ($request->has('voucher_number') && !empty($request->voucher_number)) {
                    $query->where('voucher_number', 'like', "%{$request->voucher_number}%");
                }

                if ($request->has('from_date') && !empty($request->from_date)) {
                    $query->whereDate('voucher_date', '>=', $request->from_date);
                }

                if ($request->has('to_date') && !empty($request->to_date)) {
                    $query->whereDate('voucher_date', '<=', $request->to_date);
                }

                if ($request->has('min_amount') && $request->min_amount !== null && $request->min_amount !== '') {
                    $query->where('total_amount', '>=', $request->min_amount);
                }

                if ($request->has('max_amount') && $request->max_amount !== null && $request->max_amount !== '') {
                    $query->where('total_amount', '<=', $request->max_amount);
                }

                if ($request->has('has_document') && !empty($request->has_document)) {
                    if ($request->has_document === 'yes') {
                        $query->whereNotNull('document')->where('document', '!=', '');
                    } elseif ($request->has_document === 'no') {
                        $query->where(function ($q) {
                            $q->whereNull('document')->orWhere('document', '');
                        });
                    }
                }

                if (!empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('voucher_number', 'like', "%{$searchValue}%")
                          ->orWhere('total_amount', 'like', "%{$searchValue}%")
                          ->orWhere('remarks', 'like', "%{$searchValue}%")
                          ->orWhereHas('consumableGood', function ($sq) use ($searchValue) {
                              $sq->where('name', 'like', "%{$searchValue}%");
                          });
                    });
                }
            })
            ->order(function ($query) {
                if (!request()->has('order')) {
                    $query->orderBy('voucher_date', 'desc')->orderBy('id', 'desc');
                }
            })
            ->editColumn('voucher_date', function ($row) {
                return date('d M Y', strtotime($row->voucher_date));
            })
            ->editColumn('total_amount', function ($row) {
                return '₹ ' . number_format($row->total_amount, 2);
            })
            ->addColumn('document', function ($row) {
                if ($row->document) {
                    return '<a href="' . asset($row->document) . '" target="_blank" class="btn btn-xs btn-info"><i class="fas fa-file-download mr-1"></i> View</a>';
                }
                return '<span class="badge badge-secondary">No Slip</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <a href="' . route('admin.payment.voucher.consumable.show', ['id' => $row->id]) . '" class="mr-2" data-toggle="tooltip" data-placement="top" title="View"><i class="fas fa-eye text-primary"></i></a>
                <a href="' . route('admin.payment.voucher.consumable.edit', ['id' => $row->id]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.payment.voucher.consumable.delete', ['id' => $row->id]) . '" class="ml-2" data-toggle="tooltip" data-placement="top" title="Delete" onclick="return confirm(\'Are you sure you want to delete this voucher?\')"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'document'])
            ->make(true);
    }
}
