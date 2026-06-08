@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Inventory Detail</h1>
                    <small class="text-muted">Viewing grouped inventory details</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.warehouse_stock') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Listing
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <!-- MAIN DETAILS CARD -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="card-title font-weight-bold"><i class="fas fa-box text-primary mr-2"></i> Product Information</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded shadow-xs mb-3">
                                <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Product Name</label>
                                <span class="h6 font-weight-bold text-dark">{{ $product->series->name ?? '' }} {{ $product->name_of_garment ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded shadow-xs mb-3">
                                <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Design Number</label>
                                <span class="h6 font-weight-bold text-dark">{{ $product->design_number ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 bg-light rounded shadow-xs mb-3">
                                <label class="small text-muted mb-1 d-block uppercase font-weight-bold">Size Set</label>
                                <span class="h6 font-weight-bold text-dark">{{ $sizeSet->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="font-weight-bold text-dark mb-3 mt-3"><i class="fas fa-map-marker-alt text-danger mr-2"></i> Current Location</h6>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="p-3 border rounded mb-3">
                                <label class="small text-muted mb-1 d-block">Warehouse</label>
                                <span class="font-weight-bold">{{ $rack->storeroom->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 border rounded mb-3">
                                <label class="small text-muted mb-1 d-block">Rack / Bin</label>
                                <span class="font-weight-bold">{{ $rack->name ?? 'N/A' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLE CARD -->
            <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                <div class="card-header bg-white border-0 pt-4 px-4 pb-0">
                    <h5 class="card-title font-weight-bold"><i class="fas fa-palette text-info mr-2"></i> Color Breakdown</h5>
                </div>
                <div class="card-body p-0 mt-3">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light contrast-text">
                                <tr>
                                    <th class="py-3 px-4">Color</th>
                                    <th class="py-3 text-center">Barcode</th>
                                    <th class="py-3 text-center">Pieces Per Box</th>
                                    <th class="py-3 text-center">Total Boxes</th>
                                    <th class="py-3 text-center">Total Pieces</th>
                                    <!-- <th class="py-3 text-center">Action</th> -->
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $row)
                                <tr>
                                    <td class="px-4 font-weight-bold">{{ $row->color->name ?? 'N/A' }}</td>
                                    <td class="text-center"><span class="badge badge-light border font-monospace">{{ $row->barcode }}</span></td>
                                    <td class="text-center">{{ $row->quantity }}</td>
                                    <td class="text-center"><span class="badge badge-info px-2 py-1">{{ $row->total_boxes }}</span></td>
                                    <td class="text-center"><strong>{{ $row->total_boxes * $row->quantity }}</strong></td>
                                    <!--
                                    <td class="text-center">
                                        <button type="button" class="btn btn-xs btn-success shadow-xs mr-1 btn-transfer" title="Transfer Row"
                                            data-id="{{ $row->id }}" 
                                            data-product="{{ ($row->product->design_number ?? '') . ' - ' . ($row->product->name_of_garment ?? '') }}"
                                            data-boxes="{{ $row->total_boxes }}">
                                            <i class="fas fa-exchange-alt"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-warning shadow-xs mr-1 btn-edit-attributes" title="Edit Attributes"
                                            data-id="{{ $row->id }}" 
                                            data-product-id="{{ $row->product_id }}"
                                            data-color-id="{{ $row->color_id }}"
                                            data-size-set-id="{{ $row->size_set_id }}"
                                            data-fitting-id="{{ $row->product->master_product_fitting_id ?? '' }}"
                                            data-pattern-id="{{ $row->product->master_pattern_id ?? '' }}"
                                            data-boxes="{{ $row->total_boxes }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-danger shadow-xs btn-delete-boxes" title="Delete Boxes" 
                                            data-id="{{ $row->id }}" 
                                            data-product-id="{{ $row->product_id }}"
                                            data-design-no="{{ $row->product->design_number ?? '' }}"
                                            data-color-id="{{ $row->color_id }}"
                                            data-size-set-id="{{ $row->size_set_id }}"
                                            data-fitting-id="{{ $row->product->master_product_fitting_id ?? '' }}"
                                            data-pattern-id="{{ $row->product->master_pattern_id ?? '' }}"
                                            data-available-boxes="{{ $row->total_boxes }}"
                                            data-rack-id="{{ $row->rack_id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                    -->
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
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
    .bg-light { background-color: #f8f9fa !important; }
    .shadow-xs { box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.035) !important; }
    .uppercase { text-transform: uppercase; letter-spacing: 0.5px; font-size: 0.75rem; }
    .contrast-text th {
        color: #444;
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.8rem;
        letter-spacing: 0.5px;
    }
</style>

<script>
    $(function () {
        // Initialize Select2
        $('.select2-modal-edit').select2({
            theme: 'bootstrap4',
            width: '100%',
            dropdownParent: $('#editAttributesModal')
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
                        window.location.reload();
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
                        window.location.reload();
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
                        window.location.reload();
                    } else {
                        toastr.error(res.message);
                    }
                },
                error: function(err) {
                    btn.prop('disabled', false).html('<i class="fas fa-trash mr-1"></i> Delete Permanent');
                    toastr.error('An error occurred while deleting.');
                }
            });
        });
    });
</script>
@endsection
