@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-tag text-primary"></i> Edit Brand: <span class="text-dark">{{ $data->name }}</span>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.brand.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Brands
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
            <i class="fas fa-edit mr-1 text-primary"></i> Brand Information
        </div>
        <form action="{{ route('admin.master.brand.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="name" class="form-label font-weight-bold" style="font-size: 13px;">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control erp-filter-input @error('name') is-invalid @enderror" value="{{ old('name', $data->name) }}" placeholder="Enter Brand Name" required>
                        @error('name')
                            <span class="invalid-feedback">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="logo" class="form-label font-weight-bold" style="font-size: 13px;">Brand Logo</label>
                        <div class="custom-file mb-2">
                            <input type="file" name="logo" id="logo" class="custom-file-input @error('logo') is-invalid @enderror" accept="image/*">
                            <label class="custom-file-label" for="logo">Choose logo</label>
                        </div>
                        @if($data->logo)
                            <div class="p-1 border rounded bg-light d-inline-block">
                                <img src="{{ asset('assets/brands/' . $data->logo) }}" alt="Current Logo" style="max-height: 60px; object-fit: contain;">
                            </div>
                        @endif
                        @error('logo')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="status" class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" id="status" class="form-control select2 erp-filter-select">
                            <option value="active" {{ old('status', $data->status) == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ old('status', $data->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.brand.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Update Brand
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
