@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-user-shield text-primary"></i> Edit Admin Profile
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.dashboard') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <div class="erp-card">
        <div class="px-3 py-2 border-bottom bg-light font-weight-bold" style="font-size: 13px; color: var(--erp-text-main);">
            <i class="fas fa-id-badge mr-1 text-primary"></i> Administrator Details
        </div>
        <form action="{{ route('admin.user.profileUpdate') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control erp-filter-input" placeholder="Enter first name" value="{{ $data->first_name }}">
                        @if ($errors->has('first_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('first_name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control erp-filter-input" placeholder="Enter last name" value="{{ $data->last_name }}">
                        @if ($errors->has('last_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('last_name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Email <span class="text-danger">*</span></label>
                        <input type="text" name="email" class="form-control erp-filter-input" placeholder="Enter email" value="{{ $data->email }}">
                        @if ($errors->has('email'))
                            <span class="invalid-feedback d-block">{{ $errors->first('email') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Phone</label>
                        <input type="text" name="phone" class="form-control erp-filter-input" placeholder="Enter phone" value="{{ $data->phone }}">
                        @if ($errors->has('phone'))
                            <span class="invalid-feedback d-block">{{ $errors->first('phone') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Password <small class="text-muted font-weight-normal">(Leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control erp-filter-input" placeholder="Enter new password" value="{{ old('password') }}">
                        @if ($errors->has('password'))
                            <span class="invalid-feedback d-block">{{ $errors->first('password') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control erp-filter-input" placeholder="Confirm new password" value="{{ old('confirm_password') }}">
                        @if ($errors->has('confirm_password'))
                            <span class="invalid-feedback d-block">{{ $errors->first('confirm_password') }}</span>
                        @endif
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Profile Image</label>
                        <div class="custom-file mb-2">
                            <input type="file" name="image" class="custom-file-input" id="image-input" onchange="previewImage()" accept=".jpg,.jpeg,.png">
                            <label class="custom-file-label" for="image-input">Choose file</label>
                        </div>
                        @if ($errors->has('image'))
                            <span class="invalid-feedback d-block">{{ $errors->first('image') }}</span>
                        @endif
                        <div class="p-1 border rounded bg-light d-inline-block">
                            <img src="{{ $data->image }}" alt="Preview" id="image-preview" height="60" style="object-fit: cover; border-radius: 4px;">
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Save Profile
                </button>
            </div>
        </form>
    </div>
</div>

@section('scripts')
<script>
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
            imagePreview.src = "";
        }
    }
</script>
@endsection
@endsection
