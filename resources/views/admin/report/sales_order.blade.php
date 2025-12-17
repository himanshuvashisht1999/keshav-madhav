@extends('admin.layouts.app')

@section('content')
<style>
    h5{
        color: #007bff !important;
    }
</style>
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-3 text-start">
                    <h5 class="">No. RJ 1</h5>
                </div>
                <div class="col-sm-6 text-center">
                    <h1 class="text-center">Status of Sales Orders</h1>
                </div>
                <div class="col-sm-3 text-end">
                    <h5 class="">Date : 12 Dec 2025 3:00 PM</h5>
                </div>
            </div>
        </div>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="card card-default">
                

                <div class="card-body">
                    <div class="table-responsive">
                        <table id="adjustmentTable" class="table table-striped table-bordered" data-ajax-url="{{ route('admin.purchase_order.adjustmentShipment') }}">
                            <thead>
                                <tr>
                                    <th>Sr No.</th>
                                    <th>Date of Order</th>
                                    <th>Customer</th>
                                    <th>Order Number</th>
                                    <th>No. of Pcs in order</th>
                                    <th>Lot Numbers</th>
                                    <th>No. of Pcs in each Lot</th>
                                    <th>Status</th>
                                    <th>Is it delayed?</th>                                
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr_no = 1; @endphp

                                @foreach($data as $orderNo => $lots)
                                    @php
                                        $rowspan = count($lots);
                                        $order = $lots->first();
                                    @endphp

                                    @foreach($lots as $index => $lot)
                                        <tr class="{{ $index > 0 ? 'lot-row' : '' }}">

                                            {{-- Sr No (ONLY ONCE PER ORDER) --}}
                                            @if($index === 0)
                                                <td rowspan="{{ $rowspan }}" class="order-cell">
                                                    {{ $sr_no }}
                                                </td>
                                                <td rowspan="{{ $rowspan }}" class="order-cell">
                                                    {{ \Carbon\Carbon::parse($order['order_date'])->format('d M Y') }}
                                                </td>
                                                <td rowspan="{{ $rowspan }}" class="order-cell">
                                                    {{ $order['customer'] }}
                                                </td>
                                                <td rowspan="{{ $rowspan }}" class="order-cell">
                                                    {{ $order['order_no'] }}
                                                </td>
                                                <td rowspan="{{ $rowspan }}" class="order-cell">
                                                    {{ $order['total_pcs_in_order'] }}
                                                </td>
                                            @endif

                                            {{-- Lot-level data --}}
                                            <td>{{ $lot['lot_no'] }}</td>
                                            <td>{{ $lot['pieces_in_lot'] }}</td>
                                            <td>{{ $lot['stage_name'] }}</td>
                                            <td>
                                                <a href="javascript:void(0)"
                                                class="delay-info text-decoration-none {{ $lot['isDelayed'] == 'Yes' ? 'text-danger' : 'text-success' }}"
                                                data-lot="{{ $lot['lot_no'] }}"
                                                data-allowed="{{ $lot['allowed_till_datetime'] }}"
                                                data-current="{{ $lot['current_datetime'] }}"
                                                data-status="{{ $lot['isDelayed'] }}">
                                                    {{ $lot['isDelayed'] }}
                                                </a>
                                            </td>
                                            <td>Action</td>
                                        </tr>
                                    @endforeach

                                    @php $sr_no++; @endphp
                                @endforeach
                                </tbody>

                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<div class="modal fade" id="delayModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Lot Delay Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th>Lot Number</th>
                        <td id="modalLot"></td>
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
<script>
document.addEventListener('DOMContentLoaded', function () {

    function formatDateTime(dateTimeStr) {
        if (!dateTimeStr) return '-';

        const date = new Date(dateTimeStr.replace(' ', 'T'));

        return date.toLocaleString('en-IN', {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    }

    document.querySelectorAll('.delay-info').forEach(function (el) {
        el.addEventListener('click', function () {

            document.getElementById('modalLot').innerText = this.dataset.lot;
            document.getElementById('modalAllowed').innerText =
                formatDateTime(this.dataset.allowed);
            document.getElementById('modalCurrent').innerText =
                formatDateTime(this.dataset.current);

            document.getElementById('modalStatus').innerHTML =
                this.dataset.status === 'Yes'
                    ? '<span class="badge bg-danger">Delayed</span>'
                    : '<span class="badge bg-success">On Time</span>';

            new bootstrap.Modal(document.getElementById('delayModal')).show();
        });
    });

});
</script>


@endsection