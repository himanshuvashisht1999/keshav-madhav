@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Receipt</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Fabric Receipt</li>
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
                    <h3 class="card-title">Create Fabric Receipt</h3>
                </div>
                <form action="{{route('admin.fabric_receipt.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Vendor</label>
                                    <select name="vendor_id" class="form-control select2" style="width: 100%;">
                                        @foreach($vendors as $single_data)
                                        <option value="{{$single_data->id}}" {{old('vendor_id') == $single_data->id ? 'selected' : ''}}>{{$single_data->name}}</option>
                                        @endforeach
                                        
                                    </select>
                                    @if ($errors->has('vendor_id'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('vendor_id') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="truck_number">Truck Number</label>
                                    <input type="text" name="truck_number" id="truck_number" class="form-control" placeholder="Enter truck number">
                                    @if ($errors->has('truck_number'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('truck_number') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Date & Time</label>
                                    <input type="datetime-local" name="time" class="form-control" placeholder="Enter time" value="{{ \Carbon\Carbon::now()->format('Y-m-d\TH:i') }}">
                                    @if ($errors->has('time'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('time') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="roll">Rolls / Boxes</label>
                                    <input type="number" name="roll" id="roll" class="form-control" placeholder="Enter rolls">
                                    @if ($errors->has('roll'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('roll') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="received_by">Received By</label>
                                    <input type="text" name="received_by" id="received_by" class="form-control" placeholder="Enter received by">
                                    @if ($errors->has('received_by'))
                                        <span class="invalid-feedback d-block">
                                            {{ $errors->first('received_by') }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputFile">Shipment Photo</label>
                                    <div class="input-group">
                                        <div class="custom-file">
                                            <input type="file" name="shipment_photo" class="custom-file-input" id="image-input" onchange="previewImage()"  accept=".jpg,.jpeg,.png">
                                            <label class="custom-file-label" for="exampleInputFile">Choose file</label>
                                        </div>
                                        
                                        @if ($errors->has('shipment_photo'))
                                            <span class="invalid-feedback d-block">
                                            {{ $errors->first('shipment_photo') }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
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
                            <div class="col-md-6">
                                <img class="" src="{{asset('images/image-placeholder.png')}}" alt="Preview" id="image-preview" height="80px" width="80px">
                            </div>

                           
                            <div class="col-md-12">
                                <div class="mt-2" style="float:right">
                                    <button type="submit" class="btn btn-primary">Proceed</button>
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
    function previewImage() {
        var imageInput = document.getElementById('image-input');
        var imagePreview = document.getElementById('image-preview');
        
        if (imageInput.files && imageInput.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                imagePreview.src = e.target.result;
            };
            
            reader.readAsDataURL(imageInput.files[0]);
        } else {
            // If no file is selected or supported, clear the preview
            imagePreview.src = "";
        }
    }

</script>
<script>
    function generateSKU() {
        let po_date = document.querySelector("input[name='time']").value.trim();
        let po_vendor = document.querySelector("select[name='vendor_id'] option:checked").text.trim();
        let truckNumber = document.getElementById("truck_number").value.trim();
        let roll = document.getElementById("roll").value.trim().replace(/\s+/g, '');
        let receivedBy = document.getElementById("received_by").value.trim().replace(/\s+/g, '');

        // 1️⃣ Vendor first 3 letters (alphanumeric only)
        let part1 = po_vendor.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();

        // 2️⃣ Truck number (letters & numbers only)
        let part2 = truckNumber.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        // 3️⃣ Date in format DDMMYYYY
        let part3 = "";
        if (po_date) {
            let dateObj = new Date(po_date);
            let day = String(dateObj.getDate()).padStart(2, '0');
            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
            let year = dateObj.getFullYear();
            part3 = day + month + year;
        }

        // 4️⃣ Rolls (no spaces, alphanumeric only)
        let part4 = roll.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        // 5️⃣ Received by (first 3 letters, alphanumeric only)
        let part5 = receivedBy.replace(/[^a-zA-Z0-9]/g, '').substring(0, 3).toUpperCase();

        // ✅ Combine all parts
        let sku = [part1, part2, part3, part4, part5].filter(Boolean).join("-");

        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    // When page is ready
    $(document).ready(function() {
        // Trigger SKU when any field changes
        $('input[name="time"]').on("change", generateSKU);
        $('select[name="vendor_id"]').on("change", generateSKU);
        $('#truck_number, #roll, #received_by').on("input", generateSKU);

        // Generate on page load
        generateSKU();

        // Mark SKU as edited if user types manually
        $("#sku").on("input", function() {
            this.dataset.edited = true;
        });
    });
</script>



@endsection
