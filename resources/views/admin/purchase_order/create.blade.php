@extends('admin.layouts.app')
@section('content')

    <div class="content-wrapper erp-page p-2">
        <!-- Slim Header Bar -->
        <div class="erp-header-bar">
            <div class="erp-header-title">
                <i class="fas fa-file-invoice text-primary"></i> Create Purchase Order For Fabric
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.purchase_order.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to List
                </a>
            </div>
        </div>

        <form action="{{ route('admin.purchase_order.store') }}" method="post" id="poForm">
            @csrf
            <input type="hidden" name="sku" id="sku">

            <!-- Card 1: Voucher Details -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <i class="fas fa-info-circle text-primary"></i> Purchase Order Details
                    </div>
                </div>
                <div class="erp-card-body p-2">
                    <div class="row">
                        {{-- PO Date --}}
                        <div class="col-md-3 col-sm-6 erp-form-group">
                            <label class="erp-label">PO Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control erp-input"
                                value="{{ old('date') ?? date('Y-m-d') }}" required>
                        </div>

                        {{-- Vendor --}}
                        <div class="col-md-3 col-sm-6 erp-form-group">
                            <label class="erp-label">
                                <span>Vendor <span class="required">*</span></span>
                                <span>
                                    <a href="{{ route('admin.master.vendor.create') }}" target="_blank" class="erp-pill-btn erp-pill-new" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshVendorBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="vendor_id" class="form-control select2 erp-input" required>
                                <option value="">Select Vendor</option>
                                @foreach($vendors as $v)
                                    <option value="{{ $v->id }}" {{ $selected_vendor_id == $v->id ? 'selected' : '' }}>
                                        {{ $v->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Warehouse --}}
                        <div class="col-md-3 col-sm-6 erp-form-group">
                            <label class="erp-label">
                                <span>Delivery Warehouse <span class="required">*</span></span>
                                <span>
                                    <a href="{{ route('admin.master.fabric_warehouse.create') }}" target="_blank" class="erp-pill-btn erp-pill-new" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshWarehouseBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="fabric_warehouse_id" id="warehouse-select" class="form-control select2 erp-input" required>
                                <option value="">Select Warehouse</option>
                                @foreach($fabric_warehouses as $w)
                                    <option value="{{ $w->id }}">{{ $w->cutting_master_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Expected Delivery Date --}}
                        <div class="col-md-3 col-sm-6 erp-form-group">
                            <label class="erp-label">Delivery Date <span class="required">*</span></label>
                            <input type="date" name="delivery_date" class="form-control erp-input" value="{{ old('delivery_date') }}" required>
                        </div>

                        {{-- Company --}}
                        <div class="col-md-3 col-sm-6 erp-form-group">
                            <label class="erp-label">
                                <span>Company <span class="required">*</span></span>
                                <span>
                                    <a href="{{ route('admin.master.company.create') }}" target="_blank" class="erp-pill-btn erp-pill-new" title="Create New"><i class="fas fa-plus"></i> New</a>
                                    <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh" id="refreshCompanyBtn" title="Refresh"><i class="fas fa-sync-alt"></i></a>
                                </span>
                            </label>
                            <select name="master_company_id" id="company-select" class="form-control select2 erp-input" required>
                                <option value="">Select Company</option>
                                @foreach($companies as $c)
                                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transport --}}
                        <div class="col-md-3 col-sm-6 erp-form-group">
                            <label class="erp-label">Transport Details</label>
                            <input type="text" name="transport" class="form-control erp-input" placeholder="e.g. Courier / By Road / Truck">
                        </div>

                        {{-- Remark --}}
                        <div class="col-md-6 col-sm-12 erp-form-group">
                            <label class="erp-label">Remarks / Notes</label>
                            <input type="text" name="remark" class="form-control erp-input" placeholder="Enter remarks...">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card 2: Fabric Line Items -->
            <div class="erp-card">
                <div class="erp-card-header">
                    <div class="erp-card-title">
                        <i class="fas fa-layer-group text-primary"></i> Fabric & Pricing Items
                    </div>
                    <div class="d-flex align-items-center">
                        <a href="{{ route('admin.master.fabric.create') }}" target="_blank" class="erp-pill-btn erp-pill-new mr-1" title="Create New Fabric"><i class="fas fa-plus"></i> New Fabric</a>
                        <a href="javascript:void(0)" class="erp-pill-btn erp-pill-refresh refreshFabricBtn" title="Refresh Fabrics"><i class="fas fa-sync-alt"></i> Refresh</a>
                    </div>
                </div>
                <div class="erp-card-body p-2 table-responsive">
                    <table class="erp-table table table-bordered" id="fabricTable">
                        <thead>
                            <tr>
                                <th style="width: 40px;" class="text-center">#</th>
                                <th>Fabric</th>
                                <th style="width: 140px;">Meters / Qty <span class="required">*</span></th>
                                <th style="width: 140px;">Rate / Price (₹)</th>
                                <th style="width: 160px;">Total (₹)</th>
                                <th style="width: 50px;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="fabricRollContainer">
                            <tr class="fabric-roll-row">
                                <td class="text-center row-num">1</td>
                                <td>
                                    <select name="fabrics[0][fabric_id]" class="form-control erp-input fabric-select select2" required style="width: 100%;">
                                        <option value="">Select Fabric</option>
                                        @foreach($fabrics as $f)
                                            <option value="{{ $f->id }}" data-sku="{{ $f->sku }}" {{ $selected_fabric_id == $f->id ? 'selected' : '' }}>
                                                {{ $f->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="hidden" name="fabrics[0][sku]" class="item-sku">
                                </td>
                                <td>
                                    <input type="number" name="fabrics[0][meter]" class="form-control erp-input meter-input" placeholder="0.00" step="any" required value="{{ $selected_total_meter ?? '' }}">
                                </td>
                                <td>
                                    <input type="number" name="fabrics[0][price]" class="form-control erp-input price-input" placeholder="0.00" step="any">
                                </td>
                                <td>
                                    <input type="number" name="fabrics[0][total_price]" class="form-control erp-input total-price-input" readonly value="0.00" step="any">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="erp-action-btn erp-btn-delete removeRow" title="Remove Row"><i class="fas fa-trash"></i></button>
                                </td>
                            </tr>
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="p-1 text-right" style="background: #ffffff; border-top: 1px solid #cbd5e1;">
                                    <button type="button" class="btn-erp btn-erp-success addRow">
                                        <i class="fas fa-plus mr-1"></i> Add Item Row
                                    </button>
                                </td>
                            </tr>
                            <tr style="background: #f8fafc; font-weight: 700;">
                                <td colspan="2" class="text-right text-uppercase" style="font-size: 11px;">Total:</td>
                                <td id="totalMetersSummary" class="text-primary font-weight-bold">0.00</td>
                                <td></td>
                                <td id="totalAmountSummary" class="text-success font-weight-bold">₹ 0.00</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- Footer Action Bar -->
            <div class="erp-card p-2 text-right">
                <a href="{{ route('admin.purchase_order.index') }}" class="btn-erp btn-erp-outline mr-2">Cancel</a>
                <button type="submit" class="btn-erp btn-erp-primary">
                    <i class="fas fa-save mr-1"></i> Save Purchase Order
                </button>
            </div>
        </form>
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

            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

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
                        $s.html(options).val('').select2({ theme: 'bootstrap4', width: '100%' });
                    });
                });
            });

            function recalculateTotals() {
                let totalM = 0;
                let totalAmt = 0;
                $('.fabric-roll-row').each(function (index) {
                    $(this).find('.row-num').text(index + 1);
                    let meter = parseFloat($(this).find('.meter-input').val()) || 0;
                    let price = parseFloat($(this).find('.price-input').val()) || 0;
                    let lineTotal = meter * price;
                    $(this).find('.total-price-input').val(lineTotal.toFixed(2));
                    totalM += meter;
                    totalAmt += lineTotal;
                });
                $('#totalMetersSummary').text(totalM.toFixed(2));
                $('#totalAmountSummary').text('₹ ' + totalAmt.toFixed(2));
            }

            // Add row
            $(document).on('click', '.addRow', function () {
                let options = buildFabricOptions(currentVendorFabrics);
                let row = `
                    <tr class="fabric-roll-row">
                        <td class="text-center row-num">${rowIndex + 1}</td>
                        <td>
                            <select name="fabrics[${rowIndex}][fabric_id]" class="form-control erp-input fabric-select select2" required style="width: 100%;">
                                ${options}
                            </select>
                            <input type="hidden" name="fabrics[${rowIndex}][sku]" class="item-sku">
                        </td>
                        <td>
                            <input type="number" name="fabrics[${rowIndex}][meter]" class="form-control erp-input meter-input" required placeholder="0.00" step="any">
                        </td>
                        <td>
                            <input type="number" name="fabrics[${rowIndex}][price]" class="form-control erp-input price-input" placeholder="0.00" step="any">
                        </td>
                        <td>
                            <input type="number" name="fabrics[${rowIndex}][total_price]" class="form-control erp-input total-price-input" readonly value="0.00" step="any">
                        </td>
                        <td class="text-center">
                            <button type="button" class="erp-action-btn erp-btn-delete removeRow" title="Remove Row"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                `;

                $('#fabricRollContainer').append(row);
                $('#fabricRollContainer .fabric-select').last().select2({ theme: 'bootstrap4', width: '100%' });
                rowIndex++;
                recalculateTotals();
            });

            // Remove row
            $(document).on('click', '.removeRow', function () {
                if ($('.fabric-roll-row').length > 1) {
                    $(this).closest('.fabric-roll-row').remove();
                    recalculateTotals();
                } else {
                    alert('At least one item row is required.');
                }
            });

            // Calculate total
            $(document).on('keyup change', '.meter-input, .price-input', function () {
                recalculateTotals();
            });

            // Initial calculate
            recalculateTotals();

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
                        $s.html(options).val(currentVal).select2({ theme: 'bootstrap4', width: '100%' });
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
                    select.select2({ theme: 'bootstrap4', width: '100%' });
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
                    select.select2({ theme: 'bootstrap4', width: '100%' });
                    btn.html('<i class="fas fa-sync-alt"></i>');
                }).fail(function() {
                    btn.html('<i class="fas fa-sync-alt"></i>');
                });
            });

        });
    </script>
    <script>
        if (document.getElementById("po_date")) {
            flatpickr("#po_date", {
                dateFormat: "d M Y",
                defaultDate: "{{ \Carbon\Carbon::now()->format('Y-m-d') }}",
                onChange: function (selectedDates) {
                    if (document.getElementById("po_date_hidden")) {
                        document.getElementById("po_date_hidden").value =
                            flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    }
                }
            });
        }

        if (document.getElementById("delivery_date")) {
            flatpickr("#delivery_date", {
                dateFormat: "d M Y",
                minDate: "today",
                onChange: function (selectedDates) {
                    if (document.getElementById("delivery_date_hidden")) {
                        document.getElementById("delivery_date_hidden").value =
                            flatpickr.formatDate(selectedDates[0], "Y-m-d");
                    }
                }
            });
        }
    </script>

@endsection