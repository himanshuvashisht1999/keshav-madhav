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

                <form method="POST" action="{{ route('admin.order_dispatch.store') }}">
                    @csrf

                    {{-- CUSTOMER & ORDER --}}
                    <div class="card p-3 mb-3 border">
                        <div class="row align-items-end">

                            {{-- SEARCH BY BARCODE --}}
                            <div class="col-md-3">
                                <label>Search By Bar Code</label>
                                <input type="text"
                                    id="search_barcode"
                                    name="search_barcode"
                                    class="form-control"
                                    placeholder="Enter Bar Code">
                            </div>

                            <div class="col-md-1 text-center font-weight-bold">
                                OR
                            </div>

                            {{-- SEARCH BY CUSTOMER --}}
                            <div class="col-md-4">
                                <label>Select Customer</label>
                                <select id="master_customer_id"
                                        class="form-control select2"
                                        >
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- ORDER --}}
                            <div class="col-md-4">
                                <label>Order No</label>
                                <select id="order_no"
                                        class="form-control select2"
                                        >
                                    <option value="">Select Order No</option>
                                </select>
                            </div>

                        </div>
                    </div>


                    {{-- ORDER DETAILS --}}
                    <div id="div_order_dispatch" class="row d-none">

                        {{-- LEFT --}}
                        <div class="col-md-6">

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
                                     class="card text-center shadow-sm p-3 border carton-box">
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
                                            <input type="text" id="carton_bar_code"
                                                   class="form-control uniform-control"
                                                   placeholder="Scan or enter barcode">
                                        </div>

                                        <div class="col-md-4">
                                            <label>Total Boxes</label>
                                            <input type="number" id="total_sets"
                                                   class="form-control uniform-control"
                                                   readonly>
                                        </div>

                                        <div class="col-md-2">
                                            <button type="button" id="closeCarton"
                                                    class="btn btn-outline-danger w-100 uniform-control">
                                                Close
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- RIGHT : IMAGE / PDF --}}
                        <div class="col-md-6">
                            <div class="card p-3 border text-center">
                                <img id="previewImg" class="w-100"
                                     style="border-radius:6px; display:none;">
                            </div>
                        </div>
                    </div>

                    {{-- PACKED CARTONS --}}
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
    cursor:pointer;
}
.carton-box.disabled {
    opacity:.5;
    pointer-events:none;
}
.uniform-control { height:38px; }

