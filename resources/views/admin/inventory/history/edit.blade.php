@extends('admin.layouts.app')
@section('title', 'Edit Attribute History')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Edit Attribute History</h1>
                <p class="erp-page-subtitle m-0">Editing record #{{ $history->id }} &bull; Modify and re-assign inventory attributes</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.attribute-history.show', $history->id) }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-eye mr-1"></i> View details
                </a>
                <a href="{{ route('admin.inventory.attribute-history.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to history
                </a>
            </div>
        </div>

        <!-- NOTICE CARD -->
        <div class="erp-card mb-3 p-3" style="border-left: 4px solid #f59e0b !important;">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-triangle text-warning mr-2" style="font-size: 1.2rem;"></i>
                <div>
                    <div class="font-weight-bold text-dark">Editing past attribute change record</div>
                    <div class="text-muted small">
                        This action will reverse <strong>{{ $history->box_quantity }} boxes</strong> from their current New Attributes back to the Old Attributes, and then apply the newly selected attributes.
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <!-- SOURCE / OLD ATTRIBUTES -->
            <div class="col-md-5 mb-3">
                <div class="erp-card h-100 p-0" style="border-left: 4px solid #dc3545 !important;">
                    <div class="p-3 border-bottom" style="background-color: #fff5f5;">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-history mr-2"></i> Old attributes (Original source)
                        </h6>
                    </div>
                    <div class="p-3">
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Design</label>
                            <div class="font-weight-bold text-dark">{{ $history->oldProduct ? $history->oldProduct->design_number : 'N/A' }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Color</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldColor ? $history->oldColor->name : 'N/A' }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Size set</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldSizeSet ? $history->oldSizeSet->name : 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Location</label>
                            <div class="p-2 rounded font-weight-bold" style="background-color: #f8fafc; font-size: 0.9rem;">
                                {{ $history->oldRack ? ($history->oldRack->storeroom->name . ' / ' . $history->oldRack->name) : 'N/A' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TARGET / NEW ATTRIBUTES FORM -->
            <div class="col-md-7 mb-3">
                <div class="erp-card h-100 p-0" style="border-left: 4px solid #05421c !important;">
                    <div class="p-3 border-bottom" style="background-color: #edf5ee;">
                        <h6 class="m-0 font-weight-bold" style="color: #05421c;">
                            <i class="fas fa-edit mr-2"></i> Corrected new attributes
                        </h6>
                    </div>
                    <div class="p-3">
                        <form id="editHistoryForm">
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label mb-1">Series *</label>
                                    <select class="form-control form-control-sm select2" id="series_id" required>
                                        <option value="">Select series</option>
                                        @foreach($series as $s)
                                            <option value="{{ $s->id }}" {{ ($history->newProduct && $history->newProduct->master_series_id == $s->id) ? 'selected' : '' }}>{{ $s->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label class="erp-label mb-1">Design / Product *</label>
                                    <select class="form-control form-control-sm select2" name="new_product_id" id="new_product_id" required>
                                        <option value="">Select product</option>
                                        @foreach($products as $p)
                                            <option value="{{ $p->id }}" data-series="{{ $p->master_series_id }}" {{ $history->new_product_id == $p->id ? 'selected' : '' }}>
                                                {{ ($p->series->name ?? '') . ' ' . $p->name_of_garment }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label mb-1">Size set *</label>
                                    <select class="form-control form-control-sm select2" name="new_size_set_id" id="new_size_set_id" required>
                                        <option value="">Select size set</option>
                                        @foreach($size_sets as $ss)
                                            <option value="{{ $ss->id }}" {{ $history->new_size_set_id == $ss->id ? 'selected' : '' }}>{{ $ss->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-2">
                                    <label class="erp-label mb-1">Color *</label>
                                    <select class="form-control form-control-sm select2" name="new_color_id" id="new_color_id" required>
                                        <option value="">Select color</option>
                                        @foreach($colors as $c)
                                            <option value="{{ $c->id }}" {{ $history->new_color_id == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label mb-1">Warehouse *</label>
                                    <select class="form-control form-control-sm select2" name="new_storeroom_id" id="new_storeroom_id" required>
                                        <option value="">Select warehouse</option>
                                        @foreach($storerooms as $room)
                                            <option value="{{ $room->id }}" {{ ($history->newRack && $history->newRack->storeroom_id == $room->id) ? 'selected' : '' }}>{{ $room->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="erp-label mb-1">Rack *</label>
                                    <select class="form-control form-control-sm select2" name="new_rack_id" id="new_rack_id" required>
                                        <option value="">Select rack</option>
                                        <!-- Rack options loaded via JS -->
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 pt-3 border-top text-right">
                                <button type="submit" class="btn-erp btn-erp-primary" id="submitBtn">
                                    <i class="fas fa-save mr-1"></i> Save corrected attributes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
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
                    $('#new_rack_id').empty().append('<option value="">Select rack</option>');
                    data.forEach(function (rack) {
                        var selected = (preselectRack == rack.id) ? 'selected' : '';
                        $('#new_rack_id').append('<option value="' + rack.id + '" ' + selected + '>' + rack.name + '</option>');
                    });
                    $('#new_rack_id').trigger('change.select2');
                });
            } else {
                $('#new_rack_id').empty().append('<option value="">Select rack</option>').trigger('change.select2');
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
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Saving...');

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
                        btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save corrected attributes');
                    }
                },
                error: function(xhr) {
                    var errorMsg = 'An error occurred';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    }
                    toastr.error(errorMsg);
                    btn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Save corrected attributes');
                }
            });
        });
    });
</script>
@endpush
@endsection
