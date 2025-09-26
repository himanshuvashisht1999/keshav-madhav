<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterSizeMeasurement;
use Yajra\DataTables\Facades\DataTables;

class SizeMeasurementDataTable  {

    public function __construct(MasterSizeMeasurement $master_size_measurement) {
        $this->master_size_measurement = $master_size_measurement;
    }

    public function indexList($request){
        $queue = MasterSizeMeasurement::query();
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                // $query->orderBy('id','desc');
                $query->orWhere('measurement', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('measurement') && !empty($request->measurement)) {
                    $query->where('measurement', 'like', "%{$request->get('measurement')}%");
                }
                if ($request->has('base_cloth_consumption') && !empty($request->base_cloth_consumption)) {
                    $query->where('base_cloth_consumption', 'like', "%{$request->get('base_cloth_consumption')}%");
                }
                if ($request->has('size_selection_id') && $request->filled('size_selection_id')) {
                    $query->where('size_selection_id', $request->get('size_selection_id'));
                }
                if ($request->has('status') && $request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.size-measurement.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}