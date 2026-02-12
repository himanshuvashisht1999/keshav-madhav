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
        $queue = ProductionGoods::orderBy('id','desc');
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','asc');
                $query->orWhere('name_of_garment', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('name_of_garment') && !empty($request->name_of_garment)) {
                    $query->where('name_of_garment', 'like', "%{$request->get('name_of_garment')}%");
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
                // return '
                // <a href="' . route('admin.master.production-goods.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                // <a href="' . route('admin.master.production-goods.view',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted"></i></a>
                // ';

                return '
                <a href="' . route('admin.master.production-goods.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>';
            })
            ->addColumn('main_image', function ($queue) {
                $img = $queue->mainImage; // relationship
              
                $src = $img ? $img->image : asset('assets/products/default-image.png');

                return '<img src="'.$src.'" alt="Main Image" style="height:50px;width:auto;border-radius:4px;">';
            })
            
            ->rawColumns(['action', 'status','main_image'])
            ->make(true);
    }
}