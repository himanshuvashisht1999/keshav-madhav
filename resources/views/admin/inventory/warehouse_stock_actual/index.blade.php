@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                    <!-- Left: Title -->
                    <div>
                        <h1 class="m-0 font-weight-bold text-dark">Warehouse Stock Actual</h1>
                        <small class="text-muted">Real physical stock from domestic inventory (not dispatched, unreserved)</small>
                    </div>

                    <!-- Center: Totals -->
                    <div class="d-flex">
                        <div class="card shadow-sm border-0 bg-primary text-white mb-0" style="border-radius: 12px; min-width: 200px;">
                            <div class="card-body p-2 d-flex align-items-center">
                                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-boxes fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-white-50" style="font-size: 13px;">Actual Total Boxes</h6>
                                    <h5 class="mb-0 font-weight-bold" id="header_total_boxes">0</h5>
                                </div>
                            </div>
                        </div>
                        <div class="card shadow-sm border-0 bg-success text-white mb-0 ml-3" style="border-radius: 12px; min-width: 200px;">
                            <div class="card-body p-2 d-flex align-items-center">
                                <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 40px; height: 40px;">
                                    <i class="fas fa-tshirt fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-white-50" style="font-size: 13px;">Actual Total Pcs</h6>
                                    <h5 class="mb-0 font-weight-bold" id="header_total_pcs">0</h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Action Button -->
                    <div>
                        <div class="dropdown d-inline-block">
                            <button class="btn btn-outline-success shadow-sm dropdown-toggle mr-2" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <i class="fas fa-download mr-1"></i> Export
                            </button>
                            <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                                <a class="dropdown-item" href="#" id="export_pdf"><i class="fas fa-file-pdf text-danger mr-2"></i> Download PDF</a>
                                <a class="dropdown-item" href="#" id="export_excel"><i class="fas fa-file-excel text-success mr-2"></i> Download Excel</a>
                                <a class="dropdown-item" href="#" id="export_excel_price"><i class="fas fa-file-excel text-success mr-2"></i> Download Excel (With Price)</a>
                            </div>
                        </div>
                        <a href="{{ route('admin.inventory.warehouse_stock') }}" class="btn btn-outline-secondary shadow-sm" title="Go to Stock After Order">
                            <i class="fas fa-exchange-alt mr-1"></i> Stock After Order
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">

                <!-- SINGLE CONSOLIDATED FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <div class="row align-items-end">
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Warehouse</label>
                                <select id="storeroom_filter" class="form-control select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($storerooms as $storeroom)
                                        <option value="{{ $storeroom->id }}" {{ isset($defaultStoreroomId) && $defaultStoreroomId == $storeroom->id ? 'selected' : '' }}>{{ $storeroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Rack</label>
                                <select id="rack_filter" class="form-control select2">
                                    <option value="">All Racks</option>
                                    <!-- Populated via JS -->
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Series</label>
                                <select id="series_filter" class="form-control select2">
                                    <option value="">All Series</option>
                                    @foreach($series as $s)
                                        <option value="{{ $s->id }}">{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Brand</label>
                                <select id="brand_filter" class="form-control select2">
                                    <option value="">All Brands</option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b->id }}">{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Design No.</label>
                                <select id="design_filter" class="form-control select2">
                                    <option value="">All Design Nos.</option>
                                    @foreach($designs as $design)
                                        <option value="{{ $design->design_number }}">{{ $design->design_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Product</label>
                                <select id="product_filter" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->id }}">{{ ($prod->series->name ?? '') . ' ' . $prod->name_of_garment }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                                <select id="size_set_filter" class="form-control select2">
                                    <option value="">All Size Sets</option>
                                    @foreach($size_sets as $set)
                                        <option value="{{ $set->id }}">{{ $set->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Fitting</label>
                                <select id="fitting_filter" class="form-control select2">
                                    <option value="">All Fittings</option>
                                    @foreach($fittings as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Pattern</label>
                                <select id="pattern_filter" class="form-control select2">
                                    <option value="">All Patterns</option>
                                    @foreach($patterns as $p)
                                        <option value="{{ $p->id }}">{{ $p->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Product Nature</label>
                                <select id="nature_filter" class="form-control select2">
                                    <option value="">All Natures</option>
                                    @foreach($natures as $n)
                                        <option value="{{ $n->id }}">{{ $n->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Fabric Type</label>
                                <select id="fabric_type_filter" class="form-control select2">
                                    <option value="">All Fabric Types</option>
                                    @foreach($fabric_types as $ft)
                                        <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Min Boxes</label>
                                <input type="number" id="min_boxes_filter" class="form-control" placeholder="Min Qty" min="0" value="1">
                            </div>

                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Max Boxes</label>
                                <input type="number" id="max_boxes_filter" class="form-control" placeholder="Max Qty" min="0">
                            </div>
                            
                            <div class="col-md-1 mb-3">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block" title="Reset Filters">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- TABLE CARD -->
                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="inventoryTable" class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th class="py-3">#</th>
                                        <th class="py-3 text-center">Image</th>
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Design No.</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Location (WH / Rack)</th>
                                        <th class="py-3 text-center">Actual Boxes</th>
                                        <th class="py-3 text-center">Total Pcs</th>
                                        <th class="py-3 text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div id="loading-spinner" class="text-center py-3" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="sr-only">Loading...</span>
                            </div>
                        </div>
                        <div id="no-more-data" class="text-center py-3 text-muted small" style="display: none;">
                            No more records to load.
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <style>
        /* Image Gallery Modal Styles */
        #main-gallery-image {
            width: 100%;
            height: auto;
            max-height: 400px;
            object-fit: contain;
            border-radius: 8px;
            border: 1px solid #ddd;
        }
        .thumbnail-strip {
            display: flex;
            gap: 10px;
            overflow-x: auto;
            padding: 10px 0;
            scrollbar-width: thin;
        }
        .thumbnail-strip img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            cursor: pointer;
            border: 2px solid transparent;
            transition: border-color 0.2s;
        }
        .thumbnail-strip img:hover, .thumbnail-strip img.active {
            border-color: #0f62fe;
        }
    </style>

    <!-- Image Gallery Modal -->
    <div class="modal fade" id="imageGalleryModal" tabindex="-1" role="dialog" aria-labelledby="imageGalleryModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="imageGalleryModalLabel">Variant Images</h5>
            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
            </button>
          </div>
          <div class="modal-body text-center">
            <!-- Main Big Image -->
            <div class="mb-3">
                <a id="main-gallery-link" href="#" target="_blank">
                    <img id="main-gallery-image" src="" alt="Variant Main Image">
                </a>
            </div>
            <!-- Thumbnails Strip -->
            <div class="thumbnail-strip justify-content-center" id="gallery-thumbnails">
                <!-- Thumbnails will be injected here via JS -->
            </div>
          </div>
        </div>
      </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function () {
            // Initialize select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            let nextPage = 1;
            let loading = false;
            let container = $('#inventoryTable tbody');

            function loadMore(reset = false) {
                if (loading) return;
                loading = true;
                $('#loading-spinner').show();
                $('#no-more-data').hide();

                if (reset) {
                    nextPage = 1;
                    container.css('opacity', '0.5');
                }

                $.ajax({
                    url: "{{ route('admin.inventory.warehouse_stock_actual.list') }}",
                    type: "GET",
                    data: {
                        load_more: 1,
                        page: nextPage,
                        storeroom_id: $('#storeroom_filter').val(),
                        rack_id: $('#rack_filter').val(),
                        size_set_id: $('#size_set_filter').val(),
                        design_filter: $('#design_filter').val(),
                        product_id: $('#product_filter').val(),
                        series_id: $('#series_filter').val(),
                        brand_id: $('#brand_filter').val(),
                        fitting_id: $('#fitting_filter').val(),
                        pattern_id: $('#pattern_filter').val(),
                        nature_id: $('#nature_filter').val(),
                        fabric_type_id: $('#fabric_type_filter').val(),
                        min_boxes: $('#min_boxes_filter').val(),
                        max_boxes: $('#max_boxes_filter').val()
                    },
                    success: function(res) {
                        if (reset) {
                            container.empty().css('opacity', '1');
                        }
                        
                        // Update totals
                        if (res.total_boxes !== undefined) {
                            $('#header_total_boxes').text(Number(res.total_boxes).toLocaleString());
                        }
                        if (res.total_pcs !== undefined) {
                            $('#header_total_pcs').text(Number(res.total_pcs).toLocaleString());
                        }

                        container.append(res.html);
                        nextPage = res.next_page;
                        loading = false;
                        $('#loading-spinner').hide();

                        if (!nextPage) {
                            $('#no-more-data').show();
                        }
                        
                        if (container.is(':empty')) {
                            container.append('<tr><td colspan="9" class="text-center py-5 text-muted">No actual inventory records found.</td></tr>');
                            $('#no-more-data').hide();
                        }
                    },
                    error: function() {
                        loading = false;
                        $('#loading-spinner').hide();
                        container.css('opacity', '1');
                        toastr.error('Failed to load actual inventory.');
                    }
                });
            }

            // Fetch racks if a default warehouse is selected
            if ($('#storeroom_filter').val()) {
                let wh_id = $('#storeroom_filter').val();
                let rack_filter = $('#rack_filter');
                rack_filter.html('<option value="">All Racks</option>');
                $.get('{{ url("admin/inventory/warehouse-stock-actual/racks") }}/' + wh_id, function(data) {
                    $.each(data, function(i, rack) {
                        rack_filter.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                    });
                });
            }

            // Initial Load
            loadMore();

            // Scroll Event
            $('.content-wrapper').on('scroll', function() {
                if (loading || !nextPage) return;

                let scrollTop = $(this).scrollTop();
                let innerHeight = $(this).innerHeight();
                let scrollHeight = $(this)[0].scrollHeight;

                if (scrollTop + innerHeight >= scrollHeight - 300) {
                    loadMore();
                }
            });

            // Filter events
            $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #series_filter, #brand_filter, #fitting_filter, #pattern_filter, #nature_filter, #fabric_type_filter').on('change', function () {
                loadMore(true);
            });

            // Delay on typing numbers so it doesn't trigger on every keystroke too quickly
            let filterTimer;
            $('#min_boxes_filter, #max_boxes_filter').on('input', function () {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(() => {
                    loadMore(true);
                }, 400);
            });

            // Populate racks on storeroom change
            $('#storeroom_filter').on('change', function() {
                let storeroom_id = $(this).val();
                let rack_filter = $('#rack_filter');
                rack_filter.html('<option value="">All Racks</option>');
                if (storeroom_id) {
                    $.get('{{ url("admin/inventory/warehouse-stock-actual/racks") }}/' + storeroom_id, function(data) {
                        $.each(data, function(i, rack) {
                            rack_filter.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                        });
                        rack_filter.trigger('change');
                    });
                } else {
                    rack_filter.trigger('change');
                }
            });

            // Reset filters
            $('#reset_filters').on('click', function () {
                $('#storeroom_filter').val('').trigger('change');
                $('#rack_filter').val('').trigger('change');
                $('#size_set_filter').val('').trigger('change');
                $('#design_filter').val('').trigger('change');
                $('#product_filter').val('').trigger('change');
                $('#series_filter').val('').trigger('change');
                $('#brand_filter').val('').trigger('change');
                $('#fitting_filter').val('').trigger('change');
                $('#pattern_filter').val('').trigger('change');
                $('#nature_filter').val('').trigger('change');
                $('#fabric_type_filter').val('').trigger('change');
                $('#min_boxes_filter').val('1');
                $('#max_boxes_filter').val('');
                loadMore(true);
            });

            // Helper to serialize all filter inputs
            function getFilterData() {
                return $.param({
                    storeroom_id: $('#storeroom_filter').val(),
                    rack_id: $('#rack_filter').val(),
                    size_set_id: $('#size_set_filter').val(),
                    design_filter: $('#design_filter').val(),
                    product_id: $('#product_filter').val(),
                    series_id: $('#series_filter').val(),
                    brand_id: $('#brand_filter').val(),
                    fitting_id: $('#fitting_filter').val(),
                    pattern_id: $('#pattern_filter').val(),
                    nature_id: $('#nature_filter').val(),
                    fabric_type_id: $('#fabric_type_filter').val(),
                    min_boxes: $('#min_boxes_filter').val(),
                    max_boxes: $('#max_boxes_filter').val()
                });
            }

            // Export Actions
            function handleExport(type) {
                let url = '{{ route("admin.inventory.warehouse_stock_actual.export") }}?type=' + type;
                let filters = getFilterData();
                if (filters) {
                    url += '&' + filters;
                }
                window.location.href = url;
            }

            $('#export_pdf').on('click', function (e) {
                e.preventDefault();
                handleExport('pdf');
            });

            $('#export_excel').on('click', function (e) {
                e.preventDefault();
                handleExport('excel');
            });

            $('#export_excel_price').on('click', function (e) {
                e.preventDefault();
                let url = "{{ route('admin.inventory.warehouse_stock_actual.export') }}?" + getFilterData() + '&type=excel_price';
                window.location.href = url;
            });

            // Image Gallery Modal Logic
            $(document).on('click', '.view-gallery', function(e) {
                e.preventDefault();
                let variantId = $(this).data('id');
                if(!variantId) return;

                // fetch images
                $.ajax({
                    url: "{{ url('admin/inventory/get-variant-images') }}/" + variantId,
                    type: 'GET',
                    success: function(res) {
                        if(res.success && res.images.length > 0) {
                            let images = res.images;
                            
                            // Set first image as main
                            $('#main-gallery-image').attr('src', images[0].url);
                            $('#main-gallery-link').attr('href', images[0].url);
                            
                            // Build thumbnails
                            let thumbHtml = '';
                            images.forEach(function(img, index) {
                                let activeClass = index === 0 ? 'active' : '';
                                thumbHtml += `<div style="text-align: center;">
                                                <img src="${img.url}" class="gallery-thumb ${activeClass}" data-full="${img.url}" alt="thumbnail">
                                                <small class="d-block text-muted mt-1" style="font-size: 10px;">${img.color}</small>
                                              </div>`;
                            });
                            $('#gallery-thumbnails').html(thumbHtml);
                            
                            $('#imageGalleryModal').modal('show');
                        } else {
                            alert('No images found for this variant.');
                        }
                    },
                    error: function() {
                        alert('Failed to load images.');
                    }
                });
            });

            // Thumbnail click logic
            $(document).on('click', '.gallery-thumb', function() {
                let fullImg = $(this).data('full');
                $('#main-gallery-image').attr('src', fullImg);
                $('#main-gallery-link').attr('href', fullImg);
                
                // Update active state
                $('.gallery-thumb').removeClass('active');
                $(this).addClass('active');
            });
        });
    </script>
@endsection
