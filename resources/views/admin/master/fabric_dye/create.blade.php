@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Create fabric dye</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric_dye.index') }}" class="btn-erp btn-erp-outline">
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

        <form action="{{ route('admin.master.fabric_dye.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="color">Color <span class="text-danger">*</span></label>
                        <input type="text" name="color" id="color" class="form-control erp-filter-input" placeholder="Enter color" value="{{ old('color') }}" required>
                        @if ($errors->has('color'))
                            <span class="invalid-feedback d-block">{{ $errors->first('color') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="pantone">Pantone</label>
                        <input type="text" name="pantone" id="pantone" class="form-control erp-filter-input" placeholder="Enter pantone" value="{{ old('pantone') }}">
                        @if ($errors->has('pantone'))
                            <span class="invalid-feedback d-block">{{ $errors->first('pantone') }}</span>
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
                    <a href="{{ route('admin.master.fabric_dye.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Save fabric dye
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function generateSKU() {
        let color = (document.querySelector("input[name='color']").value || '').trim();
        let pantone = (document.querySelector("input[name='pantone']").value || '').trim();
        let part1 = color.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let part2 = pantone.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let sku = part1 + (part2 ? "-" + part2 : "");
        let skuInput = document.getElementById("sku");
        if (skuInput && (!skuInput.dataset.edited || skuInput.value === "")) {
            skuInput.value = sku;
        }
    }

    document.querySelector("input[name='color']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (skuInput && !skuInput.dataset.edited) {
            generateSKU();
        }
    });

    document.querySelector("input[name='pantone']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (skuInput && !skuInput.dataset.edited) {
            generateSKU();
        }
    });

    let skuField = document.getElementById("sku");
    if (skuField) {
        skuField.addEventListener("input", function() {
            this.dataset.edited = "true";
        });
    }
</script>
@endsection
