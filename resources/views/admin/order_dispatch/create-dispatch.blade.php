@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">

        {{-- HEADER --}}
        <section class="content-header">
            <div class="container-fluid text-center">
                <h1>Order Dispatch</h1>
                <hr>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card p-3 shadow-sm">

                    <form method="POST" action="{{ route('admin.order_dispatch.store') }}">
                        @csrf

                        {{-- CUSTOMER & ORDER --}}
                        <div class="card p-3 mb-3 border">
                            <div class="row">
                                <div class="col-md-6">
                                    <label>Select Customer</label>
                                    <select id="master_customer_id" class="form-control select2">
                                        <option value="">Select Customer</option>
                                        @foreach ($customers as $c)
                                            <option value="{{ $c->id }}">{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6">
                                    <label>Order No</label>
                                    <select id="order_no" class="form-control select2">
                                        <option value="">Select Order</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- ORDER DETAILS --}}
                        <div id="div_order_dispatch" class="row d-none">

                            {{-- LEFT --}}
                            <div class="col-md-6">

                                <table class="table table-bordered">
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

                                <div id="addCartonBtn" class="card text-center p-3 border carton-box">
                                    <i class="fas fa-box-open fa-2x text-success"></i>
                                    <strong>+ Add Carton</strong>
                                </div>

                                <div id="cartonForm" class="card mt-3 border d-none">
                                    <div class="card-header bg-light">
                                        <strong>
                                            <i class="fas fa-box mr-2"></i>
                                            <span id="cartonTitle">Carton 1</span>
                                        </strong>
                                    </div>

                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <label>Bar Code</label>
                                                <input type="text" id="carton_bar_code" class="form-control">
                                            </div>
                                            <div class="col-md-4">
                                                <label>Total Boxes</label>
                                                <input type="number" id="total_sets" class="form-control" readonly>
                                            </div>
                                            <div class="col-md-2">
                                                <button type="button" id="closeCarton" class="btn btn-danger w-100">
                                                    Close
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- RIGHT : IMAGE / PDF PREVIEW --}}
                            <div class="col-md-6">
                                <div class="card p-3 border text-center">

                                    <img id="previewImg" class="w-100 mb-2" style="border-radius:6px; display:none;">

                                    <embed id="previewPDF" type="application/pdf"
                                        style="display:none; width:100%; height:550px;
                  border:1px solid #ccc; border-radius:6px;">
                                </div>
                            </div>

                        </div>

                        {{-- PACKED CARTONS --}}
                        <div id="packed_cartons" class="card p-3 mt-3 border d-none">
                            <h5>
                                <i class="fas fa-box text-success mr-2"></i>
                                Packed Cartons
                            </h5>

                            <div class="carton-grid" id="cartonList"></div>
                        </div>

                        {{-- HIDDEN CARTON DATA --}}
                        <div id="cartonHiddenInputs"></div>

                        <div class="text-right mt-3">
                            <button class="btn btn-success">Submit Dispatch</button>
                        </div>

                    </form>
                </div>
            </div>
        </section>
    </div>

    {{-- STYLES --}}
    <style>
        .carton-box:hover {
            cursor: pointer;
            background: #f8f9fa;
        }

        .carton-box.disabled {
            pointer-events: none;
            opacity: .5;
        }

        .carton-grid {
            display: grid;
            grid-template-columns: repeat(6, 1fr);
            gap: 12px;
        }

        .carton-card {
            background: linear-gradient(135deg, #e8f5e9, #fff);
            border-radius: 8px;
        }

        .carton-header {
            background: #28a745;
            color: #fff;
            padding: 6px;
            font-size: 13px;
        }
    </style>

    {{-- JS --}}
    <script>
        $(function() {

            let cartonCount = 1;
            let cartonBarcodes = {};
            let barcodeRows = {};
            let emptyBarcodeQueue = [];

            /* enable / disable Add Carton */
            function toggleAddCartonButton() {
                let hasQty = false;
                $('#orderTableBody tr').each(function() {
                    if (parseInt($(this).find('.setQty').text()) > 0) {
                        hasQty = true;
                        return false;
                    }
                });
                $('#addCartonBtn').toggleClass('disabled', !hasQty);
            }

            /* CUSTOMER → ORDERS */
            $('#master_customer_id').change(function() {
                $.get("{{ route('admin.order_dispatch.getCustomerOrders') }}", {
                    customer_id: $(this).val()
                }, function(res) {
                    $('#order_no').empty().append('<option value="">Select</option>');
                    res.data.forEach(o => {
                        $('#order_no').append(
                            `<option value="${o.id}" data-img="${o.corporate_order_file}">
${o.sku}
</option>`
                        );
                    });
                });
            });

            /* ORDER CHANGE */
            $('#order_no').change(function() {

                $('#div_order_dispatch,#packed_cartons').removeClass('d-none');

                let fileName = $(this).find(':selected').data('img');

                /* RESET PREVIEW */
                $('#previewImg').hide().attr('src', '');
                $('#previewPDF').hide().attr('src', '');

                /* PREVIEW */
                if (fileName) {
                    let path = '/assets/products/' + fileName;
                    let ext = fileName.split('.').pop().toLowerCase();

                    if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                        $('#previewImg').attr('src', path).show();
                    } else if (ext === 'pdf') {
                        $('#previewPDF').attr('src', path).show();
                    }
                }

                /* ORDER TABLE */
                $('#orderTableBody').empty();
                barcodeRows = {};
                emptyBarcodeQueue = [];

                $.get("{{ route('admin.order_dispatch.getOrdersDetails') }}", {
                    customer_id: $('#master_customer_id').val(),
                    order_main_id: $(this).val()
                }, function(res) {

                    res.data.forEach((r, i) => {

                        let barcode = r.bar_code ? r.bar_code.trim() : '';

                        $('#orderTableBody').append(`
                                <tr data-index="${i}" data-barcode="${barcode}">
                                <td>${i+1}</td>
                                <td>${barcode || '-'}</td>
                                <td>${r.design_number}</td>
                                <td>${r.set_size}</td>
                                <td>${r.color}</td>
                                <td class="pcs">${r.no_of_pcs}</td>
                                <td class="setQty">${r.set_quantity}</td>
                                <td class="totalQty">${r.total_quantity}</td>
                                </tr>
                                `);

                        if (barcode) {
                            barcodeRows[barcode] = barcodeRows[barcode] || [];
                            barcodeRows[barcode].push(i);
                        } else {
                            emptyBarcodeQueue.push(i);
                        }

                    });

                    toggleAddCartonButton();
                });
            });

            /* ADD CARTON */
            $('#addCartonBtn').click(function() {
                if ($(this).hasClass('disabled')) return;
                $('#cartonForm').removeClass('d-none');
                $('#carton_bar_code').focus();
            });

            /* BARCODE SCAN */
            function addBarcode() {

                let scanned = $('#carton_bar_code').val().trim();
                if (!scanned) return;

                let rowIndex = null;

                /* barcode present */
                if (barcodeRows[scanned]) {
                    for (let i of barcodeRows[scanned]) {
                        let row = $(`#orderTableBody tr[data-index="${i}"]`);
                        if (parseInt(row.find('.setQty').text()) > 0) {
                            rowIndex = i;
                            break;
                        }
                    }
                }
                /* barcode null rows */
                else {
                    for (let i of emptyBarcodeQueue) {
                        let row = $(`#orderTableBody tr[data-index="${i}"]`);
                        if (parseInt(row.find('.setQty').text()) > 0) {
                            rowIndex = i;
                            break;
                        }
                    }
                }

                if (rowIndex === null) {
                    alert('No quantity remaining');
                    $('#carton_bar_code').val('');
                    return;
                }

                let row = $(`#orderTableBody tr[data-index="${rowIndex}"]`);
                let pcs = parseInt(row.find('.pcs').text());

                row.find('.setQty').text(parseInt(row.find('.setQty').text()) - 1);
                row.find('.totalQty').text(parseInt(row.find('.totalQty').text()) - pcs);

                cartonBarcodes[scanned] = (cartonBarcodes[scanned] || 0) + 1;
                $('#total_sets').val(Object.values(cartonBarcodes).reduce((a, b) => a + b, 0));
                $('#carton_bar_code').val('');

                toggleAddCartonButton();
            }

            $('#carton_bar_code').keydown(function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addBarcode();
                }
            });

            /* CLOSE CARTON */
            $('#closeCarton').click(function() {

                if (Object.keys(cartonBarcodes).length === 0) {
                    alert('No barcode added');
                    return;
                }

                let rows = '',
                    items = [];

                $.each(cartonBarcodes, (b, q) => {
                    rows += `<tr><td>${b}</td><td>${q}</td></tr>`;
                    items.push({
                        barcode: b,
                        qty: q
                    });
                });

                $('#cartonList').append(`
<div class="carton-card">
<div class="carton-header">Carton ${cartonCount}</div>
<table class="table table-sm mb-0">${rows}</table>
</div>
`);

                $('#cartonHiddenInputs').append(`
<input type="hidden" name="cartons[]"
value='${JSON.stringify({carton_no:cartonCount,items:items})}'>
`);

                cartonBarcodes = {};
                cartonCount++;
                $('#cartonForm').addClass('d-none');
                $('#cartonTitle').text('Carton ' + cartonCount);
                $('#total_sets').val('');
            });

        });
    </script>
@endsection
