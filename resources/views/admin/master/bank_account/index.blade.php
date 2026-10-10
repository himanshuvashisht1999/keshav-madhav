@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2 d-flex justify-content-between align-items-center">
        <div class="erp-header-title">
            <i class="fas fa-university text-primary"></i> Manage bank accounts
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.master.bank_account.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add bank account
            </a>
        </div>
    </div>

    <!-- Main Data Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="bank_accounts_table" class="erp-table table table-bordered table-hover">
                <thead>
                    <tr role="row" class="filter">
                        <td></td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="bank_name" id="bank_name" placeholder="Search bank...">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="account_name" id="account_name" placeholder="Search account...">
                        </td>
                        <td>
                            <input type="text" class="form-control erp-filter-input" name="account_number" id="account_number" placeholder="Search number...">
                        </td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                    <tr>
                        <th style="width: 50px;">ID</th>
                        <th>Bank name</th>
                        <th>Account name</th>
                        <th>Account number</th>
                        <th>IFSC code</th>
                        <th style="width: 100px;" class="text-center">Status</th>
                        <th style="width: 100px;" class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

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
                url: '{!! route('admin.master.bank_account.indexList') !!}',
                data: function (d) {
                    d.bank_name = $('#bank_name').val();
                    d.account_name = $('#account_name').val();
                    d.account_number = $('#account_number').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'id' },
                { data: 'bank_name', name: 'bank_name' },
                { data: 'account_name', name: 'account_name' },
                { data: 'account_number', name: 'account_number' },
                { data: 'ifsc_code', name: 'ifsc_code' },
                { data: 'status', name: 'status' },
                { data: 'action', name: 'action', searchable: false }
            ],
            dom: 'lBfrtip',
            buttons: []
        });

        $('#bank_name, #account_name, #account_number').on('keyup change', function () {
            oTable.draw();
        });
    });

    function deleteData(id){
        Swal.fire({
            title: "Are you sure?",
            text: "You won't be able to revert this!",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "var(--erp-green-primary, #05421c)",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, delete it!"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "{{ route('admin.master.bank_account.delete', ['id' => '']) }}" + id;
            }
        });
    }
</script>
@endsection