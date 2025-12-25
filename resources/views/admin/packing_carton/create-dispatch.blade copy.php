@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid text-center">
            <h1 class="mb-3">Order Dispatch</h1>
            <hr>
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card p-3 shadow-sm">

                <form method="POST" action="{{ route('admin.packing_carton.store') }}">
                    @csrf

                    {{-- CUSTOMER & ORDER --}}
                    <div class="card p-3 mb-3 border">
                        <div class="row">
                            <div class="col-md-6">
                                <label>Select Customer</label>
                                <select id="master_customer_id" class="form-control select2" required>
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label>Order No</label>
                                <select id="order_no" class="form-control select2" required>
                                    <option value="">Select Order No</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- ORDER DETAILS --}}
                    <div id="div_order_dispatch" class="row d-none">

                        {{-- LEFT --}}
                        <div class="col-md-6">

                            {{-- ORDER TABLE --}}
                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bar Code</th>
                                        <th>Design</th>
                                        <th>Size</th>
                                        <th>Color</th>
                                        <th>Pcs</th>
                                        <th>Set Qty</th>
                                        <th>Total Qty</th>
                                    </tr>
                                </thead>
                                <tbody id="orderTableBody"></tbody>
                            </table>

                            {{-- ADD CARTON --}}
                            <div class="col-md-4 p-0">
                                <div id="addCartonBtn"
                                     class="card text-center shadow-sm p-3 border carton-box"
                                     style="cursor:pointer;">
                                    <i class="fas fa-box-open fa-3x text-success mb-2"></i>
                                    <strong>+ Add Carton</strong>
                                </div>
                            </div>

                            {{-- CARTON FORM --}}
                            <div id="cartonForm" class="card mt-3 border shadow-sm d-none">
                                <div class="card-header bg-light">
                                    <strong>
                                        <i class="fas fa-box text-success mr-2"></i>
                                        <span id="cartonTitle">Carton 1</span>
                                    </strong>
                                </div>

                                <div class="card-body">
                                    <div class="row align-items-end">
                                        <div class="col-md-6">
                                            <label>Bar Code</label>
                                            <input type="text"
                                                   id="carton_bar_code"
                                                   class="form-control uniform-control"
                                                   placeholder="Scan or enter barcode">
                                        </div>

                                        <div class="col-md-4">
                                            <label>Total Boxes</label>
                                            <input type="number"
                                                   id="total_sets"
                                                   class="form-control uniform-control"
                                                   placeholder="0"
                                                   readonly>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button"
                                                    id="closeCarton"
                                                    class="btn btn-outline-danger w-100 uniform-control">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- CARTON SUMMARY --}}
                        </div>
                        {{-- RIGHT (IMAGE / PDF PREVIEW) --}}
                        <div class="col-md-6">
                            <div class="card p-3 border text-center">
                                <img id="previewImg"
                                     class="w-100"
                                     style="border-radius:6px; display:none;">
                            </div>
                        </div>
                    </div>
                    <div id="packed_cartons" class="card p-3 mb-3 border d-none">
                        <h5 class="mb-3">
                            <i class="fas fa-box text-success mr-2"></i>
                            Packed Cartons
                        </h5>

                        <div class="row g-3" id="cartonList"></div>
                    </div>
                    {{-- SUBMIT --}}
                    <div class="row mt-3">
                        <div class="col-12 text-right">
                            <button class="btn btn-success">Submit Dispatch</button>
                        </div>
                    </div>

                </form>
            </div>
        </div>
    </section>
</div>

