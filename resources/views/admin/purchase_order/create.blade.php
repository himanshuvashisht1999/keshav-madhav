@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Purchase Order</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Create Purchase Order</li>
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
                    <h3 class="card-title">Create Purchase Order</h3>
                </div>
                <form action="{{route('admin.purchase_order.store')}}" method="post" enctype="multipart/form-data">
                    @csrf
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="exampleInputEmail1">Date</label>
                                    <input type="date" name="date" class="form-control" placeholder="Enter date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                                    @if ($errors->has('date'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('date') }}
                                        </span>
                                    @endif
                                </div>
                            </div>

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
                                    <label for="exampleInputEmail1">Delivery Date</label>
                                    <input type="date" name="delivery_date" class="form-control" placeholder="Enter delivery date" value="{{old('delivery_date')}}" required>
                                    @if ($errors->has('delivery_date'))
                                        <span class="invalid-feedback d-block">
                                        {{ $errors->first('delivery_date') }}
                                        </span>
                                    @endif
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


                            <!-- Dynamic Fabric & Rolls Section -->
                            <div class="col-md-12">
                                <label>Fabric & Prices</label>
                                <div id="fabricRollContainer">
                                    <div class="row fabric-roll-row mb-2">
                                        <div class="col-md-4">
                                            <select name="fabrics[0][fabric_id]" class="form-control fabric-select select2" style="width:100%" required>
                                                <option value="">Select Fabric</option>
                                                @foreach($fabrics as $single_data)
                                                <option value="{{$single_data->id}}" data-sku="{{$single_data->sku}}">{{$single_data->sku}}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="fabrics[0][meter]" class="form-control meter-input" placeholder="Enter meters" required>
                                        </div>
                                        <div class="col-md-2">
                                            <input type="number" name="fabrics[0][price]" class="form-control meter-input" placeholder="Enter Price (per meter)" required>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="number" name="fabrics[0][total_price]" class="form-control meter-input" placeholder="Enter total price" value="0" readonly required>
                                        </div>
                                        
                                        <div class="col-md-4" style="display:none;">
                                            <input type="hidden" name="fabrics[0][sku]" class="form-control item-sku" placeholder="Auto SKU">
                                        </div>
                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-success addRow">+</button>
                                        </div>
                                    </div>
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
        let po_date = document.querySelector("input[name='date']").value.trim();
        let po_vendor = document.querySelector("select[name='vendor_id'] option:checked").text.trim();

        // Get first 4 letters of vendor name (allow alphanumeric)
        let part1 = po_vendor.replace(/[^a-zA-Z0-9]/g, '').substring(0, 4).toUpperCase();
        // let part1 = po_vendor.replace(/[^a-zA-Z0-9]/g, '').toUpperCase();

        // Use date in format YYYYMMDD (or just last 6 if you want short)
        let part2 = "";
        if (po_date) {
            let dateObj = new Date(po_date);
            let day = String(dateObj.getDate()).padStart(2, '0');
            let month = String(dateObj.getMonth() + 1).padStart(2, '0');
            let year = dateObj.getFullYear();
            part2 = day + month + year; // DDMMYYYY
        }

        // Random 4-digit number
        let part3 = Math.floor(1000 + Math.random() * 9000);

        // Combine
        // let sku = 'PO-' + part1 + "-" + part2 + "-" + part3;
        let sku = 'PO-' + part1 + "-" + part2;

        let skuInput = document.getElementById("sku");
        if (!skuInput.dataset.edited || skuInput.value === "") {
            skuInput.value = sku;
        }
    }

    // When page is ready
    $(document).ready(function() {
        // Trigger SKU when date or vendor changes
        $('input[name="date"]').on("change", generateSKU);
        $('select[name="vendor_id"]').on("change", generateSKU);

        // Also generate on page load (with default date/vendor)
        generateSKU();

        // Mark SKU as edited if user types
        $("#sku").on("input", function() {
            this.dataset.edited = true;
        });
    });
</script>

<script>
    $(document).ready(function() {
        let index = 1;

        function calculateTotal(row) {
            let meter = parseFloat(row.find("input[name*='[meter]']").val()) || 0;
            let price = parseFloat(row.find("input[name*='[price]']").val()) || 0;
            let total = meter * price;
            row.find("input[name*='[total_price]']").val(total.toFixed(2)); // keep 2 decimals
        }

        // Function to generate SKU for a row
        function generateItemSKU(row) {
            let vendor = $("select[name='vendor_id'] option:selected").text().trim();
            let vendorPart = vendor.replace(/[^A-Za-z0-9]/g, '').substring(0, 4).toUpperCase();

            let dateVal = $("input[name='date']").val();
            let datePart = "";
            if (dateVal) {
                let d = new Date(dateVal);
                let day = String(d.getDate()).padStart(2, '0');
                let month = String(d.getMonth() + 1).padStart(2, '0');
                let year = d.getFullYear();
                datePart = day + month + year; // DDMMYYYY
            }

            let fabricPart = row.find(".fabric-select option:selected").data("sku") || "";
            let roll = row.find(".meter-input").val() || "";

            if (vendorPart && datePart && fabricPart && roll) {
                let sku = vendorPart + "-" + datePart + "-" + fabricPart + "-" + roll;
                row.find(".item-sku").val(sku);
            }
        }

        // Trigger SKU generation on change
        $(document).on("change keyup", ".fabric-select, .meter-input, select[name='vendor_id'], input[name='date']", function() {
            let row = $(this).closest(".fabric-roll-row");
            calculateTotal(row);
            generateItemSKU(row);
        });

        // Add new row
        $(document).on("click", ".addRow", function() {
            let newRow = `
                <div class="row fabric-roll-row mb-2">
                    <div class="col-md-4">
                        <select name="fabrics[${index}][fabric_id]" class="form-control fabric-select select2" style="width:100%" required>
                            <option value="">Select Fabric</option>
                            @foreach($fabrics as $single_data)
                            <option value="{{$single_data->id}}" data-sku="{{$single_data->sku}}">{{$single_data->sku}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="fabrics[${index}][meter]" class="form-control meter-input" placeholder="Enter meters" required>
                    </div>
                    <div class="col-md-2">
                        <input type="number" name="fabrics[${index}][price]" class="form-control meter-input" placeholder="Enter Price (per meter)" required>
                    </div>
                    <div class="col-md-3">
                        <input type="number" name="fabrics[${index}][total_price]" class="form-control meter-input" placeholder="Enter total price" value="0" readonly>
                    </div>
                    <div class="col-md-4"  style="display:none;">
                        <input type="hidden" name="fabrics[${index}][sku]" class="form-control item-sku" placeholder="Auto SKU">
                    </div>
                    <div class="col-md-1">
                        <button type="button" class="btn btn-danger removeRow">-</button>
                    </div>
                </div>`;
            $("#fabricRollContainer").append(newRow);
            $(".select2").select2();
            index++;
        });

        // Remove row
        $(document).on("click", ".removeRow", function() {
            $(this).closest(".fabric-roll-row").remove();
        });
    });
</script>




@endsection
