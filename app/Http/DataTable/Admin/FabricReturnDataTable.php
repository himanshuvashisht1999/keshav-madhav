<?php

namespace App\Http\DataTable\Admin;

use Illuminate\Http\Request;
use App\Models\FabricReturn;
use Yajra\DataTables\Facades\DataTables;

class FabricReturnDataTable
{
    public function indexList($request)
    {
        $queue = FabricReturn::with([
            'vendor',
            'receipt.vendor',
            'details.receipt_detail.fabric_receipt'
        ]);

        $this->applyFilters($queue, $request);

        // Calculate summary metrics across ALL filtered records
        $summaryQueue = clone $queue;
        $allMatching = $summaryQueue->get();

        $totalReturns = $allMatching->count();
        $totalAmount = (float) $allMatching->sum('total_amount');
        $totalRolls = 0;
        $totalMeters = 0;

        foreach ($allMatching as $ret) {
            $totalRolls += $ret->details->count();
            $totalMeters += (float) $ret->details->sum('return_meter');
        }

        $queue->orderBy('id', 'desc');

        return DataTables::of($queue)->addIndexColumn()
            ->with('total_returns', $totalReturns)
            ->with('total_amount', $totalAmount)
            ->with('total_rolls', $totalRolls)
            ->with('total_meters', round($totalMeters, 2))
            ->with('formatted_total_amount', getIndianCurrency($totalAmount))
            ->with('formatted_total_rolls', number_format($totalRolls))
            ->with('formatted_total_meters', number_format($totalMeters, 2) . ' M')
            ->editColumn('return_number', function ($queue) {
                $url = route('admin.fabric_return.view', $queue->id);
                return '<a href="' . $url . '" class="fw-bold text-decoration-none font-weight-bold" style="color: var(--brand-dark-green, #05421c);"><i class="fas fa-undo-alt mr-1"></i>' . e($queue->return_number ?? ('RET-' . $queue->id)) . '</a>';
            })
            ->editColumn('date', function ($queue) {
                return $queue->date ? getformatDate($queue->date) : '-';
            })
            ->addColumn('vendor_name', function ($queue) {
                $vendorName = $queue->vendor->name ?? $queue->receipt->vendor->name ?? '-';
                return '<span class="font-weight-bold">' . e($vendorName) . '</span>';
            })
            ->addColumn('shipments', function ($queue) {
                $shipmentIds = collect();
                if ($queue->receipt && $queue->receipt->shipment_id) {
                    $shipmentIds->push($queue->receipt->shipment_id);
                }
                foreach ($queue->details as $d) {
                    if ($d->receipt_detail && $d->receipt_detail->fabric_receipt) {
                        $sId = $d->receipt_detail->fabric_receipt->shipment_id ?? $d->receipt_detail->fabric_receipt->sku;
                        if ($sId) {
                            $shipmentIds->push($sId);
                        }
                    }
                }
                $uniqueShipments = $shipmentIds->unique()->filter();
                if ($uniqueShipments->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }

                $html = '';
                foreach ($uniqueShipments->take(2) as $s) {
                    $html .= '<span class="badge badge-light border text-dark mr-1 mb-1 font-weight-normal">' . e($s) . '</span>';
                }
                if ($uniqueShipments->count() > 2) {
                    $html .= '<span class="badge badge-secondary" title="' . e($uniqueShipments->implode(', ')) . '">+' . ($uniqueShipments->count() - 2) . ' more</span>';
                }
                return $html;
            })
            ->addColumn('rolls_count', function ($queue) {
                $cnt = $queue->details->count();
                return '<span class="badge px-2 py-1 font-weight-bold" style="background: #eaf7ec; color: var(--brand-dark-green, #05421c); border: 1px solid #b7e3bd;">' . $cnt . ' Rolls</span>';
            })
            ->addColumn('total_meters', function ($queue) {
                $mtrs = (float) $queue->details->sum('return_meter');
                return number_format($mtrs, 2) . ' M';
            })
            ->editColumn('sub_total', function ($queue) {
                return $queue->sub_total > 0 ? getIndianCurrency($queue->sub_total) : '0.00';
            })
            ->editColumn('gst_amount', function ($queue) {
                if ($queue->gst_amount > 0) {
                    return getIndianCurrency($queue->gst_amount) . ' <small class="text-muted">(' . (float)$queue->gst_percentage . '%)</small>';
                }
                return '0.00';
            })
            ->editColumn('total_amount', function ($queue) {
                return '<span class="text-danger font-weight-bold">₹ ' . ($queue->total_amount > 0 ? getIndianCurrency($queue->total_amount) : '0.00') . '</span>';
            })
            ->addColumn('action', function ($queue) {
                $id = $queue->id;
                $viewUrl = route('admin.fabric_return.view', $id);
                $pdfUrl = route('admin.fabric_return.download_report', $id);
                $deleteUrl = route('admin.fabric_return.delete', $id);

                $action = '<div class="d-inline-flex align-items-center">';
                $action .= '<a href="' . $viewUrl . '" class="erp-action-btn erp-btn-view" data-toggle="tooltip" title="View Voucher"><i class="fas fa-eye"></i></a>';
                $action .= '<a href="' . $pdfUrl . '" class="erp-action-btn erp-btn-pdf" data-toggle="tooltip" title="Download PDF"><i class="fas fa-file-pdf"></i></a>';
                $action .= '<a href="javascript:void(0)" onclick="confirmDeleteReturn(\'' . $deleteUrl . '\', \'' . e($queue->return_number) . '\')" class="erp-action-btn erp-btn-delete" data-toggle="tooltip" title="Delete Return"><i class="fas fa-trash-alt"></i></a>';
                $action .= '</div>';

                return $action;
            })
            ->rawColumns(['return_number', 'vendor_name', 'shipments', 'rolls_count', 'gst_amount', 'total_amount', 'action'])
            ->make(true);
    }

    private function applyFilters($query, $request)
    {
        if (!empty($request->get('search')['value'])) {
            $searchValue = $request->get('search')['value'];
            $query->where(function ($q) use ($searchValue) {
                $q->where('return_number', 'like', "%{$searchValue}%")
                    ->orWhere('remarks', 'like', "%{$searchValue}%")
                    ->orWhereHas('vendor', function ($vq) use ($searchValue) {
                        $vq->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('receipt.vendor', function ($vq) use ($searchValue) {
                        $vq->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('details.receipt_detail', function ($rq) use ($searchValue) {
                        $rq->where('roll_number', 'like', "%{$searchValue}%");
                    });
            });
        }

        if ($request->filled('vendor_id')) {
            $vendorId = $request->get('vendor_id');
            $query->where(function ($q) use ($vendorId) {
                $q->where('vendor_id', $vendorId)
                    ->orWhereHas('receipt', fn($rq) => $rq->where('vendor_id', $vendorId));
            });
        }

        if ($request->filled('start_date')) {
            $query->whereDate('date', '>=', $request->get('start_date'));
        }

        if ($request->filled('end_date')) {
            $query->whereDate('date', '<=', $request->get('end_date'));
        }

        if ($request->filled('return_number')) {
            $query->where('return_number', 'like', "%{$request->get('return_number')}%");
        }
    }
}
