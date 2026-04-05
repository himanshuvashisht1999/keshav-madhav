@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Domestic Inventory</h1>
                        <small class="text-muted">Manage and track packed domestic orders in inventory</small>
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
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Design Number</label>
                                <input type="text" id="design_number" class="form-control" placeholder="Search Design...">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                                <select id="size_set_filter" class="form-control select2">
                                    <option value="">All Size Sets</option>
                                    @foreach($size_sets as $set)
                                        <option value="{{ $set->size_set_id }}">{{ $set->size_set_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Product</label>
                                <select id="product_filter" class="form-control select2">
                                    <option value="">All Products</option>
                                    @foreach($products as $prod)
                                        <option value="{{ $prod->product_id }}">{{ $prod->product_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Color</label>
                                <select id="color_filter" class="form-control select2">
                                    <option value="">All Colors</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->color_id }}">{{ $color->color_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Min MRP</label>
                                <input type="number" id="mrp_filter" class="form-control" placeholder="Min MRP">
                            </div>
                            <div class="col-md-1">
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
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th class="py-3">Product Name</th>
                                        <th class="py-3">Design No.</th>
                                        <th class="py-3">Size Set</th>
                                        <th class="py-3">Color</th>
                                        <th class="py-3">Fitting</th>
                                        <th class="py-3">Pattern</th>
                                        <th class="py-3">MRP</th>
                                        <th class="text-center py-3">Total Boxes</th>
                                        <th class="text-center py-3">Total Order</th>
                                        <th class="text-center py-3" width="10%">Actions</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <!-- Edit Attributes Modal -->
    <div class="modal fade" id="editAttributesModal" tabindex="-1" role="dialog" aria-labelledby="editAttributesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden;">
                <div class="modal-header bg-warning text-dark border-0">
                    <h5 class="modal-title font-weight-bold" id="editAttributesModalLabel">
                        <i class="fas fa-edit mr-2"></i>Change Product Attributes
                    </h5>
                    <button type="button" class="close text-dark" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-info text-sm">
                        <i class="fas fa-info-circle mr-2"></i> Note: This will update all unassigned boxes matching these specific attributes. Boxes already allocated to an active Agent Order will NOT be modified.
                    </div>
                    <form id="editAttributesForm">
                        @csrf
                        <input type="hidden" name="old_product_id" id="old_product_id">
                        <input type="hidden" name="old_size_set_id" id="old_size_set_id">
                        <input type="hidden" name="old_color_id" id="old_color_id">
                        <input type="hidden" name="old_fitting_id" id="old_fitting_id">
                        <input type="hidden" name="old_pattern_id" id="old_pattern_id">
                        
                        <div class="row">
                            <div class="col-md-12 form-group">
                                <label class="small font-weight-bold text-muted">Design Number <span class="text-danger">*</span></label>
                                <select name="new_product_id" id="new_product_id" class="form-control select2_modal" required>
                                    <option value="">Select Design</option>
                                    @foreach($master_products as $product)
                                        <option value="{{ $product->id }}">{{ $product->design_number }} ({{ $product->series->name ?? '' }} {{ $product->name_of_garment }})</option>
                                    @endforeach
                                </select>
                            </div>
                            
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold text-muted">Fitting <span class="text-danger">*</span></label>
                                <select name="new_fitting_id" id="new_fitting_id" class="form-control select2_modal" required>
                                    <option value="">Select Fitting</option>
                                    @foreach($master_fittings as $f)
                                        <option value="{{ $f->id }}">{{ $f->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold text-muted">Pattern <span class="text-danger">*</span></label>
                                <select name="new_pattern_id" id="new_pattern_id" class="form-control select2_modal" required>
                                    <option value="">Select Pattern</option>
                                    @foreach($master_patterns as $pat)
                                        <option value="{{ $pat->id }}">{{ $pat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold text-muted">Size Set <span class="text-danger">*</span></label>
                                <select name="new_size_set_id" id="new_size_set_id" class="form-control select2_modal" data-placeholder="Select Size Set" required>
                                    <option value="">Select Size Set</option>
                                </select>
                            </div>
                            <div class="col-md-6 form-group">
                                <label class="small font-weight-bold text-muted">Color <span class="text-danger">*</span></label>
                                <select name="new_color_id" id="new_color_id" class="form-control select2_modal" data-placeholder="Select Color" required>
                                    <option value="">Select Color</option>
                                </select>
                            </div>
                            

                            <div class="col-md-12 form-group">
                                <hr>
                                <label class="small font-weight-bold text-dark">Quantity of Boxes to Change <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control font-weight-bold" name="change_quantity" id="change_quantity" required min="1" value="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text text-muted bg-white">Total: <strong id="total_boxes_display" class="ml-1 text-dark mr-2">0</strong> | Available: <strong id="max_boxes_display" class="ml-1 text-primary">0</strong></span>
                                    </div>
                                </div>
                                <small class="text-muted italic">Specify how many boxes from the total should be updated to the new attributes.</small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-white border-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-warning px-4 font-weight-bold" id="btnSaveAttributes">Update Inventory</button>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Delete Boxes Modal -->
    <div class="modal fade" id="deleteBoxesModal" tabindex="-1" role="dialog" aria-labelledby="deleteBoxesModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content" style="border-radius: 12px; border: none; overflow: hidden;">
                <div class="modal-header bg-danger text-white border-0">
                    <h5 class="modal-title font-weight-bold" id="deleteBoxesModalLabel">
                        <i class="fas fa-trash-alt mr-2"></i>Delete Inventory Boxes
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 bg-light">
                    <div class="alert alert-warning text-sm">
                        <i class="fas fa-exclamation-triangle mr-2"></i> <strong>Warning!</strong> This action cannot be undone. Only boxes not assigned to any order can be deleted.
                    </div>
                    <form id="deleteBoxesForm">
                        @csrf
                        <input type="hidden" name="modal_product_id" id="modal_product_id">
                        <input type="hidden" name="modal_size_set_id" id="modal_size_set_id">
                        <input type="hidden" name="modal_color_id" id="modal_color_id">
                        <input type="hidden" name="modal_fitting_id" id="modal_fitting_id">
                        <input type="hidden" name="modal_pattern_id" id="modal_pattern_id">
                        
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="small font-weight-bold text-muted d-block">Design Number</label>
                                <div id="modal_design_no_display" class="font-weight-bold text-dark h5"></div>
                            </div>
                            
                            <div class="col-md-12 form-group">
                                <label class="small font-weight-bold text-dark">Quantity of Boxes to Delete <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" class="form-control font-weight-bold form-control-lg" name="delete_quantity" id="delete_quantity" required min="1" value="1">
                                    <div class="input-group-append">
                                        <span class="input-group-text text-muted bg-white">
                                            Available: <strong id="modal_available_boxes_display" class="ml-1 text-danger">0</strong>
                                        </span>
                                    </div>
                                </div>
                                <small class="text-muted italic">Total boxes found: <span id="modal_total_boxes_display">0</span></small>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-white border-0 py-3">
                    <button type="button" class="btn btn-secondary px-4" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger px-4 font-weight-bold" id="btnConfirmDelete">Confirm Delete</button>
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

            let table = $('#inventoryTable').DataTable({
                processing: true,
                serverSide: true,
                ordering: false,
                ajax: {
                    url: "{{ route('admin.inventory.list') }}",
                    data: function (d) {
                        d.size_set_id = $('#size_set_filter').val();
                        d.product_id = $('#product_filter').val();
                        d.color_id = $('#color_filter').val();
                        d.mrp = $('#mrp_filter').val();
                        d.design_number = $('#design_number').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id', className: 'text-center text-muted' },
                    { data: 'product_name_display', name: 'product_name_display' },
                    { data: 'design_number', name: 'design_number' },
                    { data: 'size_set_name', name: 'size_set_name' },
                    { data: 'color_name', name: 'color_name' },
                    { data: 'fitting_name', name: 'fitting_name' },
                    { data: 'pattern_name', name: 'pattern_name' },
                    { data: 'mrp_display', name: 'mrp' },
                    { data: 'total_boxes', name: 'total_boxes', className: 'text-center font-weight-bold text-success' },
                    { data: 'total_order', name: 'total_order', className: 'text-center font-weight-bold text-primary' },
                    { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
                ],
                language: {
                    emptyTable: "No inventory records found",
                    zeroRecords: "No matching records found",
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });

            // Filter events
            $('#design_number, #size_set_filter, #product_filter, #color_filter, #mrp_filter').on('keyup change', function () {
                table.ajax.reload();
            });

            // Reset filter
            $('#reset_filters').on('click', function () {
                $('#design_number, #mrp_filter').val('');
                $('#size_set_filter, #product_filter, #color_filter').val('').trigger('change');
                table.ajax.reload();
            });

            // Initialize select2 for Modal
            $('#editAttributesModal').on('shown.bs.modal', function () {
                $('.select2_modal').select2({
                    theme: 'bootstrap4',
                    dropdownParent: $('#editAttributesModal'),
                    width: '100%',
                    allowClear: true
                });
            });

             // Populate Modal on Open
            $('#editAttributesModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var total = button.data('total-boxes');
                var available = button.data('available-boxes');
                var productId = button.data('product-id');
                var sizeSetId = button.data('size-set-id');
                var colorId = button.data('color-id');
                var fittingId = button.data('fitting-id');
                var patternId = button.data('pattern-id');
                
                // Set old values for where clause
                $('#old_product_id').val(productId);
                $('#old_size_set_id').val(sizeSetId);
                $('#old_color_id').val(colorId);
                $('#old_fitting_id').val(fittingId);
                $('#old_pattern_id').val(patternId);
                
                $('#change_quantity').val(available).attr('max', available);
                $('#total_boxes_display').text(total);
                $('#max_boxes_display').text(available);

                // Set initial new values and trigger dependent logic
                $('#new_product_id').val(productId).data('preselect-size', sizeSetId).data('preselect-color', colorId).trigger('change');
            });

            // Dependent Dropdowns Logic
            $('#new_product_id').on('change', function() {
                var productId = $(this).val();
                var fittingSelect = $('#new_fitting_id');
                var patternSelect = $('#new_pattern_id');
                var sizeSelect = $('#new_size_set_id');
                var colorSelect = $('#new_color_id');
                
                var preselectSize = $(this).data('preselect-size');
                var preselectColor = $(this).data('preselect-color');

                // Clear subsequent dropdowns
                sizeSelect.empty().append('<option value="">Select Size Set</option>').trigger('change.select2');
                colorSelect.empty().append('<option value="">Select Color</option>').trigger('change.select2');

                if (productId) {
                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function(data) {
                        if (data.success) {
                            // Only show specific fitting/pattern
                            fittingSelect.empty();
                            if (data.fitting_id) {
                                fittingSelect.append(`<option value="${data.fitting_id}" selected>${data.fitting_name}</option>`);
                            } else {
                                fittingSelect.append('<option value="">No Fitting</option>');
                            }
                            fittingSelect.trigger('change.select2');

                            patternSelect.empty();
                            if (data.pattern_id) {
                                patternSelect.append(`<option value="${data.pattern_id}" selected>${data.pattern_name}</option>`);
                            } else {
                                patternSelect.append('<option value="">No Pattern</option>');
                            }
                            patternSelect.trigger('change.select2');
                            
                            $('#editAttributesModal').data('variants', data.variants);
                            
                            sizeSelect.empty().append('<option value="">Select Size Set</option>');
                            data.variants.forEach(function(v) {
                                sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                            });
                            
                            if (preselectSize) {
                                sizeSelect.val(preselectSize).trigger('change');
                                $('#new_product_id').data('preselect-size', null);
                            } else {
                                sizeSelect.trigger('change.select2');
                            }
                        }
                    });
                }
            });

            $('#new_size_set_id').on('change', function () {
                var sizeSetId = $(this).val();
                var variants = $('#editAttributesModal').data('variants') || [];
                var colorSelect = $('#new_color_id');
                var preselectColor = $('#new_product_id').data('preselect-color');
                
                colorSelect.empty().append('<option value="">Select Color</option>').trigger('change.select2');

                if (sizeSetId) {
                    var variant = variants.find(v => v.size_set_id == sizeSetId);
                    if (variant) {
                        variant.colors.forEach(function(c) {
                            colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                        });
                        
                        if (preselectColor) {
                            colorSelect.val(preselectColor).trigger('change.select2');
                            $('#new_product_id').data('preselect-color', null);
                        } else {
                            colorSelect.trigger('change.select2');
                        }
                    }
                }
            });


            // Submit Changes
            $('#btnSaveAttributes').on('click', function() {
                let form = $('#editAttributesForm');
                if(!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                let btn = $(this);
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> Updating...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.inventory.update_attributes') }}",
                    type: "POST",
                    data: form.serialize(),
                    success: function(res) {
                        btn.html(originalText).prop('disabled', false);
                        if(res.success) {
                            $('#editAttributesModal').modal('hide');
                            toastr.success(res.message);
                            table.ajax.reload(null, false); // Reload without resetting pagination
                        } else {
                            toastr.error(res.message || "An error occurred.");
                        }
                    },
                    error: function(xhr) {
                        btn.html(originalText).prop('disabled', false);
                        toastr.error(xhr.responseJSON?.message || "Failed to update attributes.");
                    }
                });
            });

            // Delete Boxes Modal Population
            $('#deleteBoxesModal').on('show.bs.modal', function (event) {
                var button = $(event.relatedTarget);
                var product_id = button.data('product-id');
                var design_no = button.data('design-no');
                var size_set_id = button.data('size-set-id');
                var color_id = button.data('color-id');
                var fitting_id = button.data('fitting-id');
                var pattern_id = button.data('pattern-id');
                var total = button.data('total-boxes');
                var available = button.data('available-boxes');

                $('#modal_product_id').val(product_id);
                $('#modal_size_set_id').val(size_set_id);
                $('#modal_color_id').val(color_id);
                $('#modal_fitting_id').val(fitting_id);
                $('#modal_pattern_id').val(pattern_id);
                
                $('#modal_design_no_display').text(design_no);
                $('#modal_total_boxes_display').text(total);
                $('#modal_available_boxes_display').text(available);
                $('#delete_quantity').val(available).attr('max', available);
            });

            // Confirm Delete Submit
            $('#btnConfirmDelete').on('click', function() {
                let form = $('#deleteBoxesForm');
                if(!form[0].checkValidity()) {
                    form[0].reportValidity();
                    return;
                }

                if(!confirm('Are you absolutely sure you want to delete these boxes? This cannot be undone.')) {
                    return;
                }

                let btn = $(this);
                let originalText = btn.html();
                btn.html('<i class="fas fa-spinner fa-spin"></i> Deleting...').prop('disabled', true);

                $.ajax({
                    url: "{{ route('admin.inventory.delete_boxes') }}",
                    type: "POST",
                    data: form.serialize(),
                    success: function(res) {
                        btn.html(originalText).prop('disabled', false);
                        if(res.success) {
                            $('#deleteBoxesModal').modal('hide');
                            toastr.success(res.message);
                            table.ajax.reload(null, false);
                        } else {
                            toastr.error(res.message || "An error occurred.");
                        }
                    },
                    error: function(xhr) {
                        btn.html(originalText).prop('disabled', false);
                        toastr.error(xhr.responseJSON?.message || "Failed to delete boxes.");
                    }
                });
            });
        });
    </script>
    <style>
        .btn-icon {
            width: 38px;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
        }
        .btn-sm.btn-icon {
            width: 32px;
            height: 32px;
        }
    </style>
@endsection