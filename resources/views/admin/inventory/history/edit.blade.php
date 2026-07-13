@extends('admin.layouts.app')
@section('title', 'Edit Attribute History')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Edit Attribute History</h1>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.attribute-history.show', $history->id) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Details
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-warning">
                    <h3 class="card-title text-dark"><i class="fas fa-exclamation-triangle"></i> Editing Change Record</h3>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        You are modifying a past attribute change. This will reverse the <strong>{{ $history->box_quantity }} boxes</strong> from their current New Attributes back to the Old Attributes, and then apply the newly selected attributes.
                    </p>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <div class="card bg-light border-0">
                                <div class="card-body">
                                    <h5 class="text-secondary border-bottom pb-2">Old Attributes (Original Source)</h5>
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr><th>Design:</th><td>{{ $history->oldProduct ? $history->oldProduct->design_number : 'N/A' }}</td></tr>
                                        <tr><th>Color:</th><td>{{ $history->oldColor ? $history->oldColor->name : 'N/A' }}</td></tr>
                                        <tr><th>Size:</th><td>{{ $history->oldSizeSet ? $history->oldSizeSet->name : 'N/A' }}</td></tr>
                                        <tr><th>Location:</th><td>{{ $history->oldRack ? ($history->oldRack->storeroom->name . ' / ' . $history->oldRack->name) : 'N/A' }}</td></tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <form id="editHistoryForm">
                        @csrf
                        <h5 class="text-primary border-bottom pb-2 mb-3">Corrected New Attributes</h5>
                        
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label>Series <span class="text-danger">*</span></label>
                                <select class="form-control select2" id="series_id" required>
                                    <option value="">Select Series</option>
                                    @foreach($series as $s)
                                        <option value="{{ $s->id }}" {{ ($history->newProduct && $history->newProduct->master_series_id == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Design/Product <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="new_product_id" id="new_product_id" required>
                                    <option value="">Select Product</option>
                                    @foreach($products as $p)
                                        <option value="{{ $p->id }}" data-series="{{ $p->master_series_id }}" {{ $history->new_product_id == $p->id ? 'selected' : '' }}>
                                            {{ ($p->series->name ?? '') . ' ' . $p->name_of_garment }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Size Set <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="new_size_set_id" id="new_size_set_id" required>
                                    <option value="">Select Size Set</option>
                                    @foreach($size_sets as $ss)
                                        <option value="{{ $ss->id }}" {{ $history->new_size_set_id == $ss->id ? 'selected' : '' }}>{{ $ss->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3 mb-3">
                                <label>Color <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="new_color_id" id="new_color_id" required>
                                    <option value="">Select Color</option>
                                    @foreach($colors as $c)
                                        <option value="{{ $c->id }}" {{ $history->new_color_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label>Warehouse <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="new_storeroom_id" id="new_storeroom_id" required>
                                    <option value="">Select Warehouse</option>
                                    @foreach($storerooms as $room)
                                        <option value="{{ $room->id }}" {{ ($history->newRack && $history->newRack->storeroom_id == $room->id) ? 'selected' : '' }}>{{ $room->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label>Rack <span class="text-danger">*</span></label>
                                <select class="form-control select2" name="new_rack_id" id="new_rack_id" required>
                                    <option value="">Select Rack</option>
                                    <!-- Rack options loaded via JS -->
                                </select>
                            </div>
                        </div>

                        <div class="mt-4">
                            <button type="submit" class="btn btn-success" id="submitBtn">
                                <i class="fas fa-save"></i> Save Corrected Attributes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </section>
</div>

<script>
    $(function() {
        $('.select2').select2({ theme: 'bootstrap4' });

        // Filter products by series
        $('#series_id').change(function() {
            var seriesId = $(this).val();
            $('#new_product_id option').each(function() {
                if ($(this).val() === '') return;
                if ($(this).data('series') == seriesId || seriesId === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
            $('#new_product_id').val('').trigger('change.select2');
        });

        // Load Racks dynamically based on Warehouse
        var currentRackId = "{{ $history->new_rack_id }}";
        
        function loadRacks(warehouseId, preselectRack = null) {
            $('#new_rack_id').empty().append('<option value="">Loading...</option>').trigger('change.select2');
            if (warehouseId) {
                $.get('/admin/inventory/warehouse-stock/racks/' + warehouseId, function (data) {
                    $('#new_rack_id').empty().append('<option value="">Select Rack</option>');
                    data.forEach(function (rack) {
                        var selected = (preselectRack == rack.id) ? 'selected' : '';
                        $('#new_rack_id').append('<option value="' + rack.id + '" ' + selected + '>' + rack.name + '</option>');
                    });
                    $('#new_rack_id').trigger('change.select2');
                });
            } else {
                $('#new_rack_id').empty().append('<option value="">Select Rack</option>').trigger('change.select2');
            }
        }

        $('#new_storeroom_id').change(function() {
            loadRacks($(this).val());
        });

        // Initial Rack Load
        if ($('#new_storeroom_id').val()) {
            loadRacks($('#new_storeroom_id').val(), currentRackId);
        }

        // Form Submit
        $('#editHistoryForm').on('submit', function(e) {
            e.preventDefault();
            var btn = $('#submitBtn');
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Saving...');

            $.ajax({
                url: "{{ route('admin.inventory.attribute-history.update', $history->id) }}",
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    if (response.success) {
                        toastr.success(response.message);
                        setTimeout(function() {
                            window.location.href = "{{ route('admin.inventory.attribute-history.show', $history->id) }}";
                        }, 1000);
                    } else {
                        toastr.error(response.message);
                        btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Corrected Attributes');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> Save Corrected Attributes');
                }
            });
        });
    });
</script>
@endsection
