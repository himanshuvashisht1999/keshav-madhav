@extends('admin.layouts.app')
@section('title', 'Journal Vouchers')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-book text-primary"></i> Journal Vouchers
            </div>
            <p class="erp-page-subtitle mb-0">Double-entry bookkeeping, cross-ledger adjustment vouchers and balance reconciliations</p>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.journal-voucher.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus mr-1"></i> New Journal Voucher
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Table Card -->
    <div class="erp-card p-0">
        <div class="erp-card-header">
            <div class="erp-card-title">
                <i class="fas fa-list-alt"></i> All Journal Vouchers ({{ $vouchers->total() }})
            </div>
        </div>
        <div class="table-responsive">
            <table class="table erp-table table-bordered table-hover mb-0 w-100 text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 130px;">Voucher No</th>
                        <th style="width: 120px;">Date</th>
                        <th class="text-right" style="width: 140px;">Total Debit (₹)</th>
                        <th class="text-right" style="width: 140px;">Total Credit (₹)</th>
                        <th>Global Narration</th>
                        <th style="width: 160px;">Created At</th>
                        <th class="text-center" style="width: 120px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                    <tr>
                        <td>
                            <span class="badge-erp-status badge-erp-info font-weight-bold">
                                {{ $voucher->voucher_no }}
                            </span>
                        </td>
                        <td>{{ date('d M Y', strtotime($voucher->date)) }}</td>
                        <td class="text-right font-weight-bold" style="color: #05421c;">₹ {{ number_format($voucher->total_debit, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">₹ {{ number_format($voucher->total_credit, 2) }}</td>
                        <td title="{{ $voucher->narration }}">
                            <span class="text-dark">{{ \Str::limit($voucher->narration, 45) ?: '-' }}</span>
                        </td>
                        <td class="text-muted small">{{ $voucher->created_at->format('d M Y, h:i A') }}</td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center" style="gap: 6px;">
                                <a href="{{ route('admin.payment.journal-voucher.show', $voucher->id) }}" class="erp-action-btn erp-btn-view" title="View Voucher">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.payment.journal-voucher.edit', $voucher->id) }}" class="erp-action-btn erp-btn-edit" title="Edit Voucher">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <button type="button" onclick="deleteVoucher({{ $voucher->id }})" class="erp-action-btn erp-btn-delete border-0" title="Delete Voucher">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <form id="delete-form-{{ $voucher->id }}" action="{{ route('admin.payment.journal-voucher.delete', $voucher->id) }}" method="POST" style="display: none;">
                                    @csrf
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.4;"></i>
                            No journal vouchers found.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vouchers->hasPages())
        <div class="p-2 border-top d-flex justify-content-between align-items-center">
            <span class="text-muted small">Showing {{ $vouchers->firstItem() }} to {{ $vouchers->lastItem() }} of {{ $vouchers->total() }} vouchers</span>
            <div>{{ $vouchers->links() }}</div>
        </div>
        @endif
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
