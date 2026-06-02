<?php
namespace App\Http\Controllers\Admin\Master;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\FabricType;
use Auth;

class FabricTypeController extends Controller { 
    public function index(){
        $data = FabricType::orderBy('id', 'desc')->get();
        return view('admin.master.fabric-type.index', compact('data'));
    } 
    public function indexList(Request $request){
        $queue = FabricType::query();

        return \Yajra\DataTables\Facades\DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                if (!empty($request->get('search')['value'])) {
                    $query->where('name', 'like', "%{$request->get('search')['value']}%");
                }
            }) 
            ->order(function ($query) {
                $query->orderBy('id', 'desc');
            }) 
            ->editColumn('status', function ($queue) {
				$status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-primary">Inactive</span>';
            })
            ->addColumn('action', function ($queue) {
				$parameter = $queue->id;
                return '
                <a href="' . route('admin.master.fabric-type.edit',['id' => $parameter]) . '" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Edit"><i class="fas fa-edit text-muted"></i></a>
                <a href="javascript:void(0)" onclick="deleteData(' . $parameter . ')" class="" data-toggle="tooltip" data-placement="top" title="" data-original-title="Delete"><i class="fas fa-trash text-danger"></i></a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }
    public function allFabricTypes(){
        $data = FabricType::select('id', 'name')->where('status', 1)->get();
        return response()->json($data);
    }
    public function create(){
        return view('admin.master.fabric-type.create');
    }
    public function store(Request $request){
        $request->validate(['name' => 'required|string|max:255']);
        $save = new FabricType;
        $save->name = $request->name;
        $save->status = $request->status ?? 1;
        $save->save();
        return redirect()->route('admin.master.fabric-type.index')->withSuccess('Fabric Type has been successfully created.');
    }
    public function delete(Request $request){
        $data = FabricType::find($request->id);
        if($data) {
            $data->delete();
        }
        return redirect()->route('admin.master.fabric-type.index')->withSuccess('Fabric Type has been successfully deleted.'); 
    }
    public function edit(Request $request){
        $data = FabricType::find($request->id);
        return view('admin.master.fabric-type.edit', compact('data'));
    }
    public function update(Request $request){
        $request->validate(['name' => 'required|string|max:255']);
        $update = FabricType::find($request->id);
        $update->name = $request->name;
        $update->status = $request->status ?? 1;
        $update->save();
        return redirect()->route('admin.master.fabric-type.index')->withSuccess('Fabric Type has been successfully updated.');
    }
}
