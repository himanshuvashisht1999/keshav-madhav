@extends('admin.layouts.app')

@section('title', 'Stock Disposal Details')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Disposal Details: {{ $main->disposal_no }}</h1>
                <p class="erp-page-subtitle">Audit log for disposed items and reversal history</p>
            </div>
            <div>
                <a href="{{ route('admin.inventory.stock_disposal.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to History
                </a>
            </div>
        </div>

        <!-- Summary Card -->
        <div class="erp-card mb-2 p-3">
            <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                <i class="fas fa-info-circle mr-1"></i> Disposal Information
            </h6>
            <div class="table-responsive">
                <table class="table table-sm erp-table mb-0">
                    <tbody>
                        <tr>
                            <th style="width: 20%; background: #f8fafc;">Disposal no.</th>
                            <td class="font-weight-bold">{{ $main->disposal_no }}</td>
                            <th style="width: 20%; background: #f8fafc;">Date</th>
                            <td>{{ $main->created_at->format('D, d M Y') }}</td>
                        </tr>
                        <tr>
                            <th style="background: #f8fafc;">Item type</th>
                            <td>
                                @if($main->item_type === 'fabric')
                                    <span class="badge-erp-status badge-erp-info font-weight-bold"><i class="fas fa-scroll mr-1"></i> Fabric</span>
                                @else
                                    <span class="badge-erp-status badge-erp-primary font-weight-bold"><i class="fas fa-boxes mr-1"></i> Domestic</span>
                                @endif
                            </td>
                            <th style="background: #f8fafc;">Reason</th>
                            <td><span class="badge-erp-status badge-erp-danger font-weight-bold">{{ $main->reason }}</span></td>
                        </tr>
                        <tr>
                            <th style="background: #f8fafc;">Remarks</th>
                            <td colspan="3" class="text-muted">{{ $main->remarks ?: 'No remarks provided.' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Disposed Items Table Card -->
        <div class="erp-card p-0">
            <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                    <i class="fas fa-boxes mr-1"></i> Disposed Items ({{ ucfirst($main->item_type) }})
                </h6>
                <span class="badge-erp-status badge-erp-secondary font-weight-bold">{{ $main->items->count() }} Items</span>
            </div>
            <div class="table-responsive">
                <table class="table erp-table table-bordered table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th>Item details</th>
                            <th class="text-right" style="width: 180px;">Quantity disposed</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($main->items as $item)
                            <tr>
                                <td class="text-center text-muted font-weight-bold">{{ $loop->iteration }}</td>
                                <td>
                                    @if($main->item_type === 'fabric')
                                        <div class="font-weight-bold">
                                            Roll: <span class="badge-erp-status badge-erp-info font-weight-bold">{{ optional($item->fabricReceiptDetail)->roll_number }}</span>
                                        </div>
                                        <div class="small text-muted">{{ optional(optional($item->fabricReceiptDetail)->fabric)->name }}</div>
                                    @else
                                        <div class="font-weight-bold text-dark">
                                            {{ optional(optional($item->domesticInventory)->product)->design_number }}
                                        </div>
                                        <div class="small text-muted">
                                            {{ optional(optional($item->domesticInventory)->color)->name }} / {{ optional(optional($item->domesticInventory)->sizeSet)->name }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right font-weight-bold text-dark">
                                    {{ $item->quantity }} {{ $main->item_type === 'fabric' ? 'Mtr' : 'Boxes' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
