@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-list-alt text-primary"></i> Production Purchase Orders
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.product_order.bulkPO') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Create Bulk PO
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <form method="GET" action="{{ route('admin.product_order.poList') }}">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Search (PO / SKU)</label>
                    <input type="text" class="form-control erp-input" name="search" id="search" value="{{ request('search') }}" placeholder="Search PO No or Order SKU..." autocomplete="off">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                    <select class="form-control select2 erp-input" name="vendor_id" id="vendor_id" style="width: 100%;">
                        <option value="">-- ALL VENDORS --</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-building mr-1"></i> Customer</label>
                    <select class="form-control select2 erp-input" name="customer_id" id="customer_id" style="width: 100%;">
                        <option value="">-- ALL CUSTOMERS --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From Date</label>
                    <input type="date" class="form-control erp-input" name="start_date" id="start_date" value="{{ request('start_date') }}">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To Date</label>
                    <input type="date" class="form-control erp-input" name="end_date" id="end_date" value="{{ request('end_date') }}">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" title="Apply Filter">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.product_order.poList') }}" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Filter Summary Metrics Strip -->
    <div class="row mb-2">
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-green py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-green-light); color: var(--erp-green-primary); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg);">
                        <i class="fas fa-tshirt"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">Total Quantity</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-green-primary); line-height: 1.2;">
                            {{ number_format($total_quantity) }} Pcs
                        </div>
                    </div>
                </div>
                <span class="badge" style="background: var(--erp-green-light); color: var(--erp-green-primary); border: 1px solid var(--erp-green-border); font-size: var(--erp-font-xs); font-weight: 700; padding: 4px 9px;">
                    Quantity
                </span>
            </div>
        </div>
        <div class="col-md-6 col-sm-6 mb-1">
            <div class="erp-card erp-metric-card-left-yellow py-2 px-3 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center">
                    <div class="mr-3" style="width: 40px; height: 40px; border-radius: 6px; background: var(--erp-yellow-bg); color: var(--erp-yellow-dark); display: flex; align-items: center; justify-content: center; font-size: var(--erp-font-lg); border: 1px solid var(--erp-yellow-badge-border);">
                        <i class="fas fa-file-invoice"></i>
                    </div>
                    <div>
                        <div style="font-size: var(--erp-font-sm); font-weight: 700; color: var(--erp-text-muted); text-transform: uppercase; letter-spacing: 0.5px;">POs Total</div>
                        <div class="font-weight-bold" style="font-size: var(--erp-font-xl); color: var(--erp-text-heading); line-height: 1.2;">
                            {{ $pos->total() }} Orders
                        </div>
                    </div>
                </div>
                <span class="badge erp-badge-yellow" style="padding: 4px 9px;">
                    Count
                </span>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 140px;">PO No</th>
                        <th style="width: 110px;" class="text-center">Date</th>
                        <th>Assigned To</th>
                        <th style="width: 160px;">Sales Order</th>
                        <th style="width: 100px;" class="text-right font-weight-bold">Total Qty</th>
                        <th style="width: 110px;" class="text-center">Delivery Date</th>
                        <th style="width: 130px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pos as $index => $po)
                        <tr>
                            <td class="text-center">{{ $pos->firstItem() + $index }}</td>
                            <td class="font-weight-bold">{{ $po->po_number }}</td>
                            <td class="text-center">{{ $po->created_at->format('j M Y') }}</td>
                            <td>
                                @if($po->vendor_id)
                                    <span class="badge badge-light border text-primary px-2 py-1" style="font-size: 11.5px;">
                                        <i class="fas fa-truck mr-1"></i> Vendor: <strong>{{ $po->vendor->name ?? 'N/A' }}</strong>
                                    </span>
                                @else
                                    <span class="badge badge-light border text-info px-2 py-1" style="font-size: 11.5px;">
                                        <i class="fas fa-user mr-1"></i> Customer: <strong>{{ $po->customer->name ?? 'N/A' }}</strong>
                                    </span>
                                @endif
                            </td>
                            <td>
                                <strong>{{ $po->orderMain->sku ?? '-' }}</strong>
                            </td>
                            <td class="text-right font-weight-bold text-primary">{{ number_format($po->items->sum('quantity')) }}</td>
                            <td class="text-center">{{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : '-' }}</td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center">
                                    <a href="{{ route('admin.product_order.viewBulkPO', $po->id) }}" class="erp-action-btn erp-btn-view" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.product_order.editBulkPO', $po->id) }}" class="erp-action-btn erp-btn-edit" title="Edit PO">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="{{ route('admin.product_order.downloadBulkPO', $po->id) }}" class="erp-action-btn erp-btn-pdf" title="Download PDF">
                                        <i class="fas fa-download"></i>
                                    </a>
                                    <button type="button" class="erp-action-btn erp-btn-delete delete-po" data-id="{{ $po->id }}" title="Delete PO">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">No production POs found matching criteria.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($pos->hasPages())
            <div class="erp-card-footer p-2 d-flex justify-content-between align-items-center border-top">
                <div class="text-muted small">
                    Showing {{ $pos->firstItem() }} to {{ $pos->lastItem() }} of {{ $pos->total() }} entries
                </div>
                <div>
                    {{ $pos->appends(request()->query())->links() }}
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({ width: '100%' });
    });

    $(document).on('click', '.delete-po', function() {
        const id = $(this).data('id');
        const btn = $(this);

        Swal.fire({
            title: "Delete Production PO?",
            text: "Are you sure? This will restore the quantities to the original order sets.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#3085d6",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                btn.prop('disabled', true);
                $.ajax({
                    url: "{{ url('admin/production-order/po') }}/" + id + "/delete",
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(res) {
                        if (res.status) {
                            toastr.success(res.message);
                            location.reload();
                        } else {
                            Swal.fire({ icon: 'error', title: 'Error', text: res.message });
                            btn.prop('disabled', false);
                        }
                    },
                    error: function() {
                        Swal.fire({ icon: 'error', title: 'Error', text: 'Something went wrong.' });
                        btn.prop('disabled', false);
                    }
                });
            }
        });
    });
</script>
@endsection
