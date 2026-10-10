@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper erp-page p-2">
        <div class="container-fluid p-0">
            <!-- PAGE HEADER -->
            <div class="erp-header-bar mb-2">
                <div>
                    <h1 class="erp-page-title">Warehouse Stock After Order</h1>
                    <p class="erp-page-subtitle">Manage inventory physical locations and transfers (Stock after active order deductions)</p>
                </div>
                <div class="erp-header-actions d-flex align-items-center" style="gap: 8px;">
                    <div class="d-flex align-items-center bg-white px-2 py-1 rounded border shadow-sm">
                        <span class="text-muted mr-2 font-weight-bold" style="font-size: 11px;">Total boxes:</span>
                        <span class="font-weight-bold text-success" id="header_total_boxes" style="font-size: 13px;">0</span>
                    </div>
                    <div class="d-flex align-items-center bg-white px-2 py-1 rounded border shadow-sm">
                        <span class="text-muted mr-2 font-weight-bold" style="font-size: 11px;">Total pcs:</span>
                        <span class="font-weight-bold text-primary" id="header_total_pcs" style="font-size: 13px;">0</span>
                    </div>
                    <div class="dropdown d-inline-block">
                        <button class="btn-erp btn-erp-success dropdown-toggle" type="button" id="exportDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fas fa-download mr-1"></i> Export
                        </button>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="exportDropdown">
                            <a class="dropdown-item" href="#" id="export_pdf"><i class="fas fa-file-pdf text-danger mr-2"></i> Download PDF</a>
                            <a class="dropdown-item" href="#" id="export_excel"><i class="fas fa-file-excel text-success mr-2"></i> Download Excel</a>
                            <a class="dropdown-item" href="#" id="export_excel_price"><i class="fas fa-file-excel text-success mr-2"></i> Download Excel (With Price)</a>
                        </div>
                    </div>
                    <a href="{{ route('admin.inventory.warehouse_stock.history') }}" class="btn-erp btn-erp-outline">
                        <i class="fas fa-history mr-1"></i> Transfer History
                    </a>
                </div>
            </div>

            <!-- FILTERS CARD -->
            <div class="erp-card mb-2">
                <div class="erp-card-body p-2">
                    <div class="row align-items-end">
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Warehouse</label>
                            <select id="storeroom_filter" class="form-control select2">
                                <option value="">All Warehouses</option>
                                @foreach($storerooms as $storeroom)
                                    <option value="{{ $storeroom->id }}" {{ isset($defaultStoreroomId) && $defaultStoreroomId == $storeroom->id ? 'selected' : '' }}>{{ $storeroom->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Rack</label>
                            <select id="rack_filter" class="form-control select2">
                                <option value="">All Racks</option>
                                <!-- Populated via JS -->
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Series</label>
                            <select id="series_filter" class="form-control select2">
                                <option value="">All Series</option>
                                @foreach($series as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Brand</label>
                            <select id="brand_filter" class="form-control select2">
                                <option value="">All Brands</option>
                                @foreach($brands as $b)
                                    <option value="{{ $b->id }}">{{ $b->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Design no.</label>
                            <select id="design_filter" class="form-control select2">
                                <option value="">All Design Nos.</option>
                                @foreach($designs as $design)
                                    <option value="{{ $design->design_number }}">{{ $design->design_number }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Product</label>
                            <select id="product_filter" class="form-control select2">
                                <option value="">All Products</option>
                                @foreach($products as $prod)
                                    <option value="{{ $prod->id }}">{{ ($prod->series->name ?? '') . ' ' . $prod->name_of_garment }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Size set</label>
                            <select id="size_set_filter" class="form-control select2">
                                <option value="">All Size Sets</option>
                                @foreach($size_sets as $set)
                                    <option value="{{ $set->id }}">{{ $set->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Fitting</label>
                            <select id="fitting_filter" class="form-control select2">
                                <option value="">All Fittings</option>
                                @foreach($fittings as $f)
                                    <option value="{{ $f->id }}">{{ $f->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Pattern</label>
                            <select id="pattern_filter" class="form-control select2">
                                <option value="">All Patterns</option>
                                @foreach($patterns as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Product nature</label>
                            <select id="nature_filter" class="form-control select2">
                                <option value="">All Natures</option>
                                @foreach($natures as $n)
                                    <option value="{{ $n->id }}">{{ $n->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Fabric type</label>
                            <select id="fabric_type_filter" class="form-control select2">
                                <option value="">All Fabric Types</option>
                                @foreach($fabric_types as $ft)
                                    <option value="{{ $ft->id }}">{{ $ft->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Min boxes</label>
                            <input type="number" id="min_boxes_filter" class="form-control" placeholder="Min Qty" min="0" value="1">
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="erp-label">Max boxes</label>
                            <input type="number" id="max_boxes_filter" class="form-control" placeholder="Max Qty" min="0">
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="erp-label">MRP from</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" id="mrp_from_filter" class="form-control" placeholder="Min MRP" min="0" step="any">
                            </div>
                        </div>

                        <div class="col-md-2 mb-2">
                            <label class="erp-label">MRP to</label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text">₹</span>
                                </div>
                                <input type="number" id="mrp_to_filter" class="form-control" placeholder="Max MRP" min="0" step="any">
                            </div>
                        </div>
                        
                        <div class="col-md-1 mb-2">
                            <button id="reset_filters" class="btn-erp btn-erp-secondary w-100" title="Reset Filters" style="height: 33px;">
                                <i class="fas fa-undo"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="erp-card">
                <div class="erp-card-body p-0">
                    <div class="table-responsive">
                        <table id="inventoryTable" class="erp-table table-bordered table-hover mb-0">
                            <thead>
                                <tr>
                                    <th style="width: 45px;">#</th>
                                    <th class="text-center" style="width: 55px;">Image</th>
                                    <th>Product name</th>
                                    <th>Design no.</th>
                                    <th>Size set</th>
                                    <th>Location (WH / Rack)</th>
                                    <th class="text-center">Total boxes</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-center">MRP</th>
                                    <th class="text-center" style="width: 70px;">Action</th>
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
    </div>

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

    <script>
        $(function () {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            const container = $('#inventoryTable tbody');
            let nextPage = 1;
            let loading = false;

            function loadMore(reset = false) {
                if (loading) return;
                if (!nextPage && !reset) {
                    $('#no-more-data').show();
                    return;
                }

                loading = true;
                $('#loading-spinner').show();
                $('#no-more-data').hide();

                if (reset) {
                    nextPage = 1;
                    container.css('opacity', '0.5');
                }

                $.ajax({
                    url: "{{ route('admin.inventory.warehouse_stock.list') }}",
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
                        max_boxes: $('#max_boxes_filter').val(),
                        mrp_from: $('#mrp_from_filter').val(),
                        mrp_to: $('#mrp_to_filter').val()
                    },
                    success: function(res) {
                        if (reset) {
                            container.empty().css('opacity', '1');
                        }
                        
                        // Update totals
                        if (res.total_boxes !== undefined) {
                            $('#header_total_boxes').text(res.total_boxes.toLocaleString());
                        }
                        if (res.total_pcs !== undefined) {
                            $('#header_total_pcs').text(res.total_pcs.toLocaleString());
                        }

                        container.append(res.html);
                        nextPage = res.next_page;
                        loading = false;
                        $('#loading-spinner').hide();

                        if (!nextPage) {
                            $('#no-more-data').show();
                        }
                        
                        if (container.is(':empty')) {
                            container.append('<tr><td colspan="10" class="text-center py-5 text-muted">No inventory records found.</td></tr>');
                            $('#no-more-data').hide();
                        }
                    },
                    error: function() {
                        loading = false;
                        $('#loading-spinner').hide();
                        container.css('opacity', '1');
                        toastr.error('Failed to load inventory.');
                    }
                });
            }

            // Fetch racks if a default warehouse is selected
            if ($('#storeroom_filter').val()) {
                let wh_id = $('#storeroom_filter').val();
                let rack_filter = $('#rack_filter');
                rack_filter.html('<option value="">All Racks</option>');
                $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh_id, function(data) {
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
            $('#min_boxes_filter, #max_boxes_filter, #mrp_from_filter, #mrp_to_filter').on('input', function () {
                clearTimeout(filterTimer);
                filterTimer = setTimeout(function() {
                    loadMore(true);
                }, 600);
            });

            // Reset filter
            $('#reset_filters').on('click', function () {
                $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #series_filter, #brand_filter, #fitting_filter, #pattern_filter, #nature_filter, #fabric_type_filter').val('').trigger('change');
                $('#min_boxes_filter').val('1');
                $('#max_boxes_filter').val('');
                $('#mrp_from_filter').val('');
                $('#mrp_to_filter').val('');
                loadMore(true);
            });

            // Filter Dynamic Racks
            $('#storeroom_filter').on('change', function() {
                let wh_id = $(this).val();
                let rack_filter = $('#rack_filter');
                rack_filter.html('<option value="">All Racks</option>');
                if(wh_id) {
                    $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh_id, function(data) {
                        $.each(data, function(i, rack) {
                            rack_filter.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                        });
                    });
                }
            });

            // Export logic
            function getExportUrl(type) {
                let url = '{{ route("admin.inventory.warehouse_stock.export") }}?type=' + type;
                url += '&storeroom_id=' + ($('#storeroom_filter').val() || '');
                url += '&rack_id=' + ($('#rack_filter').val() || '');
                url += '&size_set_id=' + ($('#size_set_filter').val() || '');
                url += '&design_filter=' + ($('#design_filter').val() || '');
                url += '&product_id=' + ($('#product_filter').val() || '');
                url += '&series_id=' + ($('#series_filter').val() || '');
                url += '&brand_id=' + ($('#brand_filter').val() || '');
                url += '&fitting_id=' + ($('#fitting_filter').val() || '');
                url += '&pattern_id=' + ($('#pattern_filter').val() || '');
                url += '&nature_id=' + ($('#nature_filter').val() || '');
                url += '&fabric_type_id=' + ($('#fabric_type_filter').val() || '');
                url += '&min_boxes=' + ($('#min_boxes_filter').val() || '');
                url += '&max_boxes=' + ($('#max_boxes_filter').val() || '');
                url += '&mrp_from=' + ($('#mrp_from_filter').val() || '');
                url += '&mrp_to=' + ($('#mrp_to_filter').val() || '');
                return url;
            }

            $('#export_pdf').on('click', function(e) {
                e.preventDefault();
                window.location.href = getExportUrl('pdf');
            });

            $('#export_excel').on('click', function(e) {
                e.preventDefault();
                window.location.href = getExportUrl('excel');
            });

            // Handle Export Price
            $('#export_excel_price').click(function(e) {
                e.preventDefault();
                window.location.href = getExportUrl('excel_price');
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