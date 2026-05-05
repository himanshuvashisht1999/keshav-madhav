<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\ConsumableGood;
use Yajra\DataTables\Facades\DataTables;

class ConsumableGoodDataTable
{
    public function indexList($request)
    {
        $query = ConsumableGood::with('currentOpeningBalance');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->get('search')['value']) {
                    $searchValue = $request->get('search')['value'];
                    $query->where('name', 'like', "%{$searchValue}%");
                }
            })
            ->editColumn('status', function ($row) {
                return ($row->status == 1)
                    ? '<span class="badge badge-xs badge-success">Active</span>'
                    : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('opening_balance', function ($row) {
                $opening_balance = '₹ 0.00';
                if ($row->currentOpeningBalance) {
                    $o_type = $row->currentOpeningBalance->balance_type == 'Credit' ? '<span class="badge badge-success">Cr</span>' : '<span class="badge badge-danger">Dr</span>';
                    $opening_balance = '₹ ' . number_format($row->currentOpeningBalance->amount, 2) . ' ' . $o_type;
                }
                return $opening_balance;
            })
            ->addColumn('balance', function ($row) {
                $balance_type = $row->balance >= 0 ? '<span class="badge badge-success">Cr</span>' : '<span class="badge badge-danger">Dr</span>';
                return '₹ ' . number_format(abs($row->balance), 2) . ' ' . $balance_type;
            })
            ->addColumn('action', function ($row) {
                return '
                <a href="' . route('admin.payment.master.consumable_good.edit', ['id' => $row->id]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.payment.master.consumable_good.delete', ['id' => $row->id]) . '" class="ml-2" data-toggle="tooltip" data-placement="top" title="Delete" onclick="return confirm(\'Are you sure you want to delete this consumable good?\')"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'status', 'opening_balance', 'balance'])
            ->make(true);
    }
}
