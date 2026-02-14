@extends('sales_agent.layouts.app', ['title' => 'My Orders'])

@section('content')
    <div class="container">
        <h2 class="font-weight-bold h4 mb-4">Order History</h2>

        <div class="order-list">
            @forelse($orders as $order)
                <a href="{{ route('agent.orders.show', $order->id) }}" class="text-decoration-none">
                    <div class="app-card p-3 mb-3 shadow-sm border-0 d-flex align-items-center">
                        <div class="bg-light p-3 rounded-lg mr-3">
                            <i class="fas fa-receipt text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between mb-1 align-items-center">
                                <h6 class="font-weight-bold text-dark mb-0 truncate" style="max-width: 60%;">
                                    {{ $order->shop_name }}</h6>
                                <div class="text-right">
                                    @if($order->status == 'pending' && $order->expected_dispatch_date && $order->expected_dispatch_date < date('Y-m-d'))
                                        <span
                                            class="badge badge-danger small rounded-pill px-2 py-1 mr-1 animate__animated animate__pulse animate__infinite">
                                            DELAYED
                                        </span>
                                    @endif
                                    <span
                                        class="badge {{ $order->status == 'pending' ? 'badge-warning' : 'badge-success' }} small rounded-pill px-3 py-1">
                                        {{ ucfirst($order->status) }}
                                    </span>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <p class="text-muted small mb-0">
                                    {{ \Carbon\Carbon::parse($order->order_date)->format('d M Y') }}
                                </p>
                                @if($order->expected_dispatch_date)
                                    <small class="text-muted" style="font-size: 10px;">
                                        Disp: {{ \Carbon\Carbon::parse($order->expected_dispatch_date)->format('d M') }}
                                    </small>
                                @endif
                            </div>
                            <hr class="my-2 border-dashed">
                            <div class="d-flex justify-content-between">
                                <span class="text-muted small">{{ $order->total_qty }} Items</span>
                                <span class="font-weight-bold text-primary">₹{{ number_format($order->grand_total, 2) }}</span>
                            </div>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-history fa-3x text-muted mb-3"></i>
                    <p class="text-muted">You haven't placed any orders yet.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .border-dashed {
            border-top: 1px dashed #e2e8f0;
        }
    </style>
@endpush