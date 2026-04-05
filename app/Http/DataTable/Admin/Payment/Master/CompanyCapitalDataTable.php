<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\CompanyCapital;
use Yajra\DataTables\Facades\DataTables;

class CompanyCapitalDataTable
{
    public function indexList($request)
    {
        $capitalQuery = \DB::table('company_capitals')
            ->select(
                'id',
                'amount',
                'payment_method_type',
                'payment_method_id',
                'transaction_date',
                'remarks',
                \DB::raw("'Capital' as source")
            );

        $paymentQuery = \DB::table('payments')
            ->whereNotNull('payment_method_type')
            ->whereNotNull('payment_method_id')
            ->select(
                'id',
                \DB::raw("CASE WHEN payment_type = 'received' THEN amount ELSE -amount END as amount"),
                'payment_method_type',
                'payment_method_id',
                'payment_date as transaction_date',
                'remarks',
                \DB::raw("'Payment' as source")
            );

        // Filters
        if ($request->mode) {
            if ($request->mode == 'all_banks') {
                $capitalQuery->where('payment_method_type', 'Bank');
                $paymentQuery->where('payment_method_type', 'Bank');
            } elseif ($request->mode == 'all_cash') {
                $capitalQuery->where('payment_method_type', 'Cash');
                $paymentQuery->where('payment_method_type', 'Cash');
            } else {
                $parts = explode('_', $request->mode);
                if (count($parts) == 2) {
                    $type = $parts[0] == 'bank' ? 'Bank' : 'Cash';
                    $id = $parts[1];
                    $capitalQuery->where('payment_method_type', $type)->where('payment_method_id', $id);
                    $paymentQuery->where('payment_method_type', $type)->where('payment_method_id', $id);
                }
            }
        }

        if ($request->from_date) {
            $capitalQuery->whereDate('transaction_date', '>=', $request->from_date);
            $paymentQuery->whereDate('payment_date', '>=', $request->from_date);
        }
        if ($request->to_date) {
            $capitalQuery->whereDate('transaction_date', '<=', $request->to_date);
            $paymentQuery->whereDate('payment_date', '<=', $request->to_date);
        }

        if ($request->source) {
            if ($request->source == 'Capital') {
                $query = $capitalQuery;
            } else {
                $query = $paymentQuery;
            }
        } else {
            $query = $capitalQuery->union($paymentQuery);
        }

        // Wrap in a subquery to allow ordering and pagination on the union result
        $totalQuery = \DB::table(\DB::raw("({$query->toSql()}) as combined_capital"))
            ->mergeBindings($query)
            ->orderBy('transaction_date', 'desc')
            ->orderBy('id', 'desc');

        return DataTables::of($totalQuery)
            ->addIndexColumn()
            ->addColumn('payment_method', function ($row) {
                if ($row->payment_method_type == 'Bank') {
                    $method = \App\Models\BankAccount::find($row->payment_method_id);
                    return "Bank: " . ($method ? $method->bank_name . " (" . $method->account_number . ")" : 'N/A');
                } else {
                    $method = \App\Models\CashPayment::find($row->payment_method_id);
                    return "Cash: " . ($method ? $method->name : 'N/A');
                }
            })
            ->editColumn('amount', function ($row) {
                $color = $row->amount >= 0 ? 'text-success' : 'text-danger';
                $prefix = $row->amount >= 0 ? '+' : '';
                return '<span class="' . $color . '">' . $prefix . number_format($row->amount, 2) . '</span>';
            })
            ->editColumn('transaction_date', function ($row) {
                return date('d-m-Y', strtotime($row->transaction_date));
            })
            ->editColumn('remarks', function ($row) {
                $sourceBadge = $row->source == 'Capital' ? '<span class="badge badge-info">Capital</span> ' : '<span class="badge badge-secondary">Payment</span> ';
                return $sourceBadge . ($row->remarks ?? 'N/A');
            })
            ->rawColumns(['amount', 'remarks'])
            ->make(true);
    }
}
