@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Production Purchase Orders</h1>
                </div>
                <div class="col-sm-6">
                    <div class="d-flex justify-content-end">
                        <div class="info-box bg-light border-0 shadow-none m-0" style="min-height: 50px; padding: 5px;">
                            <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-boxes"></i></span>
                            <div class="info-box-content" style="padding: 0 10px;">
                                <span class="info-box-text text-muted small">Total Quantity</span>
                                <span class="info-box-number" style="font-size: 0.9rem;">{{ number_format($total_quantity) }} Pcs</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <!-- Filter Card -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-filter"></i> Filters</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.product_order.poList') }}">
                        <div class="row">
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="search" class="small">Search (PO No / Order SKU)</label>
                                    <input type="text" class="form-control form-control-sm" name="search" id="search" value="{{ request('search') }}" placeholder="Enter PO No or SKU">
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="vendor_id" class="small">Vendor</label>
                                    <select class="form-control form-control-sm select2" name="vendor_id" id="vendor_id" style="width: 100%;">
                                        <option value="">All Vendors</option>
                                        @foreach($vendors as $vendor)
                                            <option value="{{ $vendor->id }}" {{ request('vendor_id') == $vendor->id ? 'selected' : '' }}>{{ $vendor->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label for="customer_id" class="small">Customer</label>
                                    <select class="form-control form-control-sm select2" name="customer_id" id="customer_id" style="width: 100%;">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>{{ $customer->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3 col-sm-6">
                                <div class="form-group">
                                    <label class="small">Created Date Range</label>
                                    <div class="d-flex flex-wrap">
                                        <input type="date" class="form-control form-control-sm mb-2 mr-2" style="flex: 1 1 120px;" name="start_date" id="start_date" value="{{ request('start_date') }}">
                                        <input type="date" class="form-control form-control-sm mb-2" style="flex: 1 1 120px;" name="end_date" id="end_date" value="{{ request('end_date') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12 text-right">
                                <button type="submit" class="btn btn-sm btn-primary mr-2"><i class="fa fa-filter"></i> Filter</button>
                                <a href="{{ route('admin.product_order.poList') }}" class="btn btn-sm btn-secondary"><i class="fa fa-undo"></i> Reset</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Table Card -->
            <div class="card">
                <div class="card-body table-responsive p-0">
                    <table class="table table-hover text-nowrap">
                        <thead>
                            <tr>
                                <th>PO No</th>
                                <th>Date</th>
                                <th>To</th>
                                <th>Order</th>
                                <th>Total Qty</th>
                                <th>Delivery Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pos as $po)
                                <tr>
                                    <td><strong>{{ $po->po_number }}</strong></td>
                                    <td>{{ $po->created_at->format('j M Y') }}</td>
                                    <td>
                                        @if($po->vendor_id)
                                            <span class="badge badge-primary">Vendor: {{ $po->vendor->name ?? 'N/A' }}</span>
                                        @else
                                            <span class="badge badge-info">Customer: {{ $po->customer->name ?? 'N/A' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong>{{ $po->orderMain->sku ?? 'N/A' }}</strong>
                                    </td>
                                    <td>{{ $po->items->sum('quantity') }}</td>
                                    <td>{{ $po->delivery_date ? \Carbon\Carbon::parse($po->delivery_date)->format('j M Y') : '-' }}</td>
                                    <td>
                                        <a href="{{ route('admin.product_order.editBulkPO', $po->id) }}" class="btn btn-sm btn-success" title="Edit PO">
                                            <i class="fa fa-edit"></i>
                                        </a>
                                        <a href="{{ route('admin.product_order.viewBulkPO', $po->id) }}" class="btn btn-sm btn-primary" title="View Details">
                                            <i class="fa fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.product_order.downloadBulkPO', $po->id) }}" class="btn btn-sm btn-info" title="Download PDF">
                                            <i class="fa fa-download"></i>
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger delete-po" data-id="{{ $po->id }}" title="Delete">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center p-4">No production POs found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($pos->hasPages())
                    <div class="card-footer clearfix">
                        {{ $pos->appends(request()->query())->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            allowClear: true,
            placeholder: "Select option"
        });
    });

    $(document).on('click', '.delete-po', function() {
        if (!confirm('Are you sure you want to delete this PO? This will restore the quantities to the original order sets.')) return;
        
        const id = $(this).data('id');
        const btn = $(this);
        btn.prop('disabled', true);
 
        $.ajax({
            url: `/admin/production-order/po/${id}/delete`,
            type: 'DELETE',
            data: { _token: "{{ csrf_token() }}" },
            success: function(res) {
                if (res.status) {
                    toastr.success(res.message);
                    window.location.reload();
                } else {
                    toastr.error(res.message);
                    btn.prop('disabled', false);
                }
            },
            error: function() {
                toastr.error('Failed to delete PO');
                btn.prop('disabled', false);
            }
        });
    });
</script>
@endsection
