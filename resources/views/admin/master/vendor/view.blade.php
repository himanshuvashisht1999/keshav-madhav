@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="erp-header-title d-flex align-items-center">
            <i class="fas fa-truck text-primary mr-2" style="font-size: 1.25rem;"></i>
            <span>Vendor Details: <strong class="text-dark">{{ $data->name }}</strong></span>
        </div>
        <div class="erp-header-actions">
            @if(Route::has('admin.ledger.party.show'))
                <a href="{{ route('admin.ledger.party.show', ['type' => 'vendor', 'id' => $data->id]) }}" class="btn-erp btn-erp-outline mr-1" title="View Full Ledger">
                    <i class="fas fa-book mr-1 text-primary"></i> View Ledger
                </a>
            @endif
            <a href="{{ route('admin.master.vendor.edit', ['id' => $data->id]) }}" class="btn-erp btn-erp-primary mr-1" title="Edit Vendor">
                <i class="fas fa-edit mr-1"></i> Edit Vendor
            </a>
            <a href="{{ route('admin.master.vendor.index') }}" class="btn-erp btn-erp-outline" title="Back to Vendor List">
                <i class="fas fa-arrow-left mr-1"></i> Back to List
            </a>
        </div>
    </div>

    <!-- Summary Metrics Strip -->
    <div class="row mb-2">
        <!-- Current Balance -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'var(--erp-green-light)' : '#fee2e2' }}; color: {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-wallet"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Current Balance</div>
                        <div class="font-weight-bold" style="font-size: 1.2rem; color: {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'var(--erp-green-primary)' : '#dc2626' }}; line-height: 1.2;">
                            ₹ {{ number_format(abs($data->calculated_closing_balance ?? $data->balance ?? 0), 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge px-2 py-1" style="{{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border);' : 'background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5;' }} font-size: var(--erp-font-xs); font-weight: 700;">
                    {{ ($data->calculated_closing_balance ?? $data->balance ?? 0) >= 0 ? 'Credit' : 'Debit' }}
                </span>
            </div>
        </div>

        <!-- Opening Balance -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-balance-scale"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Opening Balance</div>
                        <div class="font-weight-bold" style="font-size: 1.2rem; color: #1e293b; line-height: 1.2;">
                            @php
                                $opAmount = $data->currentOpeningBalance ? $data->currentOpeningBalance->amount : abs($data->calculated_opening_balance ?? 0);
                                $opType = $data->currentOpeningBalance ? $data->currentOpeningBalance->balance_type : (($data->calculated_opening_balance ?? 0) >= 0 ? 'Credit' : 'Debit');
                            @endphp
                            ₹ {{ number_format($opAmount, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge badge-info px-2 py-1" style="font-size: var(--erp-font-xs); font-weight: 700;">
                    {{ $opType }}
                </span>
            </div>
        </div>

        <!-- Fabrics Supplied -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: #f5f3ff; color: #7c3aed; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-scroll"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Fabrics Supplied</div>
                        <div class="font-weight-bold" style="font-size: 1.2rem; color: #1e293b; line-height: 1.2;">
                            {{ count($data->fabrics ?? []) }} Fabrics
                        </div>
                    </div>
                </div>
                <span class="badge badge-secondary px-2 py-1" style="font-size: var(--erp-font-xs); font-weight: 700;">
                    Catalog
                </span>
            </div>
        </div>

        <!-- Status -->
        <div class="col-lg-3 col-md-6 mb-1">
            <div class="erp-card py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 42px; height: 42px; border-radius: 8px; background: {{ $data->status == 1 ? 'var(--erp-green-light)' : '#f1f5f9' }}; color: {{ $data->status == 1 ? 'var(--erp-green-primary)' : '#64748b' }}; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-xs); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Account Status</div>
                        <div class="font-weight-bold" style="font-size: 1.1rem; color: #1e293b; line-height: 1.2;">
                            {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                        </div>
                    </div>
                </div>
                <span class="badge {{ $data->status == 1 ? 'badge-success' : 'badge-danger' }} px-2 py-1" style="font-size: var(--erp-font-xs); font-weight: 700;">
                    {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Main Content Layout -->
    <div class="row">
        <!-- Left Column: Vendor Profile & Contact -->
        <div class="col-lg-4 col-md-5 mb-3">
            <div class="erp-card p-3 mb-3">
                <div class="text-center pb-3 border-bottom mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center mb-2" style="width: 76px; height: 76px; border-radius: 50%; background: #eff6ff; color: #2563eb; font-size: 2rem; border: 2px solid #bfdbfe; box-shadow: 0 2px 8px rgba(0,0,0,0.05);">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h5 class="font-weight-bold mb-1 text-dark">{{ $data->name }}</h5>
                    <div>
                        <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                            Vendor ID: #{{ $data->id }}
                        </span>
                        <span class="badge {{ $data->status == 1 ? 'badge-success' : 'badge-danger' }} px-2 py-1" style="font-size: 11px;">
                            {{ $data->status == 1 ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                </div>

                <ul class="list-group list-group-flush mb-0" style="font-size: var(--erp-font-sm);">
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-phone-alt mr-1 text-primary"></i> Phone</span>
                        <span class="font-weight-bold text-dark">
                            @if($data->phone)
                                <a href="tel:{{ $data->phone }}" class="text-dark">{{ $data->phone }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </li>

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-envelope mr-1 text-primary"></i> Email</span>
                        <span class="font-weight-bold text-dark text-truncate" style="max-width: 200px;">
                            @if($data->email)
                                <a href="mailto:{{ $data->email }}" class="text-primary">{{ $data->email }}</a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </span>
                    </li>

                    @if($data->purchaseAgent)
                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-user-tie mr-1 text-primary"></i> Purchase Agent</span>
                        <span class="font-weight-bold text-dark">{{ $data->purchaseAgent->name }}</span>
                    </li>
                    @endif

                    <li class="list-group-item d-flex justify-content-between align-items-center px-0 py-2">
                        <span class="text-muted"><i class="fas fa-calendar-alt mr-1 text-primary"></i> Created On</span>
                        <span class="text-muted">
                            {{ $data->created_at ? $data->created_at->format('d M, Y') : '-' }}
                        </span>
                    </li>

                    <li class="list-group-item px-0 pt-2 pb-2">
                        <span class="text-muted d-block mb-1"><i class="fas fa-boxes mr-1 text-primary"></i> Supplied Items / Materials</span>
                        <div>
                            @php
                                $items = !empty($data->items) ? @unserialize($data->items) : [];
                            @endphp
                            @if(is_array($items) && count($items) > 0)
                                @foreach($items as $itemId)
                                    @php
                                        $itemObj = \App\Models\Item::find($itemId);
                                    @endphp
                                    @if($itemObj)
                                        <span class="badge badge-light border text-dark mr-1 mb-1 px-2 py-1">
                                            <i class="fas fa-tag mr-1 text-success"></i>{{ $itemObj->name }}
                                        </span>
                                    @endif
                                @endforeach
                            @else
                                <span class="text-muted" style="font-size: 12px;">No specific items assigned.</span>
                            @endif
                        </div>
                    </li>

                    <li class="list-group-item px-0 pt-2 pb-0">
                        <span class="text-muted d-block mb-1"><i class="fas fa-map-marker-alt mr-1 text-danger"></i> Address</span>
                        <div class="p-2 bg-light rounded text-dark" style="font-size: 12px; line-height: 1.4;">
                            {{ $data->address ?: 'No address specified.' }}
                        </div>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Fabrics & Recent Activity -->
        <div class="col-lg-8 col-md-7">
            <!-- Fabrics List -->
            <div class="erp-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 28px; height: 28px; border-radius: 6px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <i class="fas fa-scroll"></i>
                        </div>
                        <h6 class="font-weight-bold mb-0 text-dark">Associated Fabrics</h6>
                    </div>
                    <span class="badge badge-light border text-muted px-2 py-1" style="font-size: 11px;">
                        {{ count($data->fabrics ?? []) }} Fabrics
                    </span>
                </div>

                <div class="table-responsive" style="max-height: 240px; overflow-y: auto;">
                    <table class="table table-bordered table-sm table-hover mb-0" style="font-size: var(--erp-font-sm);">
                        <thead class="thead-light" style="position: sticky; top: 0; z-index: 1;">
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Fabric Name</th>
                                <th>Type</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($data->fabrics ?? [] as $index => $fabric)
                                <tr>
                                    <td class="text-center text-muted">{{ $index + 1 }}</td>
                                    <td><strong>{{ $fabric->name }}</strong></td>
                                    <td><span class="badge badge-light border">{{ $fabric->type ?? 'Fabric' }}</span></td>
                                    <td class="text-center">
                                        <span class="badge {{ ($fabric->status ?? 1) == 1 ? 'badge-success' : 'badge-secondary' }} px-2 py-1">
                                            {{ ($fabric->status ?? 1) == 1 ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted p-3">No fabrics linked to this vendor.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Recent Receipts & Activity Section -->
            <div class="erp-card p-3 mb-3">
                <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                    <div class="d-flex align-items-center">
                        <div class="mr-2" style="width: 28px; height: 28px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: 13px;">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <h6 class="font-weight-bold mb-0 text-dark">Recent Inwards & Transactions</h6>
                    </div>
                    @if(Route::has('admin.ledger.party.show'))
                        <a href="{{ route('admin.ledger.party.show', ['type' => 'vendor', 'id' => $data->id]) }}" class="btn btn-xs btn-outline-primary">
                            <i class="fas fa-external-link-alt mr-1"></i> Full Ledger History
                        </a>
                    @endif
                </div>

                <ul class="nav nav-pills mb-3" id="vendor-activity-tabs" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active py-1 px-3" id="receipts-tab" data-toggle="pill" href="#tab-receipts" role="tab" style="font-size: 12px; font-weight: 600;">
                            <i class="fas fa-file-invoice mr-1"></i> Fabric Receipts ({{ count($data->recent_receipts ?? []) }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3" id="returns-tab" data-toggle="pill" href="#tab-returns" role="tab" style="font-size: 12px; font-weight: 600;">
                            <i class="fas fa-undo mr-1"></i> Fabric Returns ({{ count($data->recent_returns ?? []) }})
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link py-1 px-3" id="payments-tab" data-toggle="pill" href="#tab-payments" role="tab" style="font-size: 12px; font-weight: 600;">
                            <i class="fas fa-money-check-alt mr-1"></i> Payments ({{ count($data->recent_payments ?? []) }})
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="vendor-activity-content">
                    <!-- Tab 1: Fabric Receipts -->
                    <div class="tab-pane fade show active" id="tab-receipts" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 12px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>SKU / Bill No</th>
                                        <th>Shipment ID</th>
                                        <th class="text-right">Total Meter</th>
                                        <th class="text-right">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data->recent_receipts ?? [] as $r)
                                        <tr>
                                            <td>{{ $r->time ? \Carbon\Carbon::parse($r->time)->format('d M, Y') : ($r->created_at ? $r->created_at->format('d M, Y') : '-') }}</td>
                                            <td><strong>{{ $r->sku ?: ('#' . $r->id) }}</strong></td>
                                            <td><span class="badge badge-light border">{{ $r->shipment_id ?: '-' }}</span></td>
                                            <td class="text-right">{{ number_format($r->total_meter ?? 0, 2) }} m</td>
                                            <td class="text-right font-weight-bold text-dark">₹ {{ number_format($r->total_amount ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-3">No fabric receipts recorded for this vendor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Fabric Returns -->
                    <div class="tab-pane fade" id="tab-returns" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 12px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Return Number</th>
                                        <th>Remarks</th>
                                        <th class="text-right">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data->recent_returns ?? [] as $ret)
                                        <tr>
                                            <td>{{ $ret->date ? \Carbon\Carbon::parse($ret->date)->format('d M, Y') : ($ret->created_at ? $ret->created_at->format('d M, Y') : '-') }}</td>
                                            <td><strong class="text-danger">{{ $ret->return_number ?: ('#' . $ret->id) }}</strong></td>
                                            <td>{{ $ret->remarks ?: '-' }}</td>
                                            <td class="text-right font-weight-bold text-danger">₹ {{ number_format($ret->total_amount ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted p-3">No fabric returns recorded for this vendor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 3: Payments -->
                    <div class="tab-pane fade" id="tab-payments" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm table-hover mb-0" style="font-size: 12px;">
                                <thead class="thead-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Mode</th>
                                        <th>Reference</th>
                                        <th class="text-right">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data->recent_payments ?? [] as $pay)
                                        <tr>
                                            <td>{{ $pay->payment_date ? \Carbon\Carbon::parse($pay->payment_date)->format('d M, Y') : ($pay->created_at ? $pay->created_at->format('d M, Y') : '-') }}</td>
                                            <td>
                                                <span class="badge {{ in_array($pay->payment_type, ['received', 'credit']) ? 'badge-success' : 'badge-primary' }}">
                                                    {{ ucfirst($pay->payment_type) }}
                                                </span>
                                            </td>
                                            <td>{{ ucfirst($pay->payment_mode ?? 'Cash') }}</td>
                                            <td>{{ $pay->reference_id ?: ('#' . $pay->id) }}</td>
                                            <td class="text-right font-weight-bold">₹ {{ number_format($pay->amount ?? 0, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted p-3">No recent payments recorded for this vendor.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
