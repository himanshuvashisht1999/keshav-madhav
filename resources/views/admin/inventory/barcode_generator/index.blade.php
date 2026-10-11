@extends('admin.layouts.app')
@section('title', 'Barcode Generator')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-barcode text-primary"></i> Barcode Generator
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-boxes"></i> Inventory Overview
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif
        @if ($errors->any())
            <div class="alert alert-danger mb-2 py-2 px-3">
                <ul class="mb-0 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Card 1: Generate by Design -->
        <div class="erp-card mb-3 p-0">
            <div class="p-2 border-bottom bg-light d-flex justify-content-between align-items-center">
                <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                    <i class="fas fa-barcode mr-1"></i> Generate Barcodes by Design
                </h6>
                <span class="badge-erp-status badge-erp-primary">TSPL Direct Print</span>
            </div>
            <form action="{{ route('admin.inventory.barcode-generator.generate-tspl') }}" method="POST"
                target="_blank" class="allow-multiple-submit">
                @csrf
                <div class="p-3">
                    <div id="designs-container">
                        <div class="design-block border rounded p-3 mb-3 bg-light" data-index="0" style="border-left: 4px solid #05421c !important;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="badge-erp-status badge-erp-info font-weight-bold">Design Configuration</span>
                                <button type="button" class="btn-erp btn-erp-danger btn-sm remove-design-block d-none">
                                    <i class="fas fa-trash mr-1"></i> Remove Design Block
                                </button>
                            </div>
                            <div class="form-row">
                                <div class="col-md-4 mb-2">
                                    <label class="erp-label">Design number <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm erp-input select2 design-select" name="designs[0][design_id]" required>
                                        <option value="">Select design number</option>
                                        @foreach($designs as $design)
                                            <option value="{{ $design->id }}" {{ old('designs.0.design_id') == $design->id ? 'selected' : '' }}>
                                                {{ ($design->series->name ?? '') . ' ' . $design->name_of_garment . ' (' . $design->design_number . ')' }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="erp-label">Pattern <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm erp-input select2 pattern-select" name="designs[0][pattern_id]" required>
                                        <option value="">Select pattern</option>
                                        @foreach($patterns as $pattern)
                                            <option value="{{ $pattern->id }}">{{ $pattern->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <label class="erp-label">Fitting <span class="text-danger">*</span></label>
                                    <select class="form-control form-control-sm erp-input select2 fitting-select" name="designs[0][fitting_id]" required>
                                        <option value="">Select fitting</option>
                                        @foreach($fittings as $fitting)
                                            <option value="{{ $fitting->id }}">{{ $fitting->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-3 pt-2 border-top">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <label class="erp-label mb-0 font-weight-bold" style="color: #05421c;">Color and size breakdown</label>
                                </div>
                                <div class="barcode-rows">
                                    <div class="form-row barcode-row align-items-end mb-2">
                                        <div class="col-md-4">
                                            <label class="erp-label">Size set <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm erp-input select2 size-set-select" name="designs[0][size_set_ids][]" required>
                                                <option value="">Select size set</option>
                                                @foreach($sizeSets as $set)
                                                    <option value="{{ $set->id }}">{{ $set->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="erp-label">Color <span class="text-danger">*</span></label>
                                            <select class="form-control form-control-sm erp-input select2 color-select" name="designs[0][color_ids][]" required>
                                                <option value="">Select color</option>
                                                @foreach($colors as $color)
                                                    <option value="{{ $color->id }}">{{ $color->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="erp-label">Quantity <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control form-control-sm erp-input" name="designs[0][quantities][]" min="1" max="500" value="1" required>
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn-erp btn-erp-danger w-100 remove-row" title="Remove row">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <button type="button" class="btn-erp btn-erp-secondary btn-sm add-row">
                                        <i class="fas fa-plus mr-1"></i> Add Another Color/Size
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mt-2">
                        <button type="button" class="btn-erp btn-erp-secondary" id="add-design-block">
                            <i class="fas fa-plus mr-1"></i> Add Another Design Block
                        </button>
                    </div>
                </div>
                <div class="p-2 border-top bg-light text-right">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-print mr-1"></i> Generate TSPL & Print
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Generate by Location -->
        <div class="erp-card mb-3 p-0">
            <div class="p-2 border-bottom bg-light">
                <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                    <i class="fas fa-map-marker-alt mr-1"></i> Generate by Location (Warehouse & Rack)
                </h6>
            </div>
            <div class="p-3">
                <form id="form-location" target="_blank" class="allow-multiple-submit" method="POST">
                    @csrf
                    <div class="form-row align-items-end">
                        <div class="col-md-5 mb-2">
                            <label class="erp-label">Warehouse (Storeroom) <span class="text-danger">*</span></label>
                            <select class="form-control form-control-sm erp-input select2 storeroom-select" name="storeroom_id" id="storeroom_id" required>
                                <option value="">Select warehouse</option>
                                @foreach($storerooms as $storeroom)
                                    <option value="{{ $storeroom->id }}">{{ $storeroom->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-5 mb-2">
                            <label class="erp-label">Rack</label>
                            <select class="form-control form-control-sm erp-input select2 rack-select" name="rack_id" id="rack_id">
                                <option value="">Select rack (Optional)</option>
                            </select>
                            <small class="form-text text-muted">Leave empty to print all barcodes for this warehouse.</small>
                        </div>
                        <div class="col-md-2 mb-2">
                            <button type="submit" formaction="{{ route('admin.inventory.barcode-generator.generate-by-location-tspl') }}" class="btn-erp btn-erp-primary w-100" title="Generate PRN">
                                <i class="fas fa-print mr-1"></i> Generate PRN
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Card 3: Generate PRN by Barcodes -->
        <div class="erp-card p-0">
            <div class="p-2 border-bottom bg-light">
                <h6 class="font-weight-bold mb-0" style="color: #05421c;">
                    <i class="fas fa-list mr-1"></i> Generate PRN by Barcode List
                </h6>
            </div>
            <form action="{{ route('admin.inventory.barcode-generator.generate-by-barcodes') }}" method="POST"
                target="_blank" class="allow-multiple-submit">
                @csrf
                <div class="p-3">
                    <div class="form-group mb-0">
                        <label class="erp-label">Enter barcodes <span class="text-danger">*</span></label>
                        <textarea class="form-control erp-input" name="barcodes" rows="4" placeholder="Paste or type barcodes here (one barcode per line)" required></textarea>
                        <small class="form-text text-muted mt-1">
                            Enter multiple barcodes separated by a new line. The system will automatically generate a consolidated PRN file.
                        </small>
                    </div>
                </div>
                <div class="p-2 border-top bg-light text-right">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-print mr-1"></i> Generate PRN File
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function () {
        var allRacks = @json($racks);
        
        $('#storeroom_id').on('change', function() {
            var storeroomId = $(this).val();
            var rackSelect = $('#rack_id');
            rackSelect.empty().append('<option value="">Select rack (Optional)</option>');
            
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
            let block = $(this).closest('.design-block');
            let productId = $(this).val();
            let patternSelect = block.find('.pattern-select');
            let fittingSelect = block.find('.fitting-select');
            let sizeSelects = block.find('.size-set-select');
            let colorSelects = block.find('.color-select');
            
            // Clear subsequent dropdowns
            patternSelect.val('').trigger('change.select2');
            fittingSelect.val('').trigger('change.select2');
            sizeSelects.empty().append('<option value="">Select size set</option>').trigger('change.select2');
            colorSelects.empty().append('<option value="">Select color</option>').trigger('change.select2');
            block.data('variants', []);

            if (productId) {
                $.get("{{ route('admin.inventory.get_product_full_details') }}", { product_id: productId }, function(data) {
                    if (data.success) {
                        if (data.pattern_id) {
                            patternSelect.val(data.pattern_id).trigger('change.select2');
                        }
                        
                        if (data.fitting_id) {
                            fittingSelect.val(data.fitting_id).trigger('change.select2');
                        }
                        
                        block.data('variants', data.variants);
                        
                        sizeSelects.each(function() {
                            let $this = $(this);
                            $this.empty().append('<option value="">Select size set</option>');
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
            let block = $(this).closest('.design-block');
            let colorSelect = row.find('.color-select');
            let variants = block.data('variants') || [];
            
            colorSelect.empty().append('<option value="">Select color</option>').trigger('change.select2');

            if (sizeSetId && variants.length > 0) {
                let variant = variants.find(v => v.size_set_id == sizeSetId);
                if (variant) {
                    variant.colors.forEach(function(c) {
                        colorSelect.append(`<option value="${c.id}">${c.name}</option>`);
                    });
                    colorSelect.trigger('change.select2');
                }
            }
        });

        // Add new row logic inside a block
        $(document).on('click', '.add-row', function() {
            var block = $(this).closest('.design-block');
            var index = block.attr('data-index');
            var $firstRow = block.find('.barcode-row:first');
            var newRow = $firstRow.clone();
            
            newRow.find('input[type="number"]').val(1);
            
            newRow.find('.select2-container').remove();
            var $selects = newRow.find('select');
            
            $selects.each(function() {
                $(this).removeClass('select2-hidden-accessible');
                $(this).removeAttr('data-select2-id');
                $(this).removeAttr('aria-hidden');
                $(this).removeAttr('tabindex');
                $(this).find('option').removeAttr('data-select2-id');
                $(this).val('');
                $(this).show();
            });

            newRow.find('.color-select').empty().append('<option value="">Select color</option>');

            let variants = block.data('variants') || [];
            if (variants.length > 0) {
                let sizeSelect = newRow.find('.size-set-select');
                sizeSelect.empty().append('<option value="">Select size set</option>');
                variants.forEach(function(v) {
                    sizeSelect.append(`<option value="${v.size_set_id}">${v.size_set_name}</option>`);
                });
            }
            
            block.find('.barcode-rows').append(newRow);
            
            newRow.find('.select2').each(function() {
                initSelect2($(this));
            });
        });

        $(document).on('click', '.remove-row', function() {
            var block = $(this).closest('.design-block');
            if (block.find('.barcode-row').length > 1) {
                $(this).closest('.barcode-row').remove();
            } else {
                alert('At least one row is required.');
            }
        });

        let designIndex = 0;
        $('#add-design-block').click(function() {
            designIndex++;
            var $firstBlock = $('.design-block:first');
            var newBlock = $firstBlock.clone();
            newBlock.attr('data-index', designIndex);
            newBlock.data('variants', []);
            
            // Rename attributes
            newBlock.find('select, input').each(function() {
                var name = $(this).attr('name');
                if (name) {
                    name = name.replace(/designs\[0\]/, 'designs[' + designIndex + ']');
                    $(this).attr('name', name);
                }
            });
            
            // Keep only one barcode row
            newBlock.find('.barcode-row').not(':first').remove();
            
            // Reset inputs
            newBlock.find('input[type="number"]').val(1);
            
            // Clean Select2
            newBlock.find('.select2-container').remove();
            var $selects = newBlock.find('select');
            
            $selects.each(function() {
                $(this).removeClass('select2-hidden-accessible');
                $(this).removeAttr('data-select2-id');
                $(this).removeAttr('aria-hidden');
                $(this).removeAttr('tabindex');
                $(this).find('option').removeAttr('data-select2-id');
                $(this).val('');
                $(this).show();
            });
            
            newBlock.find('.size-set-select').empty().append('<option value="">Select size set</option>');
            newBlock.find('.color-select').empty().append('<option value="">Select color</option>');
            
            newBlock.find('.remove-design-block').removeClass('d-none');

            $('#designs-container').append(newBlock);
            
            newBlock.find('.select2').each(function() {
                initSelect2($(this));
            });
        });

        $(document).on('click', '.remove-design-block', function() {
            if ($('.design-block').length > 1) {
                $(this).closest('.design-block').remove();
            }
        });

        // Trigger change if design is already selected
        if ($('.design-select').val()) {
            $('.design-select').trigger('change');
        }
    });
</script>
@endsection
