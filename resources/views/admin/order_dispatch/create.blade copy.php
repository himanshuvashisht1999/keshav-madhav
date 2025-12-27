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
                                <input type="text"
                                       id="search_order_no"
                                       name ="search_order_no"
                                       class="form-control"
                                       placeholder="Enter Order Number">
                            </div>

                            <div class="col-md-1 text-center text-muted font-weight-bold">
                                AND
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
                        <button class="btn btn-success btn-lg px-4">
                            <i class="fas fa-truck mr-1"></i> Submit Dispatch
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

$(function () {

    $('#search_order_no').keydown(function(e){
        if(e.key === 'Enter'){
            e.preventDefault();

            let orderNo = $(this).val();
            $('#orderContainer').html('');

            $.get("{{ route('admin.order-dispatch.getOrderPackingData') }}",
            { search_order_no: orderNo },
            function (res) {

                let file = res.data[0]?.slip_file;
                showOrderFile(file);
                let orderId = res.data[0]?.id
                $('#final_order_no').val(orderId);
                renderOrderData(res.data);
            });
        }
    });

});

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
        $('#previewImg').hide();
        $('#image_view .card-body').append(`
            <embed id="previewPDF"
                   src="${path}"
                   type="application/pdf"
                   width="100%"
                   height="550px">
        `);
    }
}

function renderOrderData(data) {

    let html = '';

    data.forEach(order => {

        let totalBoxes = 0; // 👈 total boxes counter

        html += `
        <div class="card shadow-sm mb-3">

            <div class="order-card-header">
                <div class="row align-items-center">

                    <!-- LEFT -->
                    <div class="col-md-8">
                        <div class="order-title">
                            Order No : <strong>${order.sku}</strong>
                        </div>

                        <div class="order-meta">
            
                            <div class="customer-highlight">
                                Customer : ${order.customer}
                            </div>

                            <div class="address-text">
                                Dispatch Address : ${order.address}
                            </div>
                        </div>
                    </div>

                    <!-- RIGHT -->
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
                                <input type="checkbox" id="selectAllCartons" checked>
                            </th>
                            <th>Carton No</th>
                            <th>Boxes</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        order.cartons.forEach(carton => {

            totalBoxes += Number(carton.boxes_in_carton); // 👈 sum boxes

            html += `
            <tr>
                <td class="text-center">
                    <input type="checkbox"
                           name="cartons[]"
                           value="${carton.id}"
                           class="carton-checkbox"
                           checked>
                </td>
                <td><strong>Carton - ${carton.id}</strong></td>
                <td>${carton.boxes_in_carton}</td>
            </tr>
            `;
        });

        /* 👇 TOTAL ROW */
        html += `
            <tr class="bg-light font-weight-bold">
                <td colspan="2" class="text-right">
                    Total Boxes
                </td>
                <td>
                    ${totalBoxes}
                </td>
            </tr>
        `;

        html += `
                    </tbody>
                </table>
            </div>
        </div>
        `;
    });

    $('#orderContainer').html(html);
}

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

function resetDocumentUI() {

    isDocVisible = false;
    hasDocument = false;

    $('#image_view').addClass('d-none').hide();
    $('#previewImg').hide().attr('src','');
    $('#previewPDF').remove();

    $('#docToggleWrapper').addClass('d-none');
    $('#toggleDocumentBtn').html('<i class="fas fa-eye mr-1"></i> View Document');
}


$(document).on('change', '#selectAllCartons', function () {
    $('.carton-checkbox').prop('checked', this.checked);
});

/* If any single checkbox unchecked → Select All unchecked */
$(document).on('change', '.carton-checkbox', function () {
    if (!this.checked) {
        $('#selectAllCartons').prop('checked', false);
    } else if ($('.carton-checkbox:checked').length === $('.carton-checkbox').length) {
        $('#selectAllCartons').prop('checked', true);
    }
});
</script>
@endsection
