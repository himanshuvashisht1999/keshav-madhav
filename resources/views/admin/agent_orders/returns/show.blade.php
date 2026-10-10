@extends('admin.layouts.app')

@section('title', 'Sales Return Details')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Sales Return #SR-{{ $return->id }}</h1>
                <p class="erp-page-subtitle">Against Dispatch: #{{ $return->agent_order_dispatch_id }} | Date: {{ \Carbon\Carbon::parse($return->return_date)->format('d-m-Y') }}</p>
            </div>
            <div class="d-flex align-items-center flex-wrap" style="gap: 6px;">
                <a href="{{ route('admin.agent-orders.returns.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
                <a href="{{ route('admin.agent-orders.returns.edit', $return->id) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-edit mr-1"></i> Edit
                </a>
                <button id="deleteReturnBtn" class="btn-erp btn-erp-danger">
                    <i class="fas fa-trash mr-1"></i> Delete
                </button>
                <a href="{{ route('admin.agent-orders.returns.send-whatsapp-pdf', $return->id) }}" class="btn-erp text-white" style="background-color: #25D366; border-color: #25D366;" onclick="event.preventDefault(); let phone = prompt('Enter WhatsApp Number:', '{{ $return->dispatch->shop->phone ?? $return->dispatch->vendor->phone ?? '' }}'); if(phone) { window.location.href = this.href + '?phone=' + encodeURIComponent(phone); }">
                    <i class="fab fa-whatsapp mr-1"></i> WhatsApp
                </a>
                <a href="{{ route('admin.agent-orders.returns.download-pdf', $return->id) }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-file-pdf mr-1"></i> Download PDF
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Left Info Card -->
            <div class="col-md-4 mb-2">
                <div class="erp-card p-3 h-100">
                    <h6 class="font-weight-bold mb-3 pb-2 border-bottom" style="color: #05421c;">
                        <i class="fas fa-info-circle mr-1"></i> Return Information
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-sm erp-table mb-0">
                            <tbody>
                                <tr>
                                    <th style="width: 40%; background: #f8fafc;">Party</th>
                                    <td class="font-weight-bold">{{ $return->dispatch->party->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">Agent</th>
                                    <td>{{ $return->dispatch->agent->name ?? 'Direct' }}</td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">Return date</th>
                                    <td>{{ \Carbon\Carbon::parse($return->return_date)->format('d M Y') }}</td>
                                </tr>
                                <tr>
                                    <th style="background: #f8fafc;">Subtotal</th>
                                    <td>₹{{ number_format($return->total_amount, 2) }}</td>
                                </tr>
                                @if($return->discount_amount > 0)
                                <tr>
                                    <th style="background: #f8fafc;">Discount ({{ number_format($return->discount_percentage, 2) }}%)</th>
                                    <td class="text-danger font-weight-bold">-₹{{ number_format($return->discount_amount, 2) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <th style="background: #f8fafc;">GST ({{ number_format($return->gst_percentage, 2) }}%)</th>
                                    <td class="text-success font-weight-bold">+₹{{ number_format($return->gst_amount, 2) }}</td>
                                </tr>
                                @if($return->other_charges > 0)
                                <tr>
                                    <th style="background: #f8fafc;">Other charges</th>
                                    <td class="text-success font-weight-bold">+₹{{ number_format($return->other_charges, 2) }}</td>
                                </tr>
                                @endif
                                <tr style="background: #edf5ee;">
                                    <th style="color: #05421c; font-size: 1rem;">Grand total</th>
                                    <td class="font-weight-bold" style="color: #05421c; font-size: 1.1rem;">₹{{ number_format($return->grand_total, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    @if($return->remark)
                    <div class="mt-3 p-2 rounded bg-light border">
                        <span class="erp-label d-block text-muted mb-1">Remarks:</span>
                        <p class="mb-0 small text-dark">{{ $return->remark }}</p>
                    </div>
                    @endif

                    <div class="mt-3 pt-2 border-top small text-muted text-right">
                        Processed By: {{ $return->creator->name ?? 'N/A' }}<br>
                        {{ $return->created_at->format('d/m/Y h:i A') }}
                    </div>
                </div>
            </div>

            <!-- Right Items Table Card -->
            <div class="col-md-8 mb-2">
                <div class="erp-card p-0 h-100">
                    <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                        <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                            <i class="fas fa-box-open mr-1"></i> Returned Items Details
                        </h6>
                        <span class="badge-erp-status badge-erp-secondary font-weight-bold">{{ $return->items->count() }} items</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table erp-table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Item description</th>
                                    <th class="text-center" style="width: 130px;">Returned qty</th>
                                    <th class="text-right" style="width: 120px;">Price</th>
                                    <th class="text-right" style="width: 140px;">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($return->items as $item)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <span class="badge-erp-status badge-erp-info mr-2 px-2 py-1 font-weight-bold">
                                                    {{ strtoupper($item->item_type) }}
                                                </span>
                                                <div>
                                                    <div class="font-weight-bold text-dark">{{ $item->product_name }}</div>
                                                    <div class="small text-muted">
                                                        @if($item->item_type === 'standard')
                                                            {{ $item->design_number }} | {{ $item->color_name }} | {{ $item->size_set_name }}
                                                        @else
                                                            Fabric Roll Return
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center font-weight-bold text-dark">
                                            {{ number_format($item->quantity, ($item->item_type === 'fabric' ? 2 : 0)) }}
                                            <small class="text-muted">{{ $item->unit }}</small>
                                        </td>
                                        <td class="text-right">₹{{ number_format($item->price, 2) }}</td>
                                        <td class="text-right font-weight-bold text-dark">₹{{ number_format($item->total, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        $('#deleteReturnBtn').click(function() {
            Swal.fire({
                title: 'Are you sure?',
                text: "This will reverse inventory and party balance. This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
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
@endsection
