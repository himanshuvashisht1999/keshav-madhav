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

class PartyLedgerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->query('search');
        $typeId = $request->query('type_id'); // Adjustment Master ID or 'sales_agent'

        $masters = \App\Models\AdjustmentMaster::whereNotIn('name', ['Bank Account', 'Cash Master'])->where('status', 1)->get();
        $parties = collect();

        if ($typeId) {
            if ($typeId === 'sales_agent') {
                $items = \App\Models\SalesAgent::where('status', 1)
                    ->when($search, function ($q) use ($search) {
                        $q->where('name', 'LIKE', "%$search%");
                    })
                    ->get()
                    ->map(function ($v) {
                        $v->party_type = 'sales_agent';
                        $v->master_id_val = 'sales_agent';
                        $v->balance = $v->shops()->sum('balance');
                        return $v;
                    });
                $parties = $parties->concat($items);
            } else {
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

            // Also load Sales Agents in ALL view
            $agentItems = \App\Models\SalesAgent::where('status', 1)
                ->when($search, function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                })
                ->limit(100)
                ->get()
                ->map(function ($v) {
                    $v->party_type = 'sales_agent';
                    $v->master_id_val = 'sales_agent';
                    $v->balance = $v->shops()->sum('balance');
                    return $v;
                });
            $parties = $parties->concat($agentItems);
        }

        // Sort by name, then page it (the list page loads more rows as you scroll)
        $parties = $this->paginateParties($parties->sortBy('name')->values(), $request);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('owner.party-ledger.partials.ledger_list', compact('parties'))->render(),
                'next_page' => $parties->hasMorePages() ? $parties->currentPage() + 1 : null,
            ]);
        }

        return view('owner.party-ledger.index', compact('parties', 'masters'));
    }

    private function paginateParties($parties, Request $request, int $perPage = 50)
    {
        $page = max(1, (int) $request->query('page', 1));
        return new \Illuminate\Pagination\LengthAwarePaginator(
            $parties->forPage($page, $perPage)->values(),
            $parties->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    public function show(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        return view('owner.party-ledger.show', $data);
    }

    public function download(Request $request, $type, $id)
    {
        $data = $this->getLedgerData($request, $type, $id);
        $pdf = \PDF::loadView('owner.party-ledger.download', $data);
        $name = str_replace(' ', '_', $data['party']->name) . '_Ledger_' . date('Y-m-d') . '.pdf';
        return $pdf->download($name);
    }

    private function getLedgerData(Request $request, $type, $id)
    {
        $data = app(\App\Services\Ledger\PartyLedgerService::class)->build($request, $type, $id);
        // Same numbers as the admin ledger; the owner panel has no detail pages to link to
        foreach ($data['transactions'] as $tx) {
            $tx->view_url = '#';
        }
        foreach ($data['groupedLedgers'] ?? [] as $group) {
            $groupTx = is_array($group) ? ($group['transactions'] ?? []) : ($group->transactions ?? []);
            foreach ($groupTx as $tx) {
                $tx->view_url = '#';
            }
        }
        return $data;
    }

    private function applyLedgerFilters($transactions, Request $request)
    {
        $transactionType = $request->query('transaction_type');
        $adjustmentType = $request->query('adjustment_type');
        $refNo = $request->query('ref_no');
        $debitValue = $request->query('debit_value');
        $creditValue = $request->query('credit_value');

        if (!$transactionType && !$adjustmentType && !$refNo && $debitValue === null && $creditValue === null) {
            return $transactions;
        }

        return $transactions->filter(function ($tx) use ($transactionType, $adjustmentType, $refNo, $debitValue, $creditValue) {
            // 1. Transaction Type filter
            if ($transactionType && $transactionType !== 'all') {
                $matchType = false;
                if (strcasecmp($tx->type, $transactionType) === 0) {
                    $matchType = true;
                } elseif (strcasecmp($transactionType, 'payment') === 0 && (strcasecmp($tx->type, 'payment') === 0 || $tx->debit > 0)) {
                    $matchType = true;
                } elseif (strcasecmp($transactionType, 'receipt') === 0 && (strcasecmp($tx->type, 'receipt') === 0 || $tx->credit > 0)) {
                    $matchType = true;
                } elseif (strcasecmp($transactionType, 'adjustment') === 0 && (stripos($tx->type, 'adjustment') !== false || stripos($tx->ref ?? '', 'adj') !== false || stripos($tx->description ?? '', 'adjustment') !== false)) {
                    $matchType = true;
                }
                if (!$matchType) return false;
            }

            // 2. Adjustment filter: 1. Payment (Debit / Paid), 2. Receipt (Credit / Received)
            if ($adjustmentType && $adjustmentType !== 'all') {
                if ($adjustmentType === 'payment') {
                    if (!($tx->debit > 0)) return false;
                } elseif ($adjustmentType === 'receipt') {
                    if (!($tx->credit > 0)) return false;
                }
            }

            // 3. Ref No filter
            if ($refNo && trim($refNo) !== '') {
                $refSearch = trim($refNo);
                $txRef = (string)($tx->ref ?? '');
                if (stripos($txRef, $refSearch) === false && stripos((string)($tx->description ?? ''), $refSearch) === false) {
                    return false;
                }
            }

            // 4. Debit Value filter
            if ($debitValue !== null && trim($debitValue) !== '') {
                $val = floatval(preg_replace('/[^0-9.]/', '', $debitValue));
                if ($val > 0) {
                    $exact = abs($tx->debit - $val) < 0.01;
                    $partial = str_contains((string)$tx->debit, trim($debitValue));
                    if (!$exact && !$partial) return false;
                }
            }

            // 5. Credit Value filter
            if ($creditValue !== null && trim($creditValue) !== '') {
                $val = floatval(preg_replace('/[^0-9.]/', '', $creditValue));
                if ($val > 0) {
                    $exact = abs($tx->credit - $val) < 0.01;
                    $partial = str_contains((string)$tx->credit, trim($creditValue));
                    if (!$exact && !$partial) return false;
                }
            }

            return true;
        })->values();
    }
}
