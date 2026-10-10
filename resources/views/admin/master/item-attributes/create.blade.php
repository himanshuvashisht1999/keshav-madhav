@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div>
            <h1 class="erp-header-title">Create item attribute</h1>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.item-attributes.index', ['id' => request('id')]) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to attributes
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

        <form action="{{ route('admin.master.item-attributes.store') }}" method="post" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="id" value="{{ request('id') }}">
            <div class="row">
                <!-- ATTRIBUTE INPUTS -->
                @foreach($attributes as $single_data)
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="value-{{ $single_data->id }}">{{ $single_data->sku }} <span class="text-danger">*</span></label>
                        <input type="text" name="value[{{ $single_data->sku }}]" id="value-{{ $single_data->id }}" value="{{ old('value.' . $single_data->sku) }}" class="form-control erp-filter-input" placeholder="Enter value" required>
                        @if ($errors->has('value'))
                            <span class="invalid-feedback d-block">{{ $errors->first('value') }}</span>
                        @endif
                    </div>
                </div>
                @endforeach

                <!-- SKU FIELD -->
                <div class="col-md-6">
                    <div class="form-group mb-3">
                        <label class="form-label" for="sku">SKU</label>
                        <input type="text" name="sku" id="sku" class="form-control erp-filter-input" placeholder="Auto-generated SKU" readonly>
                        @if ($errors->has('sku'))
                            <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
                        @endif
                    </div>
                </div>

                <div class="col-md-12 d-flex justify-content-end align-items-center mt-3 pt-2 border-top">
                    <a href="{{ route('admin.master.item-attributes.index', ['id' => request('id')]) }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                    <button type="submit" class="btn-erp btn-erp-primary">
                        <i class="fas fa-check mr-1"></i> Save item attribute
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    function generateSKU() {
        let values = [];
        document.querySelectorAll("input[name^='value[']").forEach(input => {
            let val = input.value.trim().replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
            if (val !== "") {
                values.push(val);
            }
        });
        let sku = values.join('-');
        let skuInput = document.getElementById("sku");
        if (skuInput) {
            skuInput.value = sku;
        }
    }

    document.querySelectorAll("input[name^='value[']").forEach(input => {
        input.addEventListener("input", generateSKU);
    });
</script>
@endsection