.carton-card {
    background: linear-gradient(135deg,#e8f5e9,#fff);
    border:1px solid #c8e6c9;
    border-radius:10px;
}
.carton-header {
    background:linear-gradient(135deg,#28a745,#5cd08d);
    color:#fff;
}
</style>

{{-- JS --}}
<script>
$(function () {

    let cartonCount = 1;
    let cartonBarcodes = {};
    let barcodeRows = {};
    let nullBarcodeRows = [];

    function toggleAddCarton() {
        let totalRemaining = 0;
        $('#orderTableBody tr').each(function () {
            totalRemaining += parseInt($(this).find('.setQty').text()) || 0;
        });
        $('#addCartonBtn').toggleClass('disabled', totalRemaining <= 0);
    }

    $('#master_customer_id').change(function () {
        let customer_id =  $(this).val()
        if (customer_id === ""){
            $('#div_order_dispatch').addClass('d-none');
            return;
        }
        $.get("{{ route('admin.order_dispatch.getCustomerOrders') }}",
            { customer_id: customer_id },
            function (res) {
                $('#order_no').html('<option value="">Select Order No</option>');
                res.data.forEach(o => {
                    $('#order_no').append(
                        `<option value="${o.id}" data-img="${o.corporate_order_file}">${o.sku}</option>`
                    );
                });
            });
    });

    $('#order_no').change(function () {

        $('#div_order_dispatch,#packed_cartons').removeClass('d-none');

        let file = $(this).find(':selected').data('img');
        $('#previewImg').hide().attr('src','');
        $('#previewPDF').remove();

        if (file) {
            let path = '/assets/products/' + file;
            let ext = file.split('.').pop().toLowerCase();
            if (['jpg','jpeg','png','webp'].includes(ext)) {
                $('#previewImg').attr('src',path).show();
            } else if (ext === 'pdf') {
                $('#previewImg').after(`
                    <embed id="previewPDF" src="${path}"
                           type="application/pdf"
                           width="100%" height="550px">
                `);
            }
        }

        barcodeRows = {};
        nullBarcodeRows = [];
        $('#orderTableBody').empty();

        $.get("{{ route('admin.order_dispatch.getOrdersDetails') }}", {
            customer_id: $('#master_customer_id').val(),
            order_main_id: $(this).val()
        }, function (res) {

            res.data.forEach((r,i) => {

                let bc = r.bar_code ? r.bar_code.trim() : '';

                $('#orderTableBody').append(`
                    <tr data-index="${i}">
                        <td>${i+1}</td>
                        <td>${bc || '-'}</td>
                        <td>${r.design_number}</td>
                        <td>${r.set_size}</td>
                        <td>${r.color}</td>
                        <td class="pcs">${r.no_of_pcs}</td>
                        <td class="setQty">${r.set_quantity}</td>
                        <td class="totalQty">${r.total_quantity}</td>
                    </tr>
                `);

                if (bc) {
                    barcodeRows[bc] = barcodeRows[bc] || [];
                    barcodeRows[bc].push(i);
                } else {
                    nullBarcodeRows.push(i);
                }
            });

            toggleAddCarton();
        });
    });

    $('#addCartonBtn').click(function () {
        if ($(this).hasClass('disabled')) return;

        $('#cartonForm')
            .removeClass('d-none')
            .show();

        $('#carton_bar_code').val('').focus();
        $('#total_sets').val('');
    });

    function scanBarcode() {

        let code = $('#carton_bar_code').val().trim();
        if (!code) return;

        let rowIndex = null;

        if (barcodeRows[code]) {
            for (let i of barcodeRows[code]) {
                let r = $(`tr[data-index="${i}"]`);
                if (parseInt(r.find('.setQty').text()) > 0) {
                    rowIndex = i; break;
                }
            }
        }

        if (rowIndex === null) {
            for (let i of nullBarcodeRows) {
                let r = $(`tr[data-index="${i}"]`);
                if (parseInt(r.find('.setQty').text()) > 0) {
                    rowIndex = i; break;
                }
            }
        }

        if (rowIndex === null) {
            alert('Please enter correct Bar code');
            $('#carton_bar_code').val('');
            return;
        }

        let row = $(`tr[data-index="${rowIndex}"]`);
        let pcs = parseInt(row.find('.pcs').text());

        row.find('.setQty').text(parseInt(row.find('.setQty').text()) - 1);
        row.find('.totalQty').text(parseInt(row.find('.totalQty').text()) - pcs);

        cartonBarcodes[code] = (cartonBarcodes[code] || 0) + 1;
        $('#total_sets').val(Object.values(cartonBarcodes).reduce((a,b)=>a+b,0));

        $('#carton_bar_code').val('');
        toggleAddCarton();
    }

    $('#carton_bar_code').keydown(function(e){
        if(e.key === 'Enter'){ e.preventDefault(); scanBarcode(); }
    });

    $('#closeCarton').click(function () {

        if (!Object.keys(cartonBarcodes).length) {
            alert('No barcode added');
            return;
        }

        let rows = '', total = 0;

        $.each(cartonBarcodes,(b,q)=>{
            total += q;
            rows += `<tr><td>${b}</td><td class="text-right">${q}</td></tr>`;
            $('<input>',{
                type:'hidden',
                name:'cartons[]',
                value:JSON.stringify({carton:cartonCount,barcode:b,qty:q})
            }).appendTo('form');
        });

        $('#cartonList').append(`
            <div class="col-md-2 mb-3">
                <div class="card carton-card h-100">
                    <div class="card-header carton-header">
                        <strong><i class="fas fa-box mr-2"></i> Carton ${cartonCount}</strong>
                    </div>
                    <div class="card-body p-2">
                        <table class="table table-sm table-bordered bg-white">
                            <tr><td>Bar code</td><td class="text-right">Boxes</td></tr>
                            ${rows}
                        </table>
                        <div class="text-right font-weight-bold">
                            Total Boxes : ${total}
                        </div>
                    </div>
                </div>
            </div>
        `);

        cartonCount++;
        cartonBarcodes = {};

        $('#total_sets').val('');
        $('#carton_bar_code').val('');
        $('#cartonTitle').text('Carton ' + cartonCount);

        $('#cartonForm')
            .addClass('d-none')
            .hide();

        toggleAddCarton();
    });

});
</script>
@endsection
