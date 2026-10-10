@extends('admin.layouts.app')

@section('title', 'Sales Returns')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Sales Returns</h1>
                <p class="erp-page-subtitle">Track customer returned goods from dispatches and reconcile ledger accounts</p>
            </div>
            <div>
                <a href="{{ route('admin.agent-orders.dispatches.index') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> New Return (From Dispatch)
                </a>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="erp-card mb-2 p-2">
            <form action="{{ route('admin.agent-orders.returns.index') }}" method="GET">
                <div class="form-row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="erp-label">Filter by party</label>
                        <select name="shop_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All parties</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-label">Filter by vendor</label>
                        <select name="vendor_id" class="form-control form-control-sm erp-input select2">
                            <option value="">All vendors</option>
                            @foreach($vendors as $vendor)
                                <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>
                                    {{ $vendor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">From date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm erp-input" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="erp-label">To date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm erp-input" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-2 mb-2 d-flex">
                        <button type="submit" class="btn-erp btn-erp-primary flex-fill mr-1">
                            <i class="fas fa-filter mr-1"></i> Apply
                        </button>
                        <a href="{{ route('admin.agent-orders.returns.index') }}" class="btn-erp btn-erp-secondary">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table class="table erp-table table-bordered table-hover mb-0">
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
