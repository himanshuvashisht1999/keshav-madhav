@extends('sales_agent.layouts.app', ['title' => 'Order Details'])

@section('content')
    <div class="container">
        <div class="mb-4">
            <a href="{{ route('agent.orders.index') }}" class="text-muted small text-decoration-none">
                <i class="fas fa-arrow-left mr-1"></i> Back to History
            </a>
            <h2 class="font-weight-bold h4 mt-2">Order Details</h2>
            <p class="text-muted small">Reference: #ORD-{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}</p>
        </div>

        <!-- ORDER INFO CARD -->
        <div class="app-card shadow-sm border-0 mb-4 bg-primary text-white">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <p class="small mb-1 opacity-75">Status</p>
                    <h4 class="font-weight-bold mb-0 text-uppercase">{{ $order->status }}</h4>
                </div>
                <div class="text-right">
                    <p class="small mb-1 opacity-75">Total Amount</p>
                    <h3 class="font-weight-bold mb-0">₹{{ number_format($order->total_amount, 2) }}</h3>
                    <a href="{{ route('agent.orders.invoice', $order->id) }}"
                        class="btn btn-sm btn-light mt-2 text-primary font-weight-bold">
                        <i class="fas fa-download mr-1"></i> Invoice
                    </a>
                </div>
            </div>
        </div>

        <div class="app-card shadow-sm border-0 mb-4">
            <h6 class="font-weight-bold text-muted small uppercase mb-3 text-secondary">Shipping to:</h6>
            <h5 class="font-weight-bold mb-1">{{ $order->shop_name }}</h5>
            <p class="text-muted small mb-0">{{ $order->order_date }}</p>
        </div>

        @if($order->status === 'pending')
            <div class="mb-4">
                <a href="{{ route('agent.orders.edit', $order->id) }}" class="btn btn-primary btn-block py-3 rounded-lg">
                    <i class="fas fa-edit mr-2"></i> Edit Order
                </a>
            </div>
        @endif

        <!-- ITEM LIST -->
        <h6 class="font-weight-bold mb-3">Order Items ({{ count($items) }})</h6>
        @foreach($items as $item)
            <div class="app-card mb-2 p-3 shadow-none border">
                <div class="d-flex justify-content-between mb-2">
                    <h6 class="font-weight-bold mb-0">{{ $item->product_name }}</h6>
                    <span class="badge badge-light">Box #{{ $item->box_no }}</span>
                </div>
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="text-muted small bg-light px-2 rounded mr-2">Des: {{ $item->design_number }}</span>
                    <span class="text-muted small bg-light px-2 rounded mr-2">Col: {{ $item->color_name }}</span>
                    <span class="text-muted small bg-light px-2 rounded">Set: {{ $item->size_set_name }}</span>
                </div>
                <div class="d-flex justify-content-between align-items-end">
                    <span class="text-dark font-weight-bold">{{ $item->quantity }} pcs</span>
                    <span class="text-primary font-weight-bold">₹{{ $item->selling_price }} / pc</span>
                </div>
            </div>
        @endforeach

    </div>
@endsection

@push('styles')
    <style>
        .opacity-75 {
            opacity: 0.75;
        }

        .bg-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%) !important;
        }
    </style>
@endpush