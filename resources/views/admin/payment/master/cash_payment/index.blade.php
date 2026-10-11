@extends('admin.layouts.app')
@section('title', 'Manage Cash Payments')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-coins text-primary"></i> Cash Payment Master
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.payment.master.cash_payment.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> Add Cash Payment
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <div class="row align-items-end">
                <div class="col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Account Name</label>
                    <input type="text" class="form-control erp-input" name="name" id="name" placeholder="Type account name to filter...">
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="cash_payments_table" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 55px;">#</th>
                            <th>Account name</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                            <th class="text-center" style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        var oTable = $('#cash_payments_table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.payment.master.cash_payment.indexList') !!}',
                data: function (d) {
                    d.name = $('#name').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: 'text-center font-weight-bold text-muted' },
                { data: 'name', name: 'name' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ]
        });

        $('#name').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });
    });
</script>
@endsection