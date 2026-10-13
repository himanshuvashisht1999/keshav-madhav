@extends('admin.layouts.app')
@section('content')
<style>
    #customers td:nth-child(3) {
        min-width: 130px;
    }
</style>
<div class="content-wrapper erp-page p-2">
    <!-- Content Header (Page header) -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-list text-primary"></i> Manage Item Receipt
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid p-0">
            <!-- SELECT2 EXAMPLE -->
            <div class="erp-card">
                 
                <div class="erp-card-body p-2 table-responsive">
                <table id="customers" class="erp-table table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        
                        <td>
                            <select class="form-control select2" name="vendor_id" id="vendor_id" style="width: 100%;">
                                <option value="">ALL</option>
                                @foreach($vendors as $single_data)
                                    <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                                @endforeach
                            </select>
                        </td>
                        <td>
                            <input type="text" class="form-control" name="truck_number" id="truck_number" autocomplete="off">
                        </td>
                        
                        <td>
                            <input type="date" class="form-control" name="time" id="time" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="box" id="box" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="received_by" id="received_by" autocomplete="off">
                        </td>
                        
                        
                        <td>
                       
                       </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Vendor</th>
                        <th>Truck Number</th>
                        <th>Date & Time</th>
                        <th>Box</th>
                        <th>Received By</th>
                        
                        <th>Action</th>
                    </tr>
                  </thead>
                  <tbody>
                  
                  </tbody>
                  
                </table>
              </div>
            </div>
        </div>
    </section>
</div>
<script>
    $(function () {
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
                url: '{!! route('admin.item_receipt.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.truck_number = $('#truck_number').val();
                    d.time = $('#time').val();
                    d.box = $('#box').val();
                    d.received_by = $('#received_by').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'truck_number', name: 'truck_number'},
                {data: 'time', name: 'time'},
                {data: 'box', name: 'box'},
                {data: 'received_by', name: 'received_by'},
                
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: 'Add Item Receipt',
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        window.location.href = "{{ route('admin.item_receipt.create') }}";
                    }
                }
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
        
        $('#vendor_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#truck_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#box').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#received_by').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#time').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        
        

    });

    $(document).ready(function () {
        
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#53718dff",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, trigger the delete route
                window.location.href = "{{ route('admin.item_receipt.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection