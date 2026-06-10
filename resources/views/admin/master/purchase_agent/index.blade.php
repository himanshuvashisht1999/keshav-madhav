@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-4">
                        <h1>Manage Purchase Agents</h1>
                    </div>
                    <div class="col-sm-8">
                        <div class="d-flex justify-content-end">
                            <div class="info-box bg-light border-0 shadow-none m-0 mr-2" style="min-height: 50px; padding: 5px;">
                                <span class="info-box-icon bg-info" style="width: 40px; font-size: 1rem;"><i class="fas fa-user-tag"></i></span>
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
                        <h3 class="card-title">Purchase Agent List</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.master.purchase-agent.create') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-plus"></i> Add Purchase Agent
                            </a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="purchase-agents" class="table table-bordered table-striped w-100">
                            <thead>
                                <tr>
                                    <th width="60">ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
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
            var oTable = $('#purchase-agents').DataTable({
                processing: true,
                serverSide: true,
                stateSave: true,
                searching: true,
                ordering: false,
                lengthMenu: [[25, 100, -1], [25, 100, "All"]],
                "pageLength": 25,
                ajax: {
                    url: '{!! route('admin.master.purchase-agent.indexList') !!}',
                },
                drawCallback: function(settings) {
                    var json = settings.json;
                    if (json) {
                        let opBal = json.total_opening_balance || 0;
                        let currBal = json.total_current_balance || 0;
                        
                        let opType = opBal >= 0 ? 'Cr' : 'Dr';
                        let currType = currBal >= 0 ? 'Cr' : 'Dr';
                        
                        $('.info-box-text:contains("Opening")').next('.info-box-number').text('₹ ' + Math.abs(opBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + opType);
                        $('.info-box-text:contains("Current Balance")').next('.info-box-number').text('₹ ' + Math.abs(currBal).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}) + ' ' + currType);
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'name', name: 'name' },
                    { data: 'email', name: 'email' },
                    { data: 'phone', name: 'phone' },
                    { data: 'opening_balance', name: 'opening_balance' },
                    { data: 'balance', name: 'balance' },
                    { data: 'status', name: 'status' },
                    { data: 'action', name: 'action', searchable: false }
                ]
            });
        });
    </script>
@endsection
