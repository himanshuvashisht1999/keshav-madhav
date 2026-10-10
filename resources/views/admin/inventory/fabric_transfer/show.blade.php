@extends('admin.layouts.app')
@section('title', 'Transfer Details')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Transfer Details: {{ $transfer->transfer_no }}</h1>
                <p class="erp-page-subtitle">Warehouse roll dispatch and audit log</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.inventory.fabric_transfer.download-pdf', $transfer->id) }}" class="btn-erp btn-erp-danger mr-2">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
                <a href="{{ route('admin.inventory.fabric_transfer.history') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to History
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Summary Card -->
            <div class="col-md-4 mb-2">
                <div class="erp-card p-3 h-100">
                    <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                        <i class="fas fa-info-circle mr-1"></i> Transfer Summary
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm erp-table mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 40%; background: #f8fafc;">Date</th>
                                    <td>{{ $transfer->transfer_date }}</td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">From warehouse</th>
                                    <td>{{ $transfer->fromWarehouse->cutting_master_name }}</td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">To warehouse</th>
                                    <td>{{ $transfer->toWarehouse->cutting_master_name }}</td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">Total items</th>
                                    <td>
                                        <span class="badge-erp-status badge-erp-info font-weight-bold">
                                            {{ $transfer->items->count() }} Rolls
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">Total meterage</th>
                                    <td>
                                        <span class="badge-erp-status badge-erp-primary font-weight-bold">
                                            {{ number_format($transfer->items->sum('meter'), 2) }} mtr
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    @if($transfer->remarks)
                        <div class="mt-3 p-2 rounded bg-light border">
                            <span class="erp-label d-block text-muted mb-1">Remarks:</span>
                            <p class="mb-0 small text-dark">{{ $transfer->remarks }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Rolls Table Card -->
            <div class="col-md-8 mb-2">
                <div class="erp-card p-0 h-100">
                    <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                            <i class="fas fa-scroll mr-1"></i> Transferred Rolls
                        </h6>
                        <span class="badge-erp-status badge-erp-secondary">
                            {{ $transfer->items->count() }} items
                        </span>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 45px;">#</th>
                                    <th>Fabric</th>
                                    <th style="width: 150px;">Roll no</th>
                                    <th class="text-right" style="width: 140px;">Quantity (Mtr)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transfer->items as $index => $item)
                                    <tr>
                                        <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                                        <td>{{ $item->fabric->name }}</td>
                                        <td>
                                            <span class="erp-pill-gen font-weight-bold">{{ $item->fabricReceiptDetail->roll_number }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">{{ number_format($item->meter, 2) }} mtr</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
