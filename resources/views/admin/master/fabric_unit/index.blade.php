@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-ruler-combined text-primary"></i> Manage Fabric Unit
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.fabric_unit.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add Fabric Unit
            </a>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="fabric_units" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="name" id="name" placeholder="Search unit..." autocomplete="off">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="symbol" id="symbol" placeholder="Search symbol..." autocomplete="off">
                        </td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Unit Name</th>
                        <th style="width: 140px;">Symbol</th>
                        <th style="width: 90px;" class="text-center">Action</th>
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
        var oTable = $('#fabric_units').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.master.fabric_unit.indexList') !!}',
                data: function (d) {
                    d.name = $('#name').val();
                    d.symbol = $('#symbol').val();
                },
                orderable: false
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'name', name: 'name' },
                { data: 'symbol', name: 'symbol' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ],
            dom: 'lfrtip'
        });

        $('#name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
        $('#symbol').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });
</script>
@endsection
