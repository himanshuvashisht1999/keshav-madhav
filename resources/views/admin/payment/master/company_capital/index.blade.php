@extends('admin.layouts.app')
@section('title', 'Company Capital')

@section('content')
    <div class="content-wrapper erp-page p-2">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-wallet text-primary"></i> Company Capital
            </div>
            <div class="erp-header-actions">
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Total Capital</span>
                    <span class="hud-stat-value text-success font-weight-bold">₹{{ number_format($summary['total'], 2) }}</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Bank Balance</span>
                    <span class="hud-stat-value text-info font-weight-bold">₹{{ number_format($summary['bank'], 2) }}</span>
                </div>
                <div class="hud-stat-box">
                    <span class="hud-stat-label">Cash Balance</span>
                    <span class="hud-stat-value text-primary font-weight-bold">₹{{ number_format($summary['cash'], 2) }}</span>
                </div>
                <a href="{{ route('admin.payment.master.company_capital.create') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus"></i> Add Capital
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <form id="filter_form">
                <div class="row align-items-end">
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-credit-card mr-1"></i> Payment Mode</label>
                        <select name="mode" id="filter_mode" class="form-control select2 erp-input" onchange="reloadTable()" style="width: 100%;">
                            <option value="">All Methods</option>
                            <optgroup label="General">
                                <option value="all_banks">All Banks</option>
                                <option value="all_cash">All Cash</option>
                            </optgroup>
                            <optgroup label="Banks">
                                @foreach($methods['banks'] as $bank)
                                    <option value="bank_{{ $bank->id }}">{{ $bank->bank_name }} ({{ $bank->account_number }})</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="Cash">
                                @foreach($methods['cash'] as $cash)
                                    <option value="cash_{{ $cash->id }}">{{ $cash->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From Date</label>
                        <input type="date" name="from_date" id="filter_from_date" class="form-control erp-input" onchange="reloadTable()">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To Date</label>
                        <input type="date" name="to_date" id="filter_to_date" class="form-control erp-input" onchange="reloadTable()">
                    </div>
                    <div class="col-lg-2 col-md-3 col-sm-6 mb-1">
                        <label class="erp-filter-label"><i class="fas fa-layer-group mr-1"></i> Source</label>
                        <select name="source" id="filter_source" class="form-control erp-input" onchange="reloadTable()">
                            <option value="">All Sources</option>
                            <option value="Capital">Capital Only</option>
                            <option value="Payment">Payments Only</option>
                        </select>
                    </div>
                    <div class="col-auto mb-1 ml-auto">
                        <div class="erp-filter-actions">
                            <button type="button" class="btn-erp btn-erp-outline" onclick="resetFilters()" title="Reset Filters">
                                <i class="fas fa-undo"></i> Reset
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="capital_table" class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
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