@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-user-edit text-primary"></i> Edit User: <span class="text-dark">{{ $data->first_name }} {{ $data->last_name }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.users.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Users
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
            <i class="fas fa-id-card mr-1 text-primary"></i> User Information
        </div>
        <form action="{{ route('admin.users.update', $data->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">First Name <span class="text-danger">*</span></label>
                        <input type="text" name="first_name" class="form-control erp-filter-input" value="{{ $data->first_name }}" required placeholder="Enter first name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Last Name <span class="text-danger">*</span></label>
                        <input type="text" name="last_name" class="form-control erp-filter-input" value="{{ $data->last_name }}" required placeholder="Enter last name">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Email <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control erp-filter-input" value="{{ $data->email }}" required placeholder="Enter email address">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Phone</label>
                        <input type="text" name="phone" class="form-control erp-filter-input" value="{{ $data->phone }}" placeholder="Enter phone number">
                    </div>
                    <div class="col-md-8 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Role (Permissions) <span class="text-danger">*</span></label>
                        <select name="role_name" class="form-control select2 erp-filter-select" required>
                            <option value="">Select Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ $data->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Password <small class="text-muted font-weight-normal">(Leave blank to keep current)</small></label>
                        <input type="password" name="password" class="form-control erp-filter-input" placeholder="New password">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control erp-filter-input" placeholder="Confirm new password">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select">
                            <option value="1" {{ $data->status == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $data->status == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Profile Image</label>
                        <div class="custom-file mb-2">
                            <input type="file" name="image" class="custom-file-input" id="editProfileImage">
                            <label class="custom-file-label" for="editProfileImage">Choose image</label>
                        </div>
                        @if($data->image)
                            <div class="p-1 border rounded bg-light d-inline-block">
                                <img src="{{ asset('assets/user-image/' . $data->image) }}" height="50" style="object-fit: cover; border-radius: 4px;">
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <input type="hidden" name="id" value="{{ $data->id }}">
                <a href="{{ route('admin.users.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Update User
                </button>
            </div>
        </form>
    </div>
</div>
@endsection