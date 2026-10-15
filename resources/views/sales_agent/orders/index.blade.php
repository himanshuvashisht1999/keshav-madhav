@extends('sales_agent.layouts.app', ['title' => 'My Orders'])

@section('content')
    <div class="container">
        <h2 class="font-weight-bold h4 mb-4">Order History</h2>

        <div class="order-list">
            @forelse($orders as $order)
                <a href="{{ route('agent.orders.show', $order->id) }}" class="text-decoration-none">
                    <div class="app-card p-3 mb-3 shadow-sm border-0 d-flex align-items-center position-relative overflow-hidden">
                        <div style="height: 3px; background: {{ $order->status == 'pending' ? '#ffd600' : '#22c55e' }}; width: 100%; position: absolute; top: 0; left: 0;"></div>
                        <div class="p-3 rounded-lg mr-3" style="background: #fef9c3;">
                            <i class="fas fa-receipt" style="color: #ca8a04;"></i>
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
                                    @php
                                        $statusLabel = ['pending' => 'Pending', 'partially_dispatched' => 'Part Dispatched', 'dispatched' => 'Dispatched', 'delayed' => 'Delayed'][$order->status] ?? ucfirst(str_replace('_', ' ', $order->status));
                                        $statusClass = $order->status == 'dispatched' ? 'badge-success' : ($order->status == 'partially_dispatched' ? 'badge-info' : 'badge-warning');
                                    @endphp
                                    <span
                                        class="badge {{ $statusClass }} small rounded-pill px-3 py-1 font-weight-bold">
                                        {{ $statusLabel }}
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
                                <span class="badge px-2 py-1 font-weight-bold" style="background-color: #fef08a; color: #854d0e; border: 1px solid #fde047; border-radius: 6px;">ORD-{{ $order->id }} · {{ $order->total_qty }} Pcs</span>
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

        @if($orders->hasPages())
            <div class="d-flex justify-content-between align-items-center mb-4">
                @if($orders->onFirstPage())
                    <span class="btn btn-light btn-sm rounded-pill px-3 disabled">&laquo; Newer</span>
                @else
                    <a href="{{ $orders->previousPageUrl() }}" class="btn btn-light btn-sm rounded-pill px-3">&laquo; Newer</a>
                @endif
                <span class="small text-muted">Page {{ $orders->currentPage() }} of {{ $orders->lastPage() }}</span>
                @if($orders->hasMorePages())
                    <a href="{{ $orders->nextPageUrl() }}" class="btn btn-light btn-sm rounded-pill px-3">Older &raquo;</a>
                @else
                    <span class="btn btn-light btn-sm rounded-pill px-3 disabled">Older &raquo;</span>
                @endif
            </div>
        @endif
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

        .badge-info {
            background-color: #dbeafe;
            color: #1e40af;
        }

        .border-dashed {
            border-top: 1px dashed #e2e8f0;
        }
    </style>
@endpush