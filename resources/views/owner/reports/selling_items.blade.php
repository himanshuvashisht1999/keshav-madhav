@extends('owner.layouts.app')

@section('title', 'Selling Item List')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #ec4899 0%, #be185d 100%);
        --glass-bg: rgba(255, 255, 255, 0.95);
        --glass-border: rgba(255, 255, 255, 0.2);
        --card-shadow: 0 8px 32px rgba(31, 38, 135, 0.07);
    }

    body {
        background: #f8fafc;
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

    .item-card {
        background: var(--glass-bg);
        border-radius: 20px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: var(--card-shadow);
        border: 1px solid var(--glass-border);
        position: relative;
        overflow: hidden;
    }

    .design-badge {
        position: absolute;
        top: 0;
        right: 0;
        background: #fdf2f8;
        color: #ec4899;
        font-weight: 800;
        font-size: 11px;
        padding: 6px 16px;
        border-radius: 0 20px 0 20px;
    }

    .item-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
    }

    .item-icon {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: #fdf2f8;
        color: #ec4899;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
    }

    .design-title {
        font-size: 18px;
        font-weight: 900;
        color: #0f172a;
        margin: 0;
    }

    .design-subtitle {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        margin: 0;
    }

    .stats-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .stat-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        border: 1px solid #f1f5f9;
    }

    .stat-label {
        font-size: 11px;
        color: #64748b;
        font-weight: 700;
        text-transform: uppercase;
        margin-bottom: 4px;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .stat-val {
        font-size: 20px;
        font-weight: 900;
        color: #0f172a;
    }

    .total-banner {
        background: #fdf2f8;
        border-radius: 12px;
        padding: 12px 20px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }

    .total-banner .label {
        font-weight: 800;
        color: #ec4899;
        font-size: 13px;
        text-transform: uppercase;
    }

    .total-banner .value {
        font-weight: 900;
        color: #be185d;
        font-size: 22px;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="breadcrumb-custom">
            <a href="{{ route('owner.dashboard') }}">Home</a>
            <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
            <span>Reports</span>
        </div>
        <h1>Selling Item List</h1>
        <p class="mb-0 opacity-75">Combined total orders per design</p>
    </div>

    <!-- Filter Form Section -->
    <div class="px-3 mt-4 mb-3" style="position: relative; z-index: 10;">
        <form method="GET" action="{{ route('owner.reports.selling-items') }}">
            <div class="card" style="border-radius: 16px; border: none; box-shadow: var(--card-shadow); background: var(--glass-bg);">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="font-weight-bold" style="font-size: 11px; color: #64748b; text-transform: uppercase;">Start Date</label>
                            <input type="date" name="start_date" class="form-control form-control-sm" value="{{ request('start_date') }}" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; color: #334155;">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="font-weight-bold" style="font-size: 11px; color: #64748b; text-transform: uppercase;">End Date</label>
                            <input type="date" name="end_date" class="form-control form-control-sm" value="{{ request('end_date') }}" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; color: #334155;">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="font-weight-bold" style="font-size: 11px; color: #64748b; text-transform: uppercase;">Sort by Total</label>
                            <select name="order_by" class="form-control form-control-sm" style="border-radius: 8px; border: 1px solid #e2e8f0; font-size: 13px; color: #334155;">
                                <option value="desc" {{ request('order_by') == 'desc' ? 'selected' : '' }}>Highest to Lowest (Desc)</option>
                                <option value="asc" {{ request('order_by') == 'asc' ? 'selected' : '' }}>Lowest to Highest (Asc)</option>
                            </select>
                        </div>
                        <div class="col-12 d-flex justify-content-end align-items-center">
                            <a href="{{ route('owner.reports.selling-items') }}" class="btn btn-sm btn-light font-weight-bold px-3 mr-2" style="border-radius: 8px; color: #64748b; background: #f1f5f9;">Clear</a>
                            <button type="submit" class="btn btn-sm text-white font-weight-bold px-4" style="background: var(--primary-gradient); border-radius: 8px; border: none; box-shadow: 0 4px 10px rgba(236, 72, 153, 0.3);">
                                <i class="fas fa-filter mr-1"></i> Apply
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <div class="container-fluid pb-5" style="position: relative; z-index: 10;">
        <!-- List Container -->
        <div id="items-container">
            @include('owner.reports.partials.selling_items_list', ['items' => $items])
        </div>
        
        @if($totalItemsCount === 0)
            <div class="text-center py-5 mt-5">
                <i class="fas fa-box-open text-muted" style="font-size: 48px; opacity: 0.3; margin-bottom: 15px;"></i>
                <h5 class="font-weight-bold text-dark">No Data Found</h5>
                <p class="text-muted">There are no orders available to calculate selling items.</p>
            </div>
        @endif

        @if($hasMore)
            <div id="loading-indicator" class="text-center py-4">
                <div class="spinner-border text-pink" role="status" style="color: #ec4899; width: 2rem; height: 2rem;">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        @endif

        @if($items->isNotEmpty())
            <!-- Grand Total Summary Card -->
            <div class="item-card" style="background: var(--primary-gradient); color: white; border: none;">
                <h4 style="font-weight: 800; font-size: 16px; margin-bottom: 20px; opacity: 0.9;">
                    <i class="fas fa-chart-pie mr-2"></i> Overall Summary
                </h4>
                
                <div class="row text-center">
                    <div class="col-4 border-right" style="border-color: rgba(255,255,255,0.2) !important;">
                        <div style="font-size: 10px; font-weight: 700; opacity: 0.8; text-transform: uppercase;">Sales</div>
                        <div style="font-size: 18px; font-weight: 900;">{{ number_format($grandSales) }}</div>
                    </div>
                    <div class="col-4 border-right" style="border-color: rgba(255,255,255,0.2) !important;">
                        <div style="font-size: 10px; font-weight: 700; opacity: 0.8; text-transform: uppercase;">Agent</div>
                        <div style="font-size: 18px; font-weight: 900;">{{ number_format($grandAgent) }}</div>
                    </div>
                    <div class="col-4">
                        <div style="font-size: 10px; font-weight: 700; opacity: 0.8; text-transform: uppercase;">Total</div>
                        <div style="font-size: 18px; font-weight: 900;">{{ number_format($grandTotal) }}</div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>



@section('scripts')
<script>
    let page = 1;
    let isLoading = false;
    let hasMore = {{ $hasMore ? 'true' : 'false' }};

    window.addEventListener('scroll', function() {
        if (!hasMore || isLoading) return;
        
        const scrollPosition = window.innerHeight + window.scrollY;
        const scrollThreshold = document.body.offsetHeight - 400; // Load when 400px from bottom

        if (scrollPosition >= scrollThreshold) {
            loadMoreData();
        }
    });

    function loadMoreData() {
        isLoading = true;
        page++;
        
        let url = new URL(window.location.href);
        url.searchParams.set('page', page);
        
        fetch(url, {
            headers: {
                "X-Requested-With": "XMLHttpRequest"
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.html) {
                document.getElementById('items-container').insertAdjacentHTML('beforeend', data.html);
            }
            
            hasMore = data.hasMore;
            
            if (!hasMore) {
                const loader = document.getElementById('loading-indicator');
                if(loader) loader.style.display = 'none';
            }
            isLoading = false;
        })
        .catch(error => {
            console.error('Error fetching items:', error);
            isLoading = false;
        });
    }
</script>
@endsection
@endsection
