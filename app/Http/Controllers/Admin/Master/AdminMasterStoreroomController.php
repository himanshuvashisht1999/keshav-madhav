<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Storeroom;
use App\Models\Rack;
use Yajra\DataTables\Facades\DataTables;

class AdminMasterStoreroomController extends Controller
{
    public function index()
    {
        return view('admin.master.storeroom.index');
    }

    public function indexList(Request $request)
    {
        if ($request->ajax()) {
            $data = Storeroom::where('status', '!=', 3)->select('id', 'name', 'description', 'status', 'created_at');
            return Datatables::of($data)
                ->addIndexColumn()
                ->editColumn('status', function ($row) {
                    $status = $row->status;
                    return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
                })
                ->addColumn('action', function($row){
                    $parameter = $row->id;
                    return '
                    <a href="' . route('admin.master.storeroom.racks', ['id' => $parameter]) . '" class="btn btn-sm btn-info" data-toggle="tooltip" data-placement="top" title="View Racks"><i class="fas fa-list text-white"></i> Racks</a>
                    <a href="' . route('admin.master.storeroom.edit', ['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                    ';
                })
                ->rawColumns(['action', 'status'])
                ->make(true);
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        Storeroom::create([
            'name' => $request->name,
            'description' => $request->description,
            'order_taken' => $request->order_taken,
            'order_priority' => $request->order_priority,
            'status' => 1
        ]);

        return redirect()->route('admin.master.storeroom.index')->with('success', 'The storeroom has been successfully created.');
    }

    public function edit(Request $request)
    {
        $id = $request->id;
        $storeroom = Storeroom::with('racks')->findOrFail($id);
        return view('admin.master.storeroom.edit', compact('storeroom'));
    }

    public function racks($id)
    {
        $storeroom = Storeroom::with('racks')->findOrFail($id);
        return view('admin.master.storeroom.racks', compact('storeroom'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'id' => 'required',
            'name' => 'required|string|max:255',
        ]);

        $store = Storeroom::findOrFail($request->id);
        $store->update([
            'name' => $request->name,
            'description' => $request->description,
            'status' => $request->status ?? 1,
            'order_taken' => $request->order_taken,
            'order_priority' => $request->order_priority
        ]);

        return redirect()->route('admin.master.storeroom.index')->with('success', 'The storeroom has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $id = $request->id;
        $storeroom = Storeroom::find($id);
        if ($storeroom) {
            log_deletion('Master Storeroom', $id, [
                'storeroom' => $storeroom->toArray()
            ]);
        }
        $store = Storeroom::where('id', $id)->update(['status' => 3]);
        return redirect()->route('admin.master.storeroom.index')->with('success', 'The storeroom has been successfully deleted.');
    }

    // Rack Methods
    public function storeRack(Request $request)
    {
        $request->validate([
            'storeroom_id' => 'required|exists:storerooms,id',
            'name' => 'required|string|max:255'
        ]);

        $rack = Rack::create([
            'storeroom_id' => $request->storeroom_id,
            'name' => $request->name,
            'capacity' => $request->capacity,
            'status' => 1
        ]);

        return response()->json(['status' => 'success', 'message' => 'Rack added successfully.', 'rack' => $rack]);
    }

    public function deleteRack($id)
    {
        $rack = Rack::findOrFail($id);
        log_deletion('Master Rack', $id, [
            'rack' => $rack->toArray()
        ]);
        $rack->delete();
        return response()->json(['status' => 'success', 'message' => 'Rack deleted successfully.']);
    }

    public function updateRack(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:racks,id',
            'name' => 'required|string|max:255'
        ]);

        $rack = Rack::findOrFail($request->id);
        $rack->update([
            'name' => $request->name,
            'capacity' => $request->capacity
        ]);

        return response()->json(['status' => 'success', 'message' => 'Rack updated successfully.', 'rack' => $rack]);
    }
}
