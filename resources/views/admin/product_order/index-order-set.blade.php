@extends('admin.layouts.app')
@section('content')
<style>
.assign-to {
    color: #007bff !important;
}
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header"> 
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-12">
                    <h1 class="text-center">Details of Sales Order</h1>
                </div>
                <div class="col-sm-12">
                    <h4 class="text-center">Order No. - ({{$order_main->sku}})</h4>
                </div>
                {{-- <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Production Order</li>
                    </ol>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                <!-- <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage Production Order</h3>
                    </div>
                    <div class="col-3 card-header">
                        <a href="{{route('admin.sales_order.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Add Sales Order</a>
                    </div>
                </div> -->
                
                <div class="card-body table-responsive">
                    <table id="customers" class="table table-bordered table-hover">
                    <thead>
                        <tr role="row" class="filter">
                            <td>
                                <input type="hidden" class="form-control" name="id" id="id" value="{{$order_main->id}}" autocomplete="off">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="bar_code" id="bar_code" autocomplete="off">
                            </td>
                            <td>
                                <input type="text" class="form-control" name="design_number" id="design_number" autocomplete="off">
                            </td>
                            <td>
                                
                            </td>
                            <td>
                                
                            </td>
                            <td>
                                
                            </td>
                            
                            <td>
                                
                            </td>
                            <td>
                            
                            </td>
                            <td>
                            
                            </td>
                             <td>
                            
                            </td>
                            <td>
                                <!-- <select id="status" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="1">Not Issued</option>
                                    <option value="2">In Progress</option>
                                    <option value="3">Completed</option>
                                </select> -->
                            </td>

                            <td></td>
                        </tr>
                        <tr>
                            <th>ID</th>
                            <th>Provided Bar Code</th>
                            <th>Design Number</th>
                            <th>Set Size</th>
                            <th>Size Group</th>
                            <th>Colour</th>
                            <th>Set Quantity</th>
                            <th>Pcs per Set</th>
                            <th>Total Quantity</th>
                            <th>Status</th>
                            <th>Assign To</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="6" class="text-right">Total Set :</th>
                            <th id="set_qty_total"></th>          <!-- Set Quantity Total -->
                            <th>Total Quantity</th>
                            <th id="total_qty_total"></th>        <!-- Total Quantity Total -->
                            <th></th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <div>
                @if (false)
                    <section class="content">
                        <div class="container-fluid">

                            <div class="card p-3 shadow-sm">

                                <form action="{{ route('admin.product_order.assign_to') }}" method="POST" enctype="multipart/form-data">
                                    @csrf

                                    <div class="row">
                                        <div class="col-md-6">
                                        </div>
                                        <!-- LEFT -->
                                        <div class="col-md-6">

                                            <!-- Customer & Delivery -->
                                            <div class="card mb-3 p-3 border">
                                                <h3 class="mb-3 assign-to" >Assign to Cutting Master</h3>

                                                <label>Select Cutting Master</label>
                                                <select name="master_cutting_id" id="master_cutting_id" class="form-control select2 mb-2" required>
                                                    <option value="">Select Cutting Master </option>
                                                    @foreach($cutting_units as $cutting_unit)
                                                        <option value="{{ $cutting_unit->id }}">{{ $cutting_unit->cutting_master_name }}</option>
                                                    @endforeach
                                                </select>
                                                @if ($errors->has('master_cutting_id'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('master_cutting_id') }}
                                                    </span>
                                                @endif
                                                {{-- <label for="delivery_time_allowed">Delivery Time Allowed (in Days)</label>
                                                <input type="number" name="delivery_time_allowed" id="delivery_time_allowed" min='1' placeholder="Enter Delivery Time Allowed in days "  class="form-control">
                                                <label for="remarks">Remarks</label>
                                                <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Enter your remarks..."></textarea>
                                                @if ($errors->has('remarks'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('remarks') }}
                                                    </span>
                                                @endif --}}

                                                {{-- <!-- TIME TYPE -->
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Time Type</label>
                                                    <select name="time_type" id="time_type" class="form-control" required>
                                                        <option value="">Select Time Type</option>
                                                        <option value="hours">Hours</option>
                                                        <option value="days">Days</option>
                                                    </select>
                                                </div>

                                                <!-- ALLOWED TIME -->
                                                <div class="form-group">
                                                    <label class="font-weight-bold" id="allowed_time_label">
                                                        Allowed Time
                                                    </label>
                                                    <input type="number"
                                                        class="form-control"
                                                        id="allowed_time"
                                                        name="allowed_time"
                                                        placeholder="Enter Allowed Time"
                                                        min="1" required>
                                                </div> --}}

                                                <!-- REMARK -->
                                                <div class="form-group">
                                                    <label class="font-weight-bold">Remarks</label>
                                                    <textarea
                                                        class="form-control @error('final_remark') is-invalid @enderror"
                                                        id="final_remark"
                                                        name="final_remark"
                                                        rows="3"
                                                        placeholder="Enter remark (optional)"
                                                    >{{ old('final_remark') }}</textarea>
                                                </div>
                                                <input type="hidden"
                                                        class="form-control"
                                                        id="till_allowed_time"
                                                        name="till_allowed_time">
                                            </div>

                                        </div>

                                    </div>

                                    <div class="text-right mt-3">
                                        <input type="hidden" id="order_main_id" name="order_main_id" value="{{$order_main->id}}">
                                        <button class="btn btn-success px-4">Assign</button>
                                    </div>

                                </form>

                            </div>

                        </div>
                    </section>
                @endif
            
        </div>
    </section>
</div>
<div class="modal fade" id="assignModal" tabindex="-1">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content">

            <form id="assignForm">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Assign to Cutting Master</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                <div class="border rounded p-2 mb-3 bg-light">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>Design No:</strong>
                            <span id="modal_design_number"></span>
                        </div>
                        <div class="col-md-6">
                            <strong>Set Size:</strong>
                            <span id="modal_set_size"></span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Color:</strong>
                            <span id="modal_color"></span>
                        </div>
                        <div class="col-md-6 mt-2">
                            <strong>Total Qty:</strong>
                            <span id="modal_total_qty"></span>
                        </div>
                    </div>
                </div>


                    <input type="hidden" id="modal_order_set_id" name="order_product_set_id">

                    <!-- Cutting Master -->
                    <div class="form-group">
                        <label>Cutting Master</label>
                        <select name="master_cutting_id" class="form-control select2" required>
                            <option value="">Select</option>
                            @foreach($cutting_units as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->cutting_master_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Fabric -->
                    <div class="form-group">
                        <label>Fabric</label>
                        <select name="fabric_id" class="form-control select2" required>
                            <option value="">Select</option>
                            @foreach($fabrics as $fabric)
                                <option value="{{ $fabric->id }}">
                                    {{ $fabric->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Fitting</label>
                        <select name="master_fitting_id" class="form-control select2" required>
                            <option value="">Select</option>
                            @foreach($fittings as $fitting)
                                <option value="{{ $fitting->id }}">
                                    {{ $fitting->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Remark -->
                    <div class="form-group">
                        <label>Remarks</label>
                        <textarea name="remark" class="form-control" rows="3"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-success">Assign</button>
                </div>

            </form>

        </div>
    </div>
</div>
<script>
    $(document).on('click', '.assign-btn', function () {

        $('#modal_order_set_id').val($(this).data('id'));

        $('#modal_design_number').text($(this).data('design'));
        $('#modal_set_size').text($(this).data('set-size'));
        $('#modal_color').text($(this).data('color'));
        $('#modal_total_qty').text($(this).data('total'));

        $('#assignModal').modal('show');
    });
</script>
<script>
    $(function () {
        let buttonsConfig = [];

        @if ($check_assign == true)
            // buttonsConfig.push({
            //     text: 'Download Slip',
            //     className: 'btn-datatable',
            //     action: function () {
            //         window.location.href = "{{ route('admin.product_order.downloadCuttingSlip', ['id' => $order_main->id]) }}";
            //     }
            // });
        @endif
        var i = 1;
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.product_order.indexListOrderSet') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.bar_code = $('#bar_code').val();
                    d.design_number = $('#design_number').val();
                    d.status = $('#status').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'bar_code', name: 'bar_code'},
                {data: 'design_number', name: 'design_number'},  
                {data: 'set_size', name: 'set_size'},
                {data: 'size_group', name: 'size_group'},
                {data: 'color_id', name: 'color_id'},
                {data: 'set_quantity', name: 'set_quantity'},
                {data: 'no_of_pcs', name: 'no_of_pcs'},  
                {data: 'total_qty', name: 'Quantity'},                         
                {data: 'status', name: 'status'}, 
                {data: 'assign_to', name: 'assign_to'},                 
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: buttonsConfig,
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                // Convert string to number safely
                let intVal = function (i) {
                    return (typeof i === "string")
                        ? i.replace(/[\$,]/g, "") * 1
                        : (typeof i === "number" ? i : 0);
                };

                // Total SET QUANTITY (column index 5)
                let setQtyTotal = api
                    .column(6, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total TOTAL QUANTITY (column index 7)
                let totalQtyTotal = api
                    .column(8, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Show values in footer
                $('#set_qty_total').html(setQtyTotal);
                $('#total_qty_total').html(totalQtyTotal);
            }
           
        });

        $('#email-queue-search-form').on('submit', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#bar_code').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#design_number').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#status').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#time_type, #allowed_time').on('change keyup', function () {
            calculateAllowedTill();
        });

        // Final submit
        // $('#confirmFinalSubmit').on('click', function(){
        //     calculateAllowedTill();
        //     // ✅ count only real data rows
        //     let dataRowCount = $('#productTable tbody tr')
        //         .not('#noDataRow')
        //         .length;

        //     if (dataRowCount === 0) {
        //         alert('Please add at least one design before submitting.');
        //         return;
        //     }

        //     let timeType     = $('#time_type').val();
        //     let allowedTime  = $('#allowed_time').val();
        //     let remark       = $('#final_remark').val().trim();

        //     if (!timeType) {
        //         alert('Please select Time Type (Hours / Days)');
        //         return;
        //     }

        //     if (!allowedTime || allowedTime <= 0) {
        //         alert('Please enter valid allowed time');
        //         return;
        //     }

        //     // ✅ copy modal values to hidden form fields
        //     $('#remark').val(remark);
        //     $('#hidden_allowed_time').val(allowedTime);
        //     $('#hidden_time_type').val(timeType);

        //     // ✅ submit
        //     $('#confirmSubmitModal').modal('hide');
        //     $('#slip_digitalization form').submit();
        // });

        // function calculateAllowedTill() {
        //     let type  = $('#time_type').val();
        //     let value = parseInt($('#allowed_time').val());
        //     if (!type || !value || value <= 0) {
        //         $('#hidden_allowed_till').val('');
        //         return;
        //     }

        //     let now = new Date();

        //     if (type === 'hours') {
        //         now.setHours(now.getHours() + value);
        //     }

        //     if (type === 'days') {
        //         now.setDate(now.getDate() + value);
        //     }

        //     // format for backend: YYYY-MM-DD HH:mm:ss
        //     let formatted =
        //         now.getFullYear() + '-' +
        //         String(now.getMonth() + 1).padStart(2, '0') + '-' +
        //         String(now.getDate()).padStart(2, '0') + ' ' +
        //         String(now.getHours()).padStart(2, '0') + ':' +
        //         String(now.getMinutes()).padStart(2, '0') + ':00';

        //     $('#hidden_allowed_till').val(formatted);
        //     $('#till_allowed_time').val(formatted);
            
        // }

    });

$('#assignForm').on('submit', function (e) {
    e.preventDefault();

    $.ajax({
        url: "{{ route('admin.product_order.assign_to') }}",
        type: "POST",
        data: $(this).serialize(),
        success: function () {
            $('#assignModal').modal('hide');
            $('#customers').DataTable().ajax.reload(null, false);
        },
        error: function () {
            alert('Something went wrong');
        }
    });
});


</script>

@endsection
