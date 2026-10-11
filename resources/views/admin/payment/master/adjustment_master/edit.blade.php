@extends('admin.layouts.app')
@section('title', 'Edit Adjustment Master')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-sliders-h text-primary mr-2"></i> Edit Adjustment Master: {{ $data->name }}
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.master.adjustment_master.index') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Adjustment Masters
            </a>
        </div>
    </div>

    <!-- Form Card -->
    <div class="erp-card p-3 mb-2">
        <form action="{{ route('admin.payment.master.adjustment_master.update', $data->id) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Display Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control erp-input" value="{{ $data->name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Model Class <span class="text-danger">*</span></label>
                    <input type="text" name="model_name" class="form-control erp-input" value="{{ $data->model_name }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="erp-label">Status</label>
                    <select name="status" class="form-control erp-input">
                        <option value="1" {{ $data->status == 1 ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $data->status == 0 ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-12 text-right mt-2">
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-save mr-1"></i> Update Master
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
