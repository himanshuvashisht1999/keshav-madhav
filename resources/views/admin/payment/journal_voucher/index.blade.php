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

    <!-- Summary Metrics Strip -->
    <div class="row mb-2">
        <div class="col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Vouchers</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalVouchers) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Filtered
                </span>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: #e8f5e9; color: #2e7d32; display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Debit (DR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #2e7d32; line-height: 1.2;">
                            ₹ {{ number_format($totalDebit, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge badge-success font-weight-bold" style="font-size: var(--erp-font-xs); padding: 4px 9px;">
                    DR
                </span>
            </div>
        </div>

        <div class="col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: #ffebee; color: #c62828; display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Credit (CR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #c62828; line-height: 1.2;">
                            ₹ {{ number_format($totalCredit, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge badge-danger font-weight-bold" style="font-size: var(--erp-font-xs); padding: 4px 9px;">
                    CR
                </span>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.payment.journal-voucher.index') }}" class="mb-0">
            <div class="form-row align-items-end">
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Voucher No</label>
                    <input type="text" name="voucher_no" value="{{ request('voucher_no') }}" class="form-control form-control-sm erp-filter-input" placeholder="Search voucher no...">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">From Date</label>
                    <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control form-control-sm erp-filter-input">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">To Date</label>
                    <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control form-control-sm erp-filter-input">
                </div>

                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Debit Master</label>
                    <select name="debit_master" id="debit_master" class="form-control form-control-sm select2 erp-filter-select">
                        <option value="">All Debit Masters</option>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}" {{ request('debit_master') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Credit Master</label>
                    <select name="credit_master" id="credit_master" class="form-control form-control-sm select2 erp-filter-select">
                        <option value="">All Credit Masters</option>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}" {{ request('credit_master') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Debit Amount Range (₹)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" name="min_debit" value="{{ request('min_debit') }}" class="form-control erp-filter-input" placeholder="Min DR">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">-</span>
                        </div>
                        <input type="number" step="any" name="max_debit" value="{{ request('max_debit') }}" class="form-control erp-filter-input" placeholder="Max DR">
                    </div>
                </div>

                <div class="col-md-4 col-sm-6 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Credit Amount Range (₹)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" step="any" name="min_credit" value="{{ request('min_credit') }}" class="form-control erp-filter-input" placeholder="Min CR">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">-</span>
                        </div>
                        <input type="number" step="any" name="max_credit" value="{{ request('max_credit') }}" class="form-control erp-filter-input" placeholder="Max CR">
                    </div>
                </div>

                <div class="col-md-4 col-sm-12 mb-1 d-flex align-items-end justify-content-md-end" style="gap: 6px;">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm px-3" title="Apply Filters">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.payment.journal-voucher.index') }}" class="btn-erp btn-erp-outline btn-sm px-3" title="Reset Filters">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="erp-card p-0">
        <div class="erp-card-header d-flex justify-content-between align-items-center">
            <div class="erp-card-title">
                <i class="fas fa-list-alt"></i> Journal Vouchers ({{ $vouchers->total() }})
            </div>
            @if(request()->anyFilled(['voucher_no', 'from_date', 'to_date', 'debit_master', 'credit_master', 'min_debit', 'max_debit', 'min_credit', 'max_credit']))
                <span class="badge-erp-status badge-erp-info py-0 px-2 font-weight-bold" style="font-size: 11px;">
                    <i class="fas fa-filter mr-1"></i> Filter Active
                </span>
            @endif
        </div>
        <div class="table-responsive">
            <table class="table erp-table table-bordered table-hover mb-0 w-100 text-nowrap">
                <thead>
                    <tr>
                        <th style="width: 50px;" class="text-center">#</th>
                        <th style="width: 130px;">Voucher No</th>
                        <th style="width: 110px;">Date</th>
                        <th style="min-width: 180px;"><span class="text-success mr-1">●</span>Debit Master (Dr)</th>
                        <th style="min-width: 180px;"><span class="text-danger mr-1">●</span>Credit Master (Cr)</th>
                        <th class="text-right" style="width: 130px;">Total Debit (₹)</th>
                        <th class="text-right" style="width: 130px;">Total Credit (₹)</th>
                        <th>Global Narration</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                    <tr>
                        <td class="text-center text-muted font-weight-bold" style="font-size: 12px;">
                            {{ ($vouchers->currentPage() - 1) * $vouchers->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <a href="{{ route('admin.payment.journal-voucher.show', $voucher->id) }}" class="badge-erp-status badge-erp-info font-weight-bold" style="text-decoration: none;" title="View Voucher">
                                {{ $voucher->voucher_no }}
                            </a>
                        </td>
                        <td>{{ date('d M Y', strtotime($voucher->date)) }}</td>
                        <td>
                            @forelse($voucher->debit_items ?? [] as $dItem)
                                <div class="{{ !$loop->last ? 'mb-1 pb-1 border-bottom border-light' : '' }}">
                                    <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $dItem->party_name }}</div>
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        <span class="badge badge-light border text-muted py-0 px-1" style="font-size: 10.5px;">{{ $dItem->master_name }}</span>
                                        @if(count($voucher->debit_items) > 1)
                                            <span class="font-weight-bold small" style="color: #05421c;">₹ {{ number_format($dItem->amount, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td>
                            @forelse($voucher->credit_items ?? [] as $cItem)
                                <div class="{{ !$loop->last ? 'mb-1 pb-1 border-bottom border-light' : '' }}">
                                    <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $cItem->party_name }}</div>
                                    <div class="d-flex align-items-center" style="gap: 4px;">
                                        <span class="badge badge-light border text-muted py-0 px-1" style="font-size: 10.5px;">{{ $cItem->master_name }}</span>
                                        @if(count($voucher->credit_items) > 1)
                                            <span class="font-weight-bold small text-danger">₹ {{ number_format($cItem->amount, 2) }}</span>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <span class="text-muted">-</span>
                            @endforelse
                        </td>
                        <td class="text-right font-weight-bold" style="color: #05421c;">₹ {{ number_format($voucher->total_debit, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">₹ {{ number_format($voucher->total_credit, 2) }}</td>
                        <td title="{{ $voucher->narration }}">
                            <span class="text-dark">{{ \Str::limit($voucher->narration, 45) ?: '-' }}</span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex align-items-center justify-content-center" style="gap: 6px;">
                                <a href="{{ route('admin.payment.journal-voucher.show', $voucher->id) }}" class="erp-action-btn erp-btn-view" title="View Voucher">
                                    <i class="fas fa-eye"></i>
                                </a>
                                {{-- <a href="{{ route('admin.payment.journal-voucher.edit', $voucher->id) }}" class="erp-action-btn erp-btn-edit" title="Edit Voucher">
                                    <i class="fas fa-edit"></i>
                                </a> --}}
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="fas fa-inbox fa-2x mb-2 d-block" style="opacity: 0.4;"></i>
                            No journal vouchers found matching the filter criteria.
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
    $(document).ready(function() {
        if ($.fn.select2) {
            $('#debit_master').select2({
                placeholder: 'All Debit Masters',
                allowClear: true,
                width: '100%'
            });
            $('#credit_master').select2({
                placeholder: 'All Credit Masters',
                allowClear: true,
                width: '100%'
            });
        }
    });
</script>
@endpush
