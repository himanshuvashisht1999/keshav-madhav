@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">

    {{-- HEADER --}}
    <section class="content-header">
        <div class="container-fluid text-center">
            <h1 class="mb-3">Packing in Carton</h1>
            <hr>
        </div>
    </section>

    {{-- CONTENT --}}
    <section class="content">
        <div class="container-fluid">
            <div class="card p-3 shadow-sm">

                <form method="POST" action="{{ route('admin.packing-carton.store') }}">
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
                                AND
                            </div>

                            {{-- SEARCH BY CUSTOMER --}}
                            <div class="col-md-4">
                                <label>Select Customer</label>
                                <select id="master_customer_id" name="master_customer_id"
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
                                <select id="order_no" name="order_no"
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
                        <div class="col-md-12 d-none" id="image_view">
                            <div class="card p-3 border text-center">
                                <img id="previewImg" class="w-100"
                                    style="border-radius:6px; display:none;">
                            </div>
                        </div>
                        <div class="col-md-12 text-right mb-2 d-none" id="docToggleWrapper">
                            <button type="button"
                                    id="toggleDocumentBtn"
                                    class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-eye mr-1"></i> Show Document
                            </button>
                        </div>

                        <div class="col-md-12">

                            <table class="table table-bordered table-hover">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Bar Code</th>
                                        <th>Design</th>
                                        <th>Set Size</th>
                                        <th>Size Group</th>
                                        <th>Color</th>
                                        <th>Pcs</th>
                                        <th>Set Qty</th>
                                        <th>Total Qty</th>
                                        <th>Remaining Set</th>
                                        <th>Remaining Qty</th>
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
                        {{-- <div class="col-md-5">
                            <div class="card p-3 border text-center">
                                <img id="previewImg" class="w-100"
                                     style="border-radius:6px; display:none;">
                            </div>
                        </div> --}}
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
                            <input type="hidden" name="final_customer_id" id="final_customer_id">
                            <input type="hidden" name="final_order_no" id="final_order_no">
                            <button class="btn btn-success">Submit </button>
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
    let isDocVisible = false;
    let hasDocument = false;
