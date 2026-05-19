@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-4">
                        <h1>Manage Sales Agents</h1>
                    </div>
                    <div class="col-sm-8">
                        <div class="d-flex justify-content-end">
                            <div class="info-box bg-light border-0 shadow-none m-0 mr-2" style="min-height: 50px; padding: 5px;">
                                <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-user-tie"></i></span>
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
                    <div class="card-header">
                        <h3 class="card-title">Sales Agent List</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.master.sales-agent.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Sales Agent
                            </a>
                            <a href="#" id="download-pdf" class="btn btn-danger btn-sm ml-2">
                                <i class="fas fa-file-pdf"></i> Download PDF
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="sales-agents" class="table table-bordered table-striped">
                            <thead>
                                <tr role="row" class="filter">
                                    <td></td>
                                    <td><input type="text" class="form-control form-control-sm" name="name" id="name"
                                            placeholder="Search Name..." autocomplete="off"></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Name</th>
                                    <th>Total Shops</th>
                                    <th>Phone</th>
                                    <th>Opening Balance</th>
                                    <th>Total Balance</th>
                                    <th width="100">Status</th>
                                    <th width="100">Action</th>
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
            var oTable = $('#sales-agents').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: false,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.master.sales-agent.indexList') !!}',
                    data: function (d) {
                        d.name = $('#name').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'shops_count', name: 'shops_count' },
                    { data: 'phone', name: 'phone' },
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'total_balance', name: 'total_balance' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ],
                dom: 'lrtip',
            });

            $('#name').on('keyup', function (e) {
                oTable.draw();
                e.preventDefault();
            });

            $('#download-pdf').on('click', function (e) {
                e.preventDefault();
                var name = $('#name').val() || '';
                var url = "{{ route('admin.master.sales-agent.downloadPdf') }}?name=" + encodeURIComponent(name);
                window.location.href = url;
            });
        });
    </script>
@endsection