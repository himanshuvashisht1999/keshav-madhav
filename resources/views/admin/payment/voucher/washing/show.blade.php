@extends('admin.layouts.app')
@section('title', 'Washing Voucher Details')

@section('content')
<style>
    @media print {
        .no-print, .erp-header-actions, .main-sidebar, .main-header, .btn-erp, .btn {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
            background: #fff !important;
        }
        .erp-voucher-banner {
            background: #05421c !important;
            -webkit-print-color-adjust: exact;
            color: #ffffff !important;
        }
    }
</style>

<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2 no-print">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-soap text-primary"></i> Washing Voucher Details
            </div>
            <p class="erp-page-subtitle mb-0">Voucher record #{{ $data->voucher_number ?: $data->id }} | Washing unit / process billing &amp; lot tracking</p>
        </div>
        <div class="erp-header-actions">
            <button type="button" onclick="window.print()" class="btn-erp btn-erp-outline">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a href="{{ route('admin.payment.voucher.washing.edit', ['id' => $data->id]) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-edit mr-1"></i> Edit Voucher
            </a>
            <a href="{{ route('admin.payment.voucher.washing.delete', ['id' => $data->id]) }}" class="btn-erp btn-erp-outline" onclick="return confirm('Are you sure you want to delete this washing voucher?')" style="color: #dc2626 !important; border-color: #dc2626 !important;">
                <i class="fas fa-trash mr-1"></i> Delete Voucher
            </a>
            @if($data->washing_master_id)
                <a href="{{ route('admin.ledger.party.show', ['type' => 'washing master', 'id' => $data->washing_master_id]) }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-book-open mr-1"></i> Washing Ledger
                </a>
            @endif
            <a href="{{ route('admin.payment.voucher.washing.index') }}" class="btn-erp btn-erp-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- ERP Voucher Banner -->
    <div class="erp-voucher-banner mb-3">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                <div class="text-uppercase small font-weight-bold opacity-75 mb-1" style="letter-spacing: 0.8px; color: #fcee21;">
                    <i class="fas fa-tint mr-1"></i> Washing Unit Billing Voucher
                </div>
                <h3 class="font-weight-bold mb-1 text-white">
                    {{ $data->washingMaster->name ?? 'Washing Master' }}
                </h3>
                @if($data->washingMaster && ($data->washingMaster->phone || $data->washingMaster->address))
                    <div class="text-white-50 small mb-1">
                        @if($data->washingMaster->phone)
                            <span class="mr-2"><i class="fas fa-phone-alt mr-1"></i> {{ $data->washingMaster->phone }}</span>
                        @endif
                        @if($data->washingMaster->address)
                            <span><i class="fas fa-map-marker-alt mr-1"></i> {{ $data->washingMaster->address }}</span>
                        @endif
                    </div>
                @endif
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px; margin-top: 6px;">
                    <span class="badge" style="background: rgba(252, 238, 33, 0.2); color: #fcee21; border: 1px solid rgba(252, 238, 33, 0.4); font-weight: 700; padding: 4px 8px; font-size: 12px;">
                        <i class="fas fa-hashtag mr-1"></i>{{ $data->voucher_number ?: 'Voucher #' . $data->id }}
                    </span>
                    <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; padding: 4px 8px; font-size: 12px;">
                        <i class="fas fa-check-circle mr-1 text-success"></i> Active
                    </span>
                    @php
                        $uniqueLots = $data->items->filter(function($item) { return $item->orderLot && $item->orderLot->lot_no; })->pluck('orderLot.lot_no')->unique();
                    @endphp
                    @foreach($uniqueLots as $lot)
                        <span class="badge" style="background: rgba(59, 130, 246, 0.3); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.5); padding: 4px 8px; font-size: 12px;">
                            <i class="fas fa-boxes mr-1"></i> Lot #{{ $lot }}
                        </span>
                    @endforeach
                    <span class="text-white small" style="opacity: 0.9;">
                        <i class="fas fa-calendar-alt mr-1"></i>{{ $data->voucher_date ? \Carbon\Carbon::parse($data->voucher_date)->format('d M Y') : '-' }}
                    </span>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="row">
                    <div class="col-sm-6 mb-2 mb-sm-0">
                        <div class="erp-meta-box text-center">
                            <div class="erp-meta-box-label">Washing Master Current Balance</div>
                            <div class="erp-meta-box-value">
                                ₹ {{ number_format(abs($data->washingMaster->balance ?? 0), 2) }}
                                <span class="badge {{ ($data->washingMaster->balance ?? 0) >= 0 ? 'badge-erp-success' : 'badge-danger' }} ml-1" style="font-size: 10px;">
                                    {{ ($data->washingMaster->balance ?? 0) >= 0 ? 'CR' : 'DR' }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="erp-meta-box text-center" style="background: #ffffff !important; border: 1px solid #cbd5e1 !important;">
                            <div class="erp-meta-box-label" style="color: #64748b !important;">Voucher Total Amount</div>
                            <div class="erp-meta-box-value" style="color: #05421c !important; font-size: 20px;">
                                ₹ {{ number_format($data->total_amount, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- LEFT: Items Table -->
        <div class="col-lg-8 mb-2">
            <div class="erp-card p-0 mb-3">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <i class="fas fa-list-ul"></i> Process &amp; Lot Details
                    </div>
                    <span class="badge-erp-status badge-erp-info">
                        {{ count($data->items) }} {{ Str::plural('Item', count($data->items)) }}
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table erp-table table-bordered table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th style="width: 140px;">Lot No</th>
                                <th>Process / Item Name</th>
                                <th class="text-right" style="width: 110px;">Quantity</th>
                                <th class="text-right" style="width: 120px;">Rate (₹)</th>
                                <th class="text-right" style="width: 140px;">Amount (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->items as $index => $item)
                            <tr>
                                <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                                <td>
                                    @if($item->orderLot && $item->orderLot->lot_no)
                                        <span class="badge-erp-status badge-erp-info font-weight-bold">{{ $item->orderLot->lot_no }}</span>
                                    @else
                                        <span class="text-muted small">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-weight-600 text-dark">{{ $item->item_name }}</span>
                                </td>
                                <td class="text-right font-weight-500">{{ number_format($item->quantity, 2) }}</td>
                                <td class="text-right font-weight-500">₹ {{ number_format($item->rate, 2) }}</td>
                                <td class="text-right font-weight-bold" style="color: #05421c;">₹ {{ number_format($item->amount, 2) }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center py-3 text-muted">No line items recorded.</td>
                            </tr>
                            @endforelse
                        </tbody>
                        <tfoot>
                            <tr class="erp-table-grand-total">
                                <td colspan="5" class="text-right font-weight-bold">Items Total:</td>
                                <td class="text-right font-weight-bold grand-total-val">₹ {{ number_format($data->sub_total, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Remarks -->
            @if($data->remarks)
            <div class="erp-card p-3 mb-2">
                <div class="erp-card-title mb-2">
                    <i class="fas fa-comment-alt"></i> Remarks / Notes
                </div>
                <p class="text-dark mb-0 font-weight-500" style="white-space: pre-line;">{{ $data->remarks }}</p>
            </div>
            @endif
        </div>

        <!-- RIGHT: Financial Summary & Document -->
        <div class="col-lg-4 mb-2">
            <!-- Financial Breakdown -->
            <div class="erp-card p-0 mb-3">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <i class="fas fa-calculator"></i> Payment Summary
                    </div>
                </div>
                <div class="p-3">
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Sub Total</span>
                        <span class="font-weight-600">₹ {{ number_format($data->sub_total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">GST</span>
                        <span class="font-weight-600">+ ₹ {{ number_format($data->gst, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Other Charges</span>
                        <span class="font-weight-600">+ ₹ {{ number_format($data->other_charges, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-1 border-bottom">
                        <span class="text-muted">Round Off</span>
                        <span class="font-weight-600">₹ {{ number_format($data->round_off, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 mt-2" style="background: #eaf5ea; border-radius: 4px; padding-left: 8px; padding-right: 8px;">
                        <span class="font-weight-bold" style="color: #05421c; font-size: 15px;">Net Total</span>
                        <span class="font-weight-bold" style="color: #05421c; font-size: 16px;">₹ {{ number_format($data->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Uploaded Document Preview -->
            <div class="erp-card p-0 mb-3">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <i class="fas fa-paperclip"></i> Attached Slip / Document
                    </div>
                </div>
                <div class="p-3 text-center">
                    @if($data->document)
                        @php
                            $ext = pathinfo($data->document, PATHINFO_EXTENSION);
                            $isImg = in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'webp', 'gif']);
                        @endphp
                        @if($isImg)
                            <div class="mb-2 border rounded p-1" style="background: #f8fafc;">
                                <a href="{{ asset($data->document) }}" target="_blank">
                                    <img src="{{ asset($data->document) }}" alt="Slip" style="max-height: 200px; max-width: 100%; object-fit: contain; border-radius: 4px;">
                                </a>
                            </div>
                        @else
                            <div class="py-4">
                                <i class="fas fa-file-pdf text-danger" style="font-size: 48px;"></i>
                                <div class="mt-2 text-muted small font-weight-bold">{{ basename($data->document) }}</div>
                            </div>
                        @endif
                        <a href="{{ asset($data->document) }}" target="_blank" class="btn-erp btn-erp-primary btn-block text-center mt-2">
                            <i class="fas fa-external-link-alt mr-1"></i> View Full Document
                        </a>
                    @else
                        <div class="py-4 text-muted">
                            <i class="fas fa-file-excel text-secondary" style="font-size: 36px; opacity: 0.4;"></i>
                            <p class="mt-2 mb-0 small">No document or physical slip attached to this voucher.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
