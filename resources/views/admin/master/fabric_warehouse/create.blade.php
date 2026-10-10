@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Add fabric warehouse</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric_warehouse.index') }}" class="btn-erp btn-erp-outline">
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

        <form action="{{ route('admin.master.fabric_warehouse.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="cutting_master_name">Warehouse name <span class="text-danger">*</span></label>
                        <input type="text" name="cutting_master_name" id="cutting_master_name" class="form-control erp-filter-input" placeholder="Enter warehouse name" value="{{ old('cutting_master_name') }}" required>
                        @if ($errors->has('cutting_master_name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('cutting_master_name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="status">Status</label>
                        <select name="status" id="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactive</option>
                        </select>
                        @if ($errors->has('status'))
                            <span class="invalid-feedback d-block">{{ $errors->first('status') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label class="form-label" for="address">Address</label>
                        <input type="text" name="address" id="address" class="form-control erp-filter-input" placeholder="Enter address" value="{{ old('address') }}">
                        @if ($errors->has('address'))
                            <span class="invalid-feedback d-block">{{ $errors->first('address') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.fabric_warehouse.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Save warehouse
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
