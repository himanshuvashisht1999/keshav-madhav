@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Sales Return #SR-{{ $return->id }}</h1>
                    <p class="text-muted">Against Dispatch: #{{ $return->agent_order_dispatch_id }} | Date: {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</p>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.agent-orders.returns.index') }}" class="btn btn-outline-secondary rounded-pill">
                        <i class="fas fa-arrow-left mr-1"></i> Back to List
                    </a>
                    <a href="{{ route('admin.agent-orders.returns.edit', $return->id) }}" class="btn btn-warning rounded-pill ml-2">
                        <i class="fas fa-edit mr-1"></i> Edit
                    </a>
                    <button id="deleteReturnBtn" class="btn btn-danger rounded-pill ml-2">
                        <i class="fas fa-trash mr-1"></i> Delete
                    </button>
                    <a href="{{ route('admin.agent-orders.returns.send-whatsapp-pdf', $return->id) }}" class="btn text-white rounded-pill ml-2" style="background-color: #25D366; border-color: #25D366;" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $return->dispatch->shop->phone ?? $return->dispatch->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + '?phone=' + encodeURIComponent(phone); }">
                        <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                    </a>
                    <a href="{{ route('admin.agent-orders.returns.download-pdf', $return->id) }}" class="btn btn-primary rounded-pill ml-2">
                        <i class="fas fa-file-pdf mr-1"></i> Download PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4">
                    <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0 font-weight-bold">Return Information</h6>
                        </div>
                        <div class="card-body">
                            <ul class="list-group list-group-flush">
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">Party:</span>
                                    <span class="font-weight-bold">{{ $return->dispatch->party->name ?? 'N/A' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">Agent:</span>
                                    <span class="font-weight-bold">{{ $return->dispatch->agent->name ?? 'Direct' }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">Return Date:</span>
                                    <span class="font-weight-bold">{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</span>
                                </li>
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">Subtotal:</span>
                                    <span class="font-weight-bold">₹{{ number_format($return->total_amount, 2) }}</span>
                                </li>
                                @if($return->discount_amount > 0)
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">Discount ({{ number_format($return->discount_percentage, 2) }}%):</span>
                                    <span class="font-weight-bold text-danger">-₹{{ number_format($return->discount_amount, 2) }}</span>
                                </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">GST ({{ number_format($return->gst_percentage, 2) }}%):</span>
                                    <span class="font-weight-bold text-success">+₹{{ number_format($return->gst_amount, 2) }}</span>
                                </li>
                                @if($return->other_charges > 0)
                                <li class="list-group-item d-flex justify-content-between p-2">
                                    <span class="text-muted">Other Charges:</span>
                                    <span class="font-weight-bold text-success">+₹{{ number_format($return->other_charges, 2) }}</span>
                                </li>
                                @endif
                                <li class="list-group-item d-flex justify-content-between p-2 bg-light">
                                    <span class="h5 mb-0">Grand Total:</span>
                                    <span class="h5 mb-0 font-weight-bold text-primary">₹{{ number_format($return->grand_total, 2) }}</span>
                                </li>
                            </ul>
                            
                            <div class="mt-4">
                                <label class="small font-weight-bold text-muted uppercase">Remark</label>
                                <p class="mb-0 bg-light p-3 rounded" style="border-left: 4px solid #007bff;">
                                    {{ $return->remark ?: 'No remarks provided.' }}
                                </p>
                            </div>

                            <div class="mt-4 small text-muted text-right">
                                Processed By: {{ $return->creator->name ?? 'N/A' }}<br>
                                {{ $return->created_at->format('d/m/Y h:i A') }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-8">
                    <div class="card shadow-sm border-0" style="border-radius: 15px;">
                        <div class="card-header bg-dark text-white">
                            <h6 class="mb-0 font-weight-bold">Returned Items</h6>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-striped mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Item Description</th>
                                        <th class="text-center">Returned Qty</th>
                                        <th class="text-right">Price</th>
                                        <th class="text-right">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($return->items as $item)
                                        @php
                                            // Pre-fetched in controller for convenience
                                        @endphp
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="badge badge-light border mr-3 px-2 py-1">
                                                        {{ strtoupper($item->item_type) }}
                                                    </div>
                                                    <div>
                                                        <div class="font-weight-bold text-dark">{{ $item->product_name }}</div>
                                                        <small class="text-muted">
                                                            @if($item->item_type === 'standard')
                                                                {{ $item->design_number }} | {{ $item->color_name }} | {{ $item->size_set_name }}
                                                            @else
                                                                Fabric Roll Return
                                                            @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="font-weight-bold text-danger">{{ number_format($item->quantity, ($item->item_type === 'fabric' ? 2 : 0)) }}</span>
                                                <small class="text-muted">{{ $item->unit }}</small>
                                            </td>
                                            <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="text-right font-weight-bold text-primary">₹{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    $(document).ready(function() {
        $('#deleteReturnBtn').click(function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will reverse inventory and party balance. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ route('admin.agent-orders.returns.destroy', $return->id) }}",
                        method: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Deleted!', response.message, 'success')
                                .then(() => {
                                    window.location.href = "{{ route('admin.agent-orders.returns.index') }}";
                                });
                            } else {
                                Swal.fire('Error!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error!', 'Something went wrong.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>

<style>
    @media print {
        .main-sidebar, .main-footer, .btn, .content-header p, #deleteReturnBtn { display: none !important; }
        .content-wrapper { margin-left: 0 !important; padding: 0 !important; }
        .card { box-shadow: none !important; border: 1px solid #ddd !important; }
        .bg-primary, .bg-dark { background-color: #f8f9fa !important; color: #000 !important; border-bottom: 2px solid #000 !important; }
    }
</style>
@endsection
