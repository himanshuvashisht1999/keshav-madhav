@extends('owner.layouts.app')

@section('title', 'Ready Stock Inventory')

@section('styles')
<style>
    :root {
    }

    body {
        background: #f0f9ff;
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
        border: 1px solid rgba(14, 165, 233, 0.1);
    }

    .form-label-custom {
        font-size: 11px;
        font-weight: 800;
        text-transform: uppercase;
        color: var(--text-muted);
        margin-bottom: 8px;
        display: block;
    }

    .custom-input {
        border-radius: 12px !important;
        height: 48px !important;
        border: 1px solid #e2e8f0 !important;
        font-weight: 600;
        padding: 0 15px !important;
    }

    .results-container {
        padding: 40px 20px 100px;
    }

    .inventory-card {
        background: white;
        border-radius: 24px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.03);
        border: 1px solid #f0f9ff;
        display: block;
        text-decoration: none !important;
        color: inherit;
        position: relative;
        overflow: hidden;
    }

    .inventory-card::after {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        bottom: 0;
        width: 5px;
        background: var(--primary-gradient);
        opacity: 0.5;
    }

    .product-name {
        font-size: 16px;
        font-weight: 800;
        color: var(--text-main);
        margin-bottom: 4px;
    }

    .design-badge {
        font-size: 11px;
        font-weight: 700;
        background: #f1f5f9;
        color: var(--text-main);
        padding: 4px 10px;
        border-radius: 8px;
        margin-bottom: 12px;
        display: inline-block;
    }

    .meta-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #e2e8f0;
    }

    .meta-item label {
        font-size: 10px;
        color: var(--text-muted);
        font-weight: 800;
        text-transform: uppercase;
        display: block;
        margin-bottom: 2px;
    }

    .meta-item span {
        font-size: 13px;
        font-weight: 700;
        color: var(--text-main);
    }

    .stock-badge {
        position: absolute;
        top: 20px;
        right: 20px;
        text-align: right;
    }

    .boxes-val {
        font-size: 20px;
        font-weight: 900;
        color: var(--text-main);
        display: block;
    }

    .boxes-label {
        font-size: 10px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
    }

    .mrp-tag {
        background: #f0fdf4;
        color: #16a34a;
        font-size: 11px;
        font-weight: 800;
        padding: 4px 10px;
        border-radius: 10px;
        margin-top: 10px;
        display: inline-block;
    }
</style>
@endsection

@section('content')
<div class="responsive-app-view">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('owner.dashboard') }}" class="text-white opacity-75"><i class="fas fa-home"></i></a>
            <span class="font-weight-bold opacity-50" style="font-size: 12px;">READY STOCK</span>
        </div>
        <h1>Inventory</h1>
        <p class="mb-0 opacity-75">Live domestic finished goods stock</p>
    </div>

    <div class="filter-section">
        <div class="filter-card">
            <form id="filterForm">
                <div class="mb-3">
                    <label class="form-label-custom">Design Number</label>
                    <input type="text" name="design_number" class="form-control custom-input" placeholder="Search Design #">
                </div>
                <div class="row">
                    <div class="col-12 mb-3">
                        <label class="form-label-custom">Product</label>
                        <select name="product_id" class="form-control custom-input select2">
                            <option value="">All Products</option>
                            @foreach($products as $p)
                                <option value="{{ $p->product_id }}">{{ $p->product_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label-custom">Size Set</label>
                        <select name="size_set_id" class="form-control custom-input select2">
                            <option value="">All Size Sets</option>
                            @foreach($size_sets as $s)
                                <option value="{{ $s->size_set_id }}">{{ $s->size_set_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <button type="submit" class="btn btn-info btn-block py-3 font-weight-bold text-white shadow-sm" style="border-radius: 14px; background: var(--primary-gradient); border: none;">
                    Check Availability
                </button>
            </form>
        </div>
    </div>

    <div id="results" class="results-container">
        <!-- Results will load here -->
        <div class="text-center py-5 opacity-25">
            <i class="fas fa-boxes fa-4x mb-3"></i>
            <p class="h5 font-weight-bold">Search to see stock</p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        function loadResults(page = 1) {
            let formData = $('#filterForm').serialize();
            $('#results').html('<div class="text-center py-5"><div class="spinner-border text-info"></div></div>');
            
            $.get("{{ route('owner.ready-stock.list') }}?" + formData + "&page=" + page, function(html) {
                $('#results').html(html);
            });
        }

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadResults();
        });

        // Load initial
        loadResults();

        $(document).on('click', '.pagination a', function(e) {
            e.preventDefault();
            let page = $(this).attr('href').split('page=')[1];
            loadResults(page);
        });
    });
</script>
@endpush
