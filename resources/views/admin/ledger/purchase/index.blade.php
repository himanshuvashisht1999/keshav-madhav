@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-truck-loading text-primary"></i> Purchase Ledger
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.ledger.purchase.export-pdf', request()->all()) }}" class="btn-erp btn-erp-outline" title="Export to PDF">
                <i class="fas fa-file-pdf text-danger"></i> Export PDF
            </a>
            <a href="{{ route('admin.ledger.purchase.export-excel', request()->all()) }}" class="btn-erp btn-erp-primary" title="Export to Excel">
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
                        <i class="fas fa-receipt"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Records</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($totalPurchasesCount ?? 0) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Records
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-truck-loading"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Gross Purchases</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #0284c7; line-height: 1.2;">
                            ₹ {{ number_format($totalPurchaseGrandTotal ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: #e0f2fe; color: #0284c7; border: 1px solid #bae6fd; font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Purchases
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between" style="border-left: 4px solid #ef4444 !important;">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: #fee2e2; color: #dc2626; display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-undo"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Purchase Return</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: #dc2626; line-height: 1.2;">
                            ₹ {{ number_format($totalReturnGrandTotal ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: #fee2e2; color: #dc2626; border: 1px solid #fca5a5; padding: 4px 9px;">
                    Return
                </span>
            </div>
        </div>

        <div class="col-md-3 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-chart-line"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Net Purchase Total</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            ₹ {{ number_format($netGrandTotal ?? $totalGrandTotal ?? 0, 2) }}
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Net Overall
                </span>
            </div>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form action="{{ route('admin.ledger.purchase.index') }}" method="GET" class="m-0">
            <div class="row align-items-end">
                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-layer-group text-muted mr-1"></i> Report Type</label>
                    <select name="report_type" class="form-control erp-filter-select font-weight-bold">
                        <option value="both" {{ request('report_type', 'both') === 'both' ? 'selected' : '' }}>Both (Purchase + Return)</option>
                        <option value="purchase" {{ request('report_type') === 'purchase' ? 'selected' : '' }}>Purchase Only</option>
                        <option value="return" {{ request('report_type') === 'return' ? 'selected' : '' }}>Purchase Return Only</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-industry text-muted mr-1"></i> Vendor</label>
                    <select name="vendor_id" class="form-control select2 erp-filter-select">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tie text-muted mr-1"></i> Purchase Agent</label>
                    <select name="purchase_agent_id" class="form-control select2 erp-filter-select">
                        <option value="">All Agents</option>
                        @foreach($purchaseAgents as $agent)
                            <option value="{{ $agent->id }}" {{ request('purchase_agent_id') == $agent->id ? 'selected' : '' }}>
                                {{ $agent->name }}
                            </option>
                        @endforeach
                        <option value="direct" {{ request('purchase_agent_id') == 'direct' ? 'selected' : '' }}>Direct (No Agent)</option>
                    </select>
                </div>

                <div class="col-md-1 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-hashtag text-muted mr-1"></i> Bill / Ref No</label>
                    <input type="text" name="bill_no" class="form-control erp-filter-input" placeholder="Bill No..." value="{{ request('bill_no') }}">
                </div>

                <div class="col-md-1 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar text-muted mr-1"></i> From</label>
                    <input type="date" name="from_date" class="form-control erp-filter-input" value="{{ request('from_date') }}">
                </div>

                <div class="col-md-1 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar text-muted mr-1"></i> To</label>
                    <input type="date" name="to_date" class="form-control erp-filter-input" value="{{ request('to_date') }}">
                </div>

                <div class="col-md-1 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-sort-amount-down text-muted mr-1"></i> Date Sort</label>
                    <select name="sort_date" class="form-control erp-filter-select font-weight-bold">
                        <option value="desc" {{ request('sort_date', 'desc') === 'desc' ? 'selected' : '' }}>DESC</option>
                        <option value="asc" {{ request('sort_date') === 'asc' ? 'selected' : '' }}>ASC</option>
                    </select>
                </div>

                <div class="col-md-2 col-sm-12 mb-1 d-flex">
                    <button type="submit" class="btn-erp btn-erp-primary mr-2" style="height: 31px;">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.ledger.purchase.index') }}" class="btn-erp btn-erp-outline" style="height: 31px;" title="Reset filters">
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
                        <th style="width: 90px;" class="text-center">Type</th>
                        <th style="width: 105px;">Date</th>
                        <th style="width: 140px;">Bill / Invoice No</th>
                        <th>Vendor Name</th>
                        <th style="width: 150px;">Purchase Agent</th>
                        <th style="width: 130px;" class="text-center">Receipt Type</th>
                        <th class="text-right" style="width: 150px;">Amount (₹)</th>
                        <th class="text-center" style="width: 80px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rowIdx = 0; @endphp
                    @forelse($purchases as $item)
                        @php 
                            $rowIdx++; 
                            $isReturn = ($item->entry_type ?? 'purchase') === 'return';
                        @endphp
                        <tr style="{{ $isReturn ? 'background-color: #fff5f5;' : '' }}">
                            <td class="text-center text-muted font-weight-bold">{{ $rowIdx }}</td>
                            <td class="text-center">
                                @if($isReturn)
                                    <span class="badge" style="background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                                        <i class="fas fa-undo mr-1"></i> Return
                                    </span>
                                @else
                                    <span class="badge" style="background: #dcfce7; color: #166534; border: 1px solid #86efac; font-size: 10px; font-weight: 700; padding: 2px 6px;">
                                        <i class="fas fa-check mr-1"></i> Purchase
                                    </span>
                                @endif
                            </td>
                            <td class="font-weight-bold text-nowrap">
                                {{ $item->date ? date('d M Y', strtotime($item->date)) : 'N/A' }}
                            </td>
                            <td>
                                @if($item->invoice_no)
                                    <span class="badge" style="{{ $isReturn ? 'background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5;' : 'background: #f1f5f9; color: #475569; border: 1px solid #cbd5e1;' }} font-size: 11px; padding: 2px 6px;">
                                        {{ $item->invoice_no }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td>
                                <div class="font-weight-bold" style="color: var(--erp-text-heading);">{{ $item->vendor_name }}</div>
                            </td>
                            <td>
                                @if($item->purchase_agent_name)
                                    <span class="badge badge-light border text-info font-weight-bold" style="font-size: 11px;">
                                        <i class="fas fa-user-tie mr-1"></i> {{ $item->purchase_agent_name }}
                                    </span>
                                @else
                                    <span class="badge badge-light text-muted border" style="font-size: 11px;">Direct</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($isReturn)
                                    <span class="badge badge-danger" style="font-size: 11px; padding: 2px 7px;">
                                        Fabric Return
                                    </span>
                                @else
                                    <span class="badge erp-badge-yellow" style="font-size: 11px; padding: 2px 7px;">
                                        {{ $item->item_type }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-right font-weight-bold" style="{{ $isReturn ? 'color: #dc2626;' : 'color: var(--erp-green-primary);' }}">
                                {{ $isReturn ? '- ' : '' }}₹ {{ number_format($item->grand_total, 2) }}
                            </td>
                            <td class="text-center">
                                @if($isReturn)
                                    <a href="{{ route('admin.report.fabric_return_view', $item->ref_id) }}"
                                       class="erp-action-btn erp-btn-view" title="View Fabric Return Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @elseif($item->item_type == 'Fabric')
                                    <a href="{{ route('admin.fabric_receipt.view', ['id' => $item->ref_id]) }}"
                                       class="erp-action-btn erp-btn-view" title="View Fabric Receipt">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @else
                                    <a href="{{ route('admin.inventory.purchase_history.show', $item->ref_id) }}"
                                       class="erp-action-btn erp-btn-view" title="View Purchase Receipt">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-truck-loading fa-3x mb-2 text-secondary" style="opacity: 0.4;"></i>
                                <p class="font-weight-bold mb-0">No purchase or return records found.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                @if($purchases->count() > 0)
                <tfoot>
                    <tr>
                        <td colspan="7" class="text-right font-weight-bold text-muted" style="letter-spacing: 0.5px;">PAGE SUB-TOTAL (NET):</td>
                        <td class="text-right font-weight-bold" style="color: var(--erp-green-primary);">
                            ₹ {{ number_format($pageSubTotal ?? 0, 2) }}
                        </td>
                        <td></td>
                    </tr>
                    <tr class="erp-table-grand-total">
                        <td colspan="7" class="text-right font-weight-bold" style="letter-spacing: 0.5px;">OVERALL NET PURCHASE TOTAL:</td>
                        <td class="text-right font-weight-bold grand-total-val">
                            ₹ {{ number_format($netGrandTotal ?? $totalGrandTotal ?? 0, 2) }}
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>

        @if($purchases instanceof \Illuminate\Pagination\LengthAwarePaginator && $purchases->hasPages())
        <div class="d-flex justify-content-between align-items-center p-2 border-top flex-wrap" style="background: #fafafa;">
            <div class="text-muted small">
                Showing {{ $purchases->firstItem() ?? 0 }} to {{ $purchases->lastItem() ?? 0 }} of {{ $purchases->total() }} purchase bills
            </div>
            <div>
                {{ $purchases->links('pagination::bootstrap-4') }}
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2({
                width: '100%'
            });
        }
    });
</script>
@endsection
