@extends('owner.layouts.app')

@section('title', 'Bank & Cash Ledger')

@section('styles')
<style>
    :root {
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
        margin-bottom: 12px;
    }

    .search-box input, .search-box select {
        border: none;
        outline: none;
        width: 100%;
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        background: transparent;
    }

    .filter-select-wrapper {
        border-left: 1px solid #e2e8f0;
        padding-left: 12px;
        min-width: 130px;
    }

    .ledger-card {
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

    .ledger-card:active {
        transform: scale(0.98);
    }

    .party-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 15px;
    }

    .party-name {
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-bottom: 4px;
    }

    .party-phone {
        font-size: 12px;
        color: #64748b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    .type-badge {
        font-size: 10px;
        padding: 5px 10px;
        border-radius: 10px;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    /* Vibrant Type Colors */
    .party-type-customer { background: #e0e7ff; color: #4338ca; }
    .party-type-vendor { background: #ffedd5; color: #c2410c; }
    .party-type-sales_agent { background: #fce7f3; color: #be185d; }
    .party-type-default { background: #f1f5f9; color: #475569; }

    .balance-box {
        background: #f8fafc;
        border-radius: 16px;
        padding: 15px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .balance-label {
        font-size: 11px;
        color: #64748b;
        text-transform: uppercase;
        font-weight: 800;
        letter-spacing: 0.5px;
        margin-bottom: 2px;
    }

    .balance-amount {
        font-size: 20px;
        font-weight: 900;
    }

    .balance-dr { color: #dc2626; }
    .balance-cr { color: #16a34a; }

    .cr-dr-badge {
        font-size: 11px;
        font-weight: 800;
        padding: 2px 6px;
        border-radius: 6px;
        margin-left: 6px;
    }

    .badge-dr { background: #fee2e2; color: #dc2626; }
    .badge-cr { background: #dcfce7; color: #16a34a; }

    .empty-state {
        padding: 60px 20px;
        text-align: center;
        color: #94a3b8;
    }

    .empty-state i {
        font-size: 48px;
        margin-bottom: 16px;
        opacity: 0.3;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="breadcrumb-custom">
            <a href="{{ route('owner.dashboard') }}">Home</a>
            <i class="fas fa-chevron-right" style="font-size: 8px;"></i>
            <span>{{ $pageTitle ?? 'Bank & Cash Ledger' }}</span>
        </div>
        <h1>{{ $pageTitle ?? 'Bank & Cash Ledger' }}</h1>
        @if(isset($pageSubtitle))
            <p style="font-size: 13px; opacity: 0.9; margin: 0;">{{ $pageSubtitle }}</p>
        @endif
    </div>

    @if(!isset($pageTitle))
    <div class="search-container">
        <form action="{{ route('owner.bank-cash-ledger.index') }}" method="GET" id="filterForm">
            <div class="search-box">
                <i class="fas fa-search text-muted"></i>
                <input type="text" name="search" placeholder="Search party..." value="{{ request('search') }}" onchange="document.getElementById('filterForm').submit()">
                
                <div class="filter-select-wrapper">
                    <select name="type_id" onchange="document.getElementById('filterForm').submit()">
                        <option value="">All Types</option>
                        @foreach($masters as $m)
                            <option value="{{ $m->id }}" {{ request('type_id') == $m->id ? 'selected' : '' }}>{{ $m->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </form>
    </div>
    @endif

    <div class="container-fluid mt-4 pb-5">
        <div id="ledger-container">
            @include('owner.bank-cash-ledger.partials.ledger_list', ['parties' => $parties])
        </div>
        
        <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
        <div id="load-more-trigger" style="height: 10px;"></div>
    </div>
</div>


@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        let currentPage = {{ $parties->currentPage() }};
        let nextPage = {{ $parties->hasMorePages() ? $parties->currentPage() + 1 : 'null' }};
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
                const container = document.getElementById('ledger-container');
                container.insertAdjacentHTML('beforeend', data.html);
                
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
