@extends('admin.layouts.app')
@section('title', 'Direct Sales Orders')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-file-invoice-dollar text-primary"></i> Direct Sales Order History
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.direct-sales.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Create New Sales Order
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form action="{{ route('admin.direct-sales.index') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-4 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-store mr-1"></i> Party</label>
                    <select name="shop_id" class="form-control select2" style="width: 100%;">
                        <option value="">All Parties</option>
                        @foreach($shops as $shop)
                            <option value="{{ $shop->id }}" {{ request('shop_id') == $shop->id ? 'selected' : '' }}>{{ $shop->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('admin.direct-sales.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-undo"></i> Reset</a>
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
                        <th style="width: 70px;">#ID</th>
                        <th>Date</th>
                        <th>Customer / Shop</th>
                        <th>Total Qty</th>
                        <th>Total Boxes</th>
                        <th class="text-right">Grand Total</th>
                        <th class="text-right">Paid</th>
                        <th class="text-center">Status</th>
                        <th class="text-center" style="width: 100px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>#{{ $order->id }}</td>
                            <td>{{ date('d M, Y', strtotime($order->order_date)) }}</td>
                            <td>{{ $order->shop_name }}</td>
                            <td>{{ number_format($order->total_qty) }} pcs</td>
                            <td>{{ $order->total_boxes }} boxes</td>
                            <td class="text-right" style="color: var(--erp-green-primary);">₹{{ number_format($order->grand_total, 2) }}</td>
                            <td class="text-right text-success">₹{{ number_format($order->total_paid, 2) }}</td>
                            <td class="text-center"><span class="badge-erp-status badge-erp-success">Completed &amp; Dispatched</span></td>
                            <td class="text-center">
                                <a href="{{ route('admin.agent-orders.download-invoice', $order->id) }}" class="erp-action-btn erp-btn-pdf" title="Invoice"><i class="fas fa-file-invoice"></i></a>
                                <a href="{{ route('admin.agent-orders.show', $order->id) }}" class="erp-action-btn erp-btn-view" title="View"><i class="fas fa-eye"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">No direct sales orders found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="erp-card-body pt-0 d-flex justify-content-between align-items-center flex-wrap">
                <small class="text-muted">Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} entries</small>
                {{ $orders->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function () {
            $('.select2').select2({ theme: 'bootstrap4' });
        });
    </script>
@endpush
