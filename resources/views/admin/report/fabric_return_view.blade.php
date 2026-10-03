@extends('admin.layouts.app')
@section('title', 'Fabric Return Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="m-0">Return Detail: {{ $return->return_number ?? '#' . $return->id }}</h3>
                <div>
                    @if(Route::has('admin.fabric_receipt.edit_return'))
                    <a href="{{ route('admin.fabric_receipt.edit_return', $return->id) }}" class="btn btn-sm btn-outline-primary me-2">
                        <i class="fas fa-edit"></i> Edit Return
                    </a>
                    @endif
                    <a href="{{ route('admin.report.fabric_return') }}" class="btn btn-sm btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Report
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-danger text-white">
                            <h5 class="m-0">General Info</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th class="w-50">Return No:</th>
                                    <td><span class="fw-bold">{{ $return->return_number ?? '#' . $return->id }}</span></td>
                                </tr>
                                <tr>
                                    <th>Return Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier:</th>
                                    <td>{{ $return->receipt->vendor->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Shipment:</th>
                                    <td>
                                        @if(isset($return->fabric_receipt_id) && Route::has('admin.fabric_receipt.view'))
                                        <a href="{{ route('admin.fabric_receipt.view', ['id' => $return->fabric_receipt_id]) }}" class="badge bg-info text-dark text-decoration-none">
                                            {{ $return->receipt->sku ?? '-' }}
                                        </a>
                                        @else
                                        <span class="badge bg-info text-dark">{{ $return->receipt->sku ?? '-' }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Remarks:</th>
                                    <td>{{ $return->remarks ?? '-' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100 border-danger">
                        <div class="card-header bg-danger text-white">
                            <h5 class="m-0">Financial Summary</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless mb-0">
                                <tr>
                                    <th class="w-50">Sub Total:</th>
                                    <td class="text-end">{{ number_format($return->sub_total, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>GST ({{ $return->gst_percentage }}%):</th>
                                    <td class="text-end">{{ number_format($return->gst_amount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Discount:</th>
                                    <td class="text-end">-{{ number_format($return->discount, 2) }}</td>
                                </tr>
                                <tr>
                                    <th>Other Charges:</th>
                                    <td class="text-end">{{ number_format($return->other_charges, 2) }}</td>
                                </tr>
                                <tr class="border-top">
                                    <th class="pt-2 text-danger">Grand Total:</th>
                                    <td class="pt-2 text-end fw-bold text-danger">{{ number_format($return->total_amount, 2) }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-header bg-secondary text-white">
                            <h5 class="m-0">Contact Info</h5>
                        </div>
                        <div class="card-body">
                            <p class="mb-1 fw-bold">{{ $return->receipt->vendor->name ?? '-' }}</p>
                            <p class="text-muted small mb-1"><i class="fas fa-map-marker-alt me-1"></i> {{ $return->receipt->vendor->address ?? 'No address provided' }}</p>
                            <p class="text-muted small mb-0"><i class="fas fa-phone me-1"></i> {{ $return->receipt->vendor->phone ?? $return->receipt->vendor->mobile ?? '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h5 class="m-0">Fabric Items Returned</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="text-center" style="width: 60px;">Sr.No</th>
                                    <th>Fabric</th>
                                    <th>Roll No</th>
                                    <th class="text-end">Return Qty (Mtr)</th>
                                    <th class="text-end">Rate/Mtr</th>
                                    <th class="text-end">Tax (%)</th>
                                    <th class="text-end">Tax Amount</th>
                                    <th class="text-end">Line Total</th>
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
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->fabric->name ?? '-' }}</td>
                                    <td><span class="badge bg-secondary">{{ $item->receipt_detail->roll_number ?? '-' }}</span></td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($item->return_meter, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->price_per_meter, 2) }}</td>
                                    <td class="text-end">{{ number_format($gstRate, 2) }}%</td>
                                    <td class="text-end">{{ number_format($gstAmount, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($lineTotal, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="bg-light fw-bold">
                                    <th colspan="3" class="text-end">Total:</th>
                                    <th class="text-end text-danger">{{ number_format($return->details->sum('return_meter'), 2) }}</th>
                                    <th></th>
                                    <th></th>
                                    <th class="text-end">{{ number_format($return->gst_amount, 2) }}</th>
                                    <th class="text-end text-danger">{{ number_format($return->total_amount, 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
