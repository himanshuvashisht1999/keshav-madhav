<?php

namespace App\Http\Controllers\Owner\Ledger;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\MasterCustomer;
use App\Models\AgentOrderDispatch;
use App\Models\AgentOrderReturn;
use App\Models\FabricReceipt;
use App\Models\FabricReturn;
use App\Models\Payment;
use DB;

class BankCashLedgerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id'); // Adjustment Master ID or 'sales_agent'

        $masters = \App\Models\AdjustmentMaster::whereIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
                $master = \App\Models\AdjustmentMaster::find($typeId);
                if ($master) {
                    $modelName = $master->model_name;
                    if (class_exists($modelName)) {
                        $items = $modelName::where('status', 1)
                            ->when($search, function ($q) use ($search, $modelName) {
                                if ($modelName == 'App\Models\BankAccount') {
                                    $q->where('bank_name', 'LIKE', "%$search%");
                                } else {
                                    $q->where('name', 'LIKE', "%$search%");
                                }
                            })
                            ->get()
                            ->map(function ($v) use ($master) {
                                $v->party_type = strtolower($master->name);
                                $v->master_id_val = $master->id;
                                if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                                return $v;
                            });
                        $parties = $parties->concat($items);
                    }
            }
        } else {
            // Default to ALL masters items (might be too many, but let's see)
            foreach ($masters as $master) {
                $modelName = $master->model_name;
                if (class_exists($modelName)) {
                    $items = $modelName::where('status', 1)
                        ->when($search, function ($q) use ($search, $modelName) {
                            if ($modelName == 'App\Models\BankAccount') {
                                $q->where('bank_name', 'LIKE', "%$search%");
                            } else {
                                $q->where('name', 'LIKE', "%$search%");
                            }
                        })
                        ->limit(100) // Safety limit for "All" view
                        ->get()
                        ->map(function ($v) use ($master) {
                            $v->party_type = strtolower($master->name);
                            $v->master_id_val = $master->id;
                            if (!isset($v->name) && isset($v->bank_name)) $v->name = $v->bank_name;
                            return $v;
                        });
                    $parties = $parties->concat($items);
                }
            }

        }

        // Sort by name, then page it (the list page loads more rows as you scroll)
        $parties = $parties->sortBy('name')->values();
        $page = max(1, (int) $request->query('page', 1));
        $parties = new \Illuminate\Pagination\LengthAwarePaginator(
            $parties->forPage($page, 50)->values(),
            $parties->count(),
            50,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.bank-cash-ledger.partials.ledger_list', compact('parties'))->render(),
                'next_page' => $parties->hasMorePages() ? $parties->currentPage() + 1 : null,
            ]);
        }

        return view('owner.bank-cash-ledger.index', compact('parties', 'masters'));
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        return view('owner.bank-cash-ledger.show', $data);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $pdf = \PDF::loadView('owner.bank-cash-ledger.download', $data);
        $name = str_replace(' ', '_', $data['party']->name) . '_Ledger_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        $data = app(\App\Services\Ledger\BankLedgerService::class)->build($request, $type, $id);
        // Same numbers as the admin ledger; the owner panel has no detail pages to link to
        foreach ($data['transactions'] as $tx) {
            $tx->view_url = '#';
        }
        return $data;
    }
}
