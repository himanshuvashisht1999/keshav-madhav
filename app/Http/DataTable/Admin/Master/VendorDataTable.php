<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\Vendor;
use Yajra\DataTables\Facades\DataTables;

class VendorDataTable  {

    protected $vendor;
    public function __construct(Vendor $vendor) {
        $this->vendor = $vendor;
    }

    public function indexList($request){
        $queue = Vendor::with('currentOpeningBalance')->where('status', '!=', 3);

        if ($request->has('name') && !empty($request->name)) {
            $queue->where('name', 'like', "%{$request->get('name')}%");
        }
        if ($request->has('phone') && !empty($request->phone)) {
            $queue->where('phone', 'like', "%{$request->get('phone')}%");
        }
        if ($request->has('email') && !empty($request->email)) {
            $queue->where('email', 'like', "%{$request->get('email')}%");
        }
        if ($request->has('status') && $request->filled('status')) {
            $queue->where('status', $request->get('status'));
        }

        $filteredVendors = (clone $queue)->get();
        $totalCurrentBalance = $filteredVendors->sum('balance');
        
        $totalOpeningBalance = 0;
        $vendorIds = $filteredVendors->pluck('id')->toArray();
        if (!empty($vendorIds)) {
            $opBalances = \App\Models\MasterOpeningBalance::where('master_type', 'vendor')
                ->whereIn('master_id', $vendorIds)
                ->where('financial_year', \App\Models\MasterOpeningBalance::openingKey())
                ->get();
            foreach ($opBalances as $ob) {
                $amt = (float) $ob->amount;
                if (strtolower(trim($ob->balance_type)) === 'debit') {
                    $amt = -$amt;
                }
                $totalOpeningBalance += $amt;
            }
        }

        return DataTables::of($queue)->addIndexColumn()
            ->with('total_opening_balance', $totalOpeningBalance)
            ->with('total_current_balance', $totalCurrentBalance)
            ->filter(function ($query) {
                // Do nothing to preserve DataTables default if any or just let it be
            }) 
            ->order(function ($query) {
                $query->orderBy('id', 'asc');
            })
            ->editColumn('balance', function ($queue) {
                $balance = $queue->balance;
                $color = ($balance >= 0) ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                $type = ($balance >= 0) ? 'Credit' : 'Debit';
                return '<span style="color:' . $color . '; font-weight:700;">' . number_format(abs($balance), 2) . ' (' . $type . ')</span>';
            })
            ->addColumn('opening_balance', function ($queue) {
                if ($queue->currentOpeningBalance) {
                    $color = $queue->currentOpeningBalance->balance_type == 'Credit' ? 'var(--erp-green-primary)' : 'var(--erp-red-primary, #dc2626)';
                    $label = $queue->currentOpeningBalance->balance_type;
                    return '<span style="color:' . $color . '; font-weight:700;">' . number_format($queue->currentOpeningBalance->amount, 2) . ' (' . $label . ')</span>';
                }
                return '<span style="color:var(--erp-green-primary); font-weight:700;">0.00 (Credit)</span>';
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.vendor.edit',['id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="erp-action-btn erp-btn-delete" title="Delete"><i class="fas fa-trash"></i></a>
                </div>
                ';
            })
            
            ->rawColumns(['action', 'status', 'balance', 'opening_balance'])
            ->make(true);
    }
}