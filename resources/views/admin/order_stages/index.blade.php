@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">

    <!--  Header (Simplified & Compact) -->
    <section class="content-header py-2 border-bottom">
        <div class="container-fluid d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">
                <i class="fas fa-tasks text-secondary"></i> {{ $stage_data->name }} Stage
            </h5>
            <ol class="breadcrumb float-sm-right mb-0 small">
                <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-primary">Home</a></li>
                <li class="breadcrumb-item active text-muted">Manage Stage</li>
            </ol>
        </div>
    </section>

    <!--  Table Section -->
    <section class="content mt-3">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 rounded-3">
                <!-- <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0 fw-bold text-secondary">
                        <i class="fas fa-table"></i> Stage Overview
                    </h6>
                </div> -->

                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table id="order_stage" class="table table-sm table-bordered text-center align-middle mb-0" style="font-size: 13px;">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Order No</th>
                                    <th>Product SKU</th>
                                    <th>Lot No.</th>
                                    <th>From Stage</th>
                                    <th>Sub Stage</th>
                                    <th>Qty</th>
                                    <th>Remain</th>
                                    <th>Status</th>
                                    <th>Received</th>
                                    <th>Delivered</th>
                                    <th>Action</th>
                                </tr>
                                <tr class="bg-white">
                                    <td></td>
                                    <td><input type="text" class="form-control form-control-sm" id="sku" placeholder="Order No"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="order_product_id" placeholder="Product SKU"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="lot_no" placeholder="Lot No."></td>
                                    
                                    <td>
                                        <select id="from_stage_id" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach($product_stage as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td>
                                        <select id="sub_stage_id" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            @foreach($stage_data->sub_stages as $stage)
                                                <option value="{{ $stage->id }}">{{ $stage->name }}</option>
                                            @endforeach
                                        </select>
                                    </td>
                                    <td><input type="text" class="form-control form-control-sm" id="quantity" placeholder="Qty"></td>
                                    <td><input type="text" class="form-control form-control-sm" id="remaining_quantity" placeholder="Remain"></td>
                                    <td>
                                        <select id="status" class="form-control form-control-sm">
                                            <option value="">All</option>
                                            <option value="in_progress">In Progress</option>
                                            <option value="completed">Completed</option>
                                        </select>
                                    </td>
                                    <td><input type="date" class="form-control form-control-sm" id="created_at"></td>
                                    <td><input type="date" class="form-control form-control-sm" id="updated_at"></td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<!--  Transfer Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-sm rounded-3">
            <form method="POST" action="{{ route('admin.product_order.transfer') }}">
                @csrf
                <div class="modal-header bg-success text-white py-2">
                    <h6 class="modal-title fw-bold mb-0"><i class="fas fa-exchange-alt"></i> Transfer to Next Stage</h6>
                    <button type="button" class="btn-close btn-close-white" data-dismiss="modal">X</button>
                </div>

                <div class="modal-body py-3">
                    <input type="hidden" name="order_product_id" id="order_product_id_modal">
                    <input type="hidden" name="from_stage_id" id="order_stage_id">
                    <input type="hidden" name="order_transaction_id" id="order_transaction_id">

                    <div class="form-group mb-3">
                        <label class="small mb-1"><strong>Quantity to Transfer</strong></label>
                        <input type="number" name="quantity" class="form-control form-control-sm" id="total_remaining_qty" required min="1" step="1">
                        <small class="text-muted">Max allowed: <span id="maxQtyText"></span></small>
                    </div>
                    <div class="form-group mb-3">
                        <label class="small mb-1"><strong>Lot No.</strong></label>
                        <input type="number" name="lot_no" class="form-control form-control-sm" id="lot_no_m" required min="1" step="1">
                    </div>
                    <div class="form-group mb-3">
                        <label class="small mb-1"><strong>Select Sub Stage</strong></label>
                        <select name="sub_stage" id="sub_stage" class="form-control">
                            <option value=""></option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label class="small mb-1"><strong>Remarks (optional)</strong></label>
                        <textarea name="remarks" class="form-control form-control-sm" rows="2" placeholder="Enter remarks..."></textarea>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle"></i> Confirm
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!--  JS Section -->
<script>
$(function () {
    var oTable = $('#order_stage').DataTable({
        processing: true,
        serverSide: true,
        ordering: false,
        searching: false,
        pageLength: 10,
        ajax: {
            url: '{!! route('admin.order-stages.indexList',['stage_id' => $stage_data->id]) !!}',
            data: function (d) {
                d.sku = $('#sku').val();
                d.order_product_id = $('#order_product_id').val();
                d.from_stage_id = $('#from_stage_id').val();
                d.sub_stage_id = $('#sub_stage_id').val();
                d.lot_no = $('#lot_no').val();
                d.quantity = $('#quantity').val();
                d.remaining_quantity = $('#remaining_quantity').val();
                d.status = $('#status').val();
                d.created_at = $('#created_at').val();
                d.updated_at = $('#updated_at').val();
            }
        },
        columns: [
            { data: 'DT_RowIndex', name: 'id', width: '5%' },
            { data: 'sku', name: 'sku', width: '10%' },
            { data: 'order_product_id', name: 'order_product_id', width: '12%' },
            { data: 'lot_no', name: 'lot_no' },
            { data: 'from_stage_id', name: 'from_stage_id', width: '10%' },
            { data: 'sub_stage_id', name: 'sub_stage_id' },
            { data: 'quantity', name: 'quantity', width: '7%' },
            { data: 'remaining_quantity', name: 'remaining_quantity', width: '7%' },
            { data: 'status', name: 'status', width: '9%' },
            { data: 'created_at', name: 'created_at', width: '12%' },
            { data: 'updated_at', name: 'updated_at', width: '12%' },
            { data: 'action', name: 'action', width: '10%', orderable: false, searchable: false }
        ]
    });

    $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#sku').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#order_product_id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#from_stage_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#sub_stage_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#quantity').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#lot_no').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        })
        $('#remaining_quantity').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#order_product_id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
       
        $('#created_at').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });        

});

