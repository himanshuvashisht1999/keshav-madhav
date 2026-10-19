@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-file-invoice-dollar text-primary"></i> 
            Detailed Ledger Report
            @if(!empty($typeId))
                @php
                    $displayTypeName = $typeId === 'sales_agent' 
                        ? 'Sales Agent' 
                        : (isset($masters) && $masters->firstWhere('id', $typeId) ? $masters->firstWhere('id', $typeId)->name : str_replace('_', ' ', $typeId));
                @endphp
                <span class="badge ml-2" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">
                    {{ strtoupper($displayTypeName) }}
                </span>
            @endif
            <span class="badge ml-1" style="background: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: var(--erp-font-xs);">
                {{ $filteredParties->count() }} {{ \Illuminate\Support\Str::plural('Party', $filteredParties->count()) }}
            </span>
            <span class="badge ml-1" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a; font-size: var(--erp-font-xs);">
                Mode: {{ $viewMode === 'party_wise' ? 'Party-wise (Grouped)' : 'Mix (Consolidated)' }}
            </span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.party.detailedReportPdf', request()->all()) }}" class="btn-erp btn-erp-outline" title="Download PDF Report">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </a>
            <a href="{{ route('admin.ledger.party.detailedReportExcel', request()->all()) }}" class="btn-erp btn-erp-primary" title="Export Report to Excel">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.ledger.party.index', request()->except(['view_mode', 'transaction_type', 'adjustment_type', 'ref_no', 'has_slip', 'selected_party'])) }}" class="btn-erp btn-erp-outline" title="Back to Party Ledger List">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>
    </div>

    @include('admin.ledger.partials.balance_mismatch')

    <!-- Summary Metrics Strip (Exact Fabric Module Standard) -->
    <div class="row mb-2">
        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-users"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Parties Filtered</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($filteredParties->count()) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Parties
                </span>
            </div>
        </div>

        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-history"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Opening Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($totalOpeningBalance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    {{ ($totalOpeningBalance ?? 0) >= 0 ? 'CR' : 'DR' }}
                </span>
            </div>
        </div>

        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-arrow-up"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Period Debit (DR)</div>
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

        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Period Credit (CR)</div>
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

        <div class="col-lg col-md-4 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Closing Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($totalClosingBalance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    {{ ($totalClosingBalance ?? 0) >= 0 ? 'CR' : 'DR' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <form action="{{ route('admin.ledger.party.detailedReport') }}" method="GET" class="m-0">
            <input type="hidden" name="search" value="{{ request('search') }}">
            <input type="hidden" name="min_closing" value="{{ request('min_closing') }}">
            <input type="hidden" name="max_closing" value="{{ request('max_closing') }}">
            <input type="hidden" name="balance_type" value="{{ request('balance_type') }}">

            <div class="row align-items-end">
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-tags mr-1"></i> Master Type</label>
                    <select name="type_id" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL TYPES --</option>
                        <option value="sales_agent" {{ request('type_id') === 'sales_agent' ? 'selected' : '' }}>Sales Agent</option>
                        @if(isset($masters))
                            @foreach($masters as $m)
                                <option value="{{ $m->id }}" {{ (string)request('type_id') === (string)$m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control erp-input">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control erp-input">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-tag mr-1"></i> Type</label>
                    <select name="transaction_type" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL TYPES --</option>
                        @if(isset($availableTypes) && count($availableTypes) > 0)
                            @foreach($availableTypes as $t)
                                <option value="{{ $t }}" {{ strcasecmp(request('transaction_type'), $t) === 0 ? 'selected' : '' }}>{{ $t }}</option>
                            @endforeach
                        @else
                            <option value="Sale" {{ strcasecmp(request('transaction_type'), 'Sale') === 0 ? 'selected' : '' }}>Sale</option>
                            <option value="Payment" {{ strcasecmp(request('transaction_type'), 'Payment') === 0 ? 'selected' : '' }}>Payment</option>
                            <option value="Receipt" {{ strcasecmp(request('transaction_type'), 'Receipt') === 0 ? 'selected' : '' }}>Receipt</option>
                            <option value="Sale Return" {{ strcasecmp(request('transaction_type'), 'Sale Return') === 0 ? 'selected' : '' }}>Sale Return</option>
                            <option value="Adjustment" {{ strcasecmp(request('transaction_type'), 'Adjustment') === 0 ? 'selected' : '' }}>Adjustment</option>
                        @endif
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-filter mr-1"></i> Adjustment</label>
                    <select name="adjustment_type" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL --</option>
                        <option value="payment" {{ request('adjustment_type') === 'payment' ? 'selected' : '' }}>Payment (DR)</option>
                        <option value="receipt" {{ request('adjustment_type') === 'receipt' ? 'selected' : '' }}>Receipt (CR)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Ref / Voucher</label>
                    <input type="text" name="ref_no" value="{{ request('ref_no') }}" placeholder="Search Ref..." class="form-control erp-input">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-paperclip mr-1"></i> Slip</label>
                    <select name="has_slip" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL --</option>
                        <option value="yes" {{ request('has_slip') === 'yes' ? 'selected' : '' }}>With Slip</option>
                        <option value="no" {{ request('has_slip') === 'no' ? 'selected' : '' }}>Without Slip</option>
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tag mr-1"></i> Party Selection</label>
                    <select name="selected_party" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">All Filtered Parties ({{ $parties->count() }})</option>
                        @foreach($parties as $p)
                            @php $pKey = $p->party_type . '_' . $p->id; @endphp
                            <option value="{{ $pKey }}" {{ (string)($selectedParty ?? request('selected_party')) === (string)$pKey ? 'selected' : '' }}>
                                {{ $p->name }} ({{ ucfirst($p->party_type) }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-columns mr-1"></i> View Mode</label>
                    <select name="view_mode" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="mix" {{ request('view_mode', 'mix') === 'mix' ? 'selected' : '' }}>Mix (Consolidated)</option>
                        <option value="party_wise" {{ request('view_mode') === 'party_wise' ? 'selected' : '' }}>Party-wise (Grouped)</option>
                    </select>
                </div>

                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.ledger.party.detailedReport', request()->only(['type_id', 'search', 'min_closing', 'max_closing', 'balance_type'])) }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- View Mode: Party-wise (Grouped) -->
    @if(isset($viewMode) && $viewMode === 'party_wise')
        @forelse($detailedParties as $dp)
            <div class="erp-card mb-3">
                <div class="erp-card-header d-flex justify-content-between align-items-center flex-wrap">
                    <div class="erp-card-title d-flex align-items-center">
                        <i class="fas fa-user-tie text-primary mr-2"></i>
                        <span class="font-weight-bold" style="font-size: var(--erp-font-base);">{{ $dp->party->name }}</span>
                        <span class="badge ml-2" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">
                            {{ strtoupper($dp->party->party_type) }}
                        </span>
                        @if(!empty($dp->party->phone))
                            <small class="text-muted font-weight-normal ml-2"><i class="fas fa-phone-alt mr-1"></i>{{ $dp->party->phone }}</small>
                        @endif
                    </div>
                    <div class="d-flex align-items-center flex-wrap mt-1 mt-md-0">
                        <span class="mr-3 font-weight-bold" style="font-size: var(--erp-font-sm);">
                            Opening: ₹ {{ number_format(abs($dp->opening_balance), 2) }} 
                            <small class="text-muted">{{ $dp->opening_balance >= 0 ? 'CR' : 'DR' }}</small>
                        </span>
                        <span class="mr-3 font-weight-bold text-danger" style="font-size: var(--erp-font-sm);">
                            Debit: ₹ {{ number_format($dp->period_debit, 2) }}
                        </span>
                        <span class="mr-3 font-weight-bold" style="color: var(--erp-green-primary); font-size: var(--erp-font-sm);">
                            Credit: ₹ {{ number_format($dp->period_credit, 2) }}
                        </span>
                        <span class="mr-3 font-weight-bold" style="font-size: var(--erp-font-sm); color: var(--erp-text-heading);">
                            Closing: ₹ {{ number_format(abs($dp->closing_balance), 2) }} 
                            <small class="font-weight-bold">{{ $dp->closing_balance >= 0 ? 'CR' : 'DR' }}</small>
                        </span>
                        <a href="{{ route('admin.ledger.party.show', ['type' => $dp->party->party_type, 'id' => $dp->party->id]) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2" target="_blank" title="Open Individual Statement">
                            <i class="fas fa-external-link-alt mr-1"></i> Statement
                        </a>
                    </div>
                </div>
                <div class="erp-card-body p-2 table-responsive">
                    <table class="erp-table table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Date</th>
                                <th style="width: 110px;" class="text-center">Type</th>
                                <th style="width: 130px;">Reference</th>
                                <th style="width: 120px;">Bill No.</th>
                                <th>Particulars / Description</th>
                                <th class="text-right" style="width: 140px;">Debit (DR)</th>
                                <th class="text-right" style="width: 140px;">Credit (CR)</th>
                                <th class="text-right" style="width: 150px;">Balance</th>
                                <th class="text-center" style="width: 90px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $curBal = $dp->opening_balance; @endphp
                            @if($startDate)
                            <tr style="background: var(--erp-green-light);">
                                <td class="font-weight-bold">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                                <td class="text-center"><span class="badge" style="background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); border: 1px solid var(--erp-yellow-badge-border); font-size: var(--erp-font-xs);">OPENING</span></td>
                                <td class="text-muted">-</td>
                                <td class="text-muted">-</td>
                                <td class="font-weight-bold" style="color: var(--erp-text-heading);">Opening Balance Brought Forward</td>
                                <td class="text-right text-muted">-</td>
                                <td class="text-right text-muted">-</td>
                                <td class="text-right font-weight-bold">
                                    ₹ {{ number_format(abs($dp->opening_balance), 2) }}
                                    <small class="text-muted">{{ $dp->opening_balance >= 0 ? 'CR' : 'DR' }}</small>
                                </td>
                                <td></td>
                            </tr>
                            @endif
                            @forelse($dp->transactions as $tx)
                                @php $curBal = $tx->running_balance ?? $curBal; @endphp
                                <tr>
                                    <td class="font-weight-bold" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">{{ $tx->type }}</span>
                                    </td>
                                    <td class="font-weight-bold text-muted small">{{ $tx->ref }}</td>
                                    <td class="font-weight-bold text-dark small">{{ $tx->bill_no ?? '-' }}</td>
                                    <td>{{ $tx->description }}</td>
                                    <td class="text-right font-weight-bold text-danger">
                                        {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                        {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        ₹ {{ number_format(abs($curBal), 2) }}
                                        <small class="text-muted">{{ $curBal >= 0 ? 'CR' : 'DR' }}</small>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if(!empty($tx->slip_url))
                                            <button type="button" class="erp-action-btn erp-btn-view view-slip-trigger mr-1"
                                                data-slip-url="{{ $tx->slip_url }}"
                                                data-ref="{{ $tx->ref ?? '-' }}"
                                                data-date="{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}"
                                                data-type="{{ $tx->type ?? '-' }}"
                                                data-desc="{{ $tx->description ?? '-' }}"
                                                data-amount="₹ {{ number_format($tx->credit > 0 ? $tx->credit : $tx->debit, 2) }}"
                                                title="View Slip Document">
                                                <i class="fas fa-file-invoice"></i>
                                            </button>
                                        @endif
                                        @if(isset($tx->view_url) && $tx->view_url !== '#')
                                            <a href="{{ $tx->view_url }}" class="erp-action-btn erp-btn-view" title="View Details" target="_blank">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="text-center py-3 text-muted">No transactions recorded for this party in selected period.</td></tr>
                            @endforelse
                        </tbody>
                        @if(!$dp->transactions->isEmpty())
                        <tfoot>
                            <tr class="erp-table-grand-total">
                                <td colspan="5" class="text-right font-weight-bold">PARTY TOTALS:</td>
                                <td class="text-right font-weight-bold text-danger">₹ {{ number_format($dp->period_debit, 2) }}</td>
                                <td class="text-right font-weight-bold grand-total-val">₹ {{ number_format($dp->period_credit, 2) }}</td>
                                <td class="text-right font-weight-bold">₹ {{ number_format(abs($dp->closing_balance), 2) }} <small>{{ $dp->closing_balance >= 0 ? 'CR' : 'DR' }}</small></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @empty
            <div class="erp-card p-4 text-center text-muted">No parties found matching the selected filters.</div>
        @endforelse
    @else
        <!-- View Mode: Mix (Consolidated) - DEFAULT -->
        <div class="erp-card">
            <div class="erp-card-header d-flex justify-content-between align-items-center">
                <div class="erp-card-title">
                    <i class="fas fa-stream text-primary mr-1"></i>
                    Consolidated Transaction Stream
                    <small class="text-muted font-weight-normal ml-2">({{ $allMixTransactions->count() }} transactions across {{ $filteredParties->count() }} parties)</small>
                </div>
                <div class="d-flex align-items-center">
                    <span class="mr-3 font-weight-bold" style="font-size: var(--erp-font-sm);">Opening: ₹ {{ number_format(abs($totalOpeningBalance), 2) }} <small class="text-muted">{{ $totalOpeningBalance >= 0 ? 'CR' : 'DR' }}</small></span>
                    <span class="font-weight-bold" style="font-size: var(--erp-font-sm); color: var(--erp-green-primary);">Closing: ₹ {{ number_format(abs($totalClosingBalance), 2) }} <small>{{ $totalClosingBalance >= 0 ? 'CR' : 'DR' }}</small></span>
                </div>
            </div>
            <div class="erp-card-body p-2 table-responsive">
                <table class="erp-table table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 95px;">Date</th>
                            <th style="width: 170px;">Party Name</th>
                            <th style="width: 105px;" class="text-center">Type</th>
                            <th style="width: 125px;">Reference</th>
                            <th style="width: 110px;">Bill No.</th>
                            <th>Particulars / Description</th>
                            <th class="text-right" style="width: 130px;">Debit (DR)</th>
                            <th class="text-right" style="width: 130px;">Credit (CR)</th>
                            <th class="text-right" style="width: 140px;">Balance</th>
                            <th class="text-center" style="width: 85px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentBal = $totalOpeningBalance; @endphp
                        @if($startDate)
                        <tr style="background: var(--erp-green-light);">
                            <td class="font-weight-bold">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                            <td class="font-weight-bold text-muted">All Filtered Parties</td>
                            <td class="text-center"><span class="badge" style="background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); border: 1px solid var(--erp-yellow-badge-border); font-size: var(--erp-font-xs);">OPENING</span></td>
                            <td class="text-muted">-</td>
                            <td class="text-muted">-</td>
                            <td class="font-weight-bold" style="color: var(--erp-text-heading);">Opening Balance Brought Forward</td>
                            <td class="text-right text-muted">-</td>
                            <td class="text-right text-muted">-</td>
                            <td class="text-right font-weight-bold">
                                ₹ {{ number_format(abs($totalOpeningBalance), 2) }}
                                <small class="text-muted">{{ $totalOpeningBalance >= 0 ? 'CR' : 'DR' }}</small>
                            </td>
                            <td></td>
                        </tr>
                        @endif

                        @forelse($allMixTransactions as $tx)
                            @php $currentBal = $tx->running_balance ?? $currentBal; @endphp
                            <tr>
                                <td class="font-weight-bold" style="white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}
                                </td>
                                <td>
                                    <span class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $tx->party_name ?? '-' }}</span>
                                    <span class="badge ml-1" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: 10px;">
                                        {{ strtoupper($tx->party_type ?? '') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-muted small">
                                    {{ $tx->ref }}
                                </td>
                                <td class="font-weight-bold text-dark small">
                                    {{ $tx->bill_no ?? '-' }}
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
                                    ₹ {{ number_format(abs($currentBal), 2) }}
                                    <small class="font-weight-bold" style="color: var(--erp-text-heading);">
                                        {{ $currentBal >= 0 ? 'CR' : 'DR' }}
                                    </small>
                                </td>
                                <td class="text-center text-nowrap">
                                    @if(!empty($tx->slip_url))
                                        <button type="button" class="erp-action-btn erp-btn-view view-slip-trigger mr-1"
                                            data-slip-url="{{ $tx->slip_url }}"
                                            data-ref="{{ $tx->ref ?? '-' }}"
                                            data-date="{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}"
                                            data-type="{{ $tx->type ?? '-' }}"
                                            data-desc="{{ $tx->description ?? '-' }}"
                                            data-amount="₹ {{ number_format($tx->credit > 0 ? $tx->credit : $tx->debit, 2) }}"
                                            title="View Slip Document">
                                            <i class="fas fa-file-invoice"></i>
                                        </button>
                                    @endif
                                    @if(isset($tx->view_url) && $tx->view_url !== '#')
                                        <a href="{{ $tx->view_url }}" class="erp-action-btn erp-btn-view" title="View Details" target="_blank">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    No transactions recorded for the filtered parties in selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr class="erp-table-grand-total">
                            <td colspan="6" class="text-right font-weight-bold">GRAND TOTALS:</td>
                            <td class="text-right font-weight-bold text-danger">₹ {{ number_format($totalPeriodDebit, 2) }}</td>
                            <td class="text-right font-weight-bold grand-total-val">₹ {{ number_format($totalPeriodCredit, 2) }}</td>
                            <td class="text-right font-weight-bold">
                                ₹ {{ number_format(abs($totalClosingBalance), 2) }}
                                <small>{{ $totalClosingBalance >= 0 ? 'CR' : 'DR' }}</small>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @endif

    <!-- Slip Document Preview Modal -->
    <div class="modal fade" id="slipPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 820px;">
            <div class="modal-content shadow border-0" style="border-radius: 8px; overflow: hidden;">
                <div class="modal-header text-white py-2 px-3" style="background: var(--erp-header-bg);">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-receipt mr-2" style="color: var(--erp-gold-badge);"></i>
                        <div>
                            <h6 class="modal-title font-weight-bold mb-0 text-white" style="font-size: var(--erp-font-base);">Attached Slip Document</h6>
                            <small class="text-white-50" id="modalSlipRef">-</small>
                        </div>
                    </div>
                    <button type="button" class="close text-white opacity-75" data-dismiss="modal" aria-label="Close" style="outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" style="background: #f8fafc; min-height: 420px; position: relative;">
                    <!-- Metadata strip -->
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 bg-white border-bottom" style="font-size: var(--erp-font-sm);">
                        <div>
                            <span class="badge mr-2" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border);" id="modalSlipType">-</span>
                            <span class="text-muted" id="modalSlipDate">-</span>
                        </div>
                        <div>
                            <span class="text-muted mr-1">Amount:</span>
                            <span class="font-weight-bold text-primary" id="modalSlipAmount">₹ 0.00</span>
                        </div>
                    </div>
                    <div class="px-3 py-1 bg-light border-bottom text-muted" style="font-size: var(--erp-font-xs);" id="modalSlipDesc">-</div>

                    <!-- Viewer container -->
                    <div id="slipViewerContainer" class="d-flex align-items-center justify-content-center p-3" style="min-height: 380px; max-height: 600px; overflow: auto;">
                        <div id="slipLoadingIndicator" class="text-center py-5">
                            <i class="fas fa-spinner fa-spin fa-2x text-primary mb-2"></i>
                            <div class="text-muted small">Loading slip document...</div>
                        </div>

                        <img id="slipImagePreview" src="" alt="Payment Slip" class="img-fluid rounded shadow-sm" style="display: none; max-height: 520px; transition: transform 0.2s ease;">

                        <iframe id="slipPdfPreview" src="" style="width: 100%; height: 500px; border: none; display: none;" class="rounded shadow-sm"></iframe>

                        <div id="slipErrorFallback" class="text-center py-4 px-3" style="display: none;">
                            <i class="fas fa-file-invoice text-muted fa-4x mb-3"></i>
                            <h6 class="font-weight-bold text-dark">Document Preview</h6>
                            <p class="text-muted small mb-3">Direct inline preview is not supported for this file type or the file is hosted externally.</p>
                            <a id="slipFallbackDirectBtn" href="#" target="_blank" class="btn-erp btn-erp-primary btn-sm px-3 mr-2">
                                <i class="fas fa-external-link-alt mr-1"></i> Open Document Directly
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-white d-flex justify-content-between py-2 px-3">
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="slipRotateBtn" title="Rotate Image 90°">
                            <i class="fas fa-undo-alt mr-1"></i> Rotate
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary ml-1" id="slipZoomBtn" title="Reset / Toggle Zoom">
                            <i class="fas fa-search-plus mr-1"></i> Zoom
                        </button>
                    </div>
                    <div>
                        <a id="slipOpenTabBtn" href="#" target="_blank" class="btn btn-sm btn-outline-primary mr-1">
                            <i class="fas fa-external-link-alt mr-1"></i> Open Full View
                        </a>
                        <a id="slipDownloadBtn" href="#" download class="btn-erp btn-erp-primary btn-sm mr-2">
                            <i class="fas fa-download mr-1"></i> Download
                        </a>
                        <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Ensure all Select2 elements are initialized cleanly with bootstrap4 theme
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

        // Auto-submit when Master Type, Party Selection, or View Mode change
        $('select[name="type_id"]').on('change', function() {
            var form = this.form || $(this).closest('form')[0];
            if (form) {
                if (form.selected_party) {
                    $(form.selected_party).val('');
                }
                form.submit();
            }
        });

        $('select[name="selected_party"]').on('change', function() {
            var form = this.form || $(this).closest('form')[0];
            if (form) {
                form.submit();
            }
        });

        $('select[name="view_mode"]').on('change', function() {
            var form = this.form || $(this).closest('form')[0];
            if (form) {
                form.submit();
            }
        });

        let currentRotation = 0;
        let isZoomed = false;

        $(document).on('click', '.view-slip-trigger', function(e) {
            e.preventDefault();
            const btn = $(this);
            const slipUrl = btn.data('slip-url');
            const ref = btn.data('ref') || '-';
            const date = btn.data('date') || '';
            const type = btn.data('type') || '';
            const desc = btn.data('desc') || '';
            const amount = btn.data('amount') || '';

            $('#modalSlipRef').text(ref);
            $('#modalSlipDate').text(date ? 'Date: ' + date : '');
            $('#modalSlipType').text(type);
            $('#modalSlipAmount').text(amount);
            $('#modalSlipDesc').text(desc);

            $('#slipOpenTabBtn').attr('href', slipUrl);
            $('#slipDownloadBtn').attr('href', slipUrl);
            $('#slipFallbackDirectBtn').attr('href', slipUrl);

            // Reset transform & state
            currentRotation = 0;
            isZoomed = false;
            const img = $('#slipImagePreview');
            const pdf = $('#slipPdfPreview');
            const loader = $('#slipLoadingIndicator');
            const fallback = $('#slipErrorFallback');

            img.css('transform', 'none').hide();
            pdf.hide().attr('src', '');
            fallback.hide();
            loader.show();

            const isPdf = slipUrl.toLowerCase().endsWith('.pdf') || slipUrl.toLowerCase().includes('.pdf?');

            if (isPdf) {
                $('#slipRotateBtn, #slipZoomBtn').hide();
                loader.hide();
                pdf.attr('src', slipUrl).show();
            } else {
                $('#slipRotateBtn, #slipZoomBtn').show();
                img.off('load error').on('load', function() {
                    loader.hide();
                    img.show();
                }).on('error', function() {
                    loader.hide();
                    img.hide();
                    fallback.show();
                });
                img.attr('src', slipUrl);
            }

            $('#slipPreviewModal').modal('show');
        });

        $('#slipRotateBtn').on('click', function() {
            currentRotation = (currentRotation + 90) % 360;
            applyTransform();
        });

        $('#slipZoomBtn').on('click', function() {
            isZoomed = !isZoomed;
            applyTransform();
        });

        function applyTransform() {
            const scale = isZoomed ? 'scale(1.5)' : 'scale(1)';
            const rotate = 'rotate(' + currentRotation + 'deg)';
            $('#slipImagePreview').css('transform', rotate + ' ' + scale);
        }
    });
</script>
@endsection
