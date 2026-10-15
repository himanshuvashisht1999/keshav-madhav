@extends('sales_agent.layouts.app', ['title' => 'Dashboard'])

@section('content')
    <div class="container">
        <!-- WELCOME SECTION -->
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h2 class="font-weight-bold h4 mb-1">Hi, {{ Auth::guard('sales_agent')->user()->name }}! 👋</h2>
                <p class="text-muted small mb-0">{{ date('l, d M Y') }}</p>
            </div>
            <div class="snapkid-bell p-2 rounded-circle shadow-xs">
                <i class="fas fa-bell text-success"></i>
            </div>
        </div>

        <!-- STATS GRID -->
        <div class="row">
            <div class="col-6">
                <div class="app-card text-center py-4 position-relative overflow-hidden">
                    <div style="height: 3px; background: #fcee21; width: 100%; position: absolute; top: 0; left: 0;"></div>
                    <div class="bg-yellow-soft p-3 rounded-circle d-inline-block mb-2">
                        <i class="fas fa-shopping-bag text-warning" style="color: #05421c !important;"></i>
                    </div>
                    <h3 class="h2 font-weight-bold mb-0">{{ $stats['total_orders'] }}</h3>
                    <p class="text-muted small mb-0 font-weight-bold">Total Orders</p>
                </div>
            </div>
            <div class="col-6">
                <div class="app-card text-center py-4 position-relative overflow-hidden">
                    <div style="height: 3px; background: #36b54a; width: 100%; position: absolute; top: 0; left: 0;"></div>
                    <div class="bg-green-soft p-3 rounded-circle d-inline-block mb-2">
                        <i class="fas fa-store text-success"></i>
                    </div>
                    <h3 class="h2 font-weight-bold mb-0">{{ $stats['total_shops'] }}</h3>
                    <p class="text-muted small mb-0 font-weight-bold">Total Shops</p>
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
                    <div class="app-card mb-0 shadow-sm border text-center p-3 quick-card" style="background: #f0f8e7; border-color: #d6ebc4 !important;">
                        <i class="fas fa-plus-circle fa-lg text-success mb-2"></i>
                        <p class="small font-weight-bold text-dark mb-0">New Order</p>
                    </div>
                </a>
            </div>
            @if(!Auth::guard('sales_agent')->user()->is_master_agent)
            <div class="col-6">
                <a href="{{ route('agent.shops.create') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border text-center p-3 quick-card" style="background: #fffde0; border-color: #fff59d !important;">
                        <i class="fas fa-user-plus fa-lg mb-2" style="color: #05421c;"></i>
                        <p class="small font-weight-bold text-dark mb-0">Add Shop</p>
                    </div>
                </a>
            </div>
            @else
            <div class="col-6">
                <a href="{{ route('agent.orders.index') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border text-center p-3 quick-card" style="background: #fffde0; border-color: #fff59d !important;">
                        <i class="fas fa-history fa-lg mb-2" style="color: #05421c;"></i>
                        <p class="small font-weight-bold text-dark mb-0">View Orders</p>
                    </div>
                </a>
            </div>
            @endif

            <div class="col-12 mt-3">
                <a href="{{ route('agent.offline.index') }}" class="text-decoration-none">
                    <div class="app-card mb-0 shadow-sm border p-3 quick-card d-flex align-items-center justify-content-between" style="background: linear-gradient(135deg, #ffffff 0%, #f0f8e7 100%); border-color: #8bc63e !important; border-radius: 14px;">
                        <div class="d-flex align-items-center">
                            <div class="bg-dark text-white p-2 rounded-circle mr-3 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-plane"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-bold text-dark mb-0">Without Internet Orders</h6>
                                <small class="text-muted">Take orders in shops with zero mobile signal</small>
                            </div>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </a>
            </div>
        </div>

    </div>
@endsection

@push('styles')
    <style>
        .snapkid-bell {
            background-color: #f0f8e7;
            border: 1px solid #eaf4e2;
        }

        .bg-yellow-soft {
            background-color: #fffde0;
        }

        .bg-green-soft {
            background-color: #eaf4e2;
        }

        .quick-card {
            transition: all 0.25s ease;
        }

        .quick-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.06);
        }

        .badge-warning {
            background-color: #fffde0;
            color: #05421c;
        }

        .badge-success {
            background-color: #eaf4e2;
            color: #05421c;
        }
    </style>
@endpush