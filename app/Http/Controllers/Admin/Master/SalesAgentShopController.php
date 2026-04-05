<?php

namespace App\Http\Controllers\Admin\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterCustomer;
use App\Models\SalesAgent;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class SalesAgentShopController extends Controller
{
    public function index($agent_id)
    {
        $agent = SalesAgent::findOrFail($agent_id);
        return view('admin.master.sales_agent_shops.index', compact('agent'));
    }

    public function indexList(Request $request, $agent_id)
    {
        $queue = MasterCustomer::where('sales_agent_id', $agent_id);

        return DataTables::of($queue)->addIndexColumn()
            ->filter(function ($query) use ($request) {
                $query->orderBy('id', 'desc');
                if ($request->has('search') && !empty($request->get('search')['value'])) {
                    $searchValue = $request->get('search')['value'];
                    $query->where(function ($q) use ($searchValue) {
                        $q->where('name', 'like', "%{$searchValue}%")
                            ->orWhere('email', 'like', "%{$searchValue}%")
                            ->orWhere('phone', 'like', "%{$searchValue}%");
                    });
                }
            })
            ->editColumn('status', function ($queue) {
                $status = $queue->status;
                return ($status == 1) ? '<span class="badge badge-xs badge-success">Active</span>' : '<span class="badge badge-xs badge-danger">Inactive</span>';
            })
            ->addColumn('action', function ($queue) use ($agent_id) {
                $parameter = $queue->id;
                return '
                <a href="' . route('admin.master.sales-agent-shops.edit', ['agent_id' => $agent_id, 'id' => $parameter]) . '" class="btn btn-sm btn-icon shadow-sm" style="background-color: #f8f9fa;" data-toggle="tooltip" title="Edit"><i class="fas fa-edit text-muted"></i></a>
                ';
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function create($agent_id)
    {
        $agent = SalesAgent::findOrFail($agent_id);
        return view('admin.master.sales_agent_shops.create', compact('agent'));
    }

    public function store(Request $request, $agent_id)
    {
        $agent = SalesAgent::findOrFail($agent_id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
        ]);

        $validated['sales_agent_id'] = $agent->id;
        $validated['status'] = 1;

        MasterCustomer::create($validated);

        return redirect()->route('admin.master.sales-agent-shops.index', $agent->id)
            ->withSuccess('Shop added successfully for this agent.');
    }

    public function edit($agent_id, $id)
    {
        $agent = SalesAgent::findOrFail($agent_id);
        $shop = MasterCustomer::where('id', $id)
            ->where('sales_agent_id', $agent_id)
            ->firstOrFail();

        return view('admin.master.sales_agent_shops.edit', compact('agent', 'shop'));
    }

    public function update(Request $request, $agent_id, $id)
    {
        $agent = SalesAgent::findOrFail($agent_id);
        $shop = MasterCustomer::where('id', $id)
            ->where('sales_agent_id', $agent_id)
            ->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'address' => 'nullable|string',
            'email' => 'nullable|email|max:255',
            'status' => 'required|boolean'
        ]);

        $shop->update($validated);

        return redirect()->route('admin.master.sales-agent-shops.index', $agent->id)
            ->withSuccess('Shop details updated successfully.');
    }
}
