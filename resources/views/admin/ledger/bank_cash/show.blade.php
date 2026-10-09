@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-university text-primary"></i> {{ $party->name }}
                <span class="badge ml-2" style="background: var(--erp-bg-header); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px;">
                    {{ strtoupper($type) }}
                </span>
            </div>
            <div style="font-size: 11.5px; color: var(--erp-text-muted);">
                @if(isset($party->account_number) && $party->account_number)
                    Account No: <strong style="color: var(--erp-text-heading);">{{ $party->account_number }}</strong> |
                @endif
                @if(isset($party->branch_name) && $party->branch_name)
                    Branch: <strong style="color: var(--erp-text-heading);">{{ $party->branch_name }}</strong> |
                @endif
                {{ $party->phone ?? '' }}
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.bank-cash-ledger.download', array_merge(request()->query(), ['type' => $type, 'id' => $party->id])) }}" class="btn-erp btn-erp-outline" title="Download PDF Statement">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </a>
            <a href="{{ route('admin.ledger.bank-cash-ledger.export-excel', array_merge(request()->query(), ['type' => $type, 'id' => $party->id])) }}" class="btn-erp btn-erp-primary" title="Export Statement to Excel">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.ledger.bank-cash-ledger.index') }}" class="btn-erp btn-erp-outline" title="Back to Listing">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip (Exact Fabric Module Standard) -->
    <div class="row mb-2">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Opening Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($openingBalAmount ?? 0), 2) }}
                            <small style="font-size: 11px; font-weight: 700; color: {{ ($openingBalAmount ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                {{ ($openingBalAmount ?? 0) >= 0 ? 'CR' : 'DR' }}
                            </small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Opening
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Payments Out (DR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹ {{ number_format($periodTotalDebit ?? $transactions->sum('debit'), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Debit
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Receipts In (CR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            ₹ {{ number_format($periodTotalCredit ?? $transactions->sum('credit'), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Credit
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Available Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($party->balance ?? 0), 2) }}
                            <small style="font-size: 11px; font-weight: 700; color: {{ ($party->balance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                {{ ($party->balance ?? 0) >= 0 ? 'CR' : 'DR' }}
                            </small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Balance
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form action="{{ route('admin.ledger.bank-cash-ledger.show', ['type' => $type, 'id' => $party->id]) }}" method="GET" class="m-0">
            <div class="row align-items-end">
                <div class="col-md-4 col-sm-12 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt text-muted mr-1"></i> Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control erp-filter-input">
                </div>
                <div class="col-md-4 col-sm-12 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt text-muted mr-1"></i> End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control erp-filter-input">
                </div>
                <div class="col-md-4 col-sm-12 mb-1 d-flex">
                    <button type="submit" class="btn-erp btn-erp-primary mr-2" style="height: 31px;">
                        <i class="fas fa-filter mr-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('admin.ledger.bank-cash-ledger.show', ['type' => $type, 'id' => $party->id]) }}" 
                       class="btn-erp btn-erp-outline" style="height: 31px;" title="Reset filters">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 110px;">Date</th>
                        <th style="width: 110px;" class="text-center">Type</th>
                        <th style="width: 140px;">Ref / Voucher</th>
                        <th>Particulars / Description</th>
                        <th class="text-right" style="width: 140px;">Debit (DR)</th>
                        <th class="text-right" style="width: 140px;">Credit (CR)</th>
                        <th class="text-right" style="width: 150px;">Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @php $currentBalance = $openingBalAmount; @endphp
                    @if($startDate)
                    <tr style="background: var(--erp-bg-header);">
                        <td class="text-center text-muted"><i class="fas fa-clock"></i></td>
                        <td class="font-weight-bold">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                        <td class="text-center">
                            <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); font-size: 10px;">OPENING</span>
                        </td>
                        <td class="text-muted">-</td>
                        <td class="font-weight-bold" style="color: var(--erp-text-heading);"><em>Opening Balance Brought Forward</em></td>
                        <td class="text-right text-muted">-</td>
                        <td class="text-right text-muted">-</td>
                        <td class="text-right font-weight-bold" style="color: var(--erp-text-heading);">
                            ₹ {{ number_format(abs($openingBalAmount), 2) }}
                            <small class="badge ml-1" style="background: #ffffff; color: var(--erp-green-primary); font-size: 10px; padding: 2px 4px;">{{ $openingBalAmount >= 0 ? 'CR' : 'DR' }}</small>
                        </td>
                    </tr>
                    @endif

                    @forelse($transactions as $index => $tx)
                        @php $currentBalance = $tx->running_balance; @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                            <td class="font-weight-bold text-nowrap">
                                {{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 10.5px; padding: 2px 7px;">
                                    {{ $tx->type }}
                                </span>
                            </td>
                            <td class="font-weight-bold text-muted small">
                                {{ $tx->ref ?? '-' }}
                            </td>
                            <td>
                                <span class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $tx->description }}</span>
                            </td>
                            <td class="text-right font-weight-bold text-danger">
                                {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-text-heading);">
                                ₹ {{ number_format(abs($currentBalance), 2) }}
                                <small class="badge ml-1" style="background: {{ $currentBalance >= 0 ? 'var(--erp-green-light)' : '#fee2e2' }}; color: {{ $currentBalance >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; font-size: 10px; padding: 2px 4px;">
                                    {{ $currentBalance >= 0 ? 'CR' : 'DR' }}
                                </small>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-receipt fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No transaction records found in the selected period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!$transactions->isEmpty())
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="5" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">STATEMENT PERIOD TOTALS:</td>
                        <td class="text-right font-weight-bold text-danger">
                            ₹ {{ number_format($transactions->sum('debit'), 2) }}
                        </td>
                        <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                            ₹ {{ number_format($transactions->sum('credit'), 2) }}
                        </td>
                        <td class="text-right font-weight-bold grand-total-val">
                            ₹ {{ number_format(abs($currentBalance), 2) }}
                            <small class="badge ml-1" style="background: #ffffff; color: var(--erp-green-primary); font-size: 10px; padding: 2px 4px;">{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                        </td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection