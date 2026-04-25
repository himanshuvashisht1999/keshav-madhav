@extends('admin.layouts.app')
@section('title', 'Fabric Return Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="m-0">Return Detail: #{{ $return->id }}</h3>
                <a href="{{ route('admin.report.fabric_return') }}" class="btn btn-sm btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Report
                </a>
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
                                    <th class="w-50">Return Date:</th>
                                    <td>{{ \Carbon\Carbon::parse($return->date)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th>Supplier:</th>
                                    <td>{{ $return->receipt->vendor->name ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>Shipment:</th>
                                    <td><span class="badge bg-info text-dark">{{ $return->receipt->sku ?? '-' }}</span></td>
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
                            <p class="text-muted small mb-0">{{ $return->receipt->vendor->address ?? 'No address provided' }}</p>
                            <p class="text-muted small mb-0">{{ $return->receipt->vendor->mobile ?? '-' }}</p>
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
                                    <th class="text-center">Sr.No</th>
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
                                <tr>
                                    <td class="text-center">{{ $loop->iteration }}</td>
                                    <td>{{ $item->fabric->name ?? '-' }}</td>
                                    <td><span class="badge bg-secondary">{{ $item->receipt_detail->roll_number ?? '-' }}</span></td>
                                    <td class="text-end text-danger fw-bold">{{ number_format($item->return_meter, 2) }}</td>
                                    <td class="text-end">{{ number_format($item->price_per_meter, 2) }}</td>
                                    <td class="text-end">{{ $item->gst_percentage }}%</td>
                                    <td class="text-end">{{ number_format($item->gst_amount, 2) }}</td>
                                    <td class="text-end fw-bold">{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection
