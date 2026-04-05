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
    protected $production_goods;

    public function __construct(ProductionGoods $production_goods) {
        $this->production_goods = $production_goods;
    }

    public function indexList($request){
        $queue = ProductionGoods::with('series')->where('status', '!=', 3)->orderBy('id','desc');
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id', 'asc');
                if ($request->has('name_of_garment') && !empty($request->name_of_garment)) {
                    $query->where('name_of_garment', 'like', "%{$request->get('name_of_garment')}%");
                }
                if ($request->has('series_name') && !empty($request->series_name)) {
                    $query->whereHas('series', function($q) use ($request) {
                        $q->where('name', 'like', "%{$request->get('series_name')}%");
                    });
                }
                if ($request->has('design_number') && !empty($request->design_number)) {
                    $query->where('design_number', 'like', "%{$request->get('design_number')}%");
                }
                if ($request->has('fabric_sku') && !empty($request->fabric_sku)) {
                    $query->where('fabric_sku', 'like', "%{$request->get('fabric_sku')}%");
                }  
                if ($request->has('status') && ($request->status != '')) {
                    $query->where('status', $request->get('status'));
                }                            
            }) 
           
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Published</span>' : '<span class="badge badge-xs badge-primary">Pending</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.production-goods.view', ['id' => $parameter]) . '" class="mr-2" title="View"><i class="fas fa-eye text-info"></i></a>
                <a href="' . route('admin.master.production-goods.edit', ['id' => $parameter]) . '" class="mr-2" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                <a href="javascript:void(0);" onclick="deleteData(' . $parameter . ')" class="" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->addColumn('main_image', function ($queue) {
                $img = $queue->mainImage; // relationship
              
                $src = $img ? $img->image : asset('assets/products/default-image.png');

                return '<img src="'.$src.'" alt="Main Image" style="height:50px;width:auto;border-radius:4px;">';
            })
            ->addColumn('series_name', function ($queue) {
                return $queue->series ? $queue->series->name : '';
            })
            
            ->rawColumns(['action', 'status','main_image'])
            ->make(true);
    }
}