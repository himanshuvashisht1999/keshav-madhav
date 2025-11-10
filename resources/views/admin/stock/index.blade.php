@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-8">
                    <h3 style="font-size:1.2rem;">Fabric Stock SKU : <span class="text-muted">{{ $fabrics->sku }}</span></h3>
                </div>
                <div class="col-sm-4">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Fabric Stock</li>
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
                 <!-- <div class="row" >
                    <div class="col-12 card-header">
                        <h3 class="card-title">Manage Fabric Stock</h3>
                    </div>
                </div> -->
                
                <div class="card-body table-responsive">
                <!-- <div class="d-flex justify-content-end me-3 ">
                    <button id="generatePDF" class="btn btn-primary m-1" name="generatePDF">
                        <i class="nav-icon fas fa-download"></i> PDF Reports
                    </button>
                    <button id="generateExcel" class="btn btn-primary m-1" name="generateExcel">
                        <i class="nav-icon fas fa-download"></i> Excel Reports
                    </button>
                    <button name="fabric_quantity" class="btn btn-primary m-1 " onclick="window.location.href='{{ route('admin.stock.fabricQuantityExcel') }}'">
                        <i class="nav-icon fas fa-download"></i> Fabric Quantity
                    </button>
                </div> -->
                
                <table id="customers" class="table table-bordered table-hover">
                  <thead>
                    <tr role="row" class="filter">
                        
                        <td>
                           <input type="hidden" class="form-control" name="sku" id="sku" value="{{$fabrics->sku}}" autocomplete="off">
                        </td>
                        
                        <td>
                            <input type="date" class="form-control" name="date" id="date" autocomplete="off">
                        </td>
                        
                        
                        <td>
                            <input type="text" class="form-control" name="meter" id="meter" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="unique_number" id="unique_number" autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control" name="batch_no" id="batch_no" autocomplete="off">
                        </td>  
                        <td>
                       
                       </td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>Date</th>
                        <th>Meter</th>
                        <th>Unique No</th>
                        <th>Batch No</th>
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
                url: '{!! route('admin.stock.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                    d.date = $('#date').val();
                    d.meter = $('#meter').val();
                    d.unique_number = $('#unique_number').val();
                    d.batch_no = $('#batch_no').val();
                  
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'date', name: 'date'},
                {data: 'meter', name: 'meter'},
                {data: 'unique_number', name: 'unique_number'},
                {data: 'batch_no', name: 'batch_no'},
                {data: 'action', name: 'action', searchable: false}
            ],
            dom: 'lBfrtip',
            buttons: [
                {
                    text: `<i class="nav-icon fas fa-download"></i> PDF Reports`,
                    attr: {
                        id: 'generatePDF',
                        name: 'generatePDF'
                    },
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        e.preventDefault();
                        // Add your PDF generation logic here
                    }
                },
                {
                    text: `<i class="nav-icon fas fa-download"></i> Excel Reports`,
                    attr: {
                        id: 'generateExcel',
                        name: 'generateExcel'
                    },
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        e.preventDefault();
                        // Add your Excel generation logic here
                    }
                },
                {
                    text: `<i class="nav-icon fas fa-download"></i> Fabric Quantity`,
                    attr: {
                        id: 'fabric_quantity',
                        name: 'fabric_quantity'
                    },
                    className: 'btn-datatable',
                    action: function (e, dt, node, config) {
                        // Redirect to your Laravel route
                        window.location.href = "{{ route('admin.stock.fabricQuantityExcel') }}";
                    }
                }
            ]
        });

        $('#id').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        
        $('#date').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#unique_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#meter').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#batch_no').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

    });

    $(document).ready(function () {
        $('#generatePDF').on('click', function (e) {
            e.preventDefault(); // prevent default behavior

            // Collect filter values
            var data = {
                sku: $('#sku').val(),
                date: $('#date').val(),
                meter: $('#meter').val(),
                unique_number: $('#unique_number').val(),
                batch_no: $('#batch_no').val(),
                _token: '{{ csrf_token() }}'
            };

            // Create a hidden form for POST submission (so file download works)
            var form = $('<form>', {
                action: '{{ route("admin.stock.generatePdf") }}',
                method: 'POST',
                target: '_blank'
            }).append($.map(data, function(v, k) {
                return $('<input>', { type: 'hidden', name: k, value: v });
            }));

            $('body').append(form);
            form.submit();
            form.remove();
        });
        $('#generateExcel').on('click', function (e) {
            e.preventDefault(); // prevent default behavior

            // Collect filter values
            var data = {
                sku: $('#sku').val(),
                date: $('#date').val(),
                meter: $('#meter').val(),
                unique_number: $('#unique_number').val(),
                batch_no: $('#batch_no').val(),
                _token: '{{ csrf_token() }}'
            };

            // Create a hidden form for POST submission (so file download works)
            var form = $('<form>', {
                action: '{{ route("admin.stock.generateExcel") }}',
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

</script>

@endsection
