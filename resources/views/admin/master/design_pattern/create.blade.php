@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-tshirt text-primary"></i> Create Product Style
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.design-pattern.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Product Styles
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
            <i class="fas fa-edit mr-1 text-primary"></i> Style Information
        </div>
        <form action="{{ route('admin.master.design-pattern.store') }}" method="post" enctype="multipart/form-data" autocomplete="off">
            @csrf
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter name" value="{{ old('name') }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <span class="invalid-feedback d-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Pattern Photo</label>
                        <div class="custom-file">
                            <input type="file" name="pattern_img" class="custom-file-input" id="image-input2" onchange="previewImage2()" accept=".jpg,.jpeg,.png">
                            <label class="custom-file-label" for="image-input2">Choose file</label>
                        </div>
                        @if ($errors->has('pattern_img'))
                            <span class="invalid-feedback d-block">{{ $errors->first('pattern_img') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3 d-flex align-items-center">
                        <img src="{{ asset('images/image-placeholder.png') }}" alt="Preview" id="image-preview-2" class="border rounded" style="width: 70px; height: 70px; object-fit: cover;">
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.design-pattern.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Submit
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function previewImage2() {
        var imageInput = document.getElementById('image-input2');
        var imagePreview = document.getElementById('image-preview-2');
        if (imageInput.files && imageInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
                imagePreview.src = e.target.result;
            };
            reader.readAsDataURL(imageInput.files[0]);
        } else {
            imagePreview.src = "{{ asset('images/image-placeholder.png') }}";
        }
    }
</script>
@endsection