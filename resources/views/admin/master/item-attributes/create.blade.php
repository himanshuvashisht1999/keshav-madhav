@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Item Attribute</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Item Attribute</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Create Item Attribute</h3>
                </div>
                <form action="{{route('admin.master.item-attributes.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="item_id" value="{{$item_id}}">
                    <div class="card-body">
                        <div class="row">
                            <!-- ATTRIBUTE DROPDOWN -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Item Attribute</label>
                                    <select name="item_attribute_id" id="item_attribute_id" class="form-control select2" style="width: 100%;">
                                        @foreach($attributes as $single_data)
                                            <option value="{{$single_data->id}}" data-sku="{{$single_data->sku}}" {{old('item_attribute_id') == $single_data->id ? 'selected' : ''}}>
                                                {{$single_data->name}}
                                            </option>
                                        @endforeach
                                    </select>
                                    @if ($errors->has('item_attribute_id'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('item_attribute_id') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- VALUE INPUT -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="value">Value</label>
                                    <input type="text" name="value" id="value" class="form-control" placeholder="Enter value" value="{{old('value')}}" required>
                                    @if ($errors->has('value'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('value') }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- SKU FIELD -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU" readonly>
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('sku') }}</span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const $ = window.jQuery || null;

    function sanitizePart(str) {
        if (!str) return '';
        // remove leading/trailing, replace sequences of non-alnum with single dash, uppercase
        return String(str).trim()
            .replace(/[^a-zA-Z0-9]+/g, '-')   // convert spaces/symbols -> '-'
            .replace(/^-+|-+$/g, '')          // trim leading/trailing dashes
            .toUpperCase();
    }

    function getSelectedDataSku() {
        const attributeSelect = document.getElementById("item_attribute_id");
        if (!attributeSelect) return '';

        const selectedOption = attributeSelect.options[attributeSelect.selectedIndex];
        if (!selectedOption) return '';

        // prefer data-sku attribute if present and non-empty
        const ds = selectedOption.getAttribute("data-sku");
        if (ds && ds.trim() !== '') return sanitizePart(ds);

        // fallback to option text (attribute name) sanitized
        return sanitizePart(selectedOption.textContent || selectedOption.innerText || '');
    }

    function generateSKU() {
        const baseSku = getSelectedDataSku();
        const rawValue = document.getElementById("value") ? document.getElementById("value").value : '';
        const valuePart = sanitizePart(rawValue);

        const skuInput = document.getElementById("sku");
        if (!skuInput) return;

        if (baseSku && valuePart) {
            skuInput.value = `${baseSku}-${valuePart}`;
        } else if (baseSku) {
            skuInput.value = baseSku;
        } else if (valuePart) {
            skuInput.value = valuePart;
        } else {
            skuInput.value = '';
        }
    }

    // trigger when attribute changes (works for select2 and native select)
    // If jQuery/Select2 is present, use delegated listener to ensure we catch select2 changes
    if ($) {
        // in some setups Select2 triggers 'change.select2' events; listen to both
        $(document).on('change', '#item_attribute_id', generateSKU);
        $(document).on('change.select2', '#item_attribute_id', generateSKU);
    } else {
        const attr = document.getElementById("item_attribute_id");
        if (attr) attr.addEventListener('change', generateSKU);
    }

    // trigger on value input
    const valInput = document.getElementById("value");
    if (valInput) {
        valInput.addEventListener('input', generateSKU);
    }

    // ensure SKU is generated on page load (if old values exist)
    generateSKU();
});
</script>

@endsection