{{-- STYLES --}}
<style>
    .carton-box:hover {
        background:#f8f9fa;
        box-shadow:0 4px 12px rgba(0,0,0,.15);
        transform:translateY(-2px);
    }
    .uniform-control { height:38px; }

    .carton-card {
        background: linear-gradient(135deg, #e8f5e9, #ffffff);
        border: 1px solid #c8e6c9;
        border-radius: 10px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }

    .carton-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px rgba(0,0,0,0.15);
    }

    .carton-header {
        background: linear-gradient(135deg, #28a745, #5cd08d);
        color: #fff;
        border-bottom: none;
        border-radius: 10px 10px 0 0;
        font-size: 14px;
    }

    .carton-header i {
        color: #fff;
    }

    .carton-card table th {
        background: #f1f8f4;
    }

    .carton-card table td,
    .carton-card table th {
        font-size: 12px;
    }

</style>

{{-- JS --}}
<script>
$(function () {

    let cartonCount = 1;
    let cartonBarcodes = {}; // barcode => qty

    /* ================= CUSTOMER → ORDERS ================= */
    $('#master_customer_id').on('change', function () {
        let customer_id = $(this).val();
        if (!customer_id) return;

        $.get("{{ route('admin.packing_carton.getCustomerOrders') }}",
            { customer_id }, function (res) {

            $('#order_no').empty()
                .append('<option value="">Select Order No</option>');

            res.data.forEach(o => {
                $('#order_no').append(`
                    <option value="${o.id}" data-img="${o.corporate_order_file}">
                        ${o.sku}
                    </option>
                `);
            });
        });
    });

    /* ================= ORDER CHANGE ================= */
    $('#order_no').on('change', function () {

        let order_id = $(this).val();
        if (!order_id) return;

        $('#div_order_dispatch').removeClass('d-none');
        $('#packed_cartons').removeClass('d-none');
        
        let fileName = $(this).find(':selected').data('img');

        $('#previewImg').hide().attr('src','');
        $('#previewPDF').remove();

        if (fileName) {
            let filePath = '/assets/products/' + fileName;
            let ext = fileName.split('.').pop().toLowerCase();

            if (['jpg','jpeg','png','webp'].includes(ext)) {
                $('#previewImg').attr('src', filePath).show();
            } else if (ext === 'pdf') {
                $('#previewImg').after(`
                    <embed id="previewPDF"
                           src="${filePath}"
                           type="application/pdf"
                           width="100%"
                           height="550px"
                           style="border:1px solid #ccc; border-radius:6px;">
                `);
            }
        }

        $.get("{{ route('admin.packing_carton.getOrdersDetails') }}", {
            customer_id: $('#master_customer_id').val(),
            order_main_id: order_id
        }, function (res) {

            let tbody = $('#orderTableBody').empty();
            res.data.forEach((r, i) => {
                tbody.append(`
                    <tr>
                        <td>${i+1}</td>
                        <td>${r.bar_code}</td>
                        <td>${r.design_number}</td>
                        <td>${r.set_size}</td>
                        <td>${r.color}</td>
                        <td>${r.no_of_pcs}</td>
                        <td>${r.set_quantity}</td>
                        <td>${r.total_quantity}</td>
                    </tr>
                `);
            });
        });
    });

    /* ================= ADD CARTON ================= */
    $('#addCartonBtn').on('click', function () {
        $('#cartonForm').removeClass('d-none').slideDown();
        $('#carton_bar_code').focus();
    });

    /* ================= BARCODE ADD (GROUPED) ================= */
    function addBarcode() {
        let code = $('#carton_bar_code').val().trim();
        if (!code) return;

        cartonBarcodes[code] = (cartonBarcodes[code] || 0) + 1;

        let total = Object.values(cartonBarcodes)
                          .reduce((a,b)=>a+b,0);

        $('#total_sets').val(total);
        $('#carton_bar_code').val('');
    }

    $('#carton_bar_code')
        .on('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addBarcode();
            }
        })
        .on('blur', function () {
            if ($(this).val().trim() !== '') addBarcode();
        });

    /* ================= CLOSE CARTON ================= */
    $('#closeCarton').on('click', function () {

        if (Object.keys(cartonBarcodes).length === 0) {
            alert('No barcode added');
            return;
        }

        let rows = '';
        let total = 0;

        $.each(cartonBarcodes, function (barcode, qty) {
            total += qty;
            rows += `
                <tr>
                    <td>${barcode}</td>
                    <td class="text-right">${qty}</td>
                </tr>
            `;
        });

        $('#cartonList').append(`
            <div class="col-md-2 mb-3">
                <div class="card carton-card h-100">
                    <div class="card-header carton-header">
                        <strong>
                            <i class="fas fa-box mr-2"></i>
                            Carton ${cartonCount}
                        </strong>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered mb-2 bg-white">
                            <thead>
                                <tr>
                                    <th>Bar Code</th>
                                    <th class="text-right">Box</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${rows}
                            </tbody>
                        </table>
                        <div class="text-right font-weight-bold text-dark">
                            Total Boxes : ${total}
                        </div>
                    </div>
                </div>
            </div>`);

        cartonCount++;
        cartonBarcodes = {};
        $('#total_sets').val('');
        $('#carton_bar_code').val('');
        $('#cartonTitle').text('Carton ' + cartonCount);
        $('#cartonForm').slideUp();
    });

});
</script>
@endsection
