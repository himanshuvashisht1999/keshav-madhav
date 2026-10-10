@extends('admin.layouts.app')
@section('title', 'Manage Capital')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Manage Capital</h1>
                <p class="erp-page-subtitle">Master register for partner and business equity accounts</p>
            </div>
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.payment.master.capital.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> Add Capital
                </a>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
                <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
                <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
            </div>
        @endif

        <!-- Table Card -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table id="capital_table" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 60px;">ID</th>
                            <th>Partner / Capital name</th>
                            <th class="text-right" style="width: 160px;">Amount</th>
                            <th class="text-center" style="width: 100px;">Type</th>
                            <th class="text-center" style="width: 110px;">Status</th>
                            <th class="text-center" style="width: 110px;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                    <tfoot>
                        <tr>
                            <th colspan="2" class="text-right font-weight-bold">Total:</th>
                            <th id="total_amount" class="text-right font-weight-bold text-success"></th>
                            <th colspan="3"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        var oTable = $('#capital_table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: true,
            ordering: true,
            paging: false,
            dom: 'Bfrtip',
            buttons: [],
            ajax: {
                url: '{!! route('admin.payment.master.capital.indexList') !!}'
            },
            columns: [
                { data: 'id', name: 'id', className: 'text-center font-weight-bold text-muted' },
                { data: 'name', name: 'name' },
                { data: 'balance', name: 'balance', className: 'text-right font-weight-bold' },
                { data: 'type', name: 'type', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            footerCallback: function (row, data, start, end, display) {
                var api = this.api();
                
                var intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[\₹,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                var total = api
                    .column(2)
                    .data()
                    .reduce(function (a, b) {
                        return intVal(a) + intVal(b);
                    }, 0);

                $(api.column(2).footer()).html(
                    '₹ ' + total.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 })
                );
            }
        });
    });
</script>
@endpush
