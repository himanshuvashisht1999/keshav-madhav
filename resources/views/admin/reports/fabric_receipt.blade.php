@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Fabric Receipt Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Fabric Receipt Report</li>
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
                
                
                <div class="card-body table-responsive">
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        <td>
                            <!-- <input type="text" class="form-control" name="id" id="id" autocomplete="off"> -->
                        </td>
                        <td>
                            <input type="text" class="form-control" name="sku" id="sku" autocomplete="off">
                        </td>
                        
                        <td>
                            <select class="form-control" name="vendor_id" id="vendor_id" autocomplete="off">
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
                            <input type="text" class="form-control" name="roll" id="roll" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="received_by" id="received_by" autocomplete="off">
                        </td>
                        
                        
                        
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Vendor</th>
                        <th>Truck Number</th>
                        <th>Date & Time</th>
                        <th>Packet</th>
                        <th>Received By</th>
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
                url: '{!! route('admin.reports.fabricReceiptList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.truck_number = $('#truck_number').val();
                    d.time = $('#time').val();
                    d.roll = $('#roll').val();
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
                {data: 'roll', name: 'roll'},
                {data: 'received_by', name: 'received_by'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
               {
                    text: `<i class="nav-icon fas fa-download"></i> Excel Reports`,
                    attr: {
                        id: 'generateExcel',
                        name: 'generateExcel'
                    },
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        e.preventDefault();
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

        $('#roll').on('keyup', function (e) {
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

        $(document).ready(function () {
            $('#generateExcel').on('click', function (e) {
                e.preventDefault(); // prevent default behavior

                // Collect filter values
                var data = {
                    id: $('#id').val(),
                    sku: $('#sku').val(),
                    vendor_id: $('#vendor_id').val(),
                    truck_number: $('#truck_number').val(),
                    time: $('#time').val(),
                    roll: $('#roll').val(),
                    received_by: $('#received_by').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Create a hidden form for POST submission (so file download works)
                var form = $('<form>', {
                    action: '{{ route("admin.reports.fabricReceiptExcel") }}',
                    method: 'POST',
                    target: '_blank'
                }).append($.map(data, function(v, k) {
                    return $('<input>', { type: 'hidden', name: k, value: v });
                }));

                $('body').append(form);
                form.submit();
                form.remove();
            });
        });

    });

</script>

@endsection
