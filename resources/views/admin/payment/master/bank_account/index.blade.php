@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-4">
                        <h1>Manage Bank Accounts</h1>
                    </div>
                    <div class="col-sm-8">
                        <div class="d-flex justify-content-end">
                            <div class="info-box bg-light border-0 shadow-none m-0 mr-2" style="min-height: 50px; padding: 5px;">
                                <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-university"></i></span>
                                <div class="info-box-content" style="padding: 0 10px;">
                                    <span class="info-box-text text-muted small">Opening ({{ \App\Models\MasterOpeningBalance::getCurrentFinancialYear() }})</span>
                                    <span class="info-box-number" style="font-size: 0.9rem;">₹ {{ number_format(abs($total_opening_balance), 2) }} {{ $total_opening_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                                </div>
                            </div>
                            <div class="info-box bg-light border-0 shadow-none m-0" style="min-height: 50px; padding: 5px;">
                                <span class="info-box-icon bg-success" style="width: 40px; font-size: 1rem;"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content" style="padding: 0 10px;">
                                    <span class="info-box-text text-muted small">Current Balance</span>
                                    <span class="info-box-number" style="font-size: 0.9rem;">₹ {{ number_format(abs($total_current_balance), 2) }} {{ $total_current_balance >= 0 ? 'Cr' : 'Dr' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-body table-responsive">
                        <table id="bank_accounts_table" class="table table-bordered table-hover">
                            <thead>
                                <tr role="row" class="filter">
                                    <td></td>
                                    <td><input type="text" class="form-control" name="bank_name" id="bank_name"
                                            placeholder="Bank Name"></td>
                                    <td><input type="text" class="form-control" name="account_name" id="account_name"
                                            placeholder="Account Name"></td>
                                    <td><input type="text" class="form-control" name="account_number" id="account_number"
                                            placeholder="Account Number"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th>ID</th>
                                    <th>Bank Name</th>
                                    <th>Account Name</th>
                                    <th>Account Number</th>
                                    <th>IFSC Code</th>
                                    <th>Opening Balance</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <script>
        $(function () {
            var oTable = $('#bank_accounts_table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.payment.master.bank_account.indexList') !!}',
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
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'balance', name: 'balance' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ],
                dom: 'lBfrtip',
                buttons: [
                    {
                        text: 'Add Bank Account',
                        className: 'btn-datatable',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.payment.master.bank_account.create') }}";
                        }
                    }
                ]
            });

            $('#bank_name, #account_name, #account_number').on('keyup', function (e) {
                oTable.draw();
                e.preventDefault();
            });
        });
    </script>
@endsection