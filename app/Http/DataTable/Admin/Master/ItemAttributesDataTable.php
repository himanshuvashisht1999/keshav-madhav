<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\ItemAttributeValue;
use Yajra\DataTables\Facades\DataTables;

class ItemAttributesDataTable  {

    public function indexList($request){
        $queue = ItemAttributeValue::query()
        ->select('item_attribute_values.*')
        ->join('items', 'item_attribute_values.item_id', '=', 'items.id');

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id','desc');

                if ($request->has('sku') && !empty($request->sku)) {
                    $query->where('item_attribute_values.sku', 'like', "%{$request->get('sku')}%");
                }
                if ($request->has('id') && !empty($request->id)) {
                    $query->where('item_attribute_values.item_id', $request->id);
                }

                if ($request->has('status') && !empty($request->status)) {
                    $query->where('item_attribute_values.status', $request->status);
                }
                
            }) 
         
            ->editColumn('status', function ($queue) {
				$status= $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })

            ->addColumn('action', function ($queue) {
				$parameter= $queue->id;
                return '
                <a href="' . route('admin.master.item-attributes.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            
            ->rawColumns(['action', 'status'])
            ->make(true);
        }
}