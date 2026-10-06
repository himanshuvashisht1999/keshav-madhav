<?php

namespace App\Http\Controllers\Admin\Master;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use App\Models\ProductSeason;

class ProductSeasonController extends Controller
{
    public function index()
    {
        $data = ProductSeason::where('status', '!=', 3)->orderBy('id', 'desc')->get();
        return view('admin.master.product-season.index', compact('data'));
    }

    public function indexList(Request $request)
    {
        $queue = ProductSeason::where('status', '!=', 3);

        return \Yajra\DataTables\Facades\DataTables::of($queue)
            ->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if ($request->filled('name')) {
                    $query->where('name', 'like', "%{$request->get('name')}%");
                }
                if ($request->filled('status')) {
                    $query->where('status', $request->get('status'));
                }
                if (!empty($request->get('search')['value'])) {
                    $search = $request->get('search')['value'];
                    $query->where('name', 'like', "%{$search}%");
                }
            })
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            })
            ->editColumn('status', function ($queue) {
                return ($queue->status == 1)
                    ? '<span class="badge badge-xs badge-success">Active</span>'
                    : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
                $parameter = $queue->id;
                return '
                    <a href="' . route('admin.master.product-season.edit', ['id' => $parameter]) . '" class="mr-2" data-toggle="tooltip" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                    <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function allProductSeasons()
    {
        $data = ProductSeason::select('id', 'name')->where('status', 1)->orderBy('name', 'asc')->get();
        return response()->json($data);
    }

    public function create()
    {
        return view('admin.master.product-season.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_seasons', 'name')->where(function ($query) {
                    return $query->where('status', '!=', 3);
                }),
            ],
            'status' => 'nullable|in:0,1',
        ], [
            'name.required' => 'The season name is required.',
            'name.unique'   => 'This product season already exists. Duplicate names are not allowed.',
        ]);

        $save = new ProductSeason();
        $save->name = trim($request->name);
        $save->status = $request->status ?? 1;
        $save->save();

        return redirect()->route('admin.master.product-season.index')
            ->withSuccess('Product Season has been successfully created.');
    }

    public function edit(Request $request)
    {
        $data = ProductSeason::where('status', '!=', 3)->findOrFail($request->id);
        return view('admin.master.product-season.edit', compact('data'));
    }

    public function update(Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('product_seasons', 'name')
                    ->ignore($request->id)
                    ->where(function ($query) {
                        return $query->where('status', '!=', 3);
                    }),
            ],
            'status' => 'nullable|in:0,1',
        ], [
            'name.required' => 'The season name is required.',
            'name.unique'   => 'This product season already exists. Duplicate names are not allowed.',
        ]);

        $update = ProductSeason::where('status', '!=', 3)->findOrFail($request->id);
        $update->name = trim($request->name);
        $update->status = $request->status ?? 1;
        $update->save();

        return redirect()->route('admin.master.product-season.index')
            ->withSuccess('Product Season has been successfully updated.');
    }

    public function delete(Request $request)
    {
        $data = ProductSeason::find($request->id);
        if ($data) {
            // Soft-delete by setting status = 3 so it is not listed anywhere
            $data->status = 3;
            $data->save();
        }

        return redirect()->route('admin.master.product-season.index')
            ->withSuccess('Product Season has been successfully deleted.');
    }
}
