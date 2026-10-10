@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Edit shop for {{ $agent->name }}</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.sales-agent-shops.index', $agent->id) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to shops
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

        <form action="{{ route('admin.master.sales-agent-shops.update', [$agent->id, $shop->id]) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Shop name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" value="{{ old('name', $shop->name) }}" required autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Phone number <span class="text-danger">*</span></label>
                        <input type="text" name="phone" class="form-control erp-filter-input" value="{{ old('phone', $shop->phone) }}" required autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Email address <small class="text-muted">(Optional)</small></label>
                        <input type="email" name="email" class="form-control erp-filter-input" value="{{ old('email', $shop->email) }}" autocomplete="off">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ old('status', $shop->status) == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ old('status', $shop->status) == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label class="form-label">Full address <small class="text-muted">(Optional)</small></label>
                        <textarea name="address" class="form-control erp-filter-input" rows="2">{{ old('address', $shop->address) }}</textarea>
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.sales-agent-shops.index', $agent->id) }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Save changes
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
