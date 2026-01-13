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
            $data = Storeroom::select('id', 'name', 'description', 'status', 'created_at');
            return Datatables::of($data)
                ->addIndexColumn()
                ->addColumn('action', function($row){
                    $actionBtn = '<a href="'.route('admin.master.storeroom.edit', $row->id).'" class="btn btn-primary btn-sm btn-edit">Edit</a> ';
                    $actionBtn .= '<a href="'.route('admin.master.storeroom.delete', $row->id).'" class="btn btn-danger btn-sm" onclick="return confirm(\'Are you sure?\')">Delete</a>';
                    return $actionBtn;
                })
                ->rawColumns(['action'])
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
            'status' => 1
        ]);

        return redirect()->route('admin.master.storeroom.index')->with('success', 'Storeroom created successfully.');
    }

    public function edit($id)
    {
        $storeroom = Storeroom::with('racks')->findOrFail($id);
        return view('admin.master.storeroom.edit', compact('storeroom'));
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
            'status' => $request->status ?? 1
        ]);

        return redirect()->route('admin.master.storeroom.index')->with('success', 'Storeroom updated successfully.');
    }

    public function delete($id)
    {
        $store = Storeroom::findOrFail($id);
        $store->delete(); // This will cascade delete racks if DB FK set, otherwise we should check
        return redirect()->route('admin.master.storeroom.index')->with('success', 'Storeroom deleted successfully.');
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
