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
                $query->orderBy('id','asc');
                $query->orWhere('measurement', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('measurement') && !empty($request->measurement)) {
                    $query->where('measurement', 'like', "%{$request->get('measurement')}%");
                }

                if ($request->has('size_selection_id') && $request->filled('size_selection_id')) {
                    $query->where('size_selection_id', $request->get('size_selection_id'));
                }
                if ($request->has('status') && $request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('size_type') && $request->filled('size_type')) {
                    $query->where('size_type', $request->get('size_type'));
                }
                
            }) 
            ->editColumn('size_type', function ($queue) {
				$size_type= $queue->size_type;
                return ($size_type == 0) ? 'Set' : 'Individual';
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