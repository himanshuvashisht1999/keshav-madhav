@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-file-invoice text-primary"></i> Fabric Purchase Orders
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.purchase_order.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Purchase Order
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar">
        <div class="row align-items-end">
            <div class="col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> PO Number</label>
                <input type="text" class="form-control erp-input" name="sku" id="sku" placeholder="Search PO No..." autocomplete="off">
            </div>
            <div class="col-md-2 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> PO Date</label>
                <input type="date" class="form-control erp-input" name="date" id="date" autocomplete="off">
            </div>
            <div class="col-md-3 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Vendor</label>
                <select class="form-control select2 erp-input" name="vendor_id" id="vendor_id" style="width: 100%;">
                    <option value="">-- ALL VENDORS --</option>
                    @foreach($vendors as $single_data)
                        <option value="{{$single_data->id}}" >{{$single_data->name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6 mb-1">
                <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> Delivery Date</label>
                <input type="date" class="form-control erp-input" name="delivery_date" id="delivery_date" autocomplete="off">
            </div>
            <div class="col-auto mb-1 ml-auto">
                <div class="erp-filter-actions">
                    <button type="button" class="btn-erp btn-erp-primary" id="btnApplyFilter" title="Apply Filter">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <button type="button" class="btn-erp btn-erp-outline" id="btnResetFilter" title="Reset Filters">
                        <i class="fas fa-undo"></i> Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th style="width: 170px;">PO No.</th>
                        <th style="width: 120px;">PO Date</th>
                        <th>Vendor</th>
                        <th style="width: 140px;">Delivery Date</th>
                        <th style="width: 130px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>



<script>
    $(function () {
        var oTable = $('#customers').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthChange: false,
            bLengthChange: false,
            pageLength: 25,
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
                {data: 'DT_RowIndex', name: 'id', className: 'text-center'},
                {data: 'sku', name: 'sku', className: 'font-weight-bold'},
                {data: 'date', name: 'date'},
                {data: 'vendor_id', name: 'vendor_id'},
                {data: 'delivery_date', name: 'delivery_date'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ]
        });

        // Apply Filter button
        $('#btnApplyFilter').on('click', function () {
            oTable.draw();
        });

        // Trigger filter on Enter key inside PO Number input
        $('#sku').on('keypress', function (e) {
            if (e.which === 13) {
                e.preventDefault();
                oTable.draw();
            }
        });

        // Reset Filter
        $('#btnResetFilter').on('click', function () {
            $('#sku').val('');
            $('#date').val('');
            $('#vendor_id').val('').trigger('change.select2');
            $('#delivery_date').val('');
            oTable.draw();
        });
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                // If user confirms, trigger the delete route
                window.location.href = "{{ route('admin.purchase_order.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>

@endsection

