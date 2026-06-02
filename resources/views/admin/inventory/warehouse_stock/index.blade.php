@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Warehouse Stock</h1>
                        <small class="text-muted">Manage inventory physical locations and transfers</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory.warehouse_stock.history') }}" class="btn btn-outline-primary shadow-sm mr-2">
                            <i class="fas fa-history mr-1"></i> Transfer History
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">
                <!-- TOTALS CARDS -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 bg-primary text-white" style="border-radius: 12px;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="bg-white text-primary rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 48px; height: 48px;">
                                    <i class="fas fa-box fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-white-50">Total Boxes</h6>
                                    <h4 class="mb-0 font-weight-bold" id="header_total_boxes">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card shadow-sm border-0 bg-success text-white" style="border-radius: 12px;">
                            <div class="card-body p-3 d-flex align-items-center">
                                <div class="bg-white text-success rounded-circle d-flex align-items-center justify-content-center mr-3" style="width: 48px; height: 48px;">
                                    <i class="fas fa-tshirt fa-lg"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-white-50">Total Pcs</h6>
                                    <h4 class="mb-0 font-weight-bold" id="header_total_pcs">0</h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- SINGLE CONSOLIDATED FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <div class="row align-items-end">
                            <div class="col-md-2 mb-3">
                                <label class="small font-weight-bold text-muted mb-1">Warehouse</label>
                                <select id="storeroom_filter" class="form-control select2">
                                    <option value="">All Warehouses</option>
                                    @foreach($storerooms as $storeroom)
                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
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
                                <label class="small font-weight-bold text-muted mb-1">Color</label>
                                <select id="color_filter" class="form-control select2">
                                    <option value="">All Colors</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}">{{ $color->name }}</option>
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
                            
                            <div class="col-md-1 mb-3">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block">
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
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Design No.</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Color</th>
                                        <th class="py-3">Location (WH / Rack)</th>
                                        <th class="py-3 text-center">Total Boxes</th>
                                        <th class="py-3 text-center">Quantity</th>
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

    <!-- Transfer Row Modal -->
    <div class="modal fade" id="transferRowModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-exchange-alt mr-2 text-primary"></i> Transfer Inventory</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light rounded mb-3">
                        <div class="small text-muted mb-1">Product</div>
                        <div class="font-weight-bold" id="transfer_product_name">-</div>
                        <div class="mt-2">
                            <span class="small text-muted">Available Boxes:</span>
                            <span class="badge badge-primary" id="transfer_total_boxes">0</span>
                        </div>
                    </div>
                    <form id="form-transfer-row">
                        <input type="hidden" id="transfer_id">
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted mb-1">Transfer Quantity (Boxes)</label>
                            <input type="number" id="transfer_qty" class="form-control" min="1" required>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted mb-1">Destination Warehouse</label>
                            <select id="dest_storeroom" class="form-control" required>
                                <option value="">Select Warehouse</option>
                                @foreach($storerooms as $storeroom)
                                    <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted mb-1">Destination Rack</label>
                            <select id="dest_rack" class="form-control" required>
                                <option value="">Select Rack</option>
                            </select>
                        </div>
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted mb-1">Transfer Notes (Optional)</label>
                            <textarea id="transfer_notes" class="form-control" rows="2" placeholder="Reason for transfer..."></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="submit-transfer-row" class="btn btn-primary shadow-sm px-4">
                        <i class="fas fa-check mr-1"></i> Confirm Transfer
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Attributes Modal -->
    <div class="modal fade" id="editAttributesModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark"><i class="fas fa-edit mr-2 text-warning"></i> Update Stock Attributes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="form-edit-attributes">
                        <input type="hidden" id="edit_attr_id">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted mb-1">Design (Product)</label>
                                    <select id="edit_product_id" class="form-control select2-modal-edit" required>
                                        <option value="">Select Product ({{ count($products) }})</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->design_number }} - {{ ($prod->series->name ?? '') . ' ' . $prod->name_of_garment }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted mb-1">Pattern</label>
                                    <select id="edit_pattern_id" class="form-control select2-modal-edit">
                                        <option value="">None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted mb-1">Fitting</label>
                                    <select id="edit_fitting_id" class="form-control select2-modal-edit">
                                        <option value="">None</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                                    <select id="edit_size_set_id" class="form-control select2-modal-edit" required>
                                        <option value="">Select Size Set ({{ count($size_sets) }})</option>
                                        @foreach($size_sets as $set)
                                            <option value="{{ $set->id }}">{{ $set->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="small font-weight-bold text-muted mb-1">Color</label>
                                    <select id="edit_color_id" class="form-control select2-modal-edit" required>
                                        <option value="">Select Color ({{ count($colors) }})</option>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group mb-0">
                                    <label class="small font-weight-bold text-muted mb-1">Number of Boxes to Update</label>
                                    <div class="input-group">
                                        <input type="number" id="edit_update_qty" class="form-control" min="1" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light border-left-0 small text-muted">Available: <span id="edit_max_boxes" class="ml-1 font-weight-bold">0</span></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="submit-edit-attributes" class="btn btn-warning shadow-sm px-4 text-dark font-weight-bold">
                        <i class="fas fa-save mr-1"></i> Update Attributes
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Boxes Modal -->
    <div class="modal fade" id="deleteBoxesModal" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 15px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.1);">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title font-weight-bold text-dark text-danger"><i class="fas fa-trash mr-2"></i> Delete Boxes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="p-3 bg-light rounded mb-3">
                        <div class="small text-muted mb-1">Product</div>
                        <div class="font-weight-bold" id="delete_product_name">-</div>
                    </div>
                    <form id="form-delete-boxes">
                        <input type="hidden" id="delete_id">
                        <div class="form-group mb-0">
                            <label class="small font-weight-bold text-muted mb-1">Number of Boxes to Delete</label>
                            <div class="input-group">
                                <input type="number" id="delete_qty" class="form-control" min="1" required>
                                <div class="input-group-append">
                                    <span class="input-group-text bg-light border-left-0 small text-muted">Available: <span id="delete_max_boxes" class="ml-1 font-weight-bold">0</span></span>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" id="submit-delete-boxes" class="btn btn-danger shadow-sm px-4">
                        <i class="fas fa-trash mr-1"></i> Delete Permanent
                    </button>
                </div>
            </div>
        </div>
    </div>


    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }

        .badge {
            padding: 0.5em 0.8em;
            border-radius: 6px;
        }

        .form-control {
            border-radius: 8px;
        }
    </style>

    <script>
        $(function () {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // Initialize select2 when modal is shown (fix for bootstrap 4 modals)
            $('#editAttributesModal').on('shown.bs.modal', function () {
                $('.select2-modal-edit').select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: $('#editAttributesModal')
                });
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
                        color_id: $('#color_filter').val(),
                        series_id: $('#series_filter').val(),
                        brand_id: $('#brand_filter').val(),
                        fitting_id: $('#fitting_filter').val(),
                        pattern_id: $('#pattern_filter').val(),
                        nature_id: $('#nature_filter').val(),
                        fabric_type_id: $('#fabric_type_filter').val()
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
                            container.append('<tr><td colspan="9" class="text-center py-5 text-muted">No inventory records found.</td></tr>');
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

            // Filter events
            $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #color_filter, #series_filter, #brand_filter, #fitting_filter, #pattern_filter, #nature_filter, #fabric_type_filter').on('change', function () {
                loadMore(true);
            });

            // Reset filter
            $('#reset_filters').on('click', function () {
                $('#storeroom_filter, #rack_filter, #size_set_filter, #design_filter, #product_filter, #color_filter, #series_filter, #brand_filter, #fitting_filter, #pattern_filter, #nature_filter, #fabric_type_filter').val('').trigger('change');
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

            // Transfer Logic
            $(document).on('click', '.btn-transfer', function() {
                let id = $(this).data('id');
                let product = $(this).data('product');
                let boxes = $(this).data('boxes');

                $('#transfer_id').val(id);
                $('#transfer_product_name').text(product);
                $('#transfer_total_boxes').text(boxes);
                $('#transfer_qty').attr('max', boxes).val(boxes);
                
                $('#transferRowModal').modal('show');
            });

            $('#dest_storeroom').on('change', function() {
                let wh_id = $(this).val();
                let dest_rack = $('#dest_rack');
                dest_rack.html('<option value="">Select Rack</option>');
                if(wh_id) {
                    $.get('{{ url("admin/inventory/warehouse-stock/racks") }}/' + wh_id, function(data) {
                        $.each(data, function(i, rack) {
                            dest_rack.append('<option value="'+rack.id+'">'+rack.name+'</option>');
                        });
                    });
                }
            });

            $('#submit-transfer-row').on('click', function() {
                let btn = $(this);
                let form = $('#form-transfer-row');
                
                if(!$('#dest_rack').val()) {
                    toastr.error('Please select a destination rack.');
                    return;
                }

                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Processing');

                $.ajax({
                    url: "{{ route('admin.inventory.warehouse_stock.transfer_row') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: $('#transfer_id').val(),
                        rack_id: $('#dest_rack').val(),
                        transfer_qty: $('#transfer_qty').val(),
                        notes: $('#transfer_notes').val()
                    },
                    success: function(res) {
                        btn.prop('disabled', false).text('Confirm Transfer');
                        if(res.status == 'success') {
                            toastr.success(res.message);
                            $('#transferRowModal').modal('hide');
                            form[0].reset();
                            loadMore(true);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).text('Confirm Transfer');
                        toastr.error('An error occurred.');
                    }
                });
            });

            // Edit Attributes Logic
            $(document).on('click', '.btn-edit-attributes', function() {
                let id = $(this).data('id');
                let productId = $(this).data('product-id');
                let colorId = $(this).data('color-id');
                let sizeSetId = $(this).data('size-set-id');
                let fittingId = $(this).data('fitting-id');
                let patternId = $(this).data('pattern-id');
                let boxes = $(this).data('boxes');

                $('#edit_attr_id').val(id);
                $('#edit_max_boxes').text(boxes);
                $('#edit_update_qty').attr('max', boxes).val(boxes);
                
                // Set pre-select data for the product change trigger
                $('#edit_product_id').data('preselect-size', sizeSetId);
                $('#edit_product_id').data('preselect-color', colorId);
                $('#edit_product_id').data('preselect-fitting', fittingId);
                $('#edit_product_id').data('preselect-pattern', patternId);
                
                $('#edit_product_id').val(productId).trigger('change');
                
                $('#editAttributesModal').modal('show');
            });

            // Dependent Dropdowns for Edit Modal
            $('#edit_product_id').on('change', function () {
                var productId = $(this).val();
                var fittingSelect = $('#edit_fitting_id');
                var patternSelect = $('#edit_pattern_id');
                var sizeSelect = $('#edit_size_set_id');
                var colorSelect = $('#edit_color_id');

                var preselectSize = $(this).data('preselect-size');
                var preselectColor = $(this).data('preselect-color');
                var preselectFitting = $(this).data('preselect-fitting');
                var preselectPattern = $(this).data('preselect-pattern');

                // Clear subsequent dropdowns
                sizeSelect.empty().append('<option value="">Select Size Set</option>').trigger('change.select2');
                colorSelect.empty().append('<option value="">Select Color</option>').trigger('change.select2');

                if (productId) {
                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function (data) {
                        if (data.success) {
                            // Update Fitting
                            fittingSelect.empty();
                            if (data.fitting_id) {
                                fittingSelect.append(`<option value="${data.fitting_id}" selected>${data.fitting_name}</option>`);
                            } else {
                                fittingSelect.append('<option value="">No Fitting</option>');
                            }
                            fittingSelect.trigger('change.select2');
                            
                            // Update Pattern
                            patternSelect.empty();
                            if (data.pattern_id) {
                                patternSelect.append(`<option value="${data.pattern_id}" selected>${data.pattern_name}</option>`);
                            } else {
                                patternSelect.append('<option value="">No Pattern</option>');
                            }
                            patternSelect.trigger('change.select2');

                            // Store variants for color filtering
                            $('#editAttributesModal').data('variants', data.variants);

                            // Update Size Set
                            sizeSelect.empty().append('<option value="">Select Size Set</option>');
                            data.variants.forEach(function (v) {
                                sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                            });

                            if (preselectSize) {
                                sizeSelect.val(preselectSize).trigger('change');
                                $('#edit_product_id').data('preselect-size', null);
                            } else {
                                sizeSelect.trigger('change.select2');
                            }
                        }
                    });
                }
            });

            $('#edit_size_set_id').on('change', function () {
                var sizeSetId = $(this).val();
                var variants = $('#editAttributesModal').data('variants') || [];
                var colorSelect = $('#edit_color_id');
                var preselectColor = $('#edit_product_id').data('preselect-color');

                colorSelect.empty().append('<option value="">Select Color</option>').trigger('change.select2');

                if (sizeSetId) {
                    var variant = variants.find(v => v.size_set_id == sizeSetId);
                    if (variant) {
                        colorSelect.empty().append('<option value="">Select Color</option>');
                        variant.colors.forEach(function (c) {
                            colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                        });

                        if (preselectColor) {
                            colorSelect.val(preselectColor).trigger('change.select2');
                            $('#edit_product_id').data('preselect-color', null);
                        } else {
                            colorSelect.trigger('change.select2');
                        }
                    }
                }
            });

            $('#submit-edit-attributes').on('click', function() {
                let btn = $(this);
                let form = $('#form-edit-attributes');
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Updating');

                $.ajax({
                    url: "{{ route('admin.inventory.warehouse_stock.update_attributes') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: $('#edit_attr_id').val(),
                        product_id: $('#edit_product_id').val(),
                        color_id: $('#edit_color_id').val(),
                        size_set_id: $('#edit_size_set_id').val(),
                        fitting_id: $('#edit_fitting_id').val(),
                        pattern_id: $('#edit_pattern_id').val(),
                        update_qty: $('#edit_update_qty').val()
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Attributes');
                        if(res.status == 'success') {
                            toastr.success(res.message);
                            $('#editAttributesModal').modal('hide');
                            loadMore(true);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Update Attributes');
                        toastr.error('An error occurred.');
                    }
                });
            });

            // Delete Boxes Logic
            $(document).on('click', '.btn-delete-boxes', function() {
                let id = $(this).data('id');
                let product = $(this).closest('tr').find('td:eq(0)').text() + ' (' + $(this).data('design-no') + ')';
                let boxes = $(this).data('available-boxes');

                $('#delete_id').val(id);
                $('#delete_product_name').text(product);
                $('#delete_max_boxes').text(boxes);
                $('#delete_qty').attr('max', boxes).val(boxes);
                
                $('#deleteBoxesModal').modal('show');
            });

            $('#submit-delete-boxes').on('click', function() {
                if(!confirm('Are you sure you want to delete these boxes? This action cannot be undone.')) return;
                
                let btn = $(this);
                let form = $('#form-delete-boxes');
                
                btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Deleting');

                $.ajax({
                    url: "{{ route('admin.inventory.warehouse_stock.delete_boxes') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: $('#delete_id').val(),
                        delete_qty: $('#delete_qty').val()
                    },
                    success: function(res) {
                        btn.prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete Permanent');
                        if(res.status == 'success') {
                            toastr.success(res.message);
                            $('#deleteBoxesModal').modal('hide');
                            loadMore(true);
                        } else {
                            toastr.error(res.message);
                        }
                    },
                    error: function(err) {
                        btn.prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete Permanent');
                        toastr.error('An error occurred.');
                    }
                });
            });
        });
    </script>
@endsection