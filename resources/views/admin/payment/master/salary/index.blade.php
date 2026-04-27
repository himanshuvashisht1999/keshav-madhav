@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-4">
                        <h1>Manage Salary Accounts</h1>
                    </div>
                    <div class="col-sm-8">
                        <div class="d-flex justify-content-end">
                            <div class="info-box bg-light border-0 shadow-none m-0 mr-2" style="min-height: 50px; padding: 5px;">
                                <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-money-check-alt"></i></span>
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

        <section class="content">
            <div class="container-fluid">
                <div class="card card-default">
                    <div class="card-body table-responsive">
                        <table id="salary_table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
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

@endsection

@push('scripts')
    <script>
        $(function () {
            var oTable = $('#salary_table').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
                ordering: true,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                dom: 'lBfrtip',
                buttons: [
                    {
                        text: 'Add Salary Account',
                        className: 'btn btn-primary',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.payment.master.salary.create') }}";
                        }
                    }
                ],
                ajax: {
                    url: '{!! route('admin.payment.master.salary.indexList') !!}',
                },
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'balance', name: 'balance' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', orderable: false, searchable: false }
                ]
            });
        });
    </script>
@endpush
