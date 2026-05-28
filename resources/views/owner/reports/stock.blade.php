@extends('owner.layouts.app')

@section('title', 'Fabric Stock')

@section('styles')
<style>
    :root {
        --glass-bg: rgba(255, 255, 255, 0.9);
        --glass-border: rgba(255, 255, 255, 0.2);
        --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    }

    body {
        background: #fdf2ff;
        background-image: radial-gradient(at 0% 0%, hsla(253, 16%, 7%, 0.03) 0, transparent 50%), 
                          radial-gradient(at 50% 0%, hsla(225, 39%, 30%, 0.03) 0, transparent 50%), 
                          radial-gradient(at 100% 0%, hsla(339, 49%, 30%, 0.03) 0, transparent 50%);
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 40px 20px 60px;
        border-radius: 0 0 40px 40px;
        color: white;
        margin-bottom: -30px;
        position: relative;
        z-index: 1;
    }

    .app-header h1 {
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.5px;
        margin-bottom: 5px;
    }

    .breadcrumb-custom {
        display: flex;
        gap: 8px;
        font-size: 12px;
        opacity: 0.8;
        margin-bottom: 20px;
        align-items: center;
    }

    .breadcrumb-custom a {
        color: white;
        text-decoration: none;
    }

    .search-container {
        position: relative;
        z-index: 10;
        padding: 0 20px;
    }

    .search-box {
        background: white;
        border-radius: 20px;
        padding: 15px 20px;
        display: flex;
        align-items: center;
        gap: 12px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .search-box input {
        border: none;
        outline: none;
        width: 100%;
        font-size: 15px;
        font-weight: 500;
        color: var(--text-main);
    }

    .stock-card {
        background: var(--glass-bg);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: var(--card-shadow);
        transition: transform 0.2s;
        text-decoration: none !important;
        display: block;
    }

    .stock-card:active {
        transform: scale(0.98);
    }

    .fabric-title {
        font-size: 18px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 12px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .vendor-badge {
        font-size: 10px;
        background: rgba(99, 102, 241, 0.1);
        color: var(--text-main);
        padding: 4px 10px;
        border-radius: 10px;
        font-weight: 700;
        text-transform: uppercase;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 12px;
    }

    .stat-item {
        text-align: center;
    }

    .stat-label {
        font-size: 9px;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        display: block;
        margin-bottom: 4px;
    }

    .stat-value {
        font-size: 14px;
        font-weight: 900;
        color: var(--text-main);
    }

    .stat-value.remaining {
        color: var(--text-main);
    }

    .wh-badge {
        background: #f1f5f9;
        color: var(--text-main);
        padding: 6px 12px;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 700;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-bottom: 15px;
    }

    .action-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid rgba(0,0,0,0.05);
    }

    .btn-action {
        padding: 8px 16px;
        border-radius: 14px;
        font-size: 12px;
        font-weight: 800;
        display: flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s;
    }

    .btn-receipts {
        background: #ecfdf5;
        color: #059669;
    }

    .btn-usages {
        background: #fff7ed;
        color: var(--text-main);
    }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: var(--text-muted);
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 20px;
        opacity: 0.3;
    }

    .fab-container {
        position: fixed;
        bottom: 30px;
        right: 20px;
        z-index: 100;
    }

    .fab {
        width: 56px;
        height: 56px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 20px;
        box-shadow: 0 10px 25px rgba(168, 85, 247, 0.4);
        text-decoration: none !important;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="breadcrumb-custom">
            <a href="{{ route('owner.dashboard') }}">Home</a>
            <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
            <a href="{{ route('owner.stock') }}">Stock</a>
            @if(isset($fabric))
                <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
                <span>{{ $fabric->name }}</span>
            @endif
        </div>
        <h1>Fabric Stock</h1>
        <p class="mb-0 opacity-75">Real-time inventory management</p>
    </div>

    <div class="search-container">
        @if($level === 'fabrics')
        <form action="{{ route('owner.stock') }}" method="GET">
            <div class="search-box">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="search" placeholder="Search fabrics..." value="{{ request('search') }}">
                <input type="hidden" name="warehouse_id" value="{{ request('warehouse_id') }}">
            </div>
        </form>
        <div class="mt-3 px-1">
            <a href="{{ route('owner.report.stock.rolls') }}" class="btn btn-block btn-primary shadow-sm" style="border-radius: 15px; font-weight: 800; padding: 12px; background: var(--primary-gradient); border: none;">
                <i class="fas fa-list-ul mr-2"></i> View Stock by Roll Number
            </a>
        </div>
        @else
        <div class="search-box justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="fas fa-arrow-left text-muted" onclick="history.back()"></i>
                <span class="font-weight-bold" style="font-size: 14px;">Back to Summary</span>
            </div>
            <a href="{{ route('owner.stock') }}" class="text-primary font-weight-bold" style="font-size: 12px;">Reset</a>
        </div>
        @endif
    </div>

    <div class="container-fluid mt-4 pb-5">
        @if($level === 'fabrics')
            <div id="stock-container" data-has-more="{{ method_exists($data, 'hasMorePages') && $data->hasMorePages() ? 'true' : 'false' }}" data-current-page="{{ method_exists($data, 'currentPage') ? $data->currentPage() : 1 }}">
                @include('owner.reports.partials.stock_fabrics_list', ['data' => $data])
            </div>
            
            @if($data->isEmpty())
                <div class="empty-state">
                    <i class="fas fa-box-open"></i>
                    <p>No fabrics found in stock</p>
                </div>
            @endif

        @elseif($level === 'warehouses')
            <div class="wh-badge">
                <i class="fas fa-info-circle"></i> Showing stock by warehouse
            </div>
            @foreach($data as $row)
                <div class="stock-card">
                    <div class="fabric-title">
                        {{ $row->master_fabric_warehouse?->cutting_master_name ?: 'Unknown' }}
                    </div>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <span class="stat-label">Received</span>
                            <span class="stat-value">{{ number_format($row->total_received, 1) }}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">Stock</span>
                            <span class="stat-value remaining">{{ number_format($row->total_remaining, 1) }}</span>
                        </div>
                    </div>
                    <div class="action-row">
                        <a href="{{ route('owner.stock', ['fabric_id' => $fabric->id, 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'receipts']) }}" class="btn-action btn-receipts">
                            <i class="fas fa-file-import"></i> Shipments
                        </a>
                        <a href="{{ route('owner.stock', ['fabric_id' => $fabric->id, 'warehouse_id' => $row->master_fabric_warehouse_id, 'type' => 'usages']) }}" class="btn-action btn-usages">
                            <i class="fas fa-industry"></i> Usages
                        </a>
                    </div>
                </div>
            @endforeach

        @elseif($level === 'receipts')
            <div class="wh-badge">
                <i class="fas fa-ship"></i> Shipment History
            </div>
            <div id="stock-container" data-has-more="{{ method_exists($data, 'hasMorePages') && $data->hasMorePages() ? 'true' : 'false' }}" data-current-page="{{ method_exists($data, 'currentPage') ? $data->currentPage() : 1 }}">
                @include('owner.reports.partials.stock_receipts_list', ['data' => $data])
            </div>

        @elseif($level === 'usages')
            <div class="wh-badge">
                <i class="fas fa-history"></i> Usage Records
            </div>
            <div id="stock-container" data-has-more="{{ method_exists($data, 'hasMorePages') && $data->hasMorePages() ? 'true' : 'false' }}" data-current-page="{{ method_exists($data, 'currentPage') ? $data->currentPage() : 1 }}">
                @include('owner.reports.partials.stock_usages_list', ['data' => $data])
            </div>
        @endif
        
        <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
        <div id="load-more-trigger" style="height: 10px;"></div>
    </div>

    <div class="fab-container">
        <a href="{{ route('owner.report.stock.rolls') }}" class="fab">
            <i class="fas fa-barcode"></i>
        </a>
    </div>
</div>


@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('stock-container');
        if (!container) return; // For warehouse level where there is no pagination
        
        let hasMore = container.dataset.hasMore === 'true';
        let currentPage = parseInt(container.dataset.currentPage);
        let nextPage = hasMore ? currentPage + 1 : null;
        let isLoading = false;
        
        const observer = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting && nextPage !== null && !isLoading) {
                loadMoreData();
            }
        });
        
        const trigger = document.getElementById('load-more-trigger');
        if (trigger) {
            observer.observe(trigger);
        }

        function loadMoreData() {
            isLoading = true;
            document.getElementById('loading-spinner').style.display = 'block';
            
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('page', nextPage);

            fetch(currentUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.html) {
                    container.insertAdjacentHTML('beforeend', data.html);
                }
                
                nextPage = data.next_page;
                isLoading = false;
                document.getElementById('loading-spinner').style.display = 'none';
                
                if (nextPage === null) {
                    observer.unobserve(trigger);
                }
            })
            .catch(error => {
                console.error('Error loading more items:', error);
                isLoading = false;
                document.getElementById('loading-spinner').style.display = 'none';
            });
        }
    });
</script>
@endsection