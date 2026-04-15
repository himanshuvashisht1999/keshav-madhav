<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\PurchaseAgent;
use Yajra\DataTables\Facades\DataTables;

class PurchaseAgentDataTable
{
    public function indexList($request)
    {
        $queue = PurchaseAgent::withSum('vendors', 'balance');

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id', 'desc');
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%")
                          ->orWhere('email', 'like', "%{$searchValue}%")
                          ->orWhere('phone', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->editColumn('balance', function ($queue) {
                $balance = $queue->vendors_sum_balance ?? 0;
                $color = ($balance >= 0) ? 'green' : 'red';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:bold;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->editColumn('status', function ($queue) {
                return ($queue->status == 1) 
                    ? '<span class="badge badge-success">Active</span>' 
                    : '<span class="badge badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
                return '
                <a href="' . route('admin.master.purchase-agent.edit', ['id' => $queue->id]) . '" class="btn btn-sm btn-info"><i class="fas fa-edit"></i></a>
                <a href="' . route('admin.master.purchase-agent.delete', ['id' => $queue->id]) . '" class="btn btn-sm btn-danger ml-1" onclick="return confirm(\'Are you sure?\')"><i class="fas fa-trash"></i></a>
                ';
            })
            ->rawColumns(['action', 'status', 'balance'])
            ->make(true);
    }
}
