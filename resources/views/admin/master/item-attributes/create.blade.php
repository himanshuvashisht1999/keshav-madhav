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
                    <input type="hidden" name="id" value="{{ request('id') }}">
                    <div class="card-body">
                        <div class="row">
                            <!-- ATTRIBUTE DROPDOWN -->
                            @foreach($attributes as $single_data)

                            <!-- VALUE INPUT -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="value">{{$single_data->sku}}</label>
                                    <input type="text" name="value[{{$single_data->sku}}]" id="value-{{$single_data->id}}" value="{{ old('value.' . $single_data->sku) }}" class="form-control" placeholder="Enter value" required>
                                    @if ($errors->has('value'))
                                        <span class="invalid-feedback d-block">{{ $errors->first('value') }}</span>
                                    @endif
                                </div>
                            </div>
                            @endforeach

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
    function updateSKU() {
        let skuParts = [];
        let prefixAdded = false;

        document.querySelectorAll("input[name^='value']").forEach(input => {
            let key = input.name.match(/value\[(.*?)\]/)[1];
            let firstPart = key.split("-")[0].toUpperCase();   // ZIP
            let val = input.value.trim().replace(/\s+/g, "").toUpperCase(); // RED, 5METER, 40

            if (val !== "") {

                // Add ZIP only for the first field
                if (!prefixAdded) {
                    skuParts.push(firstPart);  // Add ZIP
                    prefixAdded = true;
                }

                // Add ONLY value next
                skuParts.push(val);
            }
        });

        document.getElementById("sku").value = skuParts.join("-");
    }

    document.querySelectorAll("input[name^='value']").forEach(input => {
        input.addEventListener("input", updateSKU);
    });
    
});
</script>

@endsection
