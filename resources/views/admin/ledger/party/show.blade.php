@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-file-invoice-dollar text-primary"></i> 
            {{ $party->name }} 
            <span class="badge ml-2" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">
                {{ strtoupper($type) }}
            </span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.party.download', array_merge(request()->query(), ['type' => $type, 'id' => $party->id])) }}" class="btn-erp btn-erp-outline" title="Download PDF Statement">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </a>
            <a href="{{ route('admin.ledger.party.export-excel', array_merge(request()->query(), ['type' => $type, 'id' => $party->id])) }}" class="btn-erp btn-erp-primary" title="Export Statement to Excel">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.ledger.party.index', $type === 'sales_agent' ? ['type_id' => 'sales_agent'] : []) }}" class="btn-erp btn-erp-outline" title="Back to Listing">
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
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    {{ ($openingBalAmount ?? 0) >= 0 ? 'CR' : 'DR' }}
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Period Debit (DR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹ {{ number_format($periodTotalDebit ?? $transactions->sum('debit'), 2) }}
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
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Period Credit (CR)</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            ₹ {{ number_format($periodTotalCredit ?? $transactions->sum('credit'), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    CR
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Closing Balance</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format(abs($party->balance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    {{ ($party->balance ?? 0) >= 0 ? 'CR' : 'DR' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form action="{{ route('admin.ledger.party.show', ['type' => $type, 'id' => $party->id]) }}" method="GET" class="m-0">
            <div class="row align-items-end">
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control erp-input">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control erp-input">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-tag mr-1"></i> Type</label>
                    <select name="transaction_type" class="form-control erp-input">
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
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-filter mr-1"></i> Adjustment</label>
                    <select name="adjustment_type" class="form-control erp-input">
                        <option value="">-- ALL --</option>
                        <option value="payment" {{ request('adjustment_type') === 'payment' ? 'selected' : '' }}>Payment (DR)</option>
                        <option value="receipt" {{ request('adjustment_type') === 'receipt' ? 'selected' : '' }}>Receipt (CR)</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Ref / Voucher</label>
                    <input type="text" name="ref_no" value="{{ request('ref_no') }}" placeholder="Search Ref..." class="form-control erp-input">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-paperclip mr-1"></i> Slip</label>
                    <select name="has_slip" class="form-control erp-input">
                        <option value="">-- ALL --</option>
                        <option value="yes" {{ request('has_slip') === 'yes' ? 'selected' : '' }}>With Slip</option>
                        <option value="no" {{ request('has_slip') === 'no' ? 'selected' : '' }}>Without Slip</option>
                    </select>
                </div>

                @if($type === 'sales_agent' && isset($shops))
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-store mr-1"></i> Customer / Shop</label>
                        <select name="customer_id" class="form-control erp-input" onchange="this.form.submit()">
                            <option value="">All Customers (Mix)</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('customer_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if(!request('customer_id'))
                    <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-columns mr-1"></i> View Mode</label>
                        <select name="view_mode" class="form-control erp-input" onchange="this.form.submit()">
                            <option value="mix" {{ request('view_mode', 'mix') === 'mix' ? 'selected' : '' }}>Mix (Consolidated)</option>
                            <option value="party_wise" {{ request('view_mode') === 'party_wise' ? 'selected' : '' }}>Party-wise (Grouped)</option>
                        </select>
                    </div>
                    @endif
                @endif

                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.ledger.party.show', ['type' => $type, 'id' => $party->id]) }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    @if(isset($viewMode) && $viewMode === 'party_wise' && isset($groupedLedgers))
        @forelse($groupedLedgers as $ledger)
            <div class="erp-card mb-3">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <i class="fas fa-store"></i> {{ $ledger->shop->name }}
                        <small class="text-muted font-weight-normal ml-2">({{ $ledger->shop->phone ?? '-' }})</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="mr-3 font-weight-bold" style="font-size: var(--erp-font-sm);">Opening: ₹ {{ number_format(abs($ledger->opening_balance), 2) }} <small class="text-muted">{{ $ledger->opening_balance >= 0 ? 'CR' : 'DR' }}</small></span>
                        <span class="font-weight-bold text-success" style="font-size: var(--erp-font-sm);">Closing: ₹ {{ number_format(abs($ledger->closing_balance), 2) }} <small>{{ $ledger->closing_balance >= 0 ? 'CR' : 'DR' }}</small></span>
                    </div>
                </div>
                <div class="erp-card-body p-2 table-responsive">
                    <table class="erp-table table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th style="width: 100px;">Date</th>
                                <th style="width: 100px;" class="text-center">Type</th>
                                <th style="width: 120px;">Reference</th>
                                <th>Particulars</th>
                                <th class="text-right" style="width: 140px;">Debit (DR)</th>
                                <th class="text-right" style="width: 140px;">Credit (CR)</th>
                                <th class="text-right" style="width: 150px;">Balance</th>
                                <th class="text-center" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $currentBalance = $ledger->opening_balance; @endphp
                            @forelse($ledger->transactions as $tx)
                                @php $currentBalance = $tx->running_balance; @endphp
                                <tr>
                                    <td class="font-weight-bold">{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                                    <td class="text-center">
                                        <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">{{ $tx->type }}</span>
                                    </td>
                                    <td class="font-weight-bold text-muted small">{{ $tx->ref }}</td>
                                    <td>{{ $tx->description }}</td>
                                    <td class="text-right font-weight-bold text-danger">
                                        {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                                    </td>
                                    <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                        {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        ₹ {{ number_format(abs($currentBalance), 2) }}
                                        <small class="text-muted">{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                    </td>
                                    <td class="text-center text-nowrap">
                                        @if(!empty($tx->slip_url))
                                            <button type="button" class="erp-action-btn erp-btn-view view-slip-trigger"
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
                                <tr><td colspan="8" class="text-center py-4 text-muted">No transactions recorded for this customer in selected period.</td></tr>
                            @endforelse
                        </tbody>
                        @if(!$ledger->transactions->isEmpty())
                        <tfoot>
                            <tr class="erp-table-grand-total">
                                <td colspan="4" class="text-right font-weight-bold">CUSTOMER TOTALS:</td>
                                <td class="text-right font-weight-bold text-danger">₹ {{ number_format($ledger->transactions->sum('debit'), 2) }}</td>
                                <td class="text-right font-weight-bold grand-total-val">₹ {{ number_format($ledger->transactions->sum('credit'), 2) }}</td>
                                <td class="text-right font-weight-bold">₹ {{ number_format(abs($currentBalance), 2) }} <small>{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small></td>
                                <td></td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>
        @empty
            <div class="erp-card p-4 text-center text-muted">No customers found for this agent.</div>
        @endforelse
    @else
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table class="erp-table table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th style="width: 100px;">Date</th>
                            <th style="width: 110px;" class="text-center">Type</th>
                            <th style="width: 130px;">Reference</th>
                            <th>Particulars / Description</th>
                            <th class="text-right" style="width: 140px;">Debit (DR)</th>
                            <th class="text-right" style="width: 140px;">Credit (CR)</th>
                            <th class="text-right" style="width: 150px;">Balance</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentBalance = $openingBalAmount; @endphp
                        @if($startDate)
                        <tr style="background: var(--erp-green-light);">
                            <td class="font-weight-bold">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                            <td class="text-center"><span class="badge" style="background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); border: 1px solid var(--erp-yellow-badge-border); font-size: var(--erp-font-xs);">OPENING</span></td>
                            <td class="text-muted">-</td>
                            <td class="font-weight-bold" style="color: var(--erp-text-heading);">Opening Balance Brought Forward</td>
                            <td class="text-right text-muted">-</td>
                            <td class="text-right text-muted">-</td>
                            <td class="text-right font-weight-bold">
                                ₹ {{ number_format(abs($openingBalAmount), 2) }}
                                <small class="text-muted">{{ $openingBalAmount >= 0 ? 'CR' : 'DR' }}</small>
                            </td>
                            <td></td>
                        </tr>
                        @endif

                        @forelse($transactions as $tx)
                            @php $currentBalance = $tx->running_balance; @endphp
                            <tr>
                                <td class="font-weight-bold" style="white-space: nowrap;">
                                    {{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}
                                </td>
                                <td class="text-center">
                                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">
                                        {{ $tx->type }}
                                    </span>
                                </td>
                                <td class="font-weight-bold text-muted small">
                                    {{ $tx->ref }}
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
                                    <small class="font-weight-bold" style="color: var(--erp-text-heading);">
                                        {{ $currentBalance >= 0 ? 'CR' : 'DR' }}
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
                                    @if(empty($tx->slip_url) && (!isset($tx->view_url) || $tx->view_url === '#'))
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                    <p class="font-weight-bold mb-0">No transaction records found for the selected period.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if(!$transactions->isEmpty())
                    <tfoot>
                        <tr class="erp-table-grand-total">
                            <td colspan="4" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">STATEMENT PERIOD TOTALS:</td>
                            <td class="text-right font-weight-bold text-danger">
                                ₹ {{ number_format($transactions->sum('debit'), 2) }}
                            </td>
                            <td class="text-right font-weight-bold grand-total-val">
                                ₹ {{ number_format($transactions->sum('credit'), 2) }}
                            </td>
                            <td class="text-right font-weight-bold grand-total-val">
                                ₹ {{ number_format(abs($currentBalance), 2) }}
                                <small>{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                    @endif
                </table>
            </div>
        </div>
    @endif

    {{-- SLIP PREVIEW MODAL --}}
    <div class="modal fade" id="slipPreviewModal" tabindex="-1" role="dialog" aria-labelledby="slipPreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document" style="max-width: 900px;">
            <div class="modal-content border-0" style="border-radius: 6px; overflow: hidden; border-top: 3.5px solid var(--erp-yellow-bright) !important;">
                <div class="modal-header" style="background: var(--erp-bg-header); border-bottom: 1px solid var(--erp-border);">
                    <div>
                        <h5 class="modal-title font-weight-bold mb-1" id="slipPreviewModalLabel" style="color: var(--erp-text-heading); font-size: var(--erp-font-md);">
                            <i class="fas fa-file-invoice text-primary mr-1"></i> Slip Document Preview
                        </h5>
                        <div class="small">
                            <span id="modalSlipRef" class="badge badge-light border font-weight-bold mr-2"></span>
                            <span id="modalSlipType" class="badge erp-badge-yellow mr-2"></span>
                            <span id="modalSlipDate" class="text-muted mr-2"></span>
                            <span id="modalSlipAmount" class="badge font-weight-bold" style="background: var(--erp-green-light); color: var(--erp-green-primary);"></span>
                        </div>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-3 bg-light text-center" style="min-height: 400px; display: flex; align-items: center; justify-content: center; flex-direction: column;">
                    <div id="modalSlipDescriptionBox" class="w-100 text-left mb-2 px-2 py-1 bg-white rounded border small text-muted font-italic">
                        <i class="fas fa-info-circle mr-1 text-primary"></i> <span id="modalSlipDesc"></span>
                    </div>

                    <div id="slipMediaContainer" class="w-100 position-relative" style="background: #ffffff; border-radius: 4px; border: 1px dashed var(--erp-border); padding: 15px; min-height: 450px; display: flex; align-items: center; justify-content: center; overflow: hidden;">
                        <img id="slipImagePreview" src="" alt="Slip Document" class="img-fluid rounded shadow-sm" style="max-height: 65vh; max-width: 100%; object-fit: contain; transition: transform 0.3s ease; display: none;">
                        <iframe id="slipPdfPreview" src="" style="width: 100%; height: 65vh; border: none; border-radius: 4px; display: none;"></iframe>
                        <div id="slipLoadingIndicator" class="text-muted text-center py-5">
                            <div class="spinner-border text-primary mb-2" role="status"></div>
                            <div>Loading Slip Document...</div>
                        </div>
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