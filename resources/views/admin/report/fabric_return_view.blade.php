@extends('admin.layouts.app')
@section('title', 'Fabric Return Details')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-undo"></i> Return details: {{ $return->return_number ?? '#' . $return->id }}
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="far fa-calendar-alt text-muted"></i> Date: {{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="fas fa-truck text-muted"></i> Supplier: <strong>{{ $return->receipt->vendor->name ?? '-' }}</strong></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="fas fa-barcode text-muted"></i> Shipment: <code>{{ $return->receipt->sku ?? '-' }}</code></span>
            </div>
        </div>
        <div class="erp-header-actions">
            @if(Route::has('admin.fabric_receipt.edit_return'))
            <a href="{{ route('admin.fabric_receipt.edit_return', $return->id) }}" class="btn-erp btn-erp-outline mr-1">
                <i class="fas fa-edit mr-1"></i> Edit return
            </a>
            @endif
            <a href="{{ route('admin.report.fabric_return') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to report
            </a>
        </div>
    </div>

    {{-- Info Cards --}}
    <div class="row">
        <div class="col-md-4 mb-2">
            <div class="erp-card h-100 p-0">
                <div class="px-3 py-2 border-bottom bg-light">
                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                        <i class="fas fa-info-circle text-success mr-1"></i> General info
                    </h6>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                        <tr>
                            <th class="text-muted font-weight-normal py-1" style="width: 45%;">Return no:</th>
                            <td class="font-weight-bold text-dark py-1"><code>{{ $return->return_number ?? '#' . $return->id }}</code></td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Return date:</th>
                            <td class="font-weight-bold text-dark py-1">{{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Supplier:</th>
                            <td class="font-weight-bold text-dark py-1">{{ $return->receipt->vendor->name ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Shipment:</th>
                            <td class="py-1">
                                @if(isset($return->fabric_receipt_id) && Route::has('admin.fabric_receipt.view'))
                                <a href="{{ route('admin.fabric_receipt.view', ['id' => $return->fabric_receipt_id]) }}" class="badge badge-light border">
                                    {{ $return->receipt->sku ?? '-' }}
                                </a>
                                @else
                                <span class="badge badge-light border">{{ $return->receipt->sku ?? '-' }}</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Remarks:</th>
                            <td class="text-muted py-1">{{ $return->remarks ?? '—' }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="erp-card h-100 p-0">
                <div class="px-3 py-2 border-bottom bg-light">
                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                        <i class="fas fa-calculator text-success mr-1"></i> Financial summary
                    </h6>
                </div>
                <div class="p-3">
                    <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Subtotal:</th>
                            <td class="text-right py-1">₹ {{ number_format($return->sub_total, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">GST ({{ $return->gst_percentage }}%):</th>
                            <td class="text-right py-1">₹ {{ number_format($return->gst_amount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Discount:</th>
                            <td class="text-right text-muted py-1">- ₹ {{ number_format($return->discount, 2) }}</td>
                        </tr>
                        <tr>
                            <th class="text-muted font-weight-normal py-1">Other charges:</th>
                            <td class="text-right py-1">₹ {{ number_format($return->other_charges, 2) }}</td>
                        </tr>
                        <tr class="border-top">
                            <th class="pt-2 font-weight-bold text-danger">Grand total:</th>
                            <td class="pt-2 text-right font-weight-bold text-danger" style="font-size: 15px;">₹ {{ number_format($return->total_amount, 2) }}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="erp-card h-100 p-0">
                <div class="px-3 py-2 border-bottom bg-light">
                    <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                        <i class="fas fa-address-card text-success mr-1"></i> Supplier contact
                    </h6>
                </div>
                <div class="p-3" style="font-size: 13px;">
                    <p class="mb-1 font-weight-bold text-dark">{{ $return->receipt->vendor->name ?? '-' }}</p>
                    <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt text-muted mr-1"></i> {{ $return->receipt->vendor->address ?? 'No address provided' }}</p>
                    <p class="text-muted small mb-0"><i class="fas fa-phone text-muted mr-1"></i> {{ $return->receipt->vendor->phone ?? $return->receipt->vendor->mobile ?? '—' }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Fabric Items Returned --}}
    <div class="erp-card p-0">
        <div class="px-3 py-2 border-bottom bg-light">
            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                <i class="fas fa-layer-group text-success mr-1"></i> Fabric items returned
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Fabric</th>
                        <th class="text-center">Roll no</th>
                        <th class="text-right">Return qty (m)</th>
                        <th class="text-right">Rate / meter</th>
                        <th class="text-right">Tax (%)</th>
                        <th class="text-right">Tax amount</th>
                        <th class="text-right">Line total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($return->details as $item)
                    @php
                        $lineSubTotal = (float)($item->return_meter ?? 0) * (float)($item->price_per_meter ?? 0);
                        $gstRate = isset($item->gst_percentage) && $item->gst_percentage !== null ? (float)$item->gst_percentage : (float)($return->gst_percentage ?? 0);
                        $gstAmount = isset($item->gst_amount) && $item->gst_amount !== null ? (float)$item->gst_amount : ($lineSubTotal * $gstRate / 100);
                        $lineTotal = isset($item->total_amount) && $item->total_amount !== null ? (float)$item->total_amount : ($lineSubTotal + $gstAmount);
                    @endphp
                    <tr>
                        <td class="text-center text-muted">{{ $loop->iteration }}</td>
                        <td><strong>{{ $item->fabric->name ?? '-' }}</strong></td>
                        <td class="text-center"><span class="badge badge-light border">{{ $item->receipt_detail->roll_number ?? '-' }}</span></td>
                        <td class="text-right font-weight-bold text-danger">{{ number_format($item->return_meter, 2) }}</td>
                        <td class="text-right">₹ {{ number_format($item->price_per_meter, 2) }}</td>
                        <td class="text-right">{{ number_format($gstRate, 2) }}%</td>
                        <td class="text-right">₹ {{ number_format($gstAmount, 2) }}</td>
                        <td class="text-right font-weight-bold">₹ {{ number_format($lineTotal, 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-right">Total</th>
                        <th class="text-right text-danger font-weight-bold">{{ number_format($return->details->sum('return_meter'), 2) }} m</th>
                        <th></th>
                        <th></th>
                        <th class="text-right">₹ {{ number_format($return->gst_amount, 2) }}</th>
                        <th class="text-right text-danger font-weight-bold">₹ {{ number_format($return->total_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
