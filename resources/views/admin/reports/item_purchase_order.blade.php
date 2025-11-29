@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Item Purchase Order Report</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Item Purchase Order Report</li>
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
                        <td> </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>PO No.</th>
                        <th>Purchase Order Date</th>
                        <th>Vendor</th>
                        <th>Expected Delivery Date</th>
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
                url: '{!! route('admin.reports.itemPurchaseOrderList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.vendor_id = $('#vendor_id').val();
                    d.delivery_date = $('#delivery_date').val();
                    d.selected_field = $('#selected_field').val();
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'date', name: 'date'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'delivery_date', name: 'delivery_date'},
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
            ],

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
        // $('#date_range').on('change', function (e) {
        //     oTable.draw();
        //     e.preventDefault();
        // });

        // $('#selected_field').on('change', function (e) {
        //     alert('fffd');
        //     oTable.draw();
        //     e.preventDefault();
            
        // });
        //
        $("#customers_length").append(`
        <select id="selected_field" name="selected_field" class="form-control">
            <option value="date">Purchase Order Date</option>
            <option value="delivery_date">Expected Delivery Date</option>
        </select>
        <input type="text" id="report-range" name="date_range" style="width: 200px; max-width: 100%; margin-bottom: 5px;" placeholder="Select Date Range" autocomplete="off"> 
        <input type="hidden" name="start_date" id="start_date">
        <input type="hidden" name="end_date" id="end_date" >`);

        $('#report-range').daterangepicker({
            autoUpdateInput: false, // Don't fill input initially
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD',
                cancelLabel: 'Clear'
            },
            ranges: {
                'Today': [moment(), moment()],
                'Yesterday': [moment().subtract(1, 'day'), moment().subtract(1, 'day')],
                'This Week': [moment().startOf('week'), moment().endOf('week')],
                'Last Week': [
                    moment().subtract(1, 'week').startOf('week'),
                    moment().subtract(1, 'week').endOf('week')
                ],
                'This Month': [moment().startOf('month'), moment().endOf('month')],
                'This Year': [moment().startOf('year'), moment().endOf('year')],
            },

            startDate: moment().subtract(1, 'month'),
            endDate: moment(),

        }, function(start, end) {
            $('#start_date').val(start.format('YYYY-MM-DD'));
            $('#end_date').val(end.format('YYYY-MM-DD'));
        });

        // Set value when user selects a range
        $('#report-range').on('apply.daterangepicker', function(ev, picker) {
            $(this).val(picker.startDate.format('YYYY-MM-DD') + " to " + picker.endDate.format('YYYY-MM-DD'));
            $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
            $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
            oTable.draw(); 
        });

        // Clear input when user clicks cancel
        $('#report-range').on('cancel.daterangepicker', function(ev, picker) {
            $(this).val('');
            oTable.draw(); 
        });

        $('#selected_field').on('change', function (e) { 
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
                    selected_field: $('#selected_field').val(),
                    start_date: $('#start_date').val(),
                    end_date: $('#end_date').val(),
                    _token: '{{ csrf_token() }}'
                };

                // Create a hidden form for POST submission (so file download works)
                var form = $('<form>', {
                    action: '{{ route("admin.reports.itemPurchaseOrderExcel") }}',
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

// date picker 


$(function() {

   

});
</script>

@endsection
