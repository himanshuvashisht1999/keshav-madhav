@extends('sales_agent.layouts.app', ['title' => 'Browse Inventory'])

@section('content')
<div class="container pb-5 mb-5 mt-3">
    <div class="mb-4 px-2">
        <h2 class="font-weight-bold h4 mb-1 text-dark">Browse Inventory</h2>
        <p class="text-muted small">Real-time stock availability across designs</p>
    </div>

    <!-- Filters Section (Collapsible for cleaner Mobile UI) -->
    <div class="px-2 mb-4">
        <div class="card shadow-sm border-0" style="border-radius: 16px; background: #fff;">
            <div class="card-body p-3">
                <form id="filterForm">
                    <div class="row align-items-end">
                        <div class="col-12 col-md-3 mb-3">
                            <label class="small font-weight-bold text-muted mb-1 uppercase tracking-wider">Search Design</label>
                            <div class="input-group bg-light rounded-pill px-2" style="border: 1px solid #eee;">
                                <div class="input-group-prepend border-0">
                                    <span class="input-group-text bg-transparent border-0 text-muted small"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="design_number_filter" class="form-control form-control-sm border-0 bg-transparent" placeholder="e.g. KM-101">
                            </div>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <label class="small font-weight-bold text-muted mb-1 uppercase tracking-wider">Product</label>
                            <select id="product_filter" class="form-control form-control-sm select2-simple">
                                <option value="">All Products</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->product_id }}">{{ $prod->product_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <label class="small font-weight-bold text-muted mb-1 uppercase tracking-wider">Size Set</label>
                            <select id="size_set_filter" class="form-control form-control-sm select2-simple">
                                <option value="">All Sets</option>
                                @foreach($size_sets as $set)
                                    <option value="{{ $set->size_set_id }}">{{ $set->size_set_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <label class="small font-weight-bold text-muted mb-1 uppercase tracking-wider">Color</label>
                            <select id="color_filter" class="form-control form-control-sm select2-simple">
                                <option value="">All Colors</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->color_id }}">{{ $color->color_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6 col-md-2 mb-3">
                            <button type="button" id="reset_filters" class="btn btn-light btn-block btn-sm rounded-pill text-muted font-weight-bold py-2">
                                <i class="fas fa-redo-alt mr-1"></i> Reset
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Card Grid -->
    <div class="row px-2" id="inventoryGrid">
        @foreach($inventories as $index => $row)
            @include('sales_agent.inventory.partials.row', ['row' => $row, 'index' => $index + 1])
        @endforeach
    </div>

    <!-- Infinite Scroll Loading Indicators -->
    <div id="loading-spinner" class="text-center py-4" style="display: none;">
        <div class="spinner-grow text-primary" role="status" style="width: 1.5rem; height: 1.5rem;">
            <span class="sr-only">Loading...</span>
        </div>
        <p class="small text-muted mt-2 font-weight-bold">Fetching more inventory...</p>
    </div>
    
    <div id="no-more-data" class="text-center py-4 text-muted small" style="{{ $inventories->hasMorePages() ? 'display: none;' : '' }}">
        <div class="opacity-50">
            <i class="fas fa-check-circle text-success mb-2" style="font-size: 1.5rem;"></i><br>
            All inventory loaded
        </div>
    </div>
</div>

<style>
    body { background-color: #f7f9fc; }
    .select2-container--default .select2-selection--single {
        border: 1px solid #eee !important;
        background-color: #f8f9fa !important;
        border-radius: 20px !important;
        height: 35px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 35px !important;
        font-size: 0.75rem !important;
        font-weight: 600 !important;
        padding-left: 12px !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 35px !important;
    }
    .uppercase { text-transform: uppercase; }
    .tracking-wider { letter-spacing: 0.05em; font-size: 0.65rem !important; }
    
    /* Animations */
    #inventoryGrid .col-12 {
        animation: fadeIn 0.4s ease-out forwards;
    }
    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }
</style>

@endsection

@section('scripts')
<script>
$(function() {
    // Initialize select2
    $('.select2-simple').select2({
        theme: 'bootstrap4',
        width: '100%'
    });

    let nextPage = {{ $inventories->nextPageUrl() ? 2 : 'null' }};
    let loading = false;
    const grid = $('#inventoryGrid');

    function loadMore(reset = false) {
        if (loading) return;
        if (!nextPage && !reset) return;

        loading = true;
        $('#loading-spinner').show();
        $('#no-more-data').hide();

        if (reset) {
            nextPage = 1;
            grid.css('opacity', '0.5');
        }

        $.ajax({
            url: "{{ route('agent.inventory.index') }}",
            type: "GET",
            data: {
                load_more: 1,
                page: nextPage,
                design_number: $('#design_number_filter').val(),
                product_id: $('#product_filter').val(),
                size_set_id: $('#size_set_filter').val(),
                color_id: $('#color_filter').val()
            },
            success: function(res) {
                if (reset) {
                    grid.empty().css('opacity', '1');
                }
                
                grid.append(res.html);
                nextPage = res.next_page;
                loading = false;
                $('#loading-spinner').hide();

                if (!nextPage) {
                    $('#no-more-data').show();
                }
                
                if (grid.is(':empty')) {
                    grid.append('<div class="col-12 text-center py-5 text-muted"><i class="fas fa-box-open mb-3 d-block fa-3x opacity-25"></i>No matching stock found.</div>');
                    $('#no-more-data').hide();
                }
            },
            error: function() {
                loading = false;
                $('#loading-spinner').hide();
                grid.css('opacity', '1');
                // toastr.error('Failed to load items.');
            }
        });
    }

    // Scroll listener for infinite scroll
    $(window).scroll(function() {
        if ($(window).scrollTop() + $(window).height() > $(document).height() - 300) {
            loadMore();
        }
    });

    // Filter triggers with debounce
    let typingTimer;
    $('#design_number_filter').on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(function() {
            loadMore(true);
        }, 500);
    });

    $('#product_filter, #size_set_filter, #color_filter').on('change', function() {
        loadMore(true);
    });

    $('#reset_filters').on('click', function() {
        $('#design_number_filter').val('');
        $('#product_filter, #size_set_filter, #color_filter').val('').trigger('change');
    });
});
</script>
@endsection