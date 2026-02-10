@extends('sales_agent.layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="container">
        <!-- WELCOME SECTION -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="font-weight-bold h4 mb-1">Hi, {{ Auth::guard('sales_agent')->user()->name }}!</h2>
                <p class="text-muted small mb-0">{{ date('l, d M Y') }}</p>
            </div>
            <div class="bg-primary-light p-2 rounded-circle">
                <i class="fas fa-bell text-primary"></i>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="row">
            <div class="col-6">
                <div class="app-card text-center py-4">
                    <div class="bg-blue-soft p-3 rounded-circle d-inline-block mb-2">
                        <i class="fas fa-shopping-bag text-primary"></i>
                    </div>
                    <h3 class="h2 font-weight-bold mb-0">{{ $stats['total_orders'] }}</h3>
                    <p class="text-muted small mb-0">Total Orders</p>
                </div>
            </div>
            <div class="col-6">
                <div class="app-card text-center py-4">
                    <div class="bg-green-soft p-3 rounded-circle d-inline-block mb-2">
                        <i class="fas fa-store text-success"></i>
                    </div>
                    <h3 class="h2 font-weight-bold mb-0">{{ $stats['total_shops'] }}</h3>
                    <p class="text-muted small mb-0">Total Shops</p>
                </div>
            </div>
        </div>

        <!-- QUICK ACTIONS -->
        <h5 class="font-weight-bold mb-3">Quick Actions</h5>
        <div class="row mb-4">
            <div class="col-6">
                <a href="{{ route('agent.shops.index') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border text-center p-3" style="background: #eff6ff;">
                        <i class="fas fa-plus-circle fa-lg text-primary mb-2"></i>
                        <p class="small font-weight-bold text-dark mb-0">New Order</p>
                    </div>
                </a>
            </div>
            <div class="col-6">
                <a href="{{ route('agent.shops.create') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border text-center p-3" style="background: #f0fdf4;">
                        <i class="fas fa-user-plus fa-lg text-success mb-2"></i>
                        <p class="small font-weight-bold text-dark mb-0">Add Shop</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- RECENT ORDERS -->
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h5 class="font-weight-bold mb-0">Recent Orders</h5>
            <a href="{{ route('agent.orders.index') }}" class="small text-primary font-weight-bold">View All</a>
        </div>

        <div class="recent-list">
            @forelse($recent_orders as $order)
                <a href="{{ route('agent.orders.show', $order->id) }}" class="text-decoration-none">
                    <div class="app-card p-3 d-flex align-items-center mb-3">
                        <div class="bg-light p-3 rounded-lg mr-3">
                            <i class="fas fa-receipt text-muted"></i>
                        </div>
                        <div class="flex-grow-1">
                            <h6 class="font-weight-bold text-dark mb-1">{{ $order->shop_name }}</h6>
                            <p class="text-muted small mb-0">
                                {{ \Carbon\Carbon::parse($order->order_date)->format('d M, h:i A') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="font-weight-bold text-primary mb-0">₹{{ number_format($order->total_amount, 2) }}</p>
                            <span
                                class="badge {{ $order->status == 'pending' ? 'badge-warning' : 'badge-success' }} small rounded-pill">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-center py-5">
                    <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No recent orders yet.</p>
                </div>
            @endforelse
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .bg-primary-light {
            background-color: #eef2ff;
        }

        .bg-blue-soft {
            background-color: #dbeafe;
        }

        .bg-green-soft {
            background-color: #dcfce7;
        }

        .badge-warning {
            background-color: #fef3c7;
            color: #92400e;
        }

        .badge-success {
            background-color: #dcfce7;
            color: #166534;
        }
    </style>
@endpush