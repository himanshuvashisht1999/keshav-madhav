<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use App\Models\Rent as Model;
use Illuminate\Http\Request;
use Yajra\DataTables\DataTables;

class RentDataTable
{
    public function indexList(Request $request)
    {
        $data = Model::where('status', '!=', 0);
        return DataTables::of($data)
            ->addIndexColumn()
            ->addColumn('action', function($row){
                $btn = '<a href="'.route('admin.payment.master.rent.edit', ['id' => $row->id]).'" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a> ';
                $btn .= '<a href="'.route('admin.payment.master.rent.delete', ['id' => $row->id]).'" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure you want to delete?\')"><i class="fas fa-trash"></i></a>';
                return $btn;
            })
            ->addColumn('status', function($row){
                return $row->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}
