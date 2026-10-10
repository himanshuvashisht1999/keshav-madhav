@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-scroll text-primary"></i> Create Fabric
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Fabrics
            </a>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-2" role="alert">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="erp-card">
        <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
            <i class="fas fa-edit mr-1 text-primary"></i> Fabric Information
        </div>
        <form action="{{ route('admin.master.fabric.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label font-weight-bold mb-0" style="font-size: 13px;">Supplier Name <span class="text-danger">*</span></label>
                            <a href="{{ route('admin.master.vendor.create') }}" target="_blank" style="font-size: 12px; color: var(--brand-dark-green); font-weight: 600;">Create New +</a>
                        </div>
                        <select name="vendor_id" class="form-control select2 erp-filter-select" required>
                            <option value="">Select Supplier</option>
                            @foreach ($vender_data as $single_data)
                                <option value="{{ $single_data->id }}" {{ old('vendor_id') == $single_data->id ? 'selected' : '' }}>
                                    {{ $single_data->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('vendor_id'))
                            <span class="invalid-feedback d-block">{{ $errors->first('vendor_id') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Fabric Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter fabric name" value="{{ old('name') }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label font-weight-bold mb-0" style="font-size: 13px;">Fabric Composition</label>
                            <span style="font-size: 12px;">
                                <a href="{{ route('admin.master.fabric_composition.create') }}" target="_blank" class="mr-2" style="color: var(--brand-dark-green); font-weight: 600;"><i class="fas fa-plus"></i> New</a>
                                <a href="javascript:void(0)" class="text-secondary" id="refreshCompositionBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                            </span>
                        </div>
                        <select name="composition_id" id="compositionSelect" class="form-control select2 erp-filter-select">
                            <option value="0">N/A</option>
                            @foreach ($fab_composition_data as $single_data)
                                <option value="{{ $single_data->id }}" {{ old('composition_id') == $single_data->id ? 'selected' : '' }}>
                                    {{ $single_data->name }}
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('composition_id'))
                            <span class="invalid-feedback d-block">{{ $errors->first('composition_id') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label font-weight-bold mb-0" style="font-size: 13px;">Unit <span class="text-danger">*</span></label>
                            <span style="font-size: 12px;">
                                <a href="{{ route('admin.master.fabric_unit.create') }}" target="_blank" class="mr-2" style="color: var(--brand-dark-green); font-weight: 600;"><i class="fas fa-plus"></i> New</a>
                                <a href="javascript:void(0)" class="text-secondary" id="refreshUnitBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                            </span>
                        </div>
                        <select name="fabric_unit_id" id="unitSelect" class="form-control select2 erp-filter-select" required>
                            @foreach ($fab_unit_data as $single_data)
                                <option value="{{ $single_data->id }}" {{ old('fabric_unit_id') == $single_data->id ? 'selected' : '' }}>
                                    {{ $single_data->name }} ({{ $single_data->symbol }})
                                </option>
                            @endforeach
                        </select>
                        @if ($errors->has('fabric_unit_id'))
                            <span class="invalid-feedback d-block">{{ $errors->first('fabric_unit_id') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" id="status" class="form-control select2 erp-filter-select">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <span class="invalid-feedback d-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Main Image</label>
                        <div class="custom-file">
                            <input type="file" name="image" class="custom-file-input" id="image-input" onchange="previewImage()" accept=".jpg,.jpeg,.png">
                            <label class="custom-file-label" for="image-input">Choose file</label>
                        </div>
                        @if ($errors->has('image'))
                            <span class="invalid-feedback d-block">{{ $errors->first('image') }}</span>
                        @endif
                    </div>

                    <div class="col-md-2 mb-3 d-flex align-items-center">
                        <img src="{{ asset('images/image-placeholder.png') }}" alt="Preview" id="image-preview" class="border rounded" style="width: 70px; height: 70px; object-fit: cover;">
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Other Images</label>
                        <div class="custom-file">
                            <input type="file" name="other_images[]" id="other-images" class="custom-file-input" multiple accept=".jpg,.jpeg,.png">
                            <label class="custom-file-label" for="other-images">Choose multiple files</label>
                        </div>
                        <div class="mt-2 d-flex flex-wrap" id="preview-multiple-images" style="gap: 8px;"></div>
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.fabric.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#refreshCompositionBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.master.fabric_composition.all_compositions') }}", function(data) {
                var select = $('#compositionSelect');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                select.append('<option value="0">N/A</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + '</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });

        $('#refreshUnitBtn').on('click', function() {
            var btn = $(this);
            btn.html('<i class="fas fa-spinner fa-spin"></i>');
            $.getJSON("{{ route('admin.master.fabric_unit.all_units') }}", function(data) {
                var select = $('#unitSelect');
                var currentVal = select.val();
                if (select.hasClass('select2-hidden-accessible')) {
                    select.select2('destroy');
                }
                select.empty();
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.name + ' (' + item.symbol + ')</option>');
                });
                if (currentVal) select.val(currentVal);
                select.select2({ theme: 'bootstrap4', width: '100%' });
                btn.html('<i class="fas fa-sync-alt"></i>');
            }).fail(function() {
                btn.html('<i class="fas fa-sync-alt"></i>');
            });
        });
    });

    function previewImage() {
        var imageInput = document.getElementById('image-input');
        var imagePreview = document.getElementById('image-preview');
        
        if (imageInput.files && imageInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(imageInput.files[0]);
        } else {
            imagePreview.src = "{{ asset('images/image-placeholder.png') }}";
        }
    }

    document.getElementById('other-images').addEventListener('change', function() {
        let previewContainer = document.getElementById('preview-multiple-images');
        previewContainer.innerHTML = "";
        if (this.files) {
            [...this.files].forEach(file => {
                let reader = new FileReader();
                reader.onload = function(e) {
                    let img = document.createElement("img");
                    img.src = e.target.result;
                    img.classList.add("border", "rounded");
                    img.style.width = "70px";
                    img.style.height = "70px";
                    img.style.objectFit = "cover";
                    previewContainer.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }
    });
</script>
@endsection
