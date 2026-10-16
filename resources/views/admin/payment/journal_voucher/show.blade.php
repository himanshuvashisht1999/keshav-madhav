@extends('admin.layouts.app')
@section('title', 'Journal Voucher #' . $voucher->voucher_no)

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
                <i class="fas fa-book text-primary"></i> Journal Voucher Details
            </div>
            <p class="erp-page-subtitle mb-0">Record #{{ $voucher->voucher_no }} | Double-entry journal voucher details &amp; reconciliation</p>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.journal-voucher.download', $voucher->id) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-file-pdf mr-1 text-danger"></i> Download PDF
            </a>
            <a href="{{ route('admin.payment.journal-voucher.edit', $voucher->id) }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-edit mr-1"></i> Edit Voucher
            </a>
            <button type="button" onclick="deleteVoucher({{ $voucher->id }})" class="btn-erp btn-erp-danger" title="Delete Voucher">
                <i class="fas fa-trash mr-1"></i> Delete Voucher
            </button>
            <form id="delete-form-{{ $voucher->id }}" action="{{ route('admin.payment.journal-voucher.delete', $voucher->id) }}" method="POST" style="display: none;">
                @csrf
            </form>
            <a href="{{ route('admin.payment.journal-voucher.index') }}" class="btn-erp btn-erp-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- ERP Voucher Banner -->
    <div class="erp-voucher-banner mb-3">
        <div class="row align-items-center">
            <div class="col-lg-6 col-md-12 mb-3 mb-lg-0">
                <div class="text-uppercase small font-weight-bold opacity-75 mb-1" style="letter-spacing: 0.8px; color: #fcee21;">
                    <i class="fas fa-bookmark mr-1"></i> General Journal Voucher
                </div>
                <h3 class="font-weight-bold mb-1 text-white">
                    {{ $voucher->voucher_no }}
                </h3>
                <div class="d-flex align-items-center flex-wrap" style="gap: 8px; margin-top: 6px;">
                    <span class="badge" style="background: rgba(255, 255, 255, 0.2); color: #ffffff; padding: 4px 8px; font-size: 12px;">
                        <i class="fas fa-calendar-alt mr-1"></i> {{ date('d F, Y', strtotime($voucher->date)) }}
                    </span>
                    <span class="badge" style="background: rgba(252, 238, 33, 0.2); color: #fcee21; border: 1px solid rgba(252, 238, 33, 0.4); font-weight: 700; padding: 4px 8px; font-size: 12px;">
                        <i class="fas fa-check-circle mr-1"></i> Balanced
                    </span>
                </div>
            </div>
            <div class="col-lg-6 col-md-12">
                <div class="row">
                    <div class="col-sm-6 mb-2 mb-sm-0">
                        <div class="erp-meta-box text-center">
                            <div class="erp-meta-box-label">Total Debit (DR)</div>
                            <div class="erp-meta-box-value">
                                ₹ {{ number_format($voucher->total_debit, 2) }}
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="erp-meta-box text-center" style="background: #ffffff !important; border: 1px solid #cbd5e1 !important;">
                            <div class="erp-meta-box-label" style="color: #64748b !important;">Total Credit (CR)</div>
                            <div class="erp-meta-box-value text-danger" style="font-size: 20px;">
                                ₹ {{ number_format($voucher->total_credit, 2) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Items Table Card -->
    <div class="erp-card p-0 mb-3">
        <div class="erp-card-header d-flex justify-content-between align-items-center">
            <div class="erp-card-title">
                <i class="fas fa-list-ul"></i> Journal Line Entries
            </div>
            <span class="badge-erp-status badge-erp-info">
                {{ count($voucher->items) }} Entries
            </span>
        </div>
        <div class="table-responsive">
            <table class="table erp-table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 200px;">Master Type</th>
                        <th>Party / Account Name</th>
                        <th>Item Narration</th>
                        <th class="text-right" style="width: 160px;">Debit (DR)</th>
                        <th class="text-right" style="width: 160px;">Credit (CR)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($voucher->items as $index => $item)
                    <tr>
                        <td class="text-center font-weight-bold text-muted">{{ $index + 1 }}</td>
                        <td>
                            <span class="badge-erp-status badge-erp-info font-weight-bold">{{ $item->master_name }}</span>
                        </td>
                        <td>
                            <span class="font-weight-600 text-dark">{{ $item->party_name }}</span>
                        </td>
                        <td class="text-muted small">{{ $item->narration ?? '-' }}</td>
                        <td class="text-right font-weight-bold" style="color: #05421c;">
                            {{ $item->type == 'debit' ? '₹ ' . number_format($item->amount, 2) : '-' }}
                        </td>
                        <td class="text-right font-weight-bold text-danger">
                            {{ $item->type == 'credit' ? '₹ ' . number_format($item->amount, 2) : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="4" class="text-right font-weight-bold">TOTALS:</td>
                        <td class="text-right font-weight-bold grand-total-val">₹ {{ number_format($voucher->total_debit, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">₹ {{ number_format($voucher->total_credit, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

    <!-- Overall Narration -->
    @if($voucher->narration)
    <div class="erp-card p-3 mb-3">
        <div class="erp-card-title mb-2">
            <i class="fas fa-comment-alt"></i> Overall Narration
        </div>
        <p class="text-dark mb-0 font-weight-500" style="white-space: pre-line;">{{ $voucher->narration }}</p>
    </div>
    @endif

    <div class="d-flex justify-content-between align-items-center text-muted small px-1">
        <span><i class="fas fa-clock mr-1"></i> Created on {{ $voucher->created_at->format('d M Y, h:i A') }}</span>
    </div>
</div>
@endsection

@push('scripts')
<script>
function deleteVoucher(id) {
    if (confirm('Are you sure you want to delete this voucher? All associated financial entries will be reversed.')) {
        document.getElementById('delete-form-' + id).submit();
    }
}
</script>
@endpush
