@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-tshirt text-primary"></i> Manage Products
        </div>
        <div class="erp-header-actions">
            <a href="javascript:void(0)" id="export-excel-btn" class="btn-erp btn-erp-outline" title="Export Excel">
                <i class="fas fa-file-excel text-success"></i> Excel
            </a>
            <a href="javascript:void(0)" id="busy-master-btn" class="btn-erp btn-erp-outline" title="Busy Master Excel">
                <i class="fas fa-file-alt text-info"></i> Busy Master
            </a>
            <a href="javascript:void(0)" id="export-pdf-btn" class="btn-erp btn-erp-outline" title="Export PDF">
                <i class="fas fa-file-pdf text-danger"></i> PDF
            </a>
            <a href="{{ route('admin.master.production-goods.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Product
            </a>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="customers" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="design_number" id="design_number" placeholder="Search Design..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="brand_name" id="brand_name" placeholder="Search Brand..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="name_of_garment" id="name_of_garment" placeholder="Search Name..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="fitting_name" id="fitting_name" placeholder="Search Fitting..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="pattern_name" id="pattern_name" placeholder="Search Pattern..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="nature_name" id="nature_name" placeholder="Search Nature..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="fabric_type_name" id="fabric_type_name" placeholder="Search Fabric Type..." autocomplete="off">
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Design Number</th>
                        <th>Brand</th>
                        <th>Product Name</th>
                        <th>Fitting</th>
                        <th>Pattern</th>
                        <th>Nature</th>
                        <th>Fabric Type</th>
                        <th style="width: 120px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                </tbody>
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
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.production-goods.indexList') !!}',
                data: function (d) {
                    d.id = $('#id').val();
                    d.name_of_garment = $('#name_of_garment').val();
                    d.design_number = $('#design_number').val();
                    d.brand_name = $('#brand_name').val();
                    d.fitting_name = $('#fitting_name').val();
                    d.pattern_name = $('#pattern_name').val();
                    d.nature_name = $('#nature_name').val();
                    d.fabric_type_name = $('#fabric_type_name').val();
                },
                orderable: false
            },
            columns: [
                {data: 'DT_RowIndex', name: 'id'},
                {data: 'design_number', name: 'design_number'},
                {data: 'brand_name', name: 'brand_name'},
                {data: 'product_name_display', name: 'product_name_display'},
                {data: 'fitting_name', name: 'fitting_name'},
                {data: 'pattern_name', name: 'pattern_name'},
                {data: 'nature_name', name: 'nature_name'},
                {data: 'fabric_type_name', name: 'fabric_type_name'},
                {data: 'action', name: 'action', searchable: false, className: 'text-center'}
            ]
        });

        $('#export-excel-btn').on('click', function(e) {
            e.preventDefault();
            var params = $.param({
                name_of_garment: $('#name_of_garment').val(),
                design_number: $('#design_number').val(),
                brand_name: $('#brand_name').val(),
                fitting_name: $('#fitting_name').val(),
                pattern_name: $('#pattern_name').val(),
                nature_name: $('#nature_name').val(),
                fabric_type_name: $('#fabric_type_name').val(),
            });
            window.location.href = "{{ route('admin.master.production-goods.export') }}?" + params;
        });

        $('#busy-master-btn').on('click', function(e) {
            e.preventDefault();
            var params = $.param({
                name_of_garment: $('#name_of_garment').val(),
                design_number: $('#design_number').val(),
                brand_name: $('#brand_name').val(),
                fitting_name: $('#fitting_name').val(),
                pattern_name: $('#pattern_name').val(),
                nature_name: $('#nature_name').val(),
                fabric_type_name: $('#fabric_type_name').val(),
            });
            window.location.href = "{{ route('admin.master.production-goods.busy-master-excel') }}?" + params;
        });

        $('#export-pdf-btn').on('click', function(e) {
            e.preventDefault();
            var params = $.param({
                name_of_garment: $('#name_of_garment').val(),
                design_number: $('#design_number').val(),
                brand_name: $('#brand_name').val(),
                fitting_name: $('#fitting_name').val(),
                pattern_name: $('#pattern_name').val(),
                nature_name: $('#nature_name').val(),
                fabric_type_name: $('#fabric_type_name').val(),
            });
            window.location.href = "{{ route('admin.master.production-goods.pdf') }}?" + params;
        });

        $('#name_of_garment').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#brand_name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#design_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#fitting_name').on('keyup', function (e) { oTable.draw(); e.preventDefault(); });
        $('#pattern_name').on('keyup', function (e) { oTable.draw(); e.preventDefault(); });
        $('#nature_name').on('keyup', function (e) { oTable.draw(); e.preventDefault(); });
        $('#fabric_type_name').on('keyup', function (e) { oTable.draw(); e.preventDefault(); });
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
                window.location.href = "{{ route('admin.master.production-goods.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection
