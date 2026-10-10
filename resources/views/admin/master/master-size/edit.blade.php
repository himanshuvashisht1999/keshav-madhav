@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-ruler-combined text-primary"></i> Edit Standard Size
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.size.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Standard Sizes
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
            <i class="fas fa-edit mr-1 text-primary"></i> Standard Size Information
        </div>
        <form action="{{ route('admin.master.size.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Standard Size <span class="text-danger">*</span></label>
                        <input type="text" name="size" class="form-control erp-filter-input" placeholder="Enter Standard size" value="{{ old('size', $data->size) }}" required>
                        @if ($errors->has('size'))
                            <span class="invalid-feedback d-block">{{ $errors->first('size') }}</span>
                        @endif
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select">
                            <option value="1" {{ old('status', $data->status) == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $data->status) == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <span class="invalid-feedback d-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.size.index') }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
