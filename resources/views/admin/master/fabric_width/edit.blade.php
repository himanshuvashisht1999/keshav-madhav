@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Edit fabric width</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric_width.index') }}" class="btn-erp btn-erp-outline">
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

        <form action="{{ route('admin.master.fabric_width.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="name">Width <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control erp-filter-input" placeholder="Enter width" value="{{ old('name', $data->name) }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="unit">Unit</label>
                        <select name="unit" id="unit" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="cm" {{ old('unit', $data->unit) == 'cm' ? 'selected' : '' }}>cm</option>
                            <option value="inch" {{ old('unit', $data->unit) == 'inch' ? 'selected' : '' }}>inch</option>
                            <option value="meter" {{ old('unit', $data->unit) == 'meter' ? 'selected' : '' }}>meter</option>
                        </select>
                        @if ($errors->has('unit'))
                            <span class="invalid-feedback d-block">{{ $errors->first('unit') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group mb-3">
                        <label class="form-label" for="sku">SKU</label>
                        <input type="text" name="sku" id="sku_n" class="form-control erp-filter-input" placeholder="Auto-generated SKU" value="{{ $data->sku }}" readonly>
                        @if ($errors->has('sku'))
                            <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.fabric_width.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Update fabric width
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
