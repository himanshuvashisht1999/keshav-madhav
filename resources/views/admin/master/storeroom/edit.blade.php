@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Edit storeroom: {{ $storeroom->name }}</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.storeroom.racks', $storeroom->id) }}" class="btn-erp btn-erp-primary mr-1">
                <i class="fas fa-boxes mr-1"></i> Manage racks
            </a>
            <a href="{{ route('admin.master.storeroom.index') }}" class="btn-erp btn-erp-outline">
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

        <form action="{{ route('admin.master.storeroom.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $storeroom->id }}">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" value="{{ $storeroom->name }}" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="1" {{ $storeroom->status == 1 ? 'selected' : '' }}>Active</option>
                            <option value="0" {{ $storeroom->status == 0 ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Order taken</label>
                        <select name="order_taken" id="order_taken_edit" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="No" {{ $storeroom->order_taken == 'No' ? 'selected' : '' }}>No</option>
                            <option value="Yes" {{ $storeroom->order_taken == 'Yes' ? 'selected' : '' }}>Yes</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-6" id="order_priority_div_edit" style="display: {{ $storeroom->order_taken == 'Yes' ? 'block' : 'none' }};">
                    <div class="form-group mb-3">
                        <label class="form-label">Order priority</label>
                        <input type="number" name="order_priority" class="form-control erp-filter-input" value="{{ $storeroom->order_priority }}" placeholder="Enter priority number (e.g. 1, 2, 3)" min="1">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label">Order dispatch</label>
                        <select name="order_dispatch" id="order_dispatch_edit" class="form-control select2 erp-filter-select" style="width: 100%;">
                            <option value="Yes" {{ ($storeroom->order_dispatch ?? 'Yes') == 'Yes' ? 'selected' : '' }}>Yes</option>
                            <option value="No" {{ ($storeroom->order_dispatch ?? 'Yes') == 'No' ? 'selected' : '' }}>No</option>
                        </select>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control erp-filter-input" rows="3" placeholder="Enter description">{{ $storeroom->description }}</textarea>
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.storeroom.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Update storeroom
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        $('#order_taken_edit').change(function() {
            if ($(this).val() == 'Yes') {
                $('#order_priority_div_edit').show();
            } else {
                $('#order_priority_div_edit').hide();
            }
        });
    });
</script>
@endpush
