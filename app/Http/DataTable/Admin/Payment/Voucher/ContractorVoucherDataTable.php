<?php

namespace App\Http\DataTable\Admin\Payment\Voucher;

use Illuminate\Http\Request;
use App\Models\ContractorVoucher;
use Yajra\DataTables\Facades\DataTables;

class ContractorVoucherDataTable
{
    public function indexList($request)
    {
        $query = ContractorVoucher::with(['contractor', 'orderLot', 'items.orderLot']);

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('contractor_id') && !empty($request->contractor_id)) {
                    $query->where('contractor_id', $request->contractor_id);
                }

                if ($request->has('voucher_number') && !empty($request->voucher_number)) {
                    $query->where('voucher_number', 'like', "%{$request->voucher_number}%");
                }

                if ($request->has('lot_no') && !empty($request->lot_no)) {
                    $lotNo = trim($request->lot_no);
                    $query->where(function ($q) use ($lotNo) {
                        $q->whereHas('orderLot', function ($sq) use ($lotNo) {
                            $sq->where('lot_no', 'like', "%{$lotNo}%");
                        })
                        ->orWhereHas('items.orderLot', function ($sq) use ($lotNo) {
                            $sq->where('lot_no', 'like', "%{$lotNo}%");
                        });
                    });
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
                          ->orWhereHas('contractor', function ($sq) use ($searchValue) {
                              $sq->where('name', 'like', "%{$searchValue}%");
                          })
                          ->orWhereHas('orderLot', function ($sq) use ($searchValue) {
                              $sq->where('lot_no', 'like', "%{$searchValue}%");
                          })
                          ->orWhereHas('items.orderLot', function ($sq) use ($searchValue) {
                              $sq->where('lot_no', 'like', "%{$searchValue}%");
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
                return '<span class="text-nowrap text-secondary font-weight-500">' . date('d M Y', strtotime($row->voucher_date)) . '</span>';
            })
            ->editColumn('voucher_number', function ($row) {
                return '<span class="font-weight-600 text-dark">' . e($row->voucher_number) . '</span>';
            })
            ->editColumn('total_amount', function ($row) {
                return '<span class="font-weight-600" style="color: #05421c;">₹ ' . number_format($row->total_amount, 2) . '</span>';
            })
            ->addColumn('lot_number', function ($row) {
                if ($row->orderLot && $row->orderLot->lot_no) {
                    return '<span class="badge-erp-status badge-erp-info font-weight-bold">' . e($row->orderLot->lot_no) . '</span>';
                }
                $itemLots = $row->items->filter(function($i) { return $i->orderLot && $i->orderLot->lot_no; })
                    ->map(function($i) { return $i->orderLot->lot_no; })->unique();
                if ($itemLots->count() > 0) {
                    return $itemLots->map(function($l) { return '<span class="badge-erp-status badge-erp-info font-weight-bold mr-1">' . e($l) . '</span>'; })->implode(' ');
                }
                return '<span class="text-muted" style="font-size: 11px;">N/A</span>';
            })
            ->addColumn('document', function ($row) {
                if ($row->document) {
                    return '<a href="' . asset($row->document) . '" target="_blank" class="erp-action-btn erp-btn-view" data-toggle="tooltip" title="View Document"><i class="fas fa-file-pdf"></i></a>';
                }
                return '<span class="text-muted" style="font-size: 11px;">No slip</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <div class="d-flex align-items-center justify-content-center" style="gap: 6px;">
                    <a href="' . route('admin.payment.voucher.contractor.edit', ['id' => $row->id]) . '" class="erp-action-btn erp-btn-edit" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="' . route('admin.payment.voucher.contractor.delete', ['id' => $row->id]) . '" class="erp-action-btn erp-btn-delete" data-toggle="tooltip" title="Delete" onclick="return confirm(\'Are you sure you want to delete this voucher?\')"><i class="fas fa-trash"></i></a>
                </div>';
            })
            ->rawColumns(['voucher_date', 'voucher_number', 'total_amount', 'action', 'document', 'lot_number'])
            ->make(true);
    }
}
