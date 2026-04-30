<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\MasterStageWiseTimeAllocation;
use Yajra\DataTables\Facades\DataTables;

class TimeAllocationDataTable  {

    public function indexList($request){
        $queue = MasterStageWiseTimeAllocation::query();

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');
                
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $search = $request->get('search')['value'];
                    $query->where('lot_no', 'like', "%{$search}%");
                }
            }) 
            ->editColumn('start_date_time', function ($queue) {
				return date('Y-m-d H:i', strtotime($queue->start_date_time));
            })
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                $edit = '<a href="' . route('admin.time_allocation.edit',['id' => $parameter]) . '" class="btn btn-sm btn-primary"><i class="fas fa-edit"></i> Edit</a>';
                
                return $edit;
            })
            
            ->rawColumns(['action'])
            ->make(true);
    }
}
