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
                $query->orderBy('id','desc');
                $query->orWhere('sku', 'like', "%{$request->get('search')['value']}%");
                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('fabric_sku') && !empty($request->fabric_sku)) {
                    $query->where('fabric_sku', 'like', "%{$request->get('fabric_sku')}%");
                }  
                if ($request->has('status') && !empty($request->status)) {
                    $query->where('status', $request->get('status'));
                }                            
            }) 
           
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Published</span>' : '<span class="badge badge-xs badge-primary">Pending BOM</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.production-goods.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.master.production-goods-item.create',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-eye text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
}