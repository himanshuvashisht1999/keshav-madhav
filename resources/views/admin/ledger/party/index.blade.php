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
        <div class="col-md-3 col-sm-6 mb-1">
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

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Credit (CR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            ₹ {{ number_format($totalCreditBalance ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    CR
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Debit (DR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹ {{ number_format($totalDebitBalance ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    DR
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
                <div class="col-md-5 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Search Party</label>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control erp-input" placeholder="Search by party name or phone...">
                </div>

                <div class="col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-tags mr-1"></i> Master / Party Type</label>
                    <select name="type_id" class="form-control select2 erp-input">
                        <option value="">-- ALL MASTER TYPES --</option>
                        <option value="sales_agent" {{ request('type_id') === 'sales_agent' ? 'selected' : '' }}>Sales Agent</option>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}" {{ request('type_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
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
                        <th style="width: 130px;">Contact Phone</th>
                        <th class="text-right" style="width: 160px;">Current Balance</th>
                        <th style="width: 110px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($parties as $party)
                        @php
                            $bal = (float)($party->balance ?? 0);
                        @endphp
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $loop->iteration }}</td>
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
                            <td class="text-right font-weight-bold" style="color: {{ $bal >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                ₹ {{ number_format(abs($bal), 2) }}
                                <small class="font-weight-bold" style="color: {{ $bal >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }};">
                                    {{ $bal >= 0 ? 'CR' : 'DR' }}
                                </small>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('admin.ledger.party.show', ['type' => $party->party_type, 'id' => $party->id]) }}" 
                                   class="erp-action-btn erp-btn-view" 
                                   title="View Statement">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.ledger.party.show', ['type' => $party->party_type, 'id' => $party->id]) }}" 
                                   class="btn-erp btn-erp-primary btn-xs ml-1" 
                                   style="height: 24px; font-size: 11px; padding: 2px 8px;">
                                    View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-user-slash fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No parties found matching your search.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($parties->count() > 0)
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="4" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">TOTAL OUTSTANDING NET BALANCE (ALL PARTIES):</td>
                        <td class="text-right font-weight-bold grand-total-val">
                            ₹ {{ number_format(abs($totalNetBalance ?? 0), 2) }}
                            <small>{{ ($totalNetBalance ?? 0) >= 0 ? 'CR' : 'DR' }}</small>
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
