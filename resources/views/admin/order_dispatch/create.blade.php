@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">

        <!-- PAGE HEADER -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h1 class="mb-0">Order Dispatch</h1>
                        <small class="text-muted">Search order, verify cartons & dispatch</small>
                    </div>
                </div>
            </div>
        </section>

        <!-- CONTENT -->
        <section class="content">
            <div class="container-fluid">

                <form method="POST" action="{{ route('admin.order-dispatch.store') }}">
                    @csrf

                    <!-- SEARCH SUMMARY -->
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <div class="row align-items-end">

                                <div class="col-md-3">
                                    <label class="font-weight-semibold">Order No</label>
                                    {{-- <input type="text" id="search_order_no" name="search_order_no" class="form-control"
                                        placeholder="Enter Order Number"> --}}
                                    <select id="search_order_no" name="search_order_no" class="form-control select2">
                                        <option value="">Select Order No</option>
                                        @foreach ($orders as $order)
                                            <option value="{{ $order->id }}">
                                                {{ $order->order_no }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-1 text-center text-muted font-weight-bold">
                                    OR
                                </div>

                                <div class="col-md-4">
                                    <label class="font-weight-semibold">Customer</label>
                                    <select id="customer_id" class="form-control select2">
                                        <option value="">Select Customer</option>
                                        @foreach($customers as $customer)
                                            <option value="{{ $customer->id }}">
                                                {{ $customer->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4">
                                    <label class="font-weight-semibold">Order No</label>
                                    <select id="order_no" name="order_no" class="form-control select2">
                                        <option value="">Select Order No</option>
                                    </select>
                                </div>

                            </div>
                        </div>
                    </div>

                    <!-- DOCUMENT TOGGLE -->
                    <div class="text-right mb-2 d-none" id="docToggleWrapper">
                        <button type="button" id="toggleDocumentBtn" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-eye mr-1"></i> View Document
                        </button>
                    </div>

                    <!-- DOCUMENT PREVIEW -->
                    <div class="row d-none mb-3" id="image_view">
                        <div class="col-md-12">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    <img id="previewImg" class="w-100" style="border-radius:6px; display:none;">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- PRICE SETUP (GLOBAL) -->
                    <div id="priceSetupContainer"></div>

                    <!-- ORDER DATA -->
                    <div id="orderContainer"></div>

                    <!-- DISPATCH SUMMARY & CALCULATIONS -->
                    <div class="row mt-4 d-none" id="summaryContainer">
                        <div class="col-md-6 offset-md-6">
                            <div class="card shadow-sm border-success">
                                <div class="card-header bg-success text-white py-2">
                                    <strong><i class="fas fa-calculator mr-2"></i> Dispatch Summary</strong>
                                </div>
                                <div class="card-body p-3">
                                    <div class="row mb-2">
                                        <div class="col-7 text-right align-middle"><strong>Subtotal (₹)</strong></div>
                                        <div class="col-5">
                                            <input type="text" id="calc_subtotal"
                                                class="form-control form-control-sm text-right font-weight-bold" readonly
                                                value="0.00">
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-7 text-right align-middle">
                                            <strong>Discount (%)</strong>
                                        </div>
                                        <div class="col-5">
                                            <input type="number" name="discount_percentage" id="calc_discount_p"
                                                class="form-control form-control-sm text-right" step="0.01" min="0"
                                                max="100" value="0.00">
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-7 text-right align-middle text-muted"><small>Discount Amount
                                                (₹)</small></div>
                                        <div class="col-5">
                                            <input type="text" id="calc_discount_v"
                                                class="form-control form-control-sm text-right text-muted" readonly
                                                value="0.00">
                                        </div>
                                    </div>

                                    <div class="row mb-2">
                                        <div class="col-7 text-right align-middle">
                                            <strong>GST (%)</strong>
                                        </div>
                                        <div class="col-5">
                                            <input type="number" name="gst_percentage" id="calc_gst_p"
                                                class="form-control form-control-sm text-right" step="0.01" min="0"
                                                max="100" value="5.00">
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <div class="col-7 text-right align-middle text-muted"><small>GST Amount (₹)</small>
                                        </div>
                                        <div class="col-5">
                                            <input type="text" id="calc_gst_v"
                                                class="form-control form-control-sm text-right text-muted" readonly
                                                value="0.00">
                                        </div>
                                    </div>

                                    <hr class="my-2">

                                    <div class="row">
                                        <div class="col-7 text-right align-middle">
                                            <h5 class="mb-0 font-weight-bold">Grand Total (₹)</h5>
                                        </div>
                                        <div class="col-5">
                                            <input type="hidden" name="total_amount" id="final_total_amount_hidden">
                                            <input type="text" id="calc_grand_total"
                                                class="form-control text-right font-weight-bold text-success border-success"
                                                readonly style="font-size: 1.25rem;" value="0.00">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SUBMIT -->
                    <div class="row mt-4">
                        <div class="col-12 text-right">
                            <input type="hidden" name="final_order_no" id="final_order_no">
                            <input type="hidden" name="final_customer_id" id="final_customer_id">
                            <button class="btn btn-success btn-lg px-4" id="submitDispatchBtn" disabled>
                                <i class="fas fa-truck mr-1"></i> Submit
                            </button>
                        </div>
                    </div>

                </form>

            </div>
        </section>
    </div>

    {{-- ================= STYLES ================= --}}
    <style>
        /* PAGE BASE */
        .content-wrapper {
            background-color: #f4f6f9;
        }

        .card {
            border-radius: 8px;
            border: 1px solid #e5e7eb;
        }

        .content-header h1 {
            font-size: 22px;
            font-weight: 600;
        }

        /* LABELS */
        .font-weight-semibold {
            font-weight: 600;
        }

        /* ORDER CARD */
        .order-card-header {
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            padding: 14px 16px;
        }

        .order-card-title {
            font-size: 16px;
            font-weight: 600;
        }

        .order-meta {
            font-size: 13px;
            color: #6b7280;
        }

        /* SUMMARY BOX */
        .qty-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 10px 14px;
            text-align: center;
            min-width: 120px;
        }

        .qty-label {
            font-size: 12px;
            color: #6c757d;
            font-weight: 600;
        }

        .qty-value {
            font-size: 22px;
            font-weight: 700;
            color: #198754;
        }

        /* TABLE */
        .table thead {
            background-color: #f1f3f5;
        }

        .table thead th {
            font-weight: 700;
            font-size: 18px;
        }

        .table tbody tr:hover {
            background-color: #f9fafb;
        }

        /* BADGES */
        .badge-soft {
            background-color: #e7f6ee;
            color: #198754;
            font-size: 13px;
        }

        /* BUTTON */
        #toggleDocumentBtn {
            box-shadow: 0 2px 6px rgba(0, 0, 0, .1);
        }



        /* hgggggggggggg */
        /* ===== ORDER CARD HEADER ===== */
        .order-card-header {
            background: #ffffff;
            padding: 14px 16px;
            border-bottom: 1px solid #e5e7eb;
        }

        /* TITLE */
        .order-title {
            font-size: 22px;
            font-weight: 600;
            color: #111827;
            margin-bottom: 4px;
        }

        /* META */
        .order-meta {
            font-size: 13px;
            color: #6b7280;
        }

        .order-meta i {
            color: #9ca3af;
            margin-right: 4px;
        }

        .meta-sep {
            margin: 0 6px;
            color: #d1d5db;
        }

        /* QTY */
        .qty-box {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 8px 14px;
            display: inline-block;
            text-align: center;
            min-width: 120px;
        }

        .qty-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
        }

        .qty-value {
            font-size: 22px;
            font-weight: 700;
            color: #198754;
        }


        /* QTY CARD */
        .qty-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 10px 16px;
            display: inline-block;
            min-width: 130px;
            text-align: center;
        }

        .qty-card-label {
            font-size: 12px;
            font-weight: 600;
            color: #6c757d;
        }

        .qty-card-value {
            font-size: 26px;
            font-weight: 800;
            color: #198754;
            line-height: 1.1;
        }

        /* CUSTOMER HIGHLIGHT */
        .customer-highlight {
            display: inline-block;
            background: #e7f6ee;
            color: #198754;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 18px;
            margin-bottom: 6px;
        }

        /* ADDRESS (SECONDARY) */
        .address-text {
            font-size: 15px;
            color: #000000;
        }

        /* ICON COLOR */
        .customer-highlight i,
        .address-text i {
            color: #198754;
        }

        #selectAllCartons {
            transform: scale(1.1);
            cursor: pointer;
        }

        .carton-checkbox {
            cursor: pointer;
        }
    </style>

    {{-- ================= JS (UNCHANGED) ================= --}}
    <script>
        let isDocVisible = false;
        let hasDocument = false;

        /* ================= UTILITIES ================= */

        function showNoData(message = 'No order data found') {

            $('#orderContainer').html(`
                                <div class="card shadow-sm">
                                    <div class="card-body text-center py-5">
                                        <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                        <h5 class="text-muted">${message}</h5>
                                        <p class="text-secondary mb-0">
                                            Please check Order No or Customer
                                        </p>
                                    </div>
                                </div>
                            `);

            $('#submitDispatchBtn').prop('disabled', true);
        }

        function toggleSubmitButton() {
            let checkedCount = $('.carton-checkbox:checked').length;
            $('#submitDispatchBtn').prop('disabled', checkedCount === 0);
        }

        function resetDocumentUI() {

            isDocVisible = false;
            hasDocument = false;

            $('#image_view').addClass('d-none').hide();
            $('#previewImg').hide().attr('src', '');
            $('#previewPDF').remove();

            $('#docToggleWrapper').addClass('d-none');
            $('#toggleDocumentBtn').html('<i class="fas fa-eye mr-1"></i> View Document');
        }

        /* ================= DOCUMENT ================= */

        function showOrderFile(file) {

            resetDocumentUI();
            if (!file) return;

            hasDocument = true;
            $('#docToggleWrapper').removeClass('d-none');

            let path = '/assets/products/' + file;
            let ext = file.split('.').pop().toLowerCase();

            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                $('#previewImg').attr('src', path).show();
            } else if (ext === 'pdf') {
                $('#image_view .card-body').append(`
                                    <embed id="previewPDF"
                                           src="${path}"
                                           type="application/pdf"
                                           width="100%"
                                           height="550px">
                                `);
            }
        }

        /* ================= CALCULATIONS ================= */

        function calculateDispatchTotals() {
            let subtotal = 0;
            let discountP = parseFloat($('#calc_discount_p').val()) || 0;
            let gstP = parseFloat($('#calc_gst_p').val()) || 0;

            // Get selected carton IDs
            let selectedCartons = [];
            $('.carton-checkbox:checked').each(function () {
                selectedCartons.push($(this).val());
            });

            // Map global prices
            let globalPrices = {};
            $('input[name^="global_prices"]').each(function () {
                let name = $(this).attr('name');
                let setId = name.match(/\[(.*?)\]/)[1];
                globalPrices[setId] = parseFloat($(this).val()) || 0;
            });

            // Iterate all cartons and sum based on selected ones
            // We need the raw data available or we can traverse the DOM carton summaries
            // Best to use raw data but since it's grouped, we can sum from the UI 'sets' display if we add data attributes

            $('.carton-checkbox:checked').each(function () {
                let row = $(this).closest('tr');
                row.find('.carton-set-row').each(function () {
                    let setId = $(this).data('set-id');
                    let qty = parseFloat($(this).data('qty')) || 0;
                    let price = globalPrices[setId] || 0;
                    subtotal += (price * qty);
                });
            });

            let discountV = (subtotal * discountP) / 100;
            let afterDiscount = subtotal - discountV;
            let gstV = (afterDiscount * gstP) / 100;
            let grandTotal = afterDiscount + gstV;

            $('#calc_subtotal').val(subtotal.toFixed(2));
            $('#calc_discount_v').val(discountV.toFixed(2));
            $('#calc_gst_v').val(gstV.toFixed(2));
            $('#calc_grand_total').val(grandTotal.toFixed(2));
            $('#final_total_amount_hidden').val(grandTotal.toFixed(2));
        }

        /* ================= RENDER ORDER ================= */

        function renderOrderData(data) {

            if (!data || data.length === 0) {
                showNoData('Order not found');
                $('#summaryContainer').addClass('d-none');
                return;
            }

            $('#summaryContainer').removeClass('d-none');

            let pricingHtml = '';
            // ... (rest of renderOrderData logic)
            if (data[0].unique_sets && data[0].unique_sets.length > 0) {
                pricingHtml = `
                                <div class="card shadow-sm mb-3 border-left-success">
                                    <div class="card-header bg-white py-3">
                                        <h5 class="mb-0 font-weight-bold text-success">
                                            <i class="fas fa-tag mr-2"></i> Price Setup (Set-wise)
                                        </h5>
                                        <small class="text-muted">Enter prices for unique sets across all cartons below</small>
                                    </div>
                                    <div class="card-body">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Design - Color</th>
                                                        <th class="text-center">Size Set</th>
                                                        <th width="200">Selling Price (₹)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                            `;

                data[0].unique_sets.forEach(set => {
                    pricingHtml += `
                                    <tr>
                                        <td class="align-middle">
                                            <strong>${set.design}</strong> | ${set.color}
                                        </td>
                                        <td class="text-center align-middle">
                                            <span class="badge badge-info">${set.size_set}</span>
                                        </td>
                                        <td>
                                            <div class="input-group input-group-sm">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text font-weight-bold">₹</span>
                                                </div>
                                                <input type="number" 
                                                   name="global_prices[${set.set_id}]" 
                                                   class="form-control font-weight-bold global-price-input" 
                                                   step="0.01" 
                                                   min="0" 
                                                   placeholder="0.00"
                                                   value="${set.suggested_price || '0.00'}"
                                                   required>
                                            </div>
                                        </td>
                                    </tr>
                                `;
                });

                pricingHtml += `</tbody></table></div></div></div>`;
            }
            $('#priceSetupContainer').html(pricingHtml);

            let html = '';
            let pcs_in_carton = 0;
            data.forEach(order => {
                let totalBoxes = 0;
                html += `
                                <div class="card shadow-sm mb-3">
                                    <div class="order-card-header">
                                        <div class="row align-items-center">

                                            <div class="col-md-8">
                                                <div class="order-title">
                                                    Order No : <strong>${order.sku}</strong>
                                                </div>

                                                <div class="customer-highlight">
                                                    Customer : ${order.customer}
                                                </div>

                                                <div class="address-text">
                                                    Dispatch Address : ${order.address}
                                                </div>
                                            </div>

                                            <div class="col-md-4 text-right">
                                                <div class="qty-box">
                                                    <div class="qty-label">TOTAL QTY</div>
                                                    <div class="qty-value">${order.total_quantity}</div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>

                                    <div class="card-body p-0">
                                        <table class="table table-bordered mb-0">
                                            <thead>
                                                <tr>
                                                    <th width="50" class="text-center">
                                                        <input type="checkbox" class="select-all-cartons" checked>
                                                    </th>
                                                    <th>Carton No</th>
                                                    <th>Boxes Contents</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                `;

                /* ===== NO CARTONS ===== */
                if (!order.cartons || order.cartons.length === 0) {

                    html += `
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted">
                                                <i class="fas fa-exclamation-circle mr-1"></i>
                                                No cartons available for dispatch
                                            </td>
                                        </tr>
                                    `;

                } else {
                    console.log(order.cartons);
                    order.cartons.forEach(carton => {

                        pcs_in_carton += Number(carton.pcs_in_carton);

                        let itemsHtml = `
                                        <table class="table table-sm table-borderless mb-0" style="font-size: 14px;">
                                            <thead class="text-muted" style="font-size: 12px; border-bottom: 1px solid #eee;">
                                                <tr>
                                                    <th>Design - Color</th>
                                                    <th>Size Set</th>
                                                    <th class="text-center" width="60">Total Qty</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                    `;

                        carton.sets.forEach(set => {
                            itemsHtml += `
                                <tr class="carton-set-row" data-set-id="${set.set_id}" data-qty="${set.total_qty}">
                                    <td>
                                        <strong>${set.design}</strong> 
                                        <span class="text-secondary">| ${set.color}</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">${set.size_set}</span>
                                    </td>
                                    <td class="text-center font-weight-bold">${set.total_qty}</td>
                                </tr>
                            `;
                        });

                        itemsHtml += `</tbody></table>`;

                        html += `
                                        <tr>
                                            <td class="text-center align-middle">
                                                <input type="checkbox"
                                                       name="cartons[]"
                                                       value="${carton.id}"
                                                       class="carton-checkbox"
                                                       checked>
                                            </td>
                                            <td class="align-middle">
                                                <div class="font-weight-bold text-primary">Carton No: ${carton.carton_no || carton.id}</div>
                                                <small class="text-muted">Total Boxes: ${carton.boxes_in_carton}</small>
                                            </td>
                                            <td class="p-0">
                                                ${itemsHtml}
                                            </td>
                                        </tr>
                                        `;
                    });

                    html += `
                                        <tr class="bg-light font-weight-bold">
                                            <td colspan="2" class="text-right">Total Pcs</td>
                                            <td>${pcs_in_carton}</td>
                                        </tr>
                                    `;
                }

                html += `
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                `;
            });

            $('#orderContainer').html(html);

            calculateDispatchTotals();

            toggleSubmitButton();
        }

        /* ================= EVENTS ================= */

        $(function () {
            $('#search_order_no').on('change', function () {
                let order_id = $(this).val();
                $('#order_no').val(order_id).trigger('change.select2');
                let order_no = $(this).find('option:selected').text().trim();
                if (!order_no) return;

                $.get("{{ route('admin.order-dispatch.getOrderPackingData') }}",
                    { search_order_no: order_no },
                    function (res) {

                        if (!res.data || res.data.length === 0) {
                            showNoData('Order not found');
                            return;
                        }

                        let order = res.data[0];
                        $('#final_order_no').val(order.id);
                        $('#final_customer_id').val(order.master_customer_id);

                        showOrderFile(order.slip_file);
                        renderOrderData(res.data);
                    });

            });

            $('#order_no').on('change', function () {
                let order_id = $(this).val();

                $('#search_order_no').val(order_id).trigger('change.select2');
                let order_no = $(this).find('option:selected').text().trim();
                if (!order_no) return;

                // $('#search_order_no').val(order_no);

                $.get("{{ route('admin.order-dispatch.getOrderPackingData') }}",
                    { search_order_no: order_no },
                    function (res) {

                        if (!res.data || res.data.length === 0) {
                            showNoData('Order not found');
                            return;
                        }

                        let order = res.data[0];
                        $('#final_order_no').val(order.id);
                        $('#final_customer_id').val(order.master_customer_id);

                        showOrderFile(order.slip_file);
                        renderOrderData(res.data);
                    });

                $('#final_order_no').val(order_id);
                $('#final_customer_id').val(order_id);
            });

            /* ================= CALCULATION EVENTS ================= */

            $(document).on('input', '.global-price-input, #calc_discount_p, #calc_gst_p', function () {
                calculateDispatchTotals();
            });

            $(document).on('change', '.carton-checkbox, .select-all-cartons', function () {
                calculateDispatchTotals();
            });

            $('#customer_id').on('change', function () {

                let customerId = $(this).val();

                // RESET
                $('#search_order_no').val('');
                $('#orderContainer').html('');
                $('#final_order_no').val('');
                $('#submitDispatchBtn').prop('disabled', true);

                $('#order_no').html('<option value="">Select Order No</option>');

                if (!customerId) return;

                // LOAD ORDERS BY CUSTOMER ✅
                $.get("{{ route('admin.order-dispatch.getOrdersByCustomer') }}",
                    { customer_id: customerId },
                    function (res) {

                        if (!res.data || res.data.length === 0) {
                            $('#order_no').html('<option value="">No orders found</option>');
                            return;
                        }

                        let options = '<option value="">Select Order No</option>';

                        res.data.forEach(order => {
                            options += `
                                                <option value="${order.id}">
                                                    ${order.order_no}
                                                </option>
                                            `;
                        });

                        $('#order_no').html(options);
                    }
                );
            });

        });


        /* ================= CHECKBOX ================= */

        $(document).on('change', '.select-all-cartons', function () {

            let table = $(this).closest('table');

            table.find('.carton-checkbox')
                .prop('checked', this.checked);

            toggleSubmitButton();
        });

        $(document).on('change', '.carton-checkbox', function () {

            let table = $(this).closest('table');

            let allChecked =
                table.find('.carton-checkbox').length ===
                table.find('.carton-checkbox:checked').length;

            table.find('.select-all-cartons')
                .prop('checked', allChecked);

            toggleSubmitButton();
        });

        /* ================= DOCUMENT TOGGLE ================= */

        $('#toggleDocumentBtn').on('click', function () {

            if (!hasDocument) return;

            if (isDocVisible) {
                $('#image_view').slideUp().addClass('d-none');
                $(this).html('<i class="fas fa-eye mr-1"></i> View Document');
            } else {
                $('#image_view').removeClass('d-none').slideDown();
                $(this).html('<i class="fas fa-eye-slash mr-1"></i> Hide Document');
            }

            isDocVisible = !isDocVisible;
        });



    </script>

@endsection