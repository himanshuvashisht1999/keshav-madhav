@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage {{$stage_data->name}} Stage</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage {{$stage_data->name}} Stage</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- SELECT2 EXAMPLE -->
            <div class="card card-default ">
                 <div class="row" >
                    <div class="col-9 card-header">
                        <h3 class="card-title">Manage {{$stage_data->name}} Stage</h3>
                    </div>
                    <div class="col-3 card-header">
                        {{-- <a href="{{route('admin.order_stages.create')}}" class="btn btn-primary" style =" float: right;  width: max-content;">Create Slip</a> --}}
                    </div>
                </div>
                
                <div class="card-body table-responsive">
                <table id="order_stage" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="order_product_id" id="order_product_id" autocomplete="off">
                        </td>
                       
                        <td>
                            <select name="from_stage_id" id="from_stage_id" class="form-control">
                                <option value="">All</option>
                                @foreach($product_stage as $stage)
                                <option value="{{$stage->id}}">{{$stage->name}}</option>
                                @endforeach
                            </select>
                            
                         </td>
                           
                        <td>
                            <input type="text" class="form-control" name="quantity" id="quantity" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="remaining_quantity" id="remaining_quantity" autocomplete="off">
                        </td>
                        <td>
                            <input type="date" class="form-control" name="created_at" id="created_at" autocomplete="off">
                        </td>
                        

                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Order No.</th>
                        <th>Product SKU</th>
                        <th>From Stage</th>
                        <th>Quantity</th>
                        <th>Remaining Quantity</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  <!-- <tr>
                    <td>1</td>
                    <td>wefds</td>
                    <td>Win 95+</td>
                    <td> 4</td>
                    <td>X</td>
                  </tr> -->
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
        </div>
    </section>
</div>

<!-- Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.product_order.transfer') }}">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Transfer to Next Stage</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="order_product_id" id="order_tansaction_id">
                    <input type="hidden" name="from_stage_id" id="order_stage_id">

                    <div class="form-group">
                        <label>Quantity to Transfer</label>
                        <input type="number" name="quantity" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label>Remarks (optional)</label>
                        <textarea name="remarks" class="form-control" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success btn-sm">
                        <i class="fas fa-check-circle"></i> Confirm Transfer
                    </button>
                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>
    $(function () {
        var i = 1;
        var oTable = $('#order_stage').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering:false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            "pageLength":25,
            ajax: {
                url: '{!! route('admin.order_stages.indexList',['stage_id' => $stage_data->id]) !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.order_product_id = $('#order_product_id').val();
                    d.from_stage_id = $('#from_stage_id').val();
                    d.quantity = $('#quantity').val();
                    d.remaining_quantity = $('#remaining_quantity').val();
                    d.created_at = $('#created_at').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'order_product_id', name: 'order_product_id'},                
                {data: 'from_stage_id', name: 'from_stage_id'},                
                {data: 'quantity', name: 'quantity'},
                {data: 'remaining_quantity', name: 'remaining_quantity'},
                {data: 'created_at', name: 'created_at'},                
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: ['excel', 'csv', 'pdf', 'copy']
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
        $('#quantity').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

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
        
        

    });

    $(document).on('click', '.viewBtn', function() {
        var id = $(this).data('id');
        var order_stage_id = "{{ $stage_data->id }}";
        $('#order_tansaction_id').val(id);
        $('#order_stage_id').val(order_stage_id);
        
    });

    $(document).ready(function () {
        
    });

   
</script>

@endsection
