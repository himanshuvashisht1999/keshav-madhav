@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Purchase Order Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Purchase Order Report</li>
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
                            <input type="date" class="form-control" name="date" id="date" autocomplete="off">
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
                            <input type="date" class="form-control" name="delivery_date" id="delivery_date" autocomplete="off">
                        </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>PO No.</th>
                        <th>Date</th>
                        <th>Vendor</th>
                        <th>Delivery Date</th>
                       
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
                url: '{!! route('admin.purchase_order.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.delivery_date = $('#delivery_date').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'date', name: 'date'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'delivery_date', name: 'delivery_date'},
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

        $('#date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#vendor_id').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#delivery_date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#sku').on('keyup', function (e) {
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
                    time: $('#date').val(),
                    delivery_date: $('#delivery_date').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Create a hidden form for POST submission (so file download works)
                var form = $('<form>', {
                    action: '{{ route("admin.reports.purchaseOrderExcel") }}',
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
