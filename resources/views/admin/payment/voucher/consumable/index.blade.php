@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold">Consumable Voucher</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item active">Consumable Voucher</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <!-- FILTER CARD -->
                        <div class="card card-outline card-primary shadow-sm mb-3">
                            <div class="card-header py-2">
                                <h3 class="card-title font-weight-bold" style="font-size: 15px;">
                                    <i class="fas fa-filter text-primary mr-2"></i>Filter Consumable Vouchers
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body py-3">
                                <form id="filterForm" class="allow-multiple-submit">
                                    <div class="row">
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                            <label class="small font-weight-bold text-muted mb-1">Party / Master</label>
                                            <select id="filter_party" class="form-control select2">
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
                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <label class="small font-weight-bold text-muted mb-1">Voucher No</label>
                                            <input type="text" id="filter_voucher_no" class="form-control" placeholder="Search Voucher No..." value="{{ request('voucher_number') }}">
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <label class="small font-weight-bold text-muted mb-1">From Date</label>
                                            <input type="date" id="filter_from_date" class="form-control" value="{{ request('from_date') }}">
                                        </div>
                                        <div class="col-lg-2 col-md-4 col-sm-6 mb-2">
                                            <label class="small font-weight-bold text-muted mb-1">To Date</label>
                                            <input type="date" id="filter_to_date" class="form-control" value="{{ request('to_date') }}">
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                            <label class="small font-weight-bold text-muted mb-1">Amount Range (₹)</label>
                                            <div class="input-group">
                                                <input type="number" id="filter_min_amount" step="any" class="form-control" placeholder="Min ₹" value="{{ request('min_amount') }}">
                                                <div class="input-group-prepend input-group-append">
                                                    <span class="input-group-text px-1">-</span>
                                                </div>
                                                <input type="number" id="filter_max_amount" step="any" class="form-control" placeholder="Max ₹" value="{{ request('max_amount') }}">
                                            </div>
                                        </div>
                                        <div class="col-lg-3 col-md-4 col-sm-6 mb-2">
                                            <label class="small font-weight-bold text-muted mb-1">Slip / Document</label>
                                            <select id="filter_has_document" class="form-control select2">
                                                <option value="">All (With &amp; Without Slip)</option>
                                                <option value="yes" {{ request('has_document') === 'yes' ? 'selected' : '' }}>With Document</option>
                                                <option value="no" {{ request('has_document') === 'no' ? 'selected' : '' }}>Without Document</option>
                                            </select>
                                        </div>
                                        <div class="col-lg-9 col-md-8 col-sm-12 mb-2 d-flex align-items-end justify-content-end">
                                            <button type="submit" class="btn btn-primary px-3 shadow-sm mr-2">
                                                <i class="fas fa-filter mr-1"></i> Apply Filter
                                            </button>
                                            <button type="button" id="btnResetFilter" class="btn btn-outline-secondary px-3 shadow-sm">
                                                <i class="fas fa-undo mr-1"></i> Reset
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- TABLE CARD -->
                        <div class="card card-outline card-primary shadow-sm">
                            <div class="card-body">
                                <table id="consumableVoucherTable" class="table table-bordered table-striped w-100">
                                    <thead>
                                        <tr>
                                            <th>S.No</th>
                                            <th>Voucher Date</th>
                                            <th>Voucher No</th>
                                            <th>Master Name</th>
                                            <th>Total Amount</th>
                                            <th>Doc</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@section('scripts')
    <script>
        $(function () {
            var table = $('#consumableVoucherTable').DataTable({
                processing: true,
                serverSide: true,
                responsive: true,
                lengthChange: true,
                autoWidth: false,
                dom: 'lBfrtip',
                buttons: [
                    {
                        text: '<i class="fas fa-plus mr-1"></i> Add Consumable Voucher',
                        className: 'btn btn-primary',
                        action: function (e, dt, node, config) {
                            window.location.href = "{{ route('admin.payment.voucher.consumable.create') }}";
                        }
                    }
                ],
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
                    { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                    { data: 'voucher_date', name: 'voucher_date' },
                    { data: 'voucher_number', name: 'voucher_number' },
                    { data: 'consumable_good.name', name: 'consumableGood.name' },
                    { data: 'total_amount', name: 'total_amount' },
                    { data: 'document', name: 'document', orderable: false, searchable: false },
                    { data: 'action', name: 'action', orderable: false, searchable: false },
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
