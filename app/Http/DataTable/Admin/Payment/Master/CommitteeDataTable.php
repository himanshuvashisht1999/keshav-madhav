<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use Illuminate\Http\Request;
use App\Models\Committee;
use Yajra\DataTables\Facades\DataTables;

class CommitteeDataTable
{
    public function indexList($request)
    {
        $query = Committee::query();

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
                    ? '<span class="badge badge-success">Active</span>'
                    : '<span class="badge badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($row) {
                return '
                <a href="' . route('admin.payment.master.committee.edit', ['id' => $row->id]) . '" class="text-muted" data-toggle="tooltip" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="' . route('admin.payment.master.committee.delete', ['id' => $row->id]) . '" class="ml-2 text-danger" data-toggle="tooltip" title="Delete" onclick="return confirm(\'Are you sure you want to delete this committee?\')"><i class="fas fa-trash"></i></a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
