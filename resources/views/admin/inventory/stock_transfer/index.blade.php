@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Stock Transfer</h1>
                        <small class="text-muted">Bulk transfer inventory between warehouses and racks</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <!-- STEP 1: SEARCH SOURCE STOCK -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-header bg-white border-0 py-3">
                        <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-search mr-2"></i> Step 1: Find Source Stock</h6>
                    </div>
                    <div class="card-body bg-light rounded-bottom p-4">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Source Warehouse</label>
                                <select id="storeroom_filter" class="form-control select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($storerooms as $storeroom)
                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Source Rack</label>
                                <select id="rack_filter" class="form-control select2">
                                    <option value="">All Racks</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Design No.</label>
                                <select id="design_filter" class="form-control select2">
                                    <option value="">All Design Nos.</option>
                                    @foreach($designs as $design)
                                        <option value="{{ $design->design_number }}">{{ $design->design_number }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Color</label>
                                <select id="color_filter" class="form-control select2">
                                    <option value="">All Colors</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                                <select id="size_set_filter" class="form-control select2">
                                    <option value="">All Size Sets</option>
                                    @foreach($size_sets as $set)
                                        <option value="{{ $set->id }}">{{ $set->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: SELECT ITEMS & DESTINATION -->
                <form id="bulk-transfer-form">
                    @csrf
                    <div class="row">
                        <div class="col-md-9">
                            <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                                    <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-list mr-2"></i> Step 2: Select Items to Transfer</h6>
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="select-all">
                                        <label class="custom-control-label small font-weight-bold" for="select-all">Select All</label>
                                    </div>
                                </div>
                                <div class="card-body p-0">
                                    <div class="table-responsive">
                                        <table id="inventoryTable" class="table table-hover mb-0">
                                            <thead class="bg-light contrast-text">
                                                <tr>
                                                    <th class="py-3 text-center" style="width: 50px;"></th>
                                                    <th class="py-3">Product / Design</th>
                                                    <th class="py-3">Size / Color</th>
                                                    <th class="py-3">Current Location</th>
                                                    <th class="py-3 text-center">Available</th>
                                                    <th class="py-3 text-center" style="width: 120px;">Qty to Transfer</th>
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
                        <div class="col-md-3">
                            <div class="card shadow border-0" style="border-radius: 12px; position: sticky; top: 20px;">
                                <div class="card-header bg-white border-0 py-3">
                                    <h6 class="mb-0 font-weight-bold text-primary"><i class="fas fa-map-marker-alt mr-2"></i> Step 3: Destination</h6>
                                </div>
                                <div class="card-body">
                                    <div class="form-group mb-3">
                                        <label class="small font-weight-bold text-muted mb-1">Target Warehouse</label>
                                        <select id="target_storeroom" class="form-control select2" required>
                                            <option value="">Select Warehouse</option>
                                            @foreach($storerooms as $storeroom)
                                                <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-group mb-4">
                                        <label class="small font-weight-bold text-muted mb-1">Target Rack</label>
                                        <select name="target_rack_id" id="target_rack" class="form-control select2" required>
                                            <option value="">Select Rack</option>
                                        </select>
                                    </div>
                                    <hr>
                                    <button type="submit" id="btn-submit-transfer" class="btn btn-primary btn-block py-2 font-weight-bold shadow-sm">
                                        <i class="fas fa-exchange-alt mr-2"></i> PERFORM TRANSFER
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </section>
    </div>

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 0.75rem;
            font-size: 0.9rem;
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(1.8125rem + 10px) !important;
        }
        
        .qty-input {
            text-align: center;
            font-weight: bold;
        }
    </style>

    <script>
        $(function () {
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
                    url: "{{ route('admin.inventory.stock_transfer.search') }}",
                    type: "GET",
                    data: {
                        page: nextPage,
                        storeroom_id: $('#storeroom_filter').val(),
                        rack_id: $('#rack_filter').val(),
                        size_set_id: $('#size_set_filter').val(),
                        design_filter: $('#design_filter').val(),
                        color_id: $('#color_filter').val()
                    },
                    success: function(res) {
                        if (reset) {
                            container.empty().css('opacity', '1');
                        }
                        
                        container.append(res.html);
                        nextPage = res.next_page;
                        loading = false;
                        $('#loading-spinner').hide();

                        if (!nextPage) {
                            $('#no-more-data').show();
                        }
                        
                        if (container.is(':empty')) {
                            container.append('<tr><td colspan="6" class="text-center py-5 text-muted">No inventory found for selected filters.</td></tr>');
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

            // Filters
            $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #color_filter').on('change', function () {
                loadMore(true);
            });

            $('#reset_filters').on('click', function () {
                $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #color_filter').val('').trigger('change');
            });

            // Source Rack Loading
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

            // Target Rack Loading
            $('#target_storeroom').on('change', function() {
                let wh_id = $(this).val();
                let target_rack = $('#target_rack');
                target_rack.html('<option value="">Select Rack</option>');
                if(wh_id) {
                    $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh_id, function(data) {
                        $.each(data, function(i, rack) {
                            target_rack.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                        });
                    });
                }
            });

            // Select All
            $('#select-all').on('click', function() {
                $('.inventory-checkbox').prop('checked', this.checked);
            });

            $(document).on('click', '.inventory-checkbox', function() {
                if ($('.inventory-checkbox:checked').length == $('.inventory-checkbox').length && $('.inventory-checkbox').length > 0) {
                    $('#select-all').prop('checked', true);
                } else {
                    $('#select-all').prop('checked', false);
                }
            });

            // Form Submission
            $('#bulk-transfer-form').on('submit', function(e) {
                e.preventDefault();
                
                if ($('.inventory-checkbox:checked').length === 0) {
                    toastr.error('Please select at least one item to transfer.');
                    return;
                }

                if (!$('#target_rack').val()) {
                    toastr.error('Please select a destination rack.');
                    return;
                }

                let btn = $('#btn-submit-transfer');
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i> PROCESSING...');

                $.ajax({
                    url: "{{ route('admin.inventory.stock_transfer.transfer') }}",
                    type: "POST",
                    data: $(this).serialize(),
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-exchange-alt mr-2"></i> PERFORM TRANSFER');
                        if (res.status == 'success') {
                            toastr.success(res.message);
                            loadMore(true);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-exchange-alt mr-2"></i> PERFORM TRANSFER');
                        toastr.error('An error occurred during transfer.');
                    }
                });
            });
        });
    </script>
@endsection
