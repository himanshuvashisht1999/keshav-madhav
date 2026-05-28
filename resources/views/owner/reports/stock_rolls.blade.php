@extends('owner.layouts.app')

@section('title', 'Rolls Inventory')

@section('styles')
<style>
    :root {
        --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    body {
        background: #f8fafc;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 30px 20px 50px;
        border-radius: 0 0 35px 35px;
        color: white;
    }

    .app-header h1 {
        font-size: 22px;
        font-weight: 800;
        margin-bottom: 5px;
    }

    .search-panel {
        margin-top: -30px;
        padding: 0 20px;
    }

    .filter-card {
        background: white;
        border-radius: 20px;
        padding: 20px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(0,0,0,0.05);
    }

    .roll-card {
        background: white;
        border-radius: 20px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #f1f5f9;
        box-shadow: 0 4px 10px rgba(0,0,0,0.02);
        display: block;
        text-decoration: none !important;
        color: inherit;
    }

    .roll-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 12px;
    }

    .roll-no {
        font-size: 15px;
        font-weight: 800;
        color: #ef4444;
        background: #fef2f2;
        padding: 4px 12px;
        border-radius: 10px;
    }

    .fabric-name {
        font-weight: 700;
        color: var(--text-main);
        font-size: 14px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        max-width: 150px;
    }

    .qty-badge {
        text-align: right;
    }

    .qty-value {
        font-size: 16px;
        font-weight: 900;
        color: var(--text-main);
        display: block;
    }

    .qty-label {
        font-size: 9px;
        color: var(--text-muted);
        text-transform: uppercase;
        font-weight: 700;
    }

    .roll-meta {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
        font-size: 11px;
        color: var(--text-muted);
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }

    .meta-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .form-group label {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 6px;
    }

    .form-control-custom {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        font-size: 13px;
        padding: 10px 15px;
        height: auto;
    }

    .btn-apply {
        background: var(--primary-gradient);
        border: none;
        color: white;
        padding: 12px;
        border-radius: 12px;
        font-weight: 800;
        width: 100%;
        margin-top: 15px;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('owner.stock') }}" class="text-white"><i class="fas fa-arrow-left"></i></a>
            <span class="font-weight-bold" style="font-size: 14px; opacity: 0.8;">Inventory</span>
            <div style="width: 20px;"></div>
        </div>
        <h1>Rolls List</h1>
        <p class="opacity-75" style="font-size: 13px;">Browse all fabric rolls in stock</p>
    </div>

    <div class="search-panel">
        <div class="filter-card">
            <form action="{{ route('owner.report.stock.rolls') }}" method="GET">
                <div class="row g-2">
                    <div class="col-12 mb-2">
                        <div class="form-group mb-0">
                            <label>Fabric</label>
                            <select name="fabric_id" class="form-control form-control-custom select2">
                                <option value="">All Fabrics</option>
                                @foreach($fabrics as $f)
                                    <option value="{{ $f->id }}" {{ request('fabric_id') == $f->id ? 'selected' : '' }}>{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-0">
                            <label>Warehouse</label>
                            <select name="warehouse_id" class="form-control form-control-custom">
                                <option value="">All</option>
                                @foreach($warehouses as $wh)
                                    <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->cutting_master_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="form-group mb-0">
                            <label>Roll No</label>
                            <input type="text" name="roll_no" class="form-control form-control-custom" placeholder="Search..." value="{{ request('roll_no') }}">
                        </div>
                    </div>
                </div>
                <button type="submit" class="btn-apply">Filter Rolls</button>
            </form>
        </div>
    </div>

    <div class="container-fluid mt-4 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 px-1">
            <span class="text-muted font-weight-bold" style="font-size: 12px;">{{ $rolls->total() }} Rolls Found</span>
            <a href="{{ route('owner.report.stock.rolls') }}" class="text-primary font-weight-bold" style="font-size: 12px;">Clear</a>
        </div>

        <div id="rolls-container" data-has-more="{{ $rolls->hasMorePages() ? 'true' : 'false' }}" data-current-page="{{ $rolls->currentPage() }}">
            @include('owner.reports.partials.stock_rolls_list', ['rolls' => $rolls])
        </div>

        @if($rolls->isEmpty())
            <div class="text-center py-5 opacity-50">
                <i class="fas fa-search fa-3x mb-3"></i>
                <p>No rolls found matching criteria</p>
            </div>
        @endif

        <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
        <div id="load-more-trigger" style="height: 10px;"></div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const container = document.getElementById('rolls-container');
        if (!container) return;
        
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
