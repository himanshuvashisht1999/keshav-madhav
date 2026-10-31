@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Content Header (Page header) -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-list text-primary"></i> Manage Item Stock
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
                            <select name="sku" id="sku" class="form-control select2" style="width: 100%;">
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
            dom: 'lfrtip'
        });

        $('#sku').on('change', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });

</script>

@endsection
