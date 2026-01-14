<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProductionSlipDigitization;

use App\Models\MasterProductStage;
use App\Models\StageMasterUnit;

class UploadedSlipsController extends Controller
{
    public function index(Request $request)
    {
        $query = ProductionSlipDigitization::orderBy('id', 'desc')->where('status', '!=', 3);

        if ($request->filled('from_stage_id')) {
            $query->where('from_stage_id', $request->from_stage_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('stage_master_unit_id')) {
            $query->where('stage_master_unit_id', $request->stage_master_unit_id);
        }

        $slips = $query->paginate(20);
        $stages = MasterProductStage::where('status', 1)->get();
        $units = StageMasterUnit::where('status', 1)->get();

        return view('admin.uploaded_slips.index', compact('slips', 'stages', 'units'));
    }

    public function show($id)
    {
        $slip = ProductionSlipDigitization::with(['fromStage', 'getUnitMaster'])->findOrFail($id);
        return view('admin.uploaded_slips.show', compact('slip'));
    }

    public function destroy($id)
    {
        $slip = ProductionSlipDigitization::findOrFail($id);
        
        // Optional: Delete physical file if needed
        // if(file_exists(public_path($slip->slip_file))) {
        //     unlink(public_path($slip->slip_file));
        // }

        $slip->delete();

        return redirect()->back()->with('success', 'Slip deleted successfully.');
    }
}
