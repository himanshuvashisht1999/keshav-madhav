<?php

namespace App\Http\DataTable\Admin\Master;

use Illuminate\Http\Request;
use App\Models\InventoryPrice;
use Yajra\DataTables\Facades\DataTables;

class InventoryPriceDataTable
{
    public function indexList($request)
    {
        $queue = InventoryPrice::query()->with(['design', 'color', 'sizeSet']);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id', 'desc');
                if ($request->has('design_id') && !empty($request->design_id)) {
                    $query->where('design_id', $request->design_id);
                }
                if ($request->has('color_id') && !empty($request->color_id)) {
                    $query->where('color_id', $request->color_id);
                }
                if ($request->has('size_set_id') && !empty($request->size_set_id)) {
                    $query->where('size_set_id', $request->size_set_id);
                }
                if ($request->has('name') && !empty($request->name)) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
            })
            ->editColumn('design', function ($queue) {
                return $queue->design->design_number ?? 'N/A';
            })
            ->editColumn('color', function ($queue) {
                return $queue->color->name ?? 'N/A';
            })
            ->editColumn('size', function ($queue) {
                return $queue->sizeSet->set_size ?? 'N/A';
            })
            ->editColumn('image', function ($queue) {
                $url = $queue->image_url;
                return '<img src="' . $url . '" width="50" height="50" class="img-thumbnail">';
            })
            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->editColumn('mrp', function ($queue) {
                return '₹ ' . number_format($queue->mrp, 2);
            })
            ->editColumn('selling_price', function ($queue) {
                return '₹ ' . number_format($queue->selling_price, 2);
            })
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                <a href="' . route('admin.master.inventory-price.edit', ['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="' . route('admin.master.inventory-price.delete', ['id' => $parameter]) . '" class="ml-2" onclick="return confirm(\'Are you sure?\')" data-toggle="tooltip" data-placement="top" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'name', 'status', 'image', 'size'])
            ->make(true);
    }
}
