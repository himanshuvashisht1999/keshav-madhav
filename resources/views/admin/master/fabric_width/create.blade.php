@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Width</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Fabric Width</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default">
                <div class="card-header">
                    <h3 class="card-title">Create Fabric Width</h3>
                </div>
                <form action="{{route('admin.master.fabric_width.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Width</label>
                                    <input type="text" name="name" class="form-control" placeholder="Enter name" value="{{old('name')}}">
                                    @if ($errors->has('name'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('name') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Unit</label>
                                    <select name="unit" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="cm" {{old('unit') == 'cm' ? 'selected' : ''}}>cm</option>
                                        <option value="inch" {{old('unit') == 'inch' ? 'selected' : ''}}>inch</option>
                                        <option value="meter" {{old('unit') == 'meter' ? 'selected' : ''}}>meter</option>
                                    </select>
                                    @if ($errors->has('unit'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('unit') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sku">SKU</label>
                                    <input type="text" name="sku" id="sku" class="form-control" placeholder="Auto-generated SKU">
                                    @if ($errors->has('sku'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('sku') }}
                                        </span>
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
    function generateSKU() {
        let name = document.querySelector("input[name='name']").value.trim();
        let unit = document.querySelector("select[name='unit'] option:checked").text.trim();
        let part1 = name.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let part2 = unit.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        // Combine
        let sku = part1 + "-" + part2;

        let skuInput = document.getElementById("sku");

        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    // When page is ready
    $(document).ready(function() {
        $("input[name='name']").on("input", generateSKU);
        $('select[name="unit"]').on("change", generateSKU);
        $("#sku").on("input", function() {
            this.dataset.edited = true;
        });
    });
</script>

@endsection
