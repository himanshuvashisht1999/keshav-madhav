@extends('admin.layouts.app')
@section('title', 'Manage Bank Accounts')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-university text-primary"></i> Bank Account Master
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Opening</span>
                    <span class="hud-stat-value text-info font-weight-bold">₹{{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Current balance</span>
                    <span class="hud-stat-value text-success font-weight-bold">₹{{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                </div>
                <a href="{{ route('admin.payment.master.bank_account.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> Add Bank Account
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
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-university mr-1"></i> Bank Name</label>
                    <input type="text" class="form-control erp-input" name="bank_name" id="bank_name" placeholder="Filter by bank...">
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user mr-1"></i> Account Name</label>
                    <input type="text" class="form-control erp-input" name="account_name" id="account_name" placeholder="Filter by account...">
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-credit-card mr-1"></i> Account Number</label>
                    <input type="text" class="form-control erp-input" name="account_number" id="account_number" placeholder="Filter by number...">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="button" id="btnResetFilters" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="bank_accounts_table" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th>Bank name</th>
                            <th>Account name</th>
                            <th>Account number</th>
                            <th>IFSC code</th>
                            <th class="text-right">Opening balance</th>
                            <th class="text-right">Current balance</th>
                            <th class="text-center" style="width: 100px;">Status</th>
                            <th class="text-center" style="width: 90px;">Action</th>
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
        var oTable = $('#bank_accounts_table').DataTable({
            processing: true,
            serverSide: true,
            stateSave: true,
            searching: false,
            ordering: false,
            lengthMenu: [[25, 100, -1], [25, 100, "All"]],
            pageLength: 25,
            ajax: {
                url: '{!! route('admin.payment.master.bank_account.indexList') !!}',
                data: function (d) {
                    d.bank_name = $('#bank_name').val();
                    d.account_name = $('#account_name').val();
                    d.account_number = $('#account_number').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id', className: 'text-center font-weight-bold text-muted' },
                { data: 'bank_name', name: 'bank_name' },
                { data: 'account_name', name: 'account_name' },
                { data: 'account_number', name: 'account_number' },
                { data: 'ifsc_code', name: 'ifsc_code' },
                { data: 'opening_balance', name: 'opening_balance', className: 'text-right' },
                { data: 'balance', name: 'balance', className: 'text-right font-weight-bold' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', searchable: false, className: 'text-center' }
            ]
        });

        $('#bank_name, #account_name, #account_number').on('keyup', function (e) {
            oTable.draw();
            e.preventDefault();
        });

        $('#btnResetFilters').on('click', function () {
            $('#bank_name').val('');
            $('#account_name').val('');
            $('#account_number').val('');
            oTable.draw();
        });
    });
</script>
@endsection