// Modal Handling
$(document).on('click', '.viewBtn', function() {
    const remaining = $(this).data('total_remaining_qty');
    $('#order_product_id_modal').val($(this).data('id'));
    $('#order_stage_id').val("{{ $stage_data->id }}");
    $('#order_transaction_id').val($(this).data('order_transaction_id'));
    $('#total_remaining_qty').val(remaining).attr('max', remaining);
    $('#maxQtyText').text(remaining);
});

// Prevent over-transfer
$(document).on('input', '#total_remaining_qty', function() {
    const max = parseFloat($(this).attr('max'));
    const val = parseFloat($(this).val());
    if (val > max) { 
        alert('You cannot transfer more than ' + max + ' units.');
        $(this).val(max);
    } else if (val < 0) {
        $(this).val(0);
    }
});
</script>

<script>
    // Modal Handling + API Call
$(document).on('click', '.viewBtn', function() {
    const orderProductId = $(this).data('id');
    const lot_no = $(this).data('lot_no');
    const remaining = $(this).data('total_remaining_qty');
    const from_stage_id = "{{ $stage_data->id }}";
    
    $('#order_product_id_modal').val(orderProductId);
    if (lot_no && lot_no !== '') {
        $('#lot_no_m').val(lot_no).prop('readonly', true).addClass('bg-light'); // make readonly + light background
    } else {
        $('#lot_no_m').val('').prop('readonly', false).removeClass('bg-light');
    }
    $('#order_stage_id').val("{{ $stage_data->id }}");
    $('#order_transaction_id').val($(this).data('order_transaction_id'));
    $('#total_remaining_qty').val(remaining).attr('max', remaining);
    $('#maxQtyText').text(remaining);

    // Clear existing options first
    $('#sub_stage').html('<option value="">Loading...</option>');

    //  Fetch sub stages via GET API
    let apiUrl = "{{ route('admin.order-stages.getSubStages', [':order_product_id', ':from_stage_id']) }}";
    apiUrl = apiUrl.replace(':order_product_id', orderProductId).replace(':from_stage_id', from_stage_id);
    $.ajax({
        url: apiUrl,
        type: "GET",
        success: function(response) {
            if (response.status && response.data.length > 0) {
                let options = '<option value="">Select Sub Stage</option>';
                response.data.forEach(function(stage) {
                    options += `<option value="${stage.id}">${stage.name}</option>`;
                });
                $('#sub_stage').html(options);
            } else {
                $('#sub_stage').html('<option value="">No sub stages found</option>');
            }
        },
        error: function() {
            $('#sub_stage').html('<option value="">Error fetching data</option>');
        }
    });
});

</script>
@endsection
