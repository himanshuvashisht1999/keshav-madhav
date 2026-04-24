<?php

namespace App\Http\DataTable\Admin\Payment\Master;

use App\Models\FactoryHeadMaster;
use Illuminate\Http\Request;

class FactoryHeadDataTable
{
    public function indexList(Request $request)
    {
        $draw = $request->get('draw');
        $start = $request->get("start");
        $rowperpage = $request->get("length");

        $columnIndex_arr = $request->get('order');
        $columnName_arr = $request->get('columns');
        $order_arr = $request->get('order');
        $search_arr = $request->get('search');

        $columnIndex = $columnIndex_arr[0]['column'] ?? 0;
        $columnName = $columnName_arr[$columnIndex]['data'] ?? 'id';
        $columnSortOrder = $order_arr[0]['dir'] ?? 'desc';
        $searchValue = $search_arr['value'] ?? '';

        // Total records
        $totalRecords = FactoryHeadMaster::count();
        $totalRecordwithFilter = FactoryHeadMaster::where('name', 'like', '%' . $searchValue . '%')
            ->count();

        // Fetch records
        $records = FactoryHeadMaster::orderBy($columnName, $columnSortOrder)
            ->where('name', 'like', '%' . $searchValue . '%')
            ->select('factory_head_masters.*')
            ->skip($start)
            ->take($rowperpage)
            ->get();

        $data_arr = array();

        foreach ($records as $record) {
            $status = '<span class="badge badge-' . ($record->status == 1 ? 'success' : 'danger') . '">' . ($record->status == 1 ? 'Active' : 'Inactive') . '</span>';
            
            $action = '
                <div class="btn-group">
                    <a href="' . route('admin.payment.master.factory_head.edit', ['id' => $record->id]) . '" class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></a>
                    <a href="' . route('admin.payment.master.factory_head.delete', ['id' => $record->id]) . '" class="btn btn-sm btn-danger ml-1" onclick="return confirm(\'Are you sure?\')"><i class="fa fa-trash"></i></a>
                </div>
            ';

            $balance_type = $record->balance >= 0 ? '<span class="badge badge-success">Cr</span>' : '<span class="badge badge-danger">Dr</span>';
            $balance = '₹ ' . number_format(abs($record->balance), 2) . ' ' . $balance_type;

            $data_arr[] = array(
                "id" => $record->id,
                "name" => $record->name,
                "balance" => $balance,
                "status" => $status,
                "action" => $action
            );
        }

        $response = array(
            "draw" => intval($draw),
            "iTotalRecords" => $totalRecords,
            "iTotalDisplayRecords" => $totalRecordwithFilter,
            "aaData" => $data_arr
        );

        return response()->json($response);
    }
}
