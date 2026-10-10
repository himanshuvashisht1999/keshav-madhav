@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Sales Order Report</h1>
        </div>
        <div class="erp-report-meta">
            <span>Report No : RJ 1</span>
            <span class="meta-divider">|</span>
            <span>Date : {{ now()->format('d M Y h:i A') }}</span>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.report.sales-order') }}">
                <div class="row align-items-end">
                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Order Number</label>
                        <input type="text" name="order_no" class="form-control" placeholder="Search Order No"
                               value="{{ request('order_no') }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Lot Number</label>
                        <input type="text" name="lot_no" class="form-control" placeholder="Search Lot No"
                               value="{{ request('lot_no') }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Delay Status</label>
                        <select name="delay_status" class="form-control select2">
                            <option value="">All</option>
                            <option value="Yes" {{ request('delay_status')=='Yes'?'selected':'' }}>Delayed</option>
                            <option value="No" {{ request('delay_status')=='No'?'selected':'' }}>On Time</option>
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Date From</label>
                        <input type="date" name="date_from" class="form-control"
                               value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="erp-form-label">Date To</label>
                        <input type="date" name="date_to" class="form-control"
                               value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 mb-2 d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.sales-order.export', request()->query()) }}"
                           class="btn-erp btn-erp-success" title="Export Excel">
                            <i class="fas fa-file-excel mr-1"></i> Export
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="erp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="erp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Order Date</th>
                            <th>Customer</th>
                            <th>Order Number</th>
                            <th class="text-right">Total Pcs</th>
                            <th class="text-center" style="width: 100px;">Action</th>
                            <th>Lot Number</th>
                            <th class="text-right">Pcs / Lot</th>
                            <th>Current Stage</th>
                            <th class="text-center">Delay</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $sr = 1; @endphp

                        @forelse($data as $orderNo => $lots)
                            @php
                                $rowspan = count($lots);
                                $order = $lots->first();
                            @endphp

                            @foreach($lots as $index => $lot)
                                <tr>
                                    {{-- ORDER LEVEL --}}
                                    @if($index === 0)
                                        <td rowspan="{{ $rowspan }}" class="text-center bg-white text-muted font-weight-bold">{{ $sr }}</td>
                                        <td rowspan="{{ $rowspan }}" class="bg-white">
                                            {{ \Carbon\Carbon::parse($order['order_date'])->format('d M Y') }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="bg-white font-weight-bold">
                                            {{ $order['customer'] }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="bg-white font-weight-bold">
                                            {{ $order['order_no'] }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="bg-white text-right font-weight-bold">
                                            {{ number_format($order['total_pcs_in_order']) }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="bg-white text-center">
                                            <a href="{{ route('admin.report.sales-order.detail', $order['order_id']) }}" class="btn-erp btn-erp-primary" title="View Details">
                                                <i class="fas fa-eye mr-1"></i> Details
                                            </a>
                                        </td>
                                    @endif

                                    {{-- LOT LEVEL --}}
                                    <td>
                                        <span class="badge badge-light border">{{ $lot['lot_no'] }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold">{{ number_format($lot['pieces_in_lot']) }}</td>
                                    <td>
                                        <span class="badge badge-light border">
                                            {{ $lot['stage_name'] }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-link p-0 delay-link font-weight-bold {{ $lot['isDelayed']=='Yes' ? 'text-danger' : 'text-success' }}"
                                              style="text-decoration: underline; font-size: 11px;"
                                              data-lot="{{ $lot['lot_no'] }}"
                                              data-allowed="{{ $lot['allowed_till_datetime'] }}"
                                              data-current="{{ $lot['current_datetime'] }}"
                                              data-status="{{ $lot['isDelayed'] }}">
                                            {{ $lot['isDelayed'] == 'Yes' ? 'Delayed' : 'On Time' }}
                                        </button>
                                    </td>
                                </tr>
                            @endforeach

                            @php $sr++; @endphp
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    No sales orders found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- DELAY MODAL --}}
<div class="modal fade" id="delayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title font-weight-bold">Lot Delay Details</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0">
                <table class="erp-table mb-0">
                    <tr>
                        <th style="width: 40%;">Lot Number</th>
                        <td id="modalLot" class="font-weight-bold"></td>
                    </tr>
                    <tr>
                        <th>Allowed Till</th>
                        <td id="modalAllowed"></td>
                    </tr>
                    <tr>
                        <th>Current Time</th>
                        <td id="modalCurrent"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="modalStatus"></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(document).ready(function() {
        function formatDateTime(dateTimeStr) {
            if (!dateTimeStr) return '-';
            const date = new Date(dateTimeStr.replace(' ', 'T'));
            return date.toLocaleString('en-IN', {
                day:'2-digit', month:'short', year:'numeric',
                hour:'2-digit', minute:'2-digit', hour12:true
            });
        }

        $(document).on('click', '.delay-link', function () {
            $('#modalLot').text($(this).data('lot'));
            $('#modalAllowed').text(formatDateTime($(this).data('allowed')));
            $('#modalCurrent').text(formatDateTime($(this).data('current')));

            $('#modalStatus').html(
                $(this).data('status') === 'Yes'
                    ? '<span class="erp-badge-status erp-badge-status-danger">Delayed</span>'
                    : '<span class="erp-badge-status erp-badge-status-closed">On Time</span>'
            );

            $('#delayModal').modal('show');
        });
    });
</script>
@endpush

@endsection
