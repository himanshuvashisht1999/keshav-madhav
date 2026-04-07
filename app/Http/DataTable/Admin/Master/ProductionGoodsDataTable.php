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
        $queue = ProductionGoods::with('series', 'brand')->where('status', '!=', 3)->orderBy('id','desc');
        
        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id', 'asc');
                
                if ($request->has('name_of_garment') && !empty($request->name_of_garment)) {
                    $searchTerm = $request->get('name_of_garment');
                    $query->where(function($q) use ($searchTerm) {
                        $q->where('name_of_garment', 'like', "%{$searchTerm}%")
                          ->orWhereHas('series', function($sq) use ($searchTerm) {
                              $sq->where('name', 'like', "%{$searchTerm}%");
                          });
                    });
                }

                if ($request->has('brand_name') && !empty($request->brand_name)) {
                    $brandTerm = $request->get('brand_name');
                    $query->whereHas('brand', function($q) use ($brandTerm) {
                        $q->where('name', 'like', "%{$brandTerm}%");
                    });
                }

                if ($request->has('design_number') && !empty($request->design_number)) {
                    $query->where('design_number', 'like', "%{$request->get('design_number')}%");
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
                $isInInv = \App\Models\DomesticInventory::where('product_id', $queue->id)->exists();

                $deleteBtn = '';
                if (!$isInInv) {
                    $deleteBtn = '<a href="javascript:void(0);" onclick="deleteData(' . $parameter . ')" class="" title="Delete"><i class="fas fa-trash text-danger"></i></a>';
                } else {
                    $deleteBtn = '<a href="javascript:void(0);" class="text-muted" title="Locked (Stock in Inventory)"><i class="fas fa-trash"></i></a>';
                }

                return '
                <a href="' . route('admin.master.production-goods.view', ['id' => $parameter]) . '" class="mr-2" title="View"><i class="fas fa-eye text-info"></i></a>
                <a href="' . route('admin.master.production-goods.edit', ['id' => $parameter]) . '" class="mr-2" title="Edit"><i class="fas fa-edit text-primary"></i></a>
                ' . $deleteBtn;
            })
            ->addColumn('main_image', function ($queue) {
                $img = $queue->mainImage; // relationship
                $src = $img ? $img->image : asset('assets/products/default-image.png');
                return '<img src="'.$src.'" alt="Main Image" style="height:50px;width:auto;border-radius:4px;">';
            })
            ->addColumn('brand_name', function ($queue) {
                return $queue->brand ? $queue->brand->name : '-';
            })
            ->addColumn('product_name_display', function ($queue) {
                $series = $queue->series ? $queue->series->name : '';
                return trim($series . ' ' . $queue->name_of_garment);
            })
            
            ->rawColumns(['action', 'status','main_image'])
            ->make(true);
    }
}