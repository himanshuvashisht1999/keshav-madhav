<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\MasterSizeMeasurement;
use Yajra\DataTables\Facades\DataTables;

class SizeMeasurementDataTable  {
    protected $master_size_measurement;
    public function __construct(MasterSizeMeasurement $master_size_measurement) {
        $this->master_size_measurement = $master_size_measurement;
    }

    public function indexList($request){
        $queue = MasterSizeMeasurement::where('status', '!=', 3);
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->has('customer_id') && $request->filled('customer_id')) {
                    $query->where('corporate_company_id', $request->get('customer_id'));
                }
                if ($request->has('design_number') && !empty($request->design_number)) {
                    $query->where('design_number', 'like', "%{$request->get('design_number')}%");
                }
                if ($request->has('set_size') && !empty($request->set_size)) {
                    $query->where('set_size', 'like', "%{$request->get('set_size')}%");
                }
                if ($request->has('no_of_pcs') && !empty($request->no_of_pcs)) {
                    $query->where('no_of_pcs', 'like', "%{$request->get('no_of_pcs')}%");
                }
                if ($request->has('size_group') && !empty($request->size_group)) {
                    $query->where('size_group', 'like', "%{$request->get('size_group')}%");
                }
                if ($request->has('status') && $request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
            }) 
            ->order(function ($query) {
                $query->orderBy('id', 'asc');
            }) 
            ->editColumn('customer_id', function ($queue) {
                return $queue->customer ? $queue->customer->name : '';
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.size-measurement.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'customer_id', 'status'])
            ->make(true);
    }
}