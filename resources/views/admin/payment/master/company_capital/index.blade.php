@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Company Capital</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{route('admin.dashboard')}}">Home</a></li>
                            <li class="breadcrumb-item active">Company Capital</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <!-- Summary Stats -->
                <div class="row">
                    <div class="col-lg-4 col-12">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <h3>{{ number_format($summary['total'], 2) }}</h3>
                                <p>Total Capital</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-wallet"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <h3>{{ number_format($summary['bank'], 2) }}</h3>
                                <p>Bank Balance</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-university"></i>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4 col-12">
                        <div class="small-box bg-primary">
                            <div class="inner">
                                <h3>{{ number_format($summary['cash'], 2) }}</h3>
                                <p>Cash Balance</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card card-default mb-3">
                    <div class="card-body pb-0">
                        <form id="filter_form">
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase">Payment Mode</label>
                                    <select name="mode" id="filter_mode" class="form-control select2"
                                        onchange="reloadTable()">
                                        <option value="">All Methods</option>
                                        <optgroup label="General">
                                            <option value="all_banks">All Banks</option>
                                            <option value="all_cash">All Cash</option>
                                        </optgroup>
                                        <optgroup label="Banks">
                                            @foreach($methods['banks'] as $bank)
                                                <option value="bank_{{ $bank->id }}">{{ $bank->bank_name }}
                                                    ({{ $bank->account_number }})</option>
                                            @endforeach
                                        </optgroup>
                                        <optgroup label="Cash">
                                            @foreach($methods['cash'] as $cash)
                                                <option value="cash_{{ $cash->id }}">{{ $cash->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase">From Date</label>
                                    <input type="date" name="from_date" id="filter_from_date" class="form-control"
                                        onchange="reloadTable()">
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase">To Date</label>
                                    <input type="date" name="to_date" id="filter_to_date" class="form-control"
                                        onchange="reloadTable()">
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="small font-weight-bold text-muted text-uppercase">Source</label>
                                    <select name="source" id="filter_source" class="form-control" onchange="reloadTable()">
                                        <option value="">All Sources</option>
                                        <option value="Capital">Capital Only</option>
                                        <option value="Payment">Payments Only</option>
                                    </select>
                                </div>
                                <div class="col-md-1 mb-3">
                                    <label class="d-none d-md-block">&nbsp;</label>
                                    <button type="button" class="btn btn-default btn-block" onclick="resetFilters()">
                                        <i class="fas fa-undo"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="card card-default">
                    <div class="card-header">
                        <h3 class="card-title">Capital Transactions</h3>
                        <div class="card-tools">
                            <a href="{{ route('admin.payment.master.company_capital.create') }}" class="btn btn-primary btn-sm">Add
                                Capital</a>
                        </div>
                    </div>
                    <div class="card-body table-responsive">
                        <table id="capital_table" class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Remarks</th>
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
        var capitalTable;
        $(function () {
            capitalTable = $('#capital_table').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                ordering: false,
                ajax: {
                    url: '{!! route('admin.payment.master.company_capital.indexList') !!}',
                    data: function (d) {
                        d.mode = $('#filter_mode').val();
                        d.from_date = $('#filter_from_date').val();
                        d.to_date = $('#filter_to_date').val();
                        d.source = $('#filter_source').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', name: 'id' },
                    { data: 'transaction_date', name: 'transaction_date' },
                    { data: 'amount', name: 'amount' },
                    { data: 'payment_method', name: 'payment_method' },
                    { data: 'remarks', name: 'remarks' }
                ]
            });

            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });
        });

        function reloadTable() {
            if (capitalTable) {
                capitalTable.ajax.reload();
            }
        }

        function resetFilters() {
            $('#filter_mode').val('').trigger('change.select2');
            $('#filter_from_date').val('');
            $('#filter_to_date').val('');
            $('#filter_source').val('');
            reloadTable();
        }
    </script>
@endsection