<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\BankAccount;
use Yajra\DataTables\Facades\DataTables;

class BankAccountDataTable
{
    public function indexList($request)
    {
        $query = BankAccount::with('currentOpeningBalance');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->get('search')['value']) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('bank_name', 'like', "%{$searchValue}%")
                            ->orWhere('account_name', 'like', "%{$searchValue}%")
                            ->orWhere('account_number', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->editColumn('status', function ($row) {
                return ($row->status == 1)
                    ? '<span class="badge badge-xs badge-success">Active</span>'
                    : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('balance', function ($row) {
                $type = $row->balance >= 0 ? '<span class="badge badge-success">Cr</span>' : '<span class="badge badge-danger">Dr</span>';
                return '₹ ' . number_format(abs($row->balance), 2) . ' ' . $type;
            })
            ->addColumn('opening_balance', function ($row) {
                if ($row->currentOpeningBalance) {
                    $type = $row->currentOpeningBalance->balance_type == 'Credit' ? '<span class="badge badge-success">Cr</span>' : '<span class="badge badge-danger">Dr</span>';
                    return '₹ ' . number_format($row->currentOpeningBalance->amount, 2) . ' ' . $type;
                }
                return '₹ 0.00';
            })
            ->addColumn('action', function ($row) {
                return '
                <a href="' . route('admin.payment.master.bank_account.edit', ['id' => $row->id]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.payment.master.bank_account.delete', ['id' => $row->id]) . '" class="ml-2" data-toggle="tooltip" data-placement="top" title="Delete" onclick="return confirm(\'Are you sure you want to delete this bank account?\')"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'status', 'balance', 'opening_balance'])
            ->make(true);
    }
}
