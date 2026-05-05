@extends('layouts.unit')

@section('title', 'Assignments')

@push('styles')
<style>
    body {
        background: #f1f5f9;
        color: #1e293b;
    }

    .dashboard-header-simple {
        background: #ffffff;
        border-bottom: 1px solid #e2e8f0;
        padding: 24px 0;
        margin-bottom: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
    }

    .dashboard-title-simple {
        font-size: 24px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .dashboard-subtitle-simple {
        font-size: 14px;
        color: #64748b;
    }

    .tabs-simple {
        display: flex;
        background: #e2e8f0;
        padding: 4px;
        border-radius: 8px;
        margin-bottom: 24px;
    }

    .tab-item-simple {
        flex: 1;
        text-align: center;
        padding: 10px;
        font-size: 13px;
        font-weight: 600;
        border-radius: 6px;
        text-decoration: none !important;
        color: #64748b;
        transition: all 0.2s;
    }

    .tab-item-simple.active {
        background: #ffffff;
        color: #4f46e5;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .order-card-simple {
        background: #ffffff;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        padding: 16px;
        margin-bottom: 16px;
        text-decoration: none !important;
        display: block;
        transition: all 0.2s;
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
    }

    .order-card-simple:hover {
        border-color: #4f46e5;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        transform: translateY(-2px);
    }

    .order-sku-simple {
        font-size: 16px;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .customer-info-simple {
        font-size: 13px;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .task-badge-simple {
        background: #f1f5f9;
        color: #475569;
        padding: 4px 10px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 700;
        border: 1px solid #e2e8f0;
    }

    .latest-activity-simple {
        font-size: 11px;
        color: #94a3b8;
        margin-top: 12px;
        padding-top: 12px;
        border-top: 1px solid #f1f5f9;
    }
</style>
@endpush

@section('content')
<div class="dashboard-header-simple">
    <div class="container">
        <h1 class="dashboard-title-simple">Assignments</h1>
        <p class="dashboard-subtitle-simple">Track and manage your manufacturing tasks by order</p>
    </div>
</div>

<div class="container pb-5">
    @if(!empty($canCloseTasks) && $canCloseTasks)
        <div class="tabs-simple">
            <a href="{{ route('unit.assignments', ['view' => 'open']) }}"
                class="tab-item-simple {{ ($view ?? 'open') === 'open' ? 'active' : '' }}">
                <i class="fas fa-folder-open mr-2"></i> Active Orders
            </a>
            <a href="{{ route('unit.assignments', ['view' => 'closed']) }}"
                class="tab-item-simple {{ ($view ?? 'open') === 'closed' ? 'active' : '' }}">
                <i class="fas fa-archive mr-2"></i> Archived Orders
            </a>
        </div>
    @endif

    <div class="row">
        @forelse($orders as $sku => $order)
            <div class="col-12 col-md-6 col-lg-4">
                <a href="{{ route('unit.assignments', ['order_sku' => $order['sku'], 'view' => $view]) }}" class="order-card-simple">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <div class="order-sku-simple">
                                <i class="fas fa-box text-muted mr-2"></i>
                                {{ $order['sku'] }}
                            </div>
                            <div class="customer-info-simple">
                                {{ $order['customer'] }}
                            </div>
                        </div>
                        <span class="task-badge-simple">
                            {{ $order['task_count'] }} {{ Str::plural('Task', $order['task_count']) }}
                        </span>
                    </div>
                    <div class="latest-activity-simple">
                        <i class="far fa-clock mr-1"></i>
                        Updated {{ $order['latest_task'] ? \Carbon\Carbon::parse($order['latest_task'])->diffForHumans() : 'Recently' }}
                    </div>
                </a>
            </div>
        @empty
            <div class="col-12">
                <div class="card border-0 shadow-sm p-5 text-center">
                    <h3 class="h5 font-weight-bold">No Orders Found</h3>
                    <p class="text-muted mb-0">
                        @if(($view ?? 'open') === 'closed')
                            Your archived orders list is empty.
                        @else
                            You have no pending assignments at the moment.
                        @endif
                    </p>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection