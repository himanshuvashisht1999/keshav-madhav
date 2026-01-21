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
                                {{-- <input type="text"
                                       id="search_order_no"
                                       name ="search_order_no"
                                       class="form-control"
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
                                <select id="customer_id"  
                                        class="form-control select2">
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
                    <button type="button"
                            id="toggleDocumentBtn"
                            class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-eye mr-1"></i> View Document
                    </button>
                </div>

                <!-- DOCUMENT PREVIEW -->
                <div class="row d-none mb-3" id="image_view">
                    <div class="col-md-12">
                        <div class="card shadow-sm">
                            <div class="card-body text-center">
                                <img id="previewImg" class="w-100"
                                     style="border-radius:6px; display:none;">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ORDER DATA -->
                <div id="orderContainer"></div>

                <!-- SUBMIT -->
                <div class="row mt-4">
                    <div class="col-12 text-right">
                        <input type="hidden" name="final_order_no" id="final_order_no">
                        <input type="hidden" name="final_customer_id" id="final_customer_id">
                        <button class="btn btn-success btn-lg px-4"
                                id="submitDispatchBtn"
                                disabled>
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
        box-shadow: 0 2px 6px rgba(0,0,0,.1);
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
    $('#previewImg').hide().attr('src','');
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

    if (['jpg','jpeg','png','webp'].includes(ext)) {
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

/* ================= RENDER ORDER ================= */

function renderOrderData(data) {

    if (!data || data.length === 0) {
        showNoData('Order not found');
        return;
    }

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
                            <th>Boxes</th>
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

                html += `
                <tr>
                    <td class="text-center">
                        <input type="checkbox"
                               name="cartons[]"
                               value="${carton.id}"
                               class="carton-checkbox"
                               checked>
                    </td>
                    <td><strong>Carton - ${carton.carton_no || carton.id}</strong></td>
                    <td>${carton.contents || carton.boxes_in_carton + ' (Items)'}</td>
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
        { search_order_no : order_no },
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
        { search_order_no : order_no },
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
