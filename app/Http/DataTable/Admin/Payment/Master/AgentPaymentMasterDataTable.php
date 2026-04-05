<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\AgentPaymentMaster;
use Yajra\DataTables\Facades\DataTables;

class AgentPaymentMasterDataTable
{
    public function indexList($request)
    {
        $query = AgentPaymentMaster::query();

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
                    : '<span class="badge badge-xs badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <a href="' . route('admin.payment.master.agent_payment.edit', ['id' => $row->id]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.payment.master.agent_payment.delete', ['id' => $row->id]) . '" class="ml-2" data-toggle="tooltip" data-placement="top" title="Delete" onclick="return confirm(\'Are you sure you want to delete this Agent Payment?\')"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
