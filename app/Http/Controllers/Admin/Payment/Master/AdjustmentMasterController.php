<?php

namespace App\Http\Controllers\Admin\Payment\Master;

use App\Http\Controllers\Controller;
use App\Models\AdjustmentMaster;
use Illuminate\Http\Request;

class AdjustmentMasterController extends Controller
{
    public function index()
    {
        $masters = AdjustmentMaster::all();
        return view('admin.payment.master.adjustment_master.index', compact('masters'));
    }

    public function create()
    {
        return view('admin.payment.master.adjustment_master.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|unique:adjustment_masters,name',
            'model_name' => 'required|string',
        ]);

        AdjustmentMaster::create($request->all());
        return redirect()->route('admin.payment.master.adjustment_master.index')->with('success', 'Adjustment Master added successfully.');
    }

    public function edit($id)
    {
        $data = AdjustmentMaster::findOrFail($id);
        return view('admin.payment.master.adjustment_master.edit', compact('data'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|unique:adjustment_masters,name,'.$id,
            'model_name' => 'required|string',
        ]);

        $master = AdjustmentMaster::findOrFail($id);
        $master->update($request->all());
        return redirect()->route('admin.payment.master.adjustment_master.index')->with('success', 'Adjustment Master updated successfully.');
    }

    public function delete($id)
    {
        $master = AdjustmentMaster::findOrFail($id);
        $master->delete();
        return redirect()->route('admin.payment.master.adjustment_master.index')->with('success', 'Adjustment Master deleted successfully.');
    }
}
