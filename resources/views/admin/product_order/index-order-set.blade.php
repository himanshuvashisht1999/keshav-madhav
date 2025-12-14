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
                                <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
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
                                <select id="status" class="form-control form-control-sm">
                                    <option value="">All</option>
                                    <option value="1">Not Issued</option>
                                    <option value="2">In Progress</option>
                                    <option value="3">Completed</option>
                                </select>
                            </td>

                            <td></td>
                        </tr>
                        <tr>
                            <th>ID</th>
                            <th>Product order No. (per Set)</th>
                            <th>Design Number</th>
                            <th>Set Size</th>
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
                            <th colspan="5" class="text-right">Total Set :</th>
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
                @if ($check_assign == false)
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
                                                <label for="delivery_time_allowed">Delivery Time Allowed (in Days)</label>
                                                <input type="number" name="delivery_time_allowed" id="delivery_time_allowed" min='1' placeholder="Enter Delivery Time Allowed in days "  class="form-control">
                                                <label for="remarks">Remarks</label>
                                                <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="Enter your remarks..."></textarea>
                                                @if ($errors->has('remarks'))
                                                    <span class="invalid-feedback d-block">
                                                        {{ $errors->first('remarks') }}
                                                    </span>
                                                @endif
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
        </div>
    </section>
</div>

<script>
    $(function () {
        let buttonsConfig = [];

        @if ($check_assign == true)
            buttonsConfig.push({
                text: 'Download Slip',
                className: 'btn-datatable',
                action: function () {
                    window.location.href = "{{ route('admin.product_order.downloadCuttingSlip', ['id' => $order_main->id]) }}";
                }
            });
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
                    d.sku = $('#sku').val();
                    d.design_number = $('#design_number').val();
                    d.status = $('#status').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'design_number', name: 'design_number'},  
                {data: 'set_size', name: 'set_size'},
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
                    .column(5, { page: 'current' })
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                // Total TOTAL QUANTITY (column index 7)
                let totalQtyTotal = api
                    .column(7, { page: 'current' })
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

        $('#sku').on('keyup', function (e) {
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
        
        

    });



</script>

@endsection
