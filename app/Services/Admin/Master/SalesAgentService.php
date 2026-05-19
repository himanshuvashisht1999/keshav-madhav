<?php

namespace App\Services\Admin\Master;

use Illuminate\Http\Request;
use App\Models\SalesAgent;
use App\Models\SalesAgentBrandDiscount;
use App\Models\MasterOpeningBalance;
use App\Http\DataTable\Admin\Master\SalesAgentDataTable as DataTable;

class SalesAgentService
{
    protected $datatable;
    protected $salesAgent;

    public function __construct(
        DataTable $datatable,
        SalesAgent $salesAgent
    ) {
        $this->datatable = $datatable;
        $this->salesAgent = $salesAgent;
    }

    public function indexList(Request $request)
    {
        return $this->datatable->indexList($request);
    }

    public function downloadPdf(Request $request)
    {
        $query = SalesAgent::where('status', '!=', 3)->withSum('shops as total_balance', 'balance')->with('currentOpeningBalance')->withCount('shops');

        if ($request->has('name') && !empty($request->name)) {
            $query->where('name', 'like', "%{$request->get('name')}%");
        }

        $agents = $query->orderBy('id', 'asc')->get();

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('admin.master.sales_agent.pdf', compact('agents'));
        return $pdf->download('sales-agents.pdf');
    }

    public function store(Request $request)
    {
        $save_data = new SalesAgent;
        $save_data->name = $request->name;
        $save_data->email = $request->email;
        $save_data->phone = $request->phone;
        $save_data->password = $request->password;
        $save_data->address = $request->address;
        $save_data->status = 1;
        $save_data->see_price = $request->see_price ? 1 : 0;
        $save_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'sales_agent',
                'master_id' => $save_data->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->balance_type ?? 'Credit',
            ]
        );

        if ($request->has('brand_discounts')) {
            foreach ($request->brand_discounts as $brand_id => $discount) {
                if ($discount !== null && $discount !== '') {
                    SalesAgentBrandDiscount::create([
                        'sales_agent_id' => $save_data->id,
                        'brand_id' => $brand_id,
                        'discount_percentage' => $discount,
                    ]);
                }
            }
        }

        return true;
    }

    public function edit(Request $request)
    {
        $data = SalesAgent::with(['brandDiscounts', 'currentOpeningBalance'])->where('id', $request->id)->first();
        return $data;
    }

    public function viewDetails(Request $request)
    {
        $data = SalesAgent::with(['brandDiscounts.brand', 'currentOpeningBalance', 'shops'])->where('id', $request->id)->firstOrFail();
        return $data;
    }

    public function update(Request $request)
    {
        $update_data = SalesAgent::find($request->id);
        $update_data->name = $request->name;
        $update_data->email = $request->email;
        $update_data->phone = $request->phone;
        if (!empty($request->password)) {
            $update_data->password = $request->password;
        }
        $update_data->address = $request->address;
        $update_data->status = $request->status ?? 1;
        $update_data->see_price = $request->see_price ? 1 : 0;
        $update_data->save();

        MasterOpeningBalance::updateOrCreate(
            [
                'master_type' => 'sales_agent',
                'master_id' => $update_data->id,
                'financial_year' => MasterOpeningBalance::getCurrentFinancialYear(),
            ],
            [
                'amount' => abs($request->balance ?? 0),
                'balance_type' => $request->balance_type ?? 'Credit',
            ]
        );

        if ($request->has('brand_discounts')) {
            // Delete existing ones first or use sync logic
            SalesAgentBrandDiscount::where('sales_agent_id', $update_data->id)->delete();
            foreach ($request->brand_discounts as $brand_id => $discount) {
                if ($discount !== null && $discount !== '') {
                    SalesAgentBrandDiscount::create([
                        'sales_agent_id' => $update_data->id,
                        'brand_id' => $brand_id,
                        'discount_percentage' => $discount,
                    ]);
                }
            }
        }

        return true;
    }

    public function delete(Request $request)
    {
        $agent = SalesAgent::withSum('shops as total_balance', 'balance')->find($request->id);

        if ($agent) {
            $total_balance = $agent->total_balance ?? 0;
            // If balance is 0, status = 3 (Hidden), otherwise status = 0 (Inactive)
            $newStatus = ($total_balance == 0) ? 3 : 0;

            $agent->update(['status' => $newStatus]);
            return true;
        }

        return false;
    }

}
