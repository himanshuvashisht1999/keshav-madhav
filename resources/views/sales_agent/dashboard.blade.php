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
                @if(Auth::guard('sales_agent')->user()->is_master_agent)
                <a href="{{ route('agent.orders.create') }}" class="text-decoration-none">
                @else
                <a href="{{ route('agent.shops.index') }}" class="text-decoration-none">
                @endif
                    <div class="app-card mb-0 shadow-sm border text-center p-3" style="background: #eff6ff;">
                        <i class="fas fa-plus-circle fa-lg text-primary mb-2"></i>
                        <p class="small font-weight-bold text-dark mb-0">New Order</p>
                    </div>
                </a>
            </div>
            @if(!Auth::guard('sales_agent')->user()->is_master_agent)
            <div class="col-6">
                <a href="{{ route('agent.shops.create') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border text-center p-3" style="background: #f0fdf4;">
                        <i class="fas fa-user-plus fa-lg text-success mb-2"></i>
                        <p class="small font-weight-bold text-dark mb-0">Add Shop</p>
                    </div>
                </a>
            </div>
            @else
            <div class="col-6">
                <a href="{{ route('agent.orders.index') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border text-center p-3" style="background: #fdf4ff;">
                        <i class="fas fa-history fa-lg text-danger mb-2"></i>
                        <p class="small font-weight-bold text-dark mb-0">View Orders</p>
                    </div>
                </a>
            </div>
            @endif
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