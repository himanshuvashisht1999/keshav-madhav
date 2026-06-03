<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\MasterOrderRemark;

class MasterOrderRemarkController extends Controller
{
    public function index()
    {
        return view('admin.master.order_remarks.index');
    }

    public function indexList(Request $request)
    {
        $query = MasterOrderRemark::query();
        if($request->has('search') && $request->search['value'] != '') {
            $searchValue = $request->search['value'];
            $query->where('name', 'like', '%' . $searchValue . '%');
        }
        $totalRecords = MasterOrderRemark::count();
        $filteredRecords = $query->count();
        
        $query->orderBy('id', 'desc');
        if($request->has('start') && $request->has('length') && $request->length != -1){
            $query->skip($request->start)->take($request->length);
        }
        $remarks = $query->get();
        
        $data = [];
        foreach($remarks as $key => $remark) {
            $status = $remark->status == 1 ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>';
            $action = '
                <a href="' . route('admin.master.order-remarks.edit', ['id' => $remark->id]) . '" class="btn btn-sm btn-primary" title="Edit"><i class="fas fa-edit"></i></a>
                <a href="' . route('admin.master.order-remarks.delete', ['id' => $remark->id]) . '" class="btn btn-sm btn-danger" onclick="return confirm(\'Are you sure?\')" title="Delete"><i class="fas fa-trash"></i></a>
            ';
            $data[] = [
                'DT_RowIndex' => $key + 1,
                'name' => $remark->name,
                'status' => $status,
                'action' => $action
            ];
        }

        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $totalRecords,
            "recordsFiltered" => $filteredRecords,
            "data" => $data
        ]);
    }

    public function create()
    {
        return view('admin.master.order_remarks.create');
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        MasterOrderRemark::create([
            'name' => $request->name,
            'status' => $request->status ? 1 : 0
        ]);
        return redirect()->route('admin.master.order-remarks.index')->withSuccess('Order remark created successfully.');
    }

    public function edit(Request $request)
    {
        $data['data'] = MasterOrderRemark::findOrFail($request->id);
        return view('admin.master.order_remarks.edit', $data);
    }

    public function update(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        $remark = MasterOrderRemark::findOrFail($request->id);
        $remark->update([
            'name' => $request->name,
            'status' => $request->status ? 1 : 0
        ]);
        return redirect()->route('admin.master.order-remarks.index')->withSuccess('Order remark updated successfully.');
    }

    public function delete(Request $request)
    {
        $remark = MasterOrderRemark::findOrFail($request->id);
        $remark->delete();
        return redirect()->route('admin.master.order-remarks.index')->withSuccess('Order remark deleted successfully.');
    }
}
