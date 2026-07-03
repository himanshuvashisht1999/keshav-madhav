@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Barcode Generator</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.inventory.index') }}">Inventory</a></li>
                            <li class="breadcrumb-item active">Barcode Generator</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Generate Barcodes</h3>
                            </div>
                            <form action="{{ route('admin.inventory.barcode-generator.generate-tspl') }}" method="POST"
                                target="_blank" class="allow-multiple-submit">
                                @csrf
                                <div class="card-body">
                                    @if(session('success'))
                                        <div class="alert alert-success">{{ session('success') }}</div>
                                    @endif
                                    @if(session('error'))
                                        <div class="alert alert-danger">{{ session('error') }}</div>
                                    @endif
                                    @if ($errors->any())
                                        <div class="alert alert-danger">
                                            <ul>
                                                @foreach ($errors->all() as $error)
                                                    <li>{{ $error }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    @endif

                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Design Number <span class="text-danger">*</span></label>
                                                <select class="form-control select2 design-select" name="design_id" required>
                                                    <option value="">Select Design Number</option>
                                                    @foreach($designs as $design)
                                                        <option value="{{ $design->id }}" {{ old('design_id') == $design->id ? 'selected' : '' }}>
                                                            {{ ($design->series->name ?? '') . ' ' . $design->name_of_garment . ' (' . $design->design_number . ')' }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Pattern <span class="text-danger">*</span></label>
                                                <select class="form-control select2 pattern-select" name="pattern_id" required>
                                                    <option value="">Select Pattern</option>
                                                    @foreach($patterns as $pattern)
                                                        <option value="{{ $pattern->id }}" {{ old('pattern_id') == $pattern->id ? 'selected' : '' }}>{{ $pattern->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label>Fitting <span class="text-danger">*</span></label>
                                                <select class="form-control select2 fitting-select" name="fitting_id" required>
                                                    <option value="">Select Fitting</option>
                                                    @foreach($fittings as $fitting)
                                                        <option value="{{ $fitting->id }}" {{ old('fitting_id') == $fitting->id ? 'selected' : '' }}>{{ $fitting->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <hr>
                                    <h5>Color and Size Selections</h5>
                                    <div id="barcode-rows">
                                        <div class="row barcode-row">
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Size Set <span class="text-danger">*</span></label>
                                                    <select class="form-control select2 size-set-select" name="size_set_ids[]" required>
                                                        <option value="">Select Size Set</option>
                                                        @foreach($sizeSets as $set)
                                                            <option value="{{ $set->id }}">{{ $set->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <label>Color <span class="text-danger">*</span></label>
                                                    <select class="form-control select2 color-select" name="color_ids[]" required>
                                                        <option value="">Select Color</option>
                                                        @foreach($colors as $color)
                                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                            
                                            <div class="col-md-3">
                                                <div class="form-group">
                                                    <label>Quantity <span class="text-danger">*</span></label>
                                                    <input type="number" class="form-control" name="quantities[]" min="1" max="500" value="1" required>
                                                </div>
                                            </div>
                                            <div class="col-md-1">
                                                <div class="form-group">
                                                    <label>&nbsp;</label>
                                                    <button type="button" class="btn btn-danger btn-block remove-row"><i class="fas fa-trash"></i></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-success btn-sm" id="add-row">
                                        <i class="fas fa-plus"></i> Add Another Color/Size
                                    </button>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Generate TSPL & Print</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                
                                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Generate by Location (Warehouse & Rack)</h3>
                            </div>
                            <div class="card-body">
                                <form id="form-location" target="_blank" class="allow-multiple-submit" method="POST">
                                    @csrf
                                    <div class="row">
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Warehouse (Storeroom) <span class="text-danger">*</span></label>
                                                <select class="form-control select2 storeroom-select" name="storeroom_id" id="storeroom_id" required>
                                                    <option value="">Select Warehouse</option>
                                                    @foreach($storerooms as $storeroom)
                                                        <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <div class="form-group">
                                                <label>Rack</label>
                                                <select class="form-control select2 rack-select" name="rack_id" id="rack_id">
                                                    <option value="">Select Rack (Optional)</option>
                                                </select>
                                                <small class="form-text text-muted">Leave empty to print all barcodes for this warehouse.</small>
                                            </div>
                                        </div>
                                        <div class="col-md-2 d-flex align-items-end mb-3">
                                            <div class="btn-group w-100">
                                                <button type="submit" formaction="{{ route('admin.inventory.barcode-generator.generate-by-location-tspl') }}" class="btn btn-primary" title="Generate PRN"><i class="fas fa-print"></i> Generate PRN</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-md-12">
                        <div class="card card-default">
                            <div class="card-header">
                                <h3 class="card-title">Generate PRN by Barcodes</h3>
                            </div>
                            <form action="{{ route('admin.inventory.barcode-generator.generate-by-barcodes') }}" method="POST"
                                target="_blank" class="allow-multiple-submit">
                                @csrf
                                <div class="card-body">
                                    <div class="form-group">
                                        <label>Enter Barcodes <span class="text-danger">*</span></label>
                                        <textarea class="form-control" name="barcodes" rows="5" placeholder="Paste or type barcodes here (one per line)" required></textarea>
                                        <small class="form-text text-muted">
                                            Enter multiple barcodes separated by a new line. The system will automatically generate a PRN file for all valid barcodes.
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer text-right">
                                    <button type="submit" class="btn btn-primary"><i class="fas fa-print"></i> Generate PRN File</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
    <script>
        $(document).ready(function () {
            // Store variants globally for the form
            let globalVariants = [];
            
            var allRacks = @json($racks);
            
            $('#storeroom_id').on('change', function() {
                var storeroomId = $(this).val();
                var rackSelect = $('#rack_id');
                rackSelect.empty().append('<option value="">Select Rack (Optional)</option>');
                
                if (storeroomId) {
                    var filteredRacks = allRacks.filter(function(r) { return r.storeroom_id == storeroomId; });
                    filteredRacks.forEach(function(r) {
                        rackSelect.append('<option value="' + r.id + '">' + r.name + '</option>');
                    });
                }
                rackSelect.trigger('change.select2');
            });

            // Function to initialize Select2
            function initSelect2(element) {
                element.select2({
                    theme: 'bootstrap4',
                    width: '100%',
                    dropdownParent: element.parent()
                });
            }

            // Initialize existing Select2 elements
            $('.select2').each(function() {
                initSelect2($(this));
            });

            // Handle Design Selection
            $(document).on('change', '.design-select', function() {
                let productId = $(this).val();
                let patternSelect = $('.pattern-select');
                let fittingSelect = $('.fitting-select');
                let sizeSelects = $('.size-set-select');
                let colorSelects = $('.color-select');
                
                // Clear subsequent dropdowns
                patternSelect.val('').trigger('change.select2');
                fittingSelect.val('').trigger('change.select2');
                sizeSelects.empty().append('<option value="">Select Size Set</option>').trigger('change.select2');
                colorSelects.empty().append('<option value="">Select Color</option>').trigger('change.select2');
                globalVariants = [];

                if (productId) {
                    $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function(data) {
                        if (data.success) {
                            if (data.pattern_id) {
                                patternSelect.val(data.pattern_id).trigger('change.select2');
                            }
                            
                            if (data.fitting_id) {
                                fittingSelect.val(data.fitting_id).trigger('change.select2');
                            }
                            
                            globalVariants = data.variants;
                            
                            sizeSelects.each(function() {
                                let $this = $(this);
                                $this.empty().append('<option value="">Select Size Set</option>');
                                data.variants.forEach(function(v) {
                                    $this.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                                });
                                $this.trigger('change.select2');
                            });
                        }
                    });
                }
            });

            // Handle Size Set Selection
            $(document).on('change', '.size-set-select', function () {
                let sizeSetId = $(this).val();
                let row = $(this).closest('.barcode-row');
                let colorSelect = row.find('.color-select');
                
                colorSelect.empty().append('<option value="">Select Color</option>').trigger('change.select2');

                if (sizeSetId && globalVariants.length > 0) {
                    let variant = globalVariants.find(v => v.size_set_id == sizeSetId);
                    if (variant) {
                        variant.colors.forEach(function(c) {
                            colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                        });
                        colorSelect.trigger('change.select2');
                    }
                }
            });

            // Add new row logic
            $('#add-row').click(function() {
                var $firstRow = $('.barcode-row:first');
                var newRow = $firstRow.clone();
                
                // Reset input quantities to 1
                newRow.find('input[name="quantities[]"]').val(1);
                
                // Deep clean up Select2 artifacts from the cloned row
                newRow.find('.select2-container').remove();
                var $selects = newRow.find('select');
                
                $selects.each(function() {
                    $(this).removeClass('select2-hidden-accessible');
                    $(this).removeAttr('data-select2-id');
                    $(this).removeAttr('aria-hidden');
                    $(this).removeAttr('tabindex');
                    $(this).find('option').removeAttr('data-select2-id');
                    $(this).val(''); // Reset selected value
                    $(this).show(); // Ensure original select is visible for re-init
                });

                $('#barcode-rows').append(newRow);
                
                // Clear color select of the new row initially
                newRow.find('.color-select').empty().append('<option value="">Select Color</option>');

                // Populate Size Set if globalVariants is already set
                if (globalVariants.length > 0) {
                    let sizeSelect = newRow.find('.size-set-select');
                    sizeSelect.empty().append('<option value="">Select Size Set</option>');
                    globalVariants.forEach(function(v) {
                        sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                    });
                }
                
                // Re-initialize Select2 on the new row's selects
                newRow.find('.select2').each(function() {
                    initSelect2($(this));
                });
            });

            $(document).on('click', '.remove-row', function() {
                if ($('.barcode-row').length > 1) {
                    $(this).closest('.barcode-row').remove();
                } else {
                    alert('At least one row is required.');
                }
            });

            // Trigger change if design is already selected (e.g., after validation error)
            if ($('.design-select').val()) {
                $('.design-select').trigger('change');
            }
        });
    </script>
@endsection
