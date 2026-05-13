@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Production Purchase Orders</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
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
                        {{ $pos->links() }}
                    </div>
                @endif
            </div>
        </div>
    </section>
</div>
@endsection

@section('scripts')
<script>
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
