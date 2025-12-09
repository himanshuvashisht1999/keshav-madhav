@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Product Size</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Product Size</li>
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
                    <h3 class="card-title">Create Product Size</h3>
                </div>
                <form action="{{route('admin.master.size-measurement.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Size Type</label>
                                    <select name="size_type" id="size_type" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="0" {{old('size_type') == '0' ? 'selected' : ''}}>Set</option>
                                        <option value="1" {{old('size_type') == '1' ? 'selected' : ''}}>Individual</option>
                                    </select>
                                    @if ($errors->has('size_type'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('size_type') }}
                                        </span>
                                    @endif
                                </div>
                            </div>   
                            <div class="col-md-4" >
                                <div class="form-group">
                                    <label id="label1" for="label1">To</label>
                                    <input type="text" name="size_selection" class="form-control" placeholder="Enter start size" value="{{old('size selection')}}">
                                    @if ($errors->has('size_selection'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('size_selection') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4" id="measurement_div">
                                <div class="form-group">
                                    <label id="label2" for="label2">From</label>
                                    <input type="number" name="measurement" class="form-control" placeholder="Enter end size" step="1" min="1" value="{{old('measurement')}}">
                                    @if ($errors->has('measurement'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('measurement') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- <div class="col-md-4" >
                                <div class="form-group">
                                    <label id="label1" for="label1">Selection</label>
                                    <input type="text" name="size_selection" class="form-control" placeholder="Enter size selection" value="{{old('size selection')}}">
                                    @if ($errors->has('size_selection'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('size_selection') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4" id="measurement_div">
                                <div class="form-group">
                                    <label id="label2" for="label2">Measurement</label>
                                    <input type="number" name="measurement" class="form-control" placeholder="Enter measurement" step="1" min="1" value="{{old('measurement')}}">
                                    @if ($errors->has('measurement'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('measurement') }}
                                        </span>
                                    @endif
                                </div>
                            </div> --}}

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control select2" style="width: 100%;">
                                        <!-- <option value="">Select</option> -->
                                        <option value="1" {{old('status') == '1' ? 'selected' : ''}}>Active</option>
                                        <option value="0" {{old('status') == '0' ? 'selected' : ''}}>Inactive</option>
                                    </select>
                                    @if ($errors->has('status'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('status') }}
                                        </span>
                                    @endif
                                </div>
                            </div>   
                            <div class="col-md-6" style="display: none">
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
        // let size_type = document.querySelector("select[name='size_type'] option:checked").value.trim();
        let size_selection = document.querySelector("input[name='size_selection']").value.trim();
        let measurement = document.querySelector("input[name='measurement']").value.trim();
        let part1 = size_selection.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let part2 = measurement.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();
        let sku = part1 + "-" + part2;
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    document.querySelector("input[name='size_selection']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    document.querySelector("input[name='measurement']").addEventListener("input", function() {
        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited) {
            generateSKU();
        }
    });

    // Mark as manually edited when user types in SKU
    document.getElementById("sku").addEventListener("input", function() {
        this.dataset.edited = true;
    });
   
    $(document).ready(function () {
        updateLabels();
        $("#size_type").on("change", function () {
            updateLabels();
        });

        function updateLabels() {
            let type = $("#size_type").val(); // 0 = Set, 1 = Individual
            if (type == "0") {
                // Individual selected
                $("#label1").text("To");
                $("input[name='size_selection']").attr({
                    "placeholder": "Enter start size",
                    "type": "number",
                    "step": "1",
                    "min": "1"
                });


                $("#label2").text("From");
                $("input[name='measurement']").attr("placeholder", "Enter end size");

            } else {
                // Set selected
                $("#label1").text("Selection");
                $("input[name='size_selection']")
                .attr({
                    "placeholder": "Enter size selection",
                    "type": "text"
                })
                .removeAttr("step")
                .removeAttr("min");

                $("#label2").text("Measurement");
                $("input[name='measurement']").attr("placeholder", "Enter measurement");

            }
        }

    });
</script>

@endsection
