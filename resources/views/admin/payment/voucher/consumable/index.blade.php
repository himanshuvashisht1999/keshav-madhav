@extends('admin.layouts.app')
@section('title', 'Consumable Vouchers')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div>
            <div class="erp-header-title">
                <i class="fas fa-boxes text-primary"></i> Consumable Vouchers
            </div>
            <p class="erp-page-subtitle mb-0">Manage consumable purchases, supplies and party payment vouchers</p>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.voucher.consumable.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus mr-1"></i> Add Consumable Voucher
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show mb-2 py-2 px-3" role="alert">
            <i class="fas fa-exclamation-circle mr-1"></i> {{ session('error') }}
            <button type="button" class="close py-2" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <form id="filterForm" class="allow-multiple-submit">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user mr-1"></i> Party / Master</label>
                    <select id="filter_party" class="form-control select2 erp-input">
                        <option value="">-- All Consumable Parties --</option>
                        @if(isset($consumableGoods))
                            @foreach($consumableGoods as $party)
                                <option value="{{ $party->id }}" {{ request('consumable_good_id') == $party->id ? 'selected' : '' }}>
                                    {{ $party->name }}
                                </option>
                            @endforeach
                        @endif
                    </select>
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Voucher No</label>
                    <input type="text" id="filter_voucher_no" class="form-control erp-input" placeholder="Search Voucher..." value="{{ request('voucher_number') }}">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> From Date</label>
                    <input type="date" id="filter_from_date" class="form-control erp-input" value="{{ request('from_date') }}">
                </div>
                <div class="col-lg-2 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> To Date</label>
                    <input type="date" id="filter_to_date" class="form-control erp-input" value="{{ request('to_date') }}">
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-rupee-sign mr-1"></i> Amount Range (₹)</label>
                    <div class="input-group input-group-sm">
                        <input type="number" id="filter_min_amount" step="any" class="form-control erp-input" placeholder="Min ₹" value="{{ request('min_amount') }}">
                        <div class="input-group-prepend input-group-append">
                            <span class="input-group-text px-1" style="background: #f1f5f9; border-color: var(--erp-border, #cbd5e1);">-</span>
                        </div>
                        <input type="number" id="filter_max_amount" step="any" class="form-control erp-input" placeholder="Max ₹" value="{{ request('max_amount') }}">
                    </div>
                </div>
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-paperclip mr-1"></i> Slip / Document</label>
                    <select id="filter_has_document" class="form-control select2 erp-input">
                        <option value="">All (With &amp; Without Slip)</option>
                        <option value="yes" {{ request('has_document') === 'yes' ? 'selected' : '' }}>With Document</option>
                        <option value="no" {{ request('has_document') === 'no' ? 'selected' : '' }}>Without Document</option>
                    </select>
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary" id="btnApplyFilter" title="Apply Filter">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <button type="button" id="btnResetFilter" class="btn-erp btn-erp-outline" title="Reset Filters">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table id="consumableVoucherTable" class="table erp-table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">#</th>
                        <th style="width: 120px;">Voucher Date</th>
                        <th style="width: 130px;">Voucher No</th>
                        <th>Master Name</th>
                        <th class="text-right" style="width: 140px;">Total Amount</th>
                        <th class="text-center" style="width: 80px;">Doc</th>
                        <th class="text-center" style="width: 110px;">Action</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(function () {
        if ($.fn.select2) {
            $('.select2').select2({ theme: 'bootstrap4', width: '100%' });
        }

        var table = $('#consumableVoucherTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: true,
            lengthChange: true,
            autoWidth: false,
            pageLength: 25,
            lengthMenu: [[25, 50, 100, -1], [25, 50, 100, "All"]],
            ajax: {
                url: '{!! route('admin.payment.voucher.consumable.indexList') !!}',
                data: function (d) {
                    d.consumable_good_id = $('#filter_party').val();
                    d.voucher_number = $('#filter_voucher_no').val();
                    d.from_date = $('#filter_from_date').val();
                    d.to_date = $('#filter_to_date').val();
                    d.min_amount = $('#filter_min_amount').val();
                    d.max_amount = $('#filter_max_amount').val();
                    d.has_document = $('#filter_has_document').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center font-weight-bold text-muted' },
                { data: 'voucher_date', name: 'voucher_date' },
                { data: 'voucher_number', name: 'voucher_number' },
                { data: 'consumable_good.name', name: 'consumableGood.name' },
                { data: 'total_amount', name: 'total_amount', className: 'text-right' },
                { data: 'document', name: 'document', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' },
            ]
        });

        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            table.ajax.reload();
        });

        $('#filter_party, #filter_has_document').on('change', function () {
            table.ajax.reload();
        });

        $('#filter_from_date, #filter_to_date').on('change', function () {
            table.ajax.reload();
        });

        $('#btnResetFilter').on('click', function () {
            $('#filter_voucher_no').val('');
            $('#filter_from_date').val('');
            $('#filter_to_date').val('');
            $('#filter_min_amount').val('');
            $('#filter_max_amount').val('');
            $('#filter_party').val('').trigger('change.select2');
            $('#filter_has_document').val('').trigger('change.select2');
            table.ajax.reload();
        });
    });
</script>
@endsection
