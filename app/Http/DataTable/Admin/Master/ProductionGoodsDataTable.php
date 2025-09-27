<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\ProductionGoods;
use App\Models\MasterColor;
use App\Models\MasterDesign;
use App\Models\MasterMaterial;
use App\Models\MasterSizeMeasurement;
use App\Models\Fabric;
use Yajra\DataTables\Facades\DataTables;

class ProductionGoodsDataTable  {

    public function __construct(ProductionGoods $production_goods) {
        $this->production_goods = $production_goods;
    }

    public function indexList($request){
        $queue = ProductionGoods::query();
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                // $query->orderBy('id','desc');
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('name_of_garment') && !empty($request->name_of_garment)) {
                    $query->where('name_of_garment', 'like', "%{$request->get('name_of_garment')}%");
                }
                if ($request->has('fabric_sku') && !empty($request->fabric_sku)) {
                    $query->where('fabric_sku', 'like', "%{$request->get('fabric_sku')}%");
                }
                if ($request->has('master_material_id') && $request->filled('master_material_id')) {
                    $query->where('master_material_id', $request->get('master_material_id'));
                }
                if ($request->has('master_color_id') && $request->filled('master_color_id')) {
                    $query->where('master_color_id', $request->get('master_color_id'));
                }
                if ($request->has('master_size_id') && $request->filled('master_size_id')) {
                    $query->where('master_size_id', $request->get('master_size_id'));
                }
                if ($request->has('master_design_id') && $request->filled('master_design_id')) {
                    $query->where('master_design_id', $request->get('master_design_id'));
                }
               
                
            }) 

            ->editColumn('master_material_id', function ($queue) {
				$name= $queue->material->name;
                return $name;
            })
            ->editColumn('master_color_id', function ($queue) {
				$name= $queue->color->name;
                return $name;
            })
            ->editColumn('master_design_id', function ($queue) {
				$name= $queue->design->name;
                return $name;
            })
           
            ->editColumn('master_size_id', function ($queue) {
				$name= $queue->size->sku;
                return $name;
            })
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.production-goods.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status','master_material_id','master_color_id','master_design_id','master_size_id'])
            ->make(true);
    }
}