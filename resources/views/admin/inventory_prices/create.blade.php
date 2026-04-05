@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center mb-3">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Add Pricing Profile</h1>
                        <small class="text-muted">Create a new MRP profile for an exact combination</small>
                    </div>
                    <div class="col-sm-6 text-right">
                        <a href="{{ route('admin.inventory-prices.index') }}" class="btn btn-secondary font-weight-bold shadow-sm" style="border-radius: 8px;">
                            <i class="fas fa-arrow-left mr-1"></i> Back to Prices
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                
                <div class="card shadow-sm border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white border-bottom py-3">
                        <h5 class="card-title m-0 text-primary font-weight-bold"><i class="fas fa-tag mr-2"></i> Pricing Setup</h5>
                    </div>
                    <form action="{{ route('admin.inventory-prices.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="card-body bg-light p-4">
                            
                            <div class="row">
                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold text-muted">Product <span class="text-danger">*</span></label>
                                    <select name="product_id" class="form-control select2" required>
                                        <option value="">Select Product</option>
                                        @foreach($products as $prod)
                                            <option value="{{ $prod->id }}">{{ $prod->design_number }} ({{ $prod->series->name ?? '' }} {{ $prod->name_of_garment }})</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 form-group">
                                    <label class="font-weight-bold text-muted">Color <span class="text-danger">*</span></label>
                                    <select name="color_id[]" class="form-control select2" multiple="multiple" data-placeholder="Select Colors" required>
                                        @foreach($colors as $color)
                                            <option value="{{ $color->id }}">{{ $color->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block"><input type="checkbox" id="selectAllColors"> Select All Colors</small>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold text-muted">Size Set <span class="text-danger">*</span></label>
                                    <select name="size_set_id" class="form-control select2" required>
                                        <option value="">Select Size Set</option>
                                        @foreach($size_sets as $size)
                                            <option value="{{ $size->id }}">{{ $size->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold text-muted">Fitting <small class="text-muted font-weight-normal">(Optional)</small></label>
                                    <select name="fitting_id[]" class="form-control select2" multiple="multiple" data-placeholder="Select Fittings (Optional)">
                                        @foreach($fittings as $fit)
                                            <option value="{{ $fit->id }}">{{ $fit->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block"><input type="checkbox" id="selectAllFittings"> Select All Fittings</small>
                                </div>

                                <div class="col-md-4 form-group">
                                    <label class="font-weight-bold text-muted">Pattern <small class="text-muted font-weight-normal">(Optional)</small></label>
                                    <select name="pattern_id[]" class="form-control select2" multiple="multiple" data-placeholder="Select Patterns (Optional)">
                                        @foreach($patterns as $pat)
                                            <option value="{{ $pat->id }}">{{ $pat->name }}</option>
                                        @endforeach
                                    </select>
                                    <small class="text-muted mt-1 d-block"><input type="checkbox" id="selectAllPatterns"> Select All Patterns</small>
                                </div>

                                <div class="col-md-6 form-group mt-3">
                                    <label class="font-weight-bold text-dark">MRP Price (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-lg">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text bg-white text-success border-right-0"><i class="fas fa-rupee-sign"></i></span>
                                        </div>
                                        <input type="number" step="0.01" class="form-control font-weight-bold border-left-0" name="mrp" placeholder="0.00" required>
                                    </div>
                                    <small class="text-muted mt-1 d-block">This base price will automatically apply to any matching stock that is currently unassigned.</small>
                                </div>

                                <div class="col-md-6 form-group mt-3">
                                    <label class="font-weight-bold text-dark">Product Reference Image <small class="text-muted font-weight-normal">(Optional)</small></label>
                                    <input type="file" class="form-control-file mt-2" name="image" accept="image/jpeg,image/png,image/jpg">
                                    <small class="text-muted mt-1 d-block">This image will appear in sales agents' catalogs when placing orders.</small>
                                </div>
                            </div>

                        </div>
                        <div class="card-footer bg-white border-top py-4 text-right">
                            <button type="submit" class="btn btn-primary px-5 py-2 font-weight-bold shadow-sm" style="border-radius: 8px;">
                                <i class="fas fa-save mr-2"></i> Save Pricing Profile
                            </button>
                        </div>
                    </form>
                </div>

            </div>
        </section>
    </div>

    <script>
        $(function () {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

            $('#selectAllColors').on('change', function() {
                if ($(this).is(':checked')) {
                    var allColorValues = [];
                    $('select[name="color_id[]"] option').each(function() {
                        allColorValues.push($(this).val());
                    });
                    $('select[name="color_id[]"]').val(allColorValues).trigger('change');
                } else {
                    $('select[name="color_id[]"]').val(null).trigger('change');
                }
            });

            $('#selectAllFittings').on('change', function() {
                if ($(this).is(':checked')) {
                    var allFittingValues = [];
                    $('select[name="fitting_id[]"] option').each(function() {
                        allFittingValues.push($(this).val());
                    });
                    $('select[name="fitting_id[]"]').val(allFittingValues).trigger('change');
                } else {
                    $('select[name="fitting_id[]"]').val(null).trigger('change');
                }
            });

            $('#selectAllPatterns').on('change', function() {
                if ($(this).is(':checked')) {
                    var allPatternValues = [];
                    $('select[name="pattern_id[]"] option').each(function() {
                        allPatternValues.push($(this).val());
                    });
                    $('select[name="pattern_id[]"]').val(allPatternValues).trigger('change');
                } else {
                    $('select[name="pattern_id[]"]').val(null).trigger('change');
                }
            });
        });
    </script>
@endsection