$(function () {
    $('form').on('submit', function (e) {

        // check if any carton card exists
        if ($('#cartonList').children().length === 0) {
            e.preventDefault(); // stop form submit
            alert('Please add at least one carton before submitting dispatch.');
            return false;
        }

    });
    let cartonCount = 1;
    let cartonBarcodes = {};
    let barcodeRows = {};
    let nullBarcodeRows = [];
    let isBarcodeSearch = false;

    function toggleAddCarton() {
        let totalRemaining = 0;
        $('#orderTableBody tr').each(function () {
            totalRemaining += parseInt($(this).find('.setQty').text()) || 0;
        });
        $('#addCartonBtn').toggleClass('disabled', totalRemaining <= 0);
    }

    $('#search_barcode').keydown(function(e){
        if(e.key === 'Enter'){ 
            e.preventDefault(); 
            isBarcodeSearch = true;
            let search_barcode =  $(this).val();
            barcodeRows = {};
            nullBarcodeRows = [];
            $('#orderTableBody').empty();
            $.get("{{ route('admin.packing-carton.getCustomersBybarcode') }}",
            { search_barcode: search_barcode },
            function (res) {
                $('#div_order_dispatch,#packed_cartons').removeClass('d-none');
                let customerId = res.data[0]['master_customer_id'];
                $('#master_customer_id').val(customerId).trigger('change');
                let orderId = res.data[0]['order_id'];
                let orderName = res.data[0]['order_name'];
                $('#order_no')
                    .empty()
                    .append('<option value="'+orderId+'" selected>'+orderName+'</option>');

                let file = res.data[0]['slip_file'];
                $('#final_customer_id').val(customerId);
                $('#final_order_no').val(orderId);
                setOrderTableBody(res.data);
                showOrderFile(file);
                toggleAddCarton();
            });
        }
    });

    $('#master_customer_id').change(function () {
        let customer_id =  $(this).val();
        let search_barcode =  $('#search_barcode').val();
        if (isBarcodeSearch && search_barcode != "") {
            return; //
        }

        if (customer_id === ""){
            $('#div_order_dispatch').addClass('d-none');
            resetDocumentUI();
            return;
        }
        $.get("{{ route('admin.packing-carton.getCustomerOrders') }}",
        { customer_id: customer_id },
        function (res) {
            $('#order_no').html('<option value="">Select Order No</option>');
            res.data.forEach(o => {
                $('#order_no').append(
                    `<option value="${o.id}" data-img="${o.corporate_order_file}">${o.sku}</option>`
                );
            });
            $('#final_customer_id').val(customer_id);
        });
    });

    $('#order_no').change(function () {

        $('#div_order_dispatch,#packed_cartons').removeClass('d-none');
        let order_id = $(this).val();
        $('#final_order_no').val(order_id);
        let file = $(this).find(':selected').data('img');
        showOrderFile(file);

        barcodeRows = {};
        nullBarcodeRows = [];
        $('#orderTableBody').empty();

        $.get("{{ route('admin.packing-carton.getOrdersDetails') }}", {
            customer_id: $('#master_customer_id').val(),
            order_main_id: $(this).val()
        }, function (res) {
            setOrderTableBody(res.data);
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
        row.find('.remainQty').text(parseInt(row.find('.remainQty').text()) - pcs);

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

        // $.each(cartonBarcodes,(b,q)=>{
        //     total += q;
        //     rows += `<tr><td>${b}</td><td class="text-right">${q}</td></tr>`;
        //     $('<input>',{
        //         type:'hidden',
        //         name:'cartons[]',
        //         value:JSON.stringify({carton:cartonCount,barcode:b,qty:q})
        //     }).appendTo('form');
        // });

        $.each(cartonBarcodes, (barcode, qty) => {
            total += qty;
            rows += `<tr><td>${barcode}</td><td class="text-right">${qty}</td></tr>`;

            // IMPORTANT PART (array format)
            $('<input>', {
                type: 'hidden',
                name: `cartons[carton_${cartonCount}][]`,
                value: JSON.stringify({
                    barcode: barcode,
                    qty: qty
                })
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


    function setOrderTableBody(data){
        data.forEach((r,i) => {
            let bc = r.bar_code ? r.bar_code.trim() : '';

            $('#orderTableBody').append(`
                <tr data-index="${i}">
                    <td>${i+1}</td>
                    <td>${bc || ''}</td>
                    <td>${r.design_number}</td>
                    <td>${r.set_size}</td>
                    <td>${r.size_group}</td>
                    <td>${r.color}</td>
                    <td class="pcs">${r.no_of_pcs}</td>
                    <td>${r.set_quantity}</td>
                    <td class="totalQty">${r.total_quantity}</td>
                    <td class="setQty">${r.remain_set_quantity}</td>
                    <td class="remainQty">${r.remain_total_quantity}</td>
                </tr>
            `);

            if (bc) {
                barcodeRows[bc] = barcodeRows[bc] || [];
                barcodeRows[bc].push(i);
            } else {
                nullBarcodeRows.push(i);
            }
        });
    }

    function showOrderFile(file) {

        resetDocumentUI();

        if (!file) {
            return; // no file = no button, no preview
        }

        hasDocument = true;
        $('#docToggleWrapper').removeClass('d-none'); // show button

        let path = '/assets/products/' + file;
        let ext = file.split('.').pop().toLowerCase();

        if (['jpg','jpeg','png','webp'].includes(ext)) {
            $('#previewImg').attr('src', path).show();
        } 
        else if (ext === 'pdf') {
            $('#previewImg').hide();
            $('#image_view .card').append(`
                <embed id="previewPDF"
                    src="${path}"
                    type="application/pdf"
                    width="100%"
                    height="550px">
            `);
        }
    }


});

$('#toggleDocumentBtn').on('click', function () {

    if (!hasDocument) return;

    if (isDocVisible) {
        $('#image_view').slideUp(200).addClass('d-none');
        $(this).html('<i class="fas fa-eye mr-1"></i> Show Document');
    } else {
        $('#image_view').removeClass('d-none').slideDown(200);
        $(this).html('<i class="fas fa-eye-slash mr-1"></i> Hide Document');
    }

    isDocVisible = !isDocVisible;
});
function resetDocumentUI() {

    isDocVisible = false;
    hasDocument = false;

    $('#image_view').addClass('d-none').hide();
    $('#previewImg').hide().attr('src', '');
    $('#previewPDF').remove();

    $('#docToggleWrapper').addClass('d-none');
    $('#toggleDocumentBtn')
        .html('<i class="fas fa-eye mr-1"></i> Show Document');
}

</script>
@endsection
