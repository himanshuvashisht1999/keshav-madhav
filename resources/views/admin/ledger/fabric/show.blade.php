@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> 
            {{ $fabric->name ?? 'Fabric Ledger' }}
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.fabric.export-pdf', ['id' => $fabric->id] + request()->query()) }}" class="btn-erp btn-erp-outline" title="Export Statement to PDF">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </a>
            <a href="{{ route('admin.ledger.fabric.export-excel', ['id' => $fabric->id] + request()->query()) }}" class="btn-erp btn-erp-primary" title="Export Statement to Excel">
                <i class="fas fa-file-excel"></i> Excel
            </a>
            <a href="{{ route('admin.ledger.fabric.index') }}" class="btn-erp btn-erp-outline" title="Back to Listing">
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
                            {{ number_format($openingBalanceAmount ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    B/F
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Period Inward</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalInwardSum ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Inward
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
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Period Outward</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            {{ number_format($totalOutwardSum ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Outward
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-warehouse"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Closing Stock</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            {{ number_format($closingBalanceAmount ?? 0, 2) }} <small style="font-size: 11px;">Mtr</small>
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Closing
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form method="GET" action="{{ route('admin.ledger.fabric.show', $fabric->id) }}" class="m-0">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                    <select name="vendor_id" class="form-control select2 erp-input">
                        <option value="">-- ALL VENDORS --</option>
                        @foreach($vendors as $v)
                            <option value="{{ $v->id }}" {{ request('vendor_id') == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-friends mr-1"></i> Customer</label>
                    <select name="customer_id" class="form-control select2 erp-input">
                        <option value="">-- ALL CUSTOMERS --</option>
                        @foreach($customers as $c)
                            <option value="{{ $c->id }}" {{ request('customer_id') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Start Date</label>
                    <input type="date" name="start_date" class="form-control erp-input" value="{{ $startDate ?? '' }}">
                </div>

                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> End Date</label>
                    <input type="date" name="end_date" class="form-control erp-input" value="{{ $endDate ?? '' }}">
                </div>

                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.ledger.fabric.show', $fabric->id) }}" class="btn-erp btn-erp-outline" title="Reset Filters">
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
                        <th style="width: 100px;">Date</th>
                        <th style="width: 90px;" class="text-center">Type</th>
                        <th style="width: 130px;">Ref / Doc No</th>
                        <th>Particulars / Source Party</th>
                        <th class="text-right" style="width: 140px;">Inward (Mtrs)</th>
                        <th class="text-right" style="width: 140px;">Outward (Mtrs)</th>
                        <th class="text-right" style="width: 150px;">Balance (Mtrs)</th>
                        <th style="width: 90px;" class="text-center">Rolls</th>
                    </tr>
                </thead>
                <tbody>
                    @if($startDate)
                    <tr style="background: var(--erp-green-light);">
                        <td class="text-center text-muted font-weight-bold"><i class="fas fa-clock"></i></td>
                        <td class="font-weight-bold">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</td>
                        <td class="text-center"><span class="badge" style="background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); border: 1px solid var(--erp-yellow-badge-border); font-size: var(--erp-font-xs);">OPENING</span></td>
                        <td class="text-muted">-</td>
                        <td class="font-weight-bold" style="color: var(--erp-text-heading);">Opening Stock Balance Brought Forward</td>
                        <td class="text-right text-muted">-</td>
                        <td class="text-right text-muted">-</td>
                        <td class="text-right font-weight-bold">
                            {{ number_format($openingBalanceAmount ?? 0, 2) }}
                        </td>
                        <td class="text-center text-muted">-</td>
                    </tr>
                    @endif

                    @forelse($transactions as $index => $tx)
                        <tr>
                            <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                            <td class="font-weight-bold" style="white-space: nowrap;">{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                            <td class="text-center">
                                @if($tx->type == 'Inward')
                                    <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs);">INWARD</span>
                                @else
                                    <span class="badge erp-badge-yellow">OUTWARD</span>
                                @endif
                            </td>
                            <td class="font-weight-bold text-muted small">
                                @if(isset($tx->link) && $tx->link)
                                    <a href="{{ $tx->link }}" target="_blank" style="color: var(--erp-green-primary); text-decoration: underline;">
                                        {{ $tx->reference ?? 'View Document' }}
                                    </a>
                                @else
                                    {{ $tx->reference ?? '-' }}
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $tx->particulars }}</span>
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                                {{ $tx->inward > 0 ? number_format($tx->inward, 2) : '-' }}
                            </td>
                            <td class="text-right font-weight-bold text-danger">
                                {{ $tx->outward > 0 ? number_format($tx->outward, 2) : '-' }}
                            </td>
                            <td class="text-right font-weight-bold" style="color: var(--erp-text-heading);">
                                {{ number_format($tx->running_balance, 2) }}
                            </td>
                            <td class="text-center">
                                @if(isset($tx->rolls) && count($tx->rolls) > 0)
                                    <button type="button" class="btn-erp btn-erp-outline btn-xs" data-toggle="collapse" data-target="#rolls-row-{{ $index }}">
                                        {{ count($tx->rolls) }} <i class="fas fa-chevron-down ml-1"></i>
                                    </button>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        @if(isset($tx->rolls) && count($tx->rolls) > 0)
                        <tr class="collapse" id="rolls-row-{{ $index }}">
                            <td colspan="9" class="p-2" style="background: var(--erp-green-light);">
                                <div class="p-2 bg-white rounded border">
                                    <strong class="d-block mb-1 small text-success"><i class="fas fa-scroll mr-1"></i> Roll Details Breakdown:</strong>
                                    <div class="d-flex flex-wrap" style="gap: 6px;">
                                        @foreach($tx->rolls as $r)
                                            <span class="badge badge-light border" style="font-size: 11px;">
                                                Roll #{{ $r->roll_no ?? $loop->iteration }}: <strong>{{ number_format($r->quantity ?? $r->meter ?? 0, 2) }} Mtr</strong>
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No transaction records found for the selected period.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if(!$transactions->isEmpty())
                <tfoot>
                    <tr class="erp-table-grand-total">
                        <td colspan="5" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">STATEMENT CLOSING TOTALS:</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($totalInwardSum ?? 0, 2) }}</td>
                        <td class="text-right font-weight-bold text-danger">{{ number_format($totalOutwardSum ?? 0, 2) }}</td>
                        <td class="text-right font-weight-bold grand-total-val">{{ number_format($closingBalanceAmount ?? 0, 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>
</div>
@endsection
