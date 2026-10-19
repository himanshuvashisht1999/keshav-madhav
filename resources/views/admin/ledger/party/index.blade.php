@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-handshake text-primary"></i> 
            @if(request('type_id') === 'sales_agent')
                Sales Agent Ledger
            @else
                Party Financial Ledger
            @endif
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.party.detailedReport', request()->all()) }}" target="_blank" class="btn-erp btn-erp-primary" id="detailedReportBtn" title="Detailed Ledger Report for Filtered Parties">
                <i class="fas fa-file-invoice-dollar mr-1"></i> Detailed Ledger Report
            </a>
            <a href="{{ route('admin.ledger.party.export-list-pdf', request()->all()) }}" class="btn-erp btn-erp-outline" title="Export to PDF">
                <i class="fas fa-file-pdf text-danger"></i> Export PDF
            </a>
            <a href="{{ route('admin.ledger.party.export-list-excel', request()->all()) }}" class="btn-erp btn-erp-primary" title="Export to Excel">
                <i class="fas fa-file-excel"></i> Export Excel
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip (Exact Fabric Module Standard) -->
    <div class="row mb-2">
        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Parties</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalPartiesCount ?? 0) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Parties
                </span>
            </div>
        </div>

        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card {{ ($totalOpening ?? 0) >= 0 ? 'erp-metric-card-left-green' : 'erp-metric-card-left-yellow' }} py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: {{ ($totalOpening ?? 0) >= 0 ? 'var(--erp-green-light)' : 'var(--erp-yellow-bg)' }}; color: {{ ($totalOpening ?? 0) >= 0 ? 'var(--erp-green-primary)' : 'var(--erp-yellow-dark)' }}; display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); {{ ($totalOpening ?? 0) < 0 ? 'border: 1px solid var(--erp-yellow-badge-border);' : '' }}">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Opening Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: {{ ($totalOpening ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; line-height: 1.2;">
                            ₹ {{ number_format(abs($totalOpening ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge {{ ($totalOpening ?? 0) >= 0 ? '' : 'erp-badge-yellow' }}" style="{{ ($totalOpening ?? 0) >= 0 ? 'background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border);' : '' }} font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    {{ ($totalOpening ?? 0) >= 0 ? 'CR' : 'DR' }}
                </span>
            </div>
        </div>

        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Credit (CR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            ₹ {{ number_format($totalPeriodCredit ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    CR
                </span>
            </div>
        </div>

        <div class="col-lg col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Debit (DR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹ {{ number_format($totalPeriodDebit ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    DR
                </span>
            </div>
        </div>

        <div class="col-lg col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Net Outstanding</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($totalNetBalance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    {{ ($totalNetBalance ?? 0) >= 0 ? 'CR' : 'DR' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form action="{{ route('admin.ledger.party.index') }}" method="GET" class="m-0">
            <div class="row align-items-end">
                <div class="col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Search Party</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control erp-input" placeholder="Search by party name or phone...">
                </div>

                <div class="col-md-2 col-sm-3 col-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From</label>
                    <input type="date" name="start_date" id="ledger_from" value="{{ $startDate }}" max="{{ $endDate }}" class="form-control erp-input">
                </div>

                <div class="col-md-2 col-sm-3 col-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> To</label>
                    <input type="date" name="end_date" id="ledger_to" value="{{ $endDate }}" min="{{ $startDate }}" class="form-control erp-input">
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-tags mr-1"></i> Master Type</label>
                    <select name="type_id" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL TYPES --</option>
                        <option value="sales_agent" {{ request('type_id') === 'sales_agent' ? 'selected' : '' }}>Sales Agent</option>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}" {{ request('type_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-3 col-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-rupee-sign mr-1"></i> Min Closing</label>
                    <input type="number" step="0.01" name="min_closing" value="{{ request('min_closing') }}" class="form-control erp-input" placeholder="Min Amount">
                </div>

                <div class="col-md-2 col-sm-3 col-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-rupee-sign mr-1"></i> Max Closing</label>
                    <input type="number" step="0.01" name="max_closing" value="{{ request('max_closing') }}" class="form-control erp-input" placeholder="Max Amount">
                </div>

                <div class="col-md-2 col-sm-3 col-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-exchange-alt mr-1"></i> Balance Type</label>
                    <select name="balance_type" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">ALL BALANCE</option>
                        <option value="credit" {{ request('balance_type') == 'credit' ? 'selected' : '' }}>Credit (CR)</option>
                        <option value="debit" {{ request('balance_type') == 'debit' ? 'selected' : '' }}>Debit (DR)</option>
                    </select>
                </div>

                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Apply Filter
                        </button>
                        <a href="{{ route('admin.ledger.party.index', request('type_id') === 'sales_agent' ? ['type_id' => 'sales_agent'] : []) }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Party Name</th>
                        <th style="width: 140px;" class="text-center">Master Type</th>
                        <th style="width: 120px;">Contact Phone</th>
                        <th class="text-right" style="width: 150px;">
                            Opening Balance
                            <div class="font-weight-normal" style="font-size: 10px; text-transform: none;">{{ $startDate ? 'on ' . \Carbon\Carbon::parse($startDate)->format('d M Y') : 'opening entry' }}</div>
                        </th>
                        <th class="text-right" style="width: 130px;">Debit (DR)</th>
                        <th class="text-right" style="width: 130px;">Credit (CR)</th>
                        <th class="text-right" style="width: 150px;">
                            Closing Balance
                            <div class="font-weight-normal" style="font-size: 10px; text-transform: none;">{{ $endDate ? 'on ' . \Carbon\Carbon::parse($endDate)->format('d M Y') : 'today' }}</div>
                        </th>
                        <th style="width: 110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parties as $party)
                        @php
                            $bal = (float)($party->balance ?? 0);
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">
                                {{ $parties instanceof \Illuminate\Pagination\LengthAwarePaginator ? ($parties->firstItem() + $loop->index) : $loop->iteration }}
                            </td>
                            <td>
                                <div class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $party->name }}</div>
                                @if(isset($party->address) && $party->address)
                                    <small class="text-muted"><i class="fas fa-map-marker-alt mr-1"></i> {{ \Illuminate\Support\Str::limit($party->address, 50) }}</small>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 3px 8px;">
                                    {{ ucwords(str_replace('_', ' ', $party->party_type)) }}
                                </span>
                            </td>
                            <td class="text-muted font-weight-bold">
                                {{ $party->phone ?? '-' }}
                            </td>
                            @php $open = (float)($party->opening_balance ?? 0); @endphp
                            <td class="text-right font-weight-bold text-nowrap" style="color: {{ $open >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                ₹ {{ number_format(abs($open), 2) }}
                                <small class="font-weight-bold">{{ $open >= 0 ? 'CR' : 'DR' }}</small>
                            </td>
                            <td class="text-right text-nowrap {{ ($party->period_debit ?? 0) > 0 ? '' : 'text-muted' }}">₹ {{ number_format((float)($party->period_debit ?? 0), 2) }}</td>
                            <td class="text-right text-nowrap {{ ($party->period_credit ?? 0) > 0 ? '' : 'text-muted' }}">₹ {{ number_format((float)($party->period_credit ?? 0), 2) }}</td>
                            <td class="text-right font-weight-bold text-nowrap" style="color: {{ $bal >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                ₹ {{ number_format(abs($bal), 2) }}
                                <small class="font-weight-bold" style="color: {{ $bal >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                    {{ $bal >= 0 ? 'CR' : 'DR' }}
                                </small>
                            </td>
                            @php $statementUrl = route('admin.ledger.party.show', array_filter(['type' => $party->party_type, 'id' => $party->id, 'start_date' => $startDate, 'end_date' => $endDate])); @endphp
                            <td class="text-center">
                                <a href="{{ $statementUrl }}"
                                   class="erp-action-btn erp-btn-view"
                                   title="View Statement">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No parties found matching your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($parties instanceof \Illuminate\Pagination\LengthAwarePaginator && $parties->hasPages())
        <div class="d-flex justify-content-between align-items-center p-2 border-top flex-wrap" style="font-size: var(--erp-font-sm);">
            <div class="text-muted font-weight-bold">
                Showing {{ $parties->firstItem() ?? 0 }} to {{ $parties->lastItem() ?? 0 }} of {{ number_format($parties->total()) }} parties
            </div>
            <div>
                {{ $parties->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').each(function() {
                if ($(this).data('select2')) {
                    $(this).select2('destroy');
                }
                $(this).select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            });
        }

        // From cannot be after To (and the other way round)
        var fromEl = document.getElementById('ledger_from');
        var toEl = document.getElementById('ledger_to');
        if (fromEl && toEl) {
            fromEl.addEventListener('change', function () { toEl.min = this.value; });
            toEl.addEventListener('change', function () { fromEl.max = this.value; });
        }

        // Update Detailed Ledger Report button URL when Master Type changes
        const masterTypeSelect = document.querySelector('select[name="type_id"]');
        if (masterTypeSelect) {
            const updateDetailedLink = function () {
                const btn = document.getElementById('detailedReportBtn');
                if (btn) {
                    const url = new URL(btn.href, window.location.origin);
                    if (masterTypeSelect.value) {
                        url.searchParams.set('type_id', masterTypeSelect.value);
                    } else {
                        url.searchParams.delete('type_id');
                    }
                    btn.href = url.toString();
                }
            };
            masterTypeSelect.addEventListener('change', updateDetailedLink);
            $(masterTypeSelect).on('change', updateDetailedLink);
        }
    });
</script>
@endsection
