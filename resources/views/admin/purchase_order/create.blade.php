@extends('admin.layouts.app')
@section('content')

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12 text-center">
                    <h1>Create Purchase Order For Fabric</h1>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">

                <div class="card-header mb-1" style="background: blue;">
                    <h3 style="color:white;text-align:center;font-size:1.1rem;font-weight:600;margin:0;">
                        Purchase Order
                    </h3>
                </div>

                <form action="{{ route('admin.purchase_order.store') }}" method="post">
                    @csrf

                    <div class="card-body">
                        <div class="row">

                            {{-- PO Date --}}
                            <div class="col-md-6">
                                <label>Purchase Order Date</label>

                                <input type="text"
                                    id="po_date"
                                    class="form-control"
                                    placeholder="Select PO Date">

                                <input type="hidden"
                                    name="date"
                                    id="po_date_hidden"
                                    value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}">
                            </div>

                            {{-- Vendor --}}
                            <div class="col-md-6">
                                <label>Vendor</label>
                                <select name="vendor_id" class="form-control select2">
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}"
                                            {{ $selected_vendor_id == $v->id ? 'selected' : '' }}>
                                            {{ $v->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Warehouse --}}
                            <div class="col-md-6 mt-2">
                                <label>Delivery Warehouse</label>
                                <select name="fabric_warehouse_id" class="form-control select2">
                                    @foreach($fabric_warehouses as $w)
                                        <option value="{{ $w->id }}">{{ $w->cutting_master_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Delivery Date --}}
                            <div class="col-md-6 mt-2">
                                <label>Expected Delivery Date</label>

                                <input type="text"
                                    id="delivery_date"
                                    class="form-control"
                                    placeholder="Select Expected Delivery Date">

                                <input type="hidden"
                                    name="delivery_date"
                                    id="delivery_date_hidden">
                            </div>

                            {{-- Hidden SKU --}}
                            <input type="hidden" name="sku" id="sku">

                            {{-- FABRIC SECTION --}}
                            <div class="col-md-12 mt-3">
                                <div class="card-header mb-1" style="background: blue;">
                                    <h3 style="color:white;text-align:center;font-size:1.1rem;font-weight:600;margin:0;">
                                        Fabric & Prices
                                    </h3>
                                </div>

                                <div id="fabricRollContainer">
                                    <div class="row fabric-roll-row mb-2">

                                        <div class="col-md-4">
                                            <select name="fabrics[0][fabric_id]"
                                                    class="form-control fabric-select select2" required>
                                                <option value="">Select Fabric</option>
                                                @foreach($fabrics as $f)
                                                    <option value="{{ $f->id }}" data-sku="{{ $f->sku }}">
                                                        {{ $f->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-2">
                                            <input type="number" name="fabrics[0][meter]"
                                                   class="form-control meter-input"
                                                   placeholder="Meters" step="any" required>
                                        </div>

                                        <div class="col-md-2">
                                            <input type="number" name="fabrics[0][price]"
                                                   class="form-control meter-input"
                                                   placeholder="Price" step="any">
                                        </div>

                                        <div class="col-md-3">
                                            <input type="number" name="fabrics[0][total_price]"
                                                   class="form-control" readonly value="0" step="any">
                                        </div>

                                        <input type="hidden" name="fabrics[0][sku]" class="item-sku">

                                        <div class="col-md-1">
                                            <button type="button" class="btn btn-success addRow">+</button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- Submit --}}
                            <div class="col-md-12 mt-3 text-right">
                                <button type="submit" class="btn btn-primary">Submit</button>
                            </div>

                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

{{-- ================= JS ================= --}}

<script>
    // ✅ initial vendor fabrics from backend
    let currentVendorFabrics = @json($fabrics);
    let rowIndex = 1;
</script>

<script>
    function buildFabricOptions(fabrics) {
        let html = '<option value="">Select Fabric</option>';
        fabrics.forEach(f => {
            html += `<option value="${f.id}" data-sku="${f.sku ?? ''}">${f.name}</option>`;
        });
        return html;
    }
</script>

<script>
$(document).ready(function(){

    $('.select2').select2({ width:'100%' });

    // Vendor change → reload fabrics
    $('select[name="vendor_id"]').on('change', function () {

        let vendorId = $(this).val();
        let url = "{{ route('admin.purchase_order.vendor_fabrics', 'VID') }}".replace('VID', vendorId);

        $.getJSON(url, function (data) {

            currentVendorFabrics = data;
            let options = buildFabricOptions(data);

            $('.fabric-select').each(function () {
                let $s = $(this);
                if ($s.hasClass('select2-hidden-accessible')) {
                    $s.select2('destroy');
                }
                $s.html(options).val('').select2({ width:'100%' });
            });
        });
    });

    // Add row
    $(document).on('click', '.addRow', function () {

        let options = buildFabricOptions(currentVendorFabrics);

        let row = `
            <div class="row fabric-roll-row mb-2">
                <div class="col-md-4">
                    <select name="fabrics[${rowIndex}][fabric_id]"
                            class="form-control fabric-select select2" required>
                        ${options}
                    </select>
                </div>

                <div class="col-md-2">
                    <input type="number" name="fabrics[${rowIndex}][meter]"
                           class="form-control meter-input" required placeholder="Meters" step="any">
                </div>

                <div class="col-md-2">
                    <input type="number" name="fabrics[${rowIndex}][price]"
                           class="form-control meter-input" placeholder="Price" step="any">
                </div>

                <div class="col-md-3">
                    <input type="number" name="fabrics[${rowIndex}][total_price]"
                           class="form-control" readonly value="0" step="any">
                </div>

                <input type="hidden" name="fabrics[${rowIndex}][sku]" class="item-sku">

                <div class="col-md-1">
                    <button type="button" class="btn btn-danger removeRow">-</button>
                </div>
            </div>
        `;

        $('#fabricRollContainer').append(row);
        $('#fabricRollContainer .fabric-select').last().select2({ width:'100%' });
        rowIndex++;
    });

    // Remove row
    $(document).on('click', '.removeRow', function(){
        $(this).closest('.fabric-roll-row').remove();
    });

    // Calculate total + SKU
    $(document).on('keyup change', '.meter-input, .fabric-select', function(){
        let row = $(this).closest('.fabric-roll-row');
        let meter = parseFloat(row.find('[name*="[meter]"]').val()) || 0;
        let price = parseFloat(row.find('[name*="[price]"]').val()) || 0;
        row.find('[name*="[total_price]"]').val((meter * price).toFixed(2));
    });

});
</script>
<script>
flatpickr("#po_date", {
    dateFormat: "d M Y",
    defaultDate: "{{ \Carbon\Carbon::now()->format('Y-m-d') }}",
    onChange: function(selectedDates) {
        document.getElementById("po_date_hidden").value =
            flatpickr.formatDate(selectedDates[0], "Y-m-d");
    }
});

flatpickr("#delivery_date", {
    dateFormat: "d M Y",
    minDate: "today",
    onChange: function(selectedDates) {
        document.getElementById("delivery_date_hidden").value =
            flatpickr.formatDate(selectedDates[0], "Y-m-d");
    }
});
</script>

@endsection
