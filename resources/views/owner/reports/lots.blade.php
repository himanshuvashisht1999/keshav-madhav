@extends('owner.layouts.app')

@section('title', 'Production Lots')

@section('styles')
<style>
    :root {
        --accent-glow: 0 10px 30px rgba(245, 158, 11, 0.15);
    }

    body {
        background: #fffcf5;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 40px 20px 60px;
        border-radius: 0 0 40px 40px;
        color: white;
        margin-bottom: -30px;
        position: relative;
    }

    .app-header h1 {
        font-size: 26px;
        font-weight: 900;
        letter-spacing: -0.5px;
    }

    .filter-section {
        position: relative;
        z-index: 10;
        padding: 0 20px;
    }

    .filter-card {
        background: white;
        border-radius: 24px;
        padding: 20px;
        box-shadow: 0 12px 40px rgba(0,0,0,0.06);
        border: 1px solid rgba(0,0,0,0.02);
    }

    .lot-card {
        background: white;
        border-radius: 24px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.03);
        border: 1px solid #fff4e5;
        transition: transform 0.2s;
        display: block;
        text-decoration: none !important;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .lot-card::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 6px;
        background: var(--primary-gradient);
        opacity: 0.3;
    }

    .lot-id {
        font-size: 18px;
        font-weight: 900;
        color: var(--text-main);
        margin-bottom: 5px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .qty-tag {
        background: #fef3c7;
        color: #92400e;
        padding: 4px 12px;
        border-radius: 12px;
        font-size: 13px;
        font-weight: 800;
    }

    .order-info {
        font-size: 13px;
        color: var(--text-muted);
        font-weight: 600;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .customer-name {
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        padding: 10px;
        background: #f8fafc;
        border-radius: 12px;
        display: block;
    }

    .action-link {
        margin-top: 15px;
        text-align: right;
        font-weight: 800;
        font-size: 13px;
        color: var(--text-main);
        display: flex;
        align-items: center;
        justify-content: flex-end;
        gap: 5px;
    }

    .form-label-custom {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 8px;
        display: block;
    }

    .select2-container--default .select2-selection--single {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        height: 45px;
        padding: 8px;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('owner.dashboard') }}" class="text-white opacity-75"><i class="fas fa-home"></i></a>
            <span class="font-weight-bold opacity-50" style="font-size: 12px;">REPORT RJ 4</span>
        </div>
        <h1>Production Lots</h1>
        <p class="mb-0 opacity-75">Tracking batch status & progress</p>
    </div>

    <div class="filter-section">
        <div class="filter-card">
            <form method="GET" action="{{ route('owner.lots') }}">
                <div class="row g-2">
                    <div class="col-12 mb-3">
                        <label class="form-label-custom">Order Number</label>
                        <select name="order_id" id="order_no" class="form-control select2" onchange="changeOrderId(this.value)">
                            <option value="">All Orders</option>
                            @foreach(collect($lotNos)->unique('order_id') as $row)
                                <option value="{{ $row['order_id'] }}" {{ request('order_id') == $row['order_id'] ? 'selected' : '' }}>
                                    {{ $row['order_no'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label-custom">Lot Number</label>
                        <select name="lot_no" id="lot_no" class="form-control select2">
                            <option value="">All Lots</option>
                            @foreach($lotNos as $row)
                                <option value="{{ $row['lot_no'] }}" {{ request('lot_no') == $row['lot_no'] ? 'selected' : '' }}>
                                    {{ $row['lot_no'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="d-flex gap-2 mt-2">
                    <button type="submit" class="btn btn-warning btn-block py-3 font-weight-bold text-white" style="border-radius: 14px; flex: 2; background: var(--primary-gradient); border: none;">
                        Search Lots
                    </button>
                    <a href="{{ route('owner.lots') }}" class="btn btn-light py-3 px-4" style="border-radius: 14px; flex: 1;">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <div class="container-fluid mt-5 pb-5">
        <div class="d-flex justify-content-between align-items-center mb-4 px-2">
            <span class="text-muted font-weight-bold" style="font-size: 13px;">{{ $data->total() }} ACTIVE LOTS</span>
        </div>

        <div id="lots-container">
            @include('owner.reports.partials.lots_list', ['data' => $data])
        </div>

        @if($data->isEmpty())
            <div class="text-center py-5 opacity-25">
                <i class="fas fa-layer-group fa-4x mb-3"></i>
                <p class="h5 font-weight-bold">No active lots found</p>
            </div>
        @endif

        <div id="loading-spinner" style="display: none; text-align: center; padding: 20px;">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
        <div id="load-more-trigger" style="height: 10px;"></div>
    </div>
</div>



<script>
    const lotData = @json($lotNos);
    const lotSelect = $('#lot_no');

    function unique(arr) { return [...new Set(arr)]; }

    function fillLotDropdown(lots) {
        lotSelect.empty().append(`<option value="">All Lots</option>`);
        lots.forEach(lot => {
            lotSelect.append(`<option value="${lot}">${lot}</option>`);
        });
        lotSelect.trigger('change');
    }

    function changeOrderId(selectedOrderId) {
        if (!selectedOrderId) {
            const allLots = unique(lotData.map(i => i.lot_no));
            fillLotDropdown(allLots);
            return;
        }
        const filteredLots = unique(lotData.filter(i => i.order_id == selectedOrderId).map(i => i.lot_no));
        fillLotDropdown(filteredLots);
    }

    $(document).ready(function() {
        if ($.fn.select2) {
            $('.select2').select2();
        }

        let currentPage = {{ $data->currentPage() }};
        let nextPage = {{ $data->hasMorePages() ? $data->currentPage() + 1 : 'null' }};
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
            $('#loading-spinner').show();
            
            let currentUrl = new URL(window.location.href);
            currentUrl.searchParams.set('page', nextPage);

            $.ajax({
                url: currentUrl.toString(),
                type: 'GET',
                success: function(response) {
                    $('#lots-container').append(response.html);
                    nextPage = response.next_page;
                    isLoading = false;
                    $('#loading-spinner').hide();
                    
                    if (nextPage === null) {
                        observer.unobserve(trigger);
                    }
                },
                error: function() {
                    isLoading = false;
                    $('#loading-spinner').hide();
                }
            });
        }
    });
</script>
@endsection
