@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Edit Stage Units ({{ $stage_data->name }})
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.product-sub-stage.index', ['stage_id' => $stage_data->id]) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left"></i> Back to Stage Units
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
            <i class="fas fa-edit mr-1 text-primary"></i> Unit Information
        </div>
        <form action="{{ route('admin.master.product-sub-stage.update') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ $data->id }}">
            <input type="hidden" name="master_product_stage_id" value="{{ $data->master_product_stage_id }}">
            <div class="erp-card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control erp-filter-input" placeholder="Enter unit name" value="{{ old('name', $data->name) }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="font-size: 13px;">SKU</label>
                        <input type="text" name="sku" id="sku_n" class="form-control erp-filter-input" placeholder="Auto-generated SKU" value="{{ old('sku', $data->sku) }}" readonly>
                        @if ($errors->has('sku'))
                            <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
                        @endif
                    </div>
                </div>
            </div>
            <div class="px-3 py-2 border-top bg-white d-flex justify-content-end" style="gap: 8px;">
                <a href="{{ route('admin.master.product-sub-stage.index', ['stage_id' => $stage_data->id]) }}" class="btn-erp btn-erp-outline">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save"></i> Update
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
