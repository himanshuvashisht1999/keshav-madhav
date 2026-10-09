@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-university text-primary"></i> Bank &amp; Cash Ledger
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.bank-cash-ledger.export-list-pdf', request()->all()) }}" class="btn-erp btn-erp-outline" title="Export to PDF">
                <i class="fas fa-file-pdf text-danger"></i> Export PDF
            </a>
            <a href="{{ route('admin.ledger.bank-cash-ledger.export-list-excel', request()->all()) }}" class="btn-erp btn-erp-primary" title="Export to Excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip (Exact Fabric Module Standard) -->
    <div class="row mb-2">
        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Accounts</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalAccountsCount ?? 0) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Accounts
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Positive Balance (CR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            ₹ {{ number_format($totalCreditBalance ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Credit
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Overdraft (DR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹ {{ number_format($totalDebitBalance ?? 0, 2) }}
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
                        <i class="fas fa-vault"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Net Available Liquidity</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($totalNetBalance ?? 0), 2) }}
                            <small style="font-size: 11px; font-weight: 700; color: {{ ($totalNetBalance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                {{ ($totalNetBalance ?? 0) >= 0 ? 'CR' : 'DR' }}
                            </small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Net
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form action="{{ route('admin.ledger.bank-cash-ledger.index') }}" method="GET" class="m-0">
            <div class="row align-items-end">
                <div class="col-md-5 col-sm-12 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search text-muted mr-1"></i> Search Account Name</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control erp-filter-input" placeholder="Search by bank or cash account name...">
                </div>

                <div class="col-md-4 col-sm-12 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-landmark text-muted mr-1"></i> Account Type</label>
                    <select name="type_id" class="form-control erp-filter-select">
                        <option value="">All Account Types</option>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}" {{ request('type_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 col-sm-12 mb-1 d-flex">
                    <button type="submit" class="btn-erp btn-erp-primary mr-2" style="height: 31px;">
                        <i class="fas fa-filter mr-1"></i> Apply Filter
                    </button>
                    <a href="{{ route('admin.ledger.bank-cash-ledger.index') }}" class="btn-erp btn-erp-outline" style="height: 31px;" title="Reset filters">
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
                        <th>Account Name</th>
                        <th style="width: 140px;" class="text-center">Type</th>
                        <th style="width: 180px;">Account Number / Code</th>
                        <th class="text-right" style="width: 180px;">Current Balance</th>
                        <th class="text-center" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parties as $party)
                        @php
                            $bal = (float)($party->balance ?? 0);
                            $isBank = stripos($party->party_type, 'bank') !== false;
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $loop->iteration }}</td>
                            <td>
                                <div class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $party->name }}</div>
                                @if(isset($party->branch_name) && $party->branch_name)
                                    <small class="text-muted"><i class="fas fa-map-pin mr-1"></i> Branch: {{ $party->branch_name }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isBank)
                                    <span class="badge" style="background: var(--erp-bg-header); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 11px; padding: 3px 7px;">
                                        <i class="fas fa-landmark mr-1"></i> {{ ucwords(str_replace('_', ' ', $party->party_type)) }}
                                    </span>
                                @else
                                    <span class="badge erp-badge-yellow" style="font-size: 11px; padding: 3px 7px;">
                                        <i class="fas fa-money-bill-wave mr-1"></i> {{ ucwords(str_replace('_', ' ', $party->party_type)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-muted font-weight-bold">
                                {{ $party->account_number ?? $party->code ?? '-' }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: {{ $bal >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                ₹ {{ number_format(abs($bal), 2) }}
                                <span class="badge ml-1" style="background: {{ $bal >= 0 ? 'var(--erp-green-light)' : '#fee2e2' }}; color: {{ $bal >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; font-size: 10px; padding: 2px 5px;">
                                    {{ $bal >= 0 ? 'CR' : 'DR' }}
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.ledger.bank-cash-ledger.show', ['type' => $party->party_type, 'id' => $party->id]) }}" 
                                   class="erp-action-btn erp-btn-view" 
                                   title="View Account Statement">
                                    <i class="fas fa-book-open"></i>
                                </a>
                                <a href="{{ route('admin.ledger.bank-cash-ledger.show', ['type' => $party->party_type, 'id' => $party->id]) }}" 
                                   class="btn-erp btn-erp-primary btn-xs ml-1" 
                                   style="height: 24px; font-size: 11px; padding: 2px 8px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-university fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No bank or cash accounts found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($parties->count() > 0)
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="4" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">TOTAL NET LIQUIDITY (ALL LISTED ACCOUNTS):</td>
                        <td class="text-right font-weight-bold grand-total-val">
                            ₹ {{ number_format(abs($totalNetBalance ?? 0), 2) }}
                            <span class="badge ml-1" style="background: #ffffff; color: var(--erp-green-primary); font-size: 10px; padding: 2px 5px;">
                                {{ ($totalNetBalance ?? 0) >= 0 ? 'CR' : 'DR' }}
                            </span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
