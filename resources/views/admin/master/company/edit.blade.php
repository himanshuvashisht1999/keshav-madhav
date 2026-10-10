@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Edit company</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.company.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to list
            </a>
        </div>
    </div>

    <!-- Main Content -->
    <div class="erp-card p-3">
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show mb-3" style="border-radius: 6px;">
                <button type="button" class="close" data-dismiss="alert">&times;</button>
                <strong><i class="fas fa-exclamation-triangle mr-1"></i> Please correct the following errors:</strong>
                <ul class="mb-0 mt-2 pl-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.master.company.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="name">Company name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control erp-filter-input @error('name') is-invalid @enderror" value="{{ old('name', $data->name) }}" placeholder="Enter company name" required>
                        @error('name')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="gst_number">GST number <span class="text-danger">*</span></label>
                        <input type="text" name="gst_number" id="gst_number" class="form-control erp-filter-input @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $data->gst_number) }}" placeholder="Enter GST number" required>
                        @error('gst_number')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="phone">Phone</label>
                        <input type="text" name="phone" id="phone" class="form-control erp-filter-input @error('phone') is-invalid @enderror" value="{{ old('phone', $data->phone) }}" placeholder="Enter phone">
                        @error('phone')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input type="email" name="email" id="email" class="form-control erp-filter-input @error('email') is-invalid @enderror" value="{{ old('email', $data->email) }}" placeholder="Enter email">
                        @error('email')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ old('status', $data->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $data->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label class="form-label" for="address">Address</label>
                        <textarea name="address" id="address" class="form-control erp-filter-input @error('address') is-invalid @enderror" rows="3" placeholder="Enter company address">{{ old('address', $data->address) }}</textarea>
                        @error('address')
                            <span class="invalid-feedback d-block">{{ $message }}</span>
                        @enderror
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.company.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Update company
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
