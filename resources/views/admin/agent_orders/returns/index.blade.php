@extends('admin.layouts.app')

@section('title', 'Sales Returns')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-undo-alt text-primary"></i> Sales Returns
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total returns</span>
                    <span class="hud-stat-value text-success">{{ number_format($totalReturnsCount ?? 0) }}</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total amount</span>
                    <span class="hud-stat-value text-primary">₹ {{ number_format($totalReturnAmount ?? 0, 2) }}</span>
                </div>
                <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> New Return (From Dispatch)
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <form action="{{ route('admin.agent-orders.returns.index') }}" method="GET">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-store mr-1"></i> Party</label>
                        <select name="shop_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL PARTIES --</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                        <select name="vendor_id" class="form-control select2 erp-input" style="width: 100%;">
                            <option value="">-- ALL VENDORS --</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From Date</label>
                        <input type="date" name="from_date" class="form-control erp-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To Date</label>
                        <input type="date" name="to_date" class="form-control erp-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-auto mb-1 ml-auto">
                        <div class="erp-filter-actions">
                            <button type="submit" class="btn-erp btn-erp-primary">
                                <i class="fas fa-filter"></i> Filter
                            </button>
                            <a href="{{ route('admin.agent-orders.returns.index') }}" class="btn-erp btn-erp-outline">
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
                <table class="erp-table table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 110px;">Return id</th>
                            <th style="width: 100px;">Dispatch #</th>
                            <th>Party name</th>
                            <th>Agent</th>
                            <th style="width: 120px;">Return date</th>
                            <th class="text-right" style="width: 140px;">Grand total</th>
                            <th class="text-center" style="width: 80px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $return)
                            <tr>
                                <td class="text-center">
                                    <span class="badge-erp-status badge-erp-danger font-weight-bold">#SR-{{ $return->id }}</span>
                                </td>
                                <td class="font-weight-bold text-dark">
                                    #{{ $return->agent_order_dispatch_id }}
                                </td>
                                <td>
                                    {{ $return->dispatch->party->name ?? 'N/A' }}
                                </td>
                                <td>
                                    {{ $return->dispatch->agent->name ?? 'Direct' }}
                                </td>
                                <td>
                                    {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}
                                </td>
                                <td class="text-right font-weight-bold text-dark">
                                    ₹{{ number_format($return->grand_total, 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.agent-orders.returns.show', $return->id) }}" class="erp-action-btn erp-btn-view" title="View Return">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-undo-alt fa-2x mb-2 text-secondary"></i>
                                    <div>No sales returns recorded yet.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($returns->hasPages())
                <div class="p-2 border-top bg-white">
                    {{ $returns->appends(request()->query())->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
    });
</script>
@endsection
