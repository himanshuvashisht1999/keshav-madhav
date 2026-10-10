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
                return ($status == 1) 
                    ? '<span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 8px;">Active</span>' 
                    : '<span class="badge badge-secondary" style="font-size: 11px; padding: 3px 8px;">Inactive</span>';
            })
            ->addColumn('action', function ($queue) use ($agent_id) {
                $parameter = $queue->id;
                return '
                <div class="d-inline-flex" style="gap: 4px;">
                    <a href="' . route('admin.master.sales-agent-shops.edit', ['agent_id' => $agent_id, 'id' => $parameter]) . '" class="erp-action-btn erp-btn-edit" title="Edit"><i class="fas fa-edit"></i></a>
                </div>
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
