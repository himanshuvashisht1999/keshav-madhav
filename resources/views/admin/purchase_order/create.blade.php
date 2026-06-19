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

                    <div class="card-header mb-1" style="background: #007bff;">
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

                                    <input type="date" name="date" id="" class="form-control"
                                        value="{{ old('date') ?? date('Y-m-d') }}" placeholder="Select PO Date">

                                    <!-- <input type="hidden"
                                                name="date"
                                                id="po_date_hidden"
                                                value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}"> -->
                                </div>

                                {{-- Vendor --}}
                                <div class="col-md-6">
                                    <label class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Vendor</span>
                                        <span class="action-links">
                                            <a href="{{ route('admin.master.vendor.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                            <a href="javascript:void(0)" class="text-info" id="refreshVendorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                        </span>
                                    </label>
                                    <select name="vendor_id" class="form-control select2">
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ $selected_vendor_id == $v->id ? 'selected' : '' }}>
                                                {{ $v->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Warehouse --}}
                                <div class="col-md-6 mt-2">
                                    <label class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Delivery Warehouse</span>
                                        <span class="action-links">
                                            <a href="{{ route('admin.master.fabric_warehouse.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                            <a href="javascript:void(0)" class="text-info" id="refreshWarehouseBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                        </span>
                                    </label>
                                    <select name="fabric_warehouse_id" id="warehouse-select" class="form-control select2" required>
                                        @foreach($fabric_warehouses as $w)
                                            <option value="{{ $w->id }}">{{ $w->cutting_master_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Delivery Date --}}
                                <div class="col-md-6 mt-2">
                                    <label>Expected Delivery Date</label>

                                    <input type="date" name="delivery_date" class="form-control" value="{{ old('delivery_date') }}" placeholder="Select Expected Delivery Date" required>


                                </div>

                                {{-- Company --}}
                                <div class="col-md-6 mt-2">
                                    <label class="d-flex justify-content-between align-items-center mb-1">
                                        <span>Company</span>
                                        <span class="action-links">
                                            <a href="{{ route('admin.master.company.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                            <a href="javascript:void(0)" class="text-info" id="refreshCompanyBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                        </span>
                                    </label>
                                    <select name="master_company_id" id="company-select" class="form-control select2" required>
                                        <option value="">Select Company</option>
                                        @foreach($companies as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Transport --}}
                                <div class="col-md-6 mt-2">
                                    <label>Transport</label>
                                    <input type="text" name="transport" class="form-control"
                                        placeholder="Enter Transport Details">
                                </div>

                                {{-- Remark --}}
                                <div class="col-md-12 mt-2">
                                    <label>Remark</label>
                                    <textarea name="remark" class="form-control" rows="2"
                                        placeholder="Enter Remark"></textarea>
                                </div>

                                {{-- Hidden SKU --}}
                                <input type="hidden" name="sku" id="sku">

                                {{-- FABRIC SECTION --}}
                                <div class="col-md-12 mt-3">
                                    <div class="card-header mb-1" style="background: #007bff;">
                                        <h3
                                            style="color:white;text-align:center;font-size:1.1rem;font-weight:600;margin:0;">
                                            Fabric & Prices
                                        </h3>
                                    </div>

                                    <div id="fabricRollContainer">
                                        <div class="row fabric-roll-row mb-2">

                                            <div class="col-md-4">
                                                <label class="d-flex justify-content-between align-items-center mb-1">
                                                    <span>Select Fabric</span>
                                                    <span class="action-links">
                                                        <a href="{{ route('admin.master.fabric.create') }}" target="_blank" class="text-primary mr-2" title="Create New"><i class="fas fa-plus"></i> New</a>
                                                        <a href="javascript:void(0)" class="text-info refreshFabricBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                                    </span>
                                                </label>
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

                                            <div class="col-md-2 mt-4 pt-1">
                                                <input type="number" name="fabrics[0][meter]"
                                                    class="form-control meter-input" placeholder="Meters" step="any"
                                                    required>
                                            </div>

                                            <div class="col-md-2 mt-4 pt-1">
                                                <input type="number" name="fabrics[0][price]"
                                                    class="form-control meter-input" placeholder="Price" step="any">
                                            </div>

                                            <div class="col-md-3 mt-4 pt-1">
                                                <input type="number" name="fabrics[0][total_price]" class="form-control"
                                                    readonly value="0" step="any">
                                            </div>

                                            <input type="hidden" name="fabrics[0][sku]" class="item-sku">

                                            <div class="col-md-1 mt-4 pt-1">
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
        $(document).ready(function () {

            $('.select2').select2({ width: '100%' });

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
                        $s.html(options).val('').select2({ width: '100%' });
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
                $('#fabricRollContainer .fabric-select').last().select2({ width: '100%' });
                rowIndex++;
            });

            // Remove row
            $(document).on('click', '.removeRow', function () {
                $(this).closest('.fabric-roll-row').remove();
            });

            // Calculate total + SKU
            $(document).on('keyup change', '.meter-input, .fabric-select', function () {
                let row = $(this).closest('.fabric-roll-row');
                let meter = parseFloat(row.find('[name*="[meter]"]').val()) || 0;
                let price = parseFloat(row.find('[name*="[price]"]').val()) || 0;
                row.find('[name*="[total_price]"]').val((meter * price).toFixed(2));
            });

            // Refresh Vendor
            $('#refreshVendorBtn').click(function () {
                let btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.purchase_order.all_vendors') }}", function (data) {
                    let select = $('select[name="vendor_id"]');
                    let currentVal = select.val();
                    select.empty();
                    select.append('<option value="">Select Vendor</option>');
                    data.forEach(function (v) {
                        select.append(`<option value="${v.id}">${v.name}</option>`);
                    });
                    if (currentVal) select.val(currentVal);
                    select.trigger('change');
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

            // Refresh Fabric
            $(document).on('click', '.refreshFabricBtn', function () {
                let btn = $(this);
                let vendorId = $('select[name="vendor_id"]').val();
                let url = vendorId ? "{{ route('admin.purchase_order.vendor_fabrics', 'VID') }}".replace('VID', vendorId) : "{{ route('admin.purchase_order.all_fabrics') }}";
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON(url, function (data) {
                    currentVendorFabrics = data;
                    let options = buildFabricOptions(data);
                    $('.fabric-select').each(function () {
                        let $s = $(this);
                        let currentVal = $s.val();
                        if ($s.hasClass('select2-hidden-accessible')) {
                            $s.select2('destroy');
                        }
                        $s.html(options).val(currentVal).select2({ width: '100%' });
                    });
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

            // Refresh Warehouse
            $('#refreshWarehouseBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.purchase_order.all_warehouses') }}", function(data) {
                    var select = $('#warehouse-select');
                    var currentVal = select.val();
                    if (select.hasClass('select2-hidden-accessible')) {
                        select.select2('destroy');
                    }
                    select.empty();
                    data.forEach(function(item) {
                        select.append('<option value="' + item.id + '">' + item.cutting_master_name + '</option>');
                    });
                    if (currentVal) select.val(currentVal);
                    select.select2({ width: '100%' });
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

            // Refresh Company
            $('#refreshCompanyBtn').on('click', function() {
                var btn = $(this);
                btn.html('<i class="fas fa-spinner fa-spin"></i>');
                $.getJSON("{{ route('admin.purchase_order.all_companies') }}", function(data) {
                    var select = $('#company-select');
                    var currentVal = select.val();
                    if (select.hasClass('select2-hidden-accessible')) {
                        select.select2('destroy');
                    }
                    select.empty();
                    select.append('<option value="">Select Company</option>');
                    data.forEach(function(item) {
                        select.append('<option value="' + item.id + '">' + item.name + '</option>');
                    });
                    if (currentVal) select.val(currentVal);
                    select.select2({ width: '100%' });
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

        });
    </script>
    <script>
        flatpickr("#po_date", {
            dateFormat: "d M Y",
            defaultDate: "{{ \Carbon\Carbon::now()->format('Y-m-d') }}",
            onChange: function (selectedDates) {
                document.getElementById("po_date_hidden").value =
                    flatpickr.formatDate(selectedDates[0], "Y-m-d");
            }
        });

        flatpickr("#delivery_date", {
            dateFormat: "d M Y",
            minDate: "today",
            onChange: function (selectedDates) {
                document.getElementById("delivery_date_hidden").value =
                    flatpickr.formatDate(selectedDates[0], "Y-m-d");
            }
        });
    </script>

@endsection