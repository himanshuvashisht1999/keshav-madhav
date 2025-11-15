@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Manage Item Stock</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                        <li class="breadcrumb-item active">Manage Item Stock</li>
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
                            <select name="sku" id="sku"  class="form-control">
                                <option value="">All Item SKU</option>
                                @foreach($items as $item)
                                    <option value="{{ $item->sku }}">{{ $item->sku }}</option>
                                @endforeach
                            </select>
                        </td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th>ID</th>
                        <th>SKU</th>
                        <th>Total Item (Quantity)</th>
                        <th>action</th>
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
                url: '{!! route('admin.item_stock.itemIndexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.sku = $('#sku').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'sku', name: 'sku'},
                {data: 'total_quantity', name: 'total_quantity'},
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

        $('#sku').on('change', function (e) {
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
                    _token: '{{ csrf_token() }}'
                };

                // Create a hidden form for POST submission (so file download works)
                var form = $('<form>', {
                    action: '{{ route("admin.reports.fabricStockSkuExcel") }}',
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
