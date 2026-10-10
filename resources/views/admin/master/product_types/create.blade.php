@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Create product type</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.product-types.index') }}" class="btn-erp btn-erp-outline">
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

        <form action="{{ route('admin.master.product-types.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="name">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control erp-filter-input" placeholder="Enter name" value="{{ old('name') }}" required>
                        @if ($errors->has('name'))
                            <span class="invalid-feedback d-block">{{ $errors->first('name') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="sku">SKU</label>
                        <input type="text" name="sku" id="sku" class="form-control erp-filter-input" placeholder="Auto-generated SKU" value="{{ old('sku') }}">
                        @if ($errors->has('sku'))
                            <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.product-types.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Save product type
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function generateSKU() {
        let nameInput = document.querySelector("input[name='name']");
        if (!nameInput) return;
        let name = nameInput.value.trim();
        let part1 = name.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let sku = 'TYPE-' + part1;
        let skuInput = document.getElementById("sku");
        if (skuInput && (!skuInput.dataset.edited || skuInput.value === "")) {
            skuInput.value = sku;
        }
    }

    let nameField = document.querySelector("input[name='name']");
    if (nameField) {
        nameField.addEventListener("input", function() {
            let skuInput = document.getElementById("sku");
            if (skuInput && !skuInput.dataset.edited) {
                generateSKU();
            }
        });
    }

    let skuField = document.getElementById("sku");
    if (skuField) {
        skuField.addEventListener("input", function() {
            this.dataset.edited = "true";
        });
    }
</script>
@endsection
