@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">Order tracking system</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item"><i class="fas fa-file-alt mr-1 text-muted"></i> Report no: RJ 4</span>
                <span class="meta-divider">•</span>
                <span class="meta-item"><i class="far fa-clock mr-1 text-muted"></i> Date: {{ now()->format('d M Y h:i A') }}</span>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.report.orderTracking.export', request()->query()) }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-file-excel mr-1 text-success"></i> Export excel
            </a>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="card erp-card mb-3">
        <div class="card-body p-2">
            <form method="GET" action="{{ route('admin.report.orderTrackingSystem') }}">
                <div class="row g-2 align-items-end">

                    <div class="col-md-2">
                        <label class="erp-label mb-1">Order no</label>
                        <input type="text" name="order_no" class="form-control form-control-sm"
                            placeholder="Order no"
                            value="{{ request('order_no') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="erp-label mb-1">Lot no</label>
                        <input type="text" name="lot_no" class="form-control form-control-sm"
                            placeholder="Lot no"
                            value="{{ request('lot_no') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="erp-label mb-1">Delay status</label>
                        <select name="delay_status" class="form-control form-control-sm">
                            <option value="">All</option>
                            <option value="Yes" {{ request('delay_status')=='Yes'?'selected':'' }}>Delayed</option>
                            <option value="No" {{ request('delay_status')=='No'?'selected':'' }}>On time</option>
                        </select>
                    </div>

                    <div class="col-md-2">
                        <label class="erp-label mb-1">Date from</label>
                        <input type="date" name="date_from" class="form-control form-control-sm"
                            value="{{ request('date_from') }}">
                    </div>

                    <div class="col-md-2">
                        <label class="erp-label mb-1">Date to</label>
                        <input type="date" name="date_to" class="form-control form-control-sm"
                            value="{{ request('date_to') }}">
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn-erp btn-erp-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.orderTrackingSystem') }}" class="btn-erp btn-erp-outline">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card erp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table erp-table mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Order no</th>
                            <th>Customer</th>
                            <th class="text-right">Total pcs</th>
                            <th>Promised delivery</th>
                            <th>Lot no</th>
                            <th class="text-right">Pcs / lot</th>
                            <th class="text-center">Delay</th>
                            <th>Expected delivery</th>
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
                                        <td rowspan="{{ $rowspan }}" class="order-cell text-center">{{ $sr }}</td>
                                        <td rowspan="{{ $rowspan }}" class="order-cell">
                                            <span class="font-weight-bold">{{ $order['order_no'] }}</span>
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="order-cell">
                                            {{ $order['customer'] }}
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="order-cell text-right">
                                            <span class="font-weight-bold">{{ $order['total_pcs_in_order'] }}</span>
                                        </td>
                                        <td rowspan="{{ $rowspan }}" class="order-cell">
                                            {{ \Carbon\Carbon::parse($order['expected_delivery_date'])->format('d M Y') }}
                                        </td>
                                    @endif

                                    {{-- LOT LEVEL --}}
                                    <td class="{{ $lot['stage_name']=='Not Issued' ? 'text-warning font-weight-bold' : '' }}">
                                        {{ $lot['lot_no'] }}
                                    </td>
                                    <td class="text-right {{ $lot['stage_name']=='Not Issued' ? 'text-warning font-weight-bold' : '' }}">
                                        {{ $lot['pieces_in_lot'] }}
                                    </td>

                                    <td class="text-center">
                                        <span
                                            class="view-lot erp-badge-status {{ $lot['isDelayed']=='Yes' ? 'erp-badge-status-danger' : 'erp-badge-status-success' }}"
                                            data-lot="{{ $lot['lot_no'] }}"
                                            data-order="{{ $lot['order_no'] }}">
                                            @if($lot['isDelayed'] == 'Yes')
                                                <i class="fas fa-exclamation-triangle mr-1"></i> Delayed
                                            @else
                                                <i class="fas fa-check-circle mr-1"></i> On time
                                            @endif
                                        </span>
                                    </td>

                                    <td>
                                        <span>
                                            {{ $lot['expected_time'] }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach

                            @php $sr++; @endphp

                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
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

{{-- ================= DELAY MODAL ================= --}}
<div class="modal fade" id="delayModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <h5 class="modal-title font-weight-bold mb-0" style="color: var(--brand-dark-green, #05421c); font-size: 15px;">
                    <i class="fas fa-clock mr-1"></i> Lot delay details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-3">
                <table class="table erp-table mb-0">
                    <tr>
                        <th style="width: 40%;">Lot number</th>
                        <td id="modalLot" class="font-weight-bold"></td>
                    </tr>
                    <tr>
                        <th>Allowed till</th>
                        <td id="modalAllowed"></td>
                    </tr>
                    <tr>
                        <th>Current time</th>
                        <td id="modalCurrent"></td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td id="modalStatus"></td>
                    </tr>
                </table>
            </div>

            <div class="modal-footer py-2 px-3 bg-light border-top">
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

{{-- ================= LOT TRACKING MODAL ================= --}}
<div class="modal fade" id="lotTrackingModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <h5 class="modal-title font-weight-bold mb-0" style="color: var(--brand-dark-green, #05421c); font-size: 15px;">
                    <i class="fas fa-stream mr-1"></i> Lot stage tracking
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-3">

                <div class="d-flex flex-wrap mb-3 pb-2 border-bottom" style="gap: 12px;">
                    <div class="erp-stat-pill">
                        Order no: <strong id="modalOrderNo">-</strong>
                    </div>
                    <div class="erp-stat-pill">
                        Lot no: <strong id="modalLotNo">-</strong>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table erp-table mb-0">
                        <thead>
                            <tr>
                                <th style="width: 50px;" class="text-center">#</th>
                                <th>Stage name</th>
                                <th>Expected time</th>
                            </tr>
                        </thead>
                        <tbody id="lotTrackingBody">
                            <tr>
                                <td colspan="3" class="text-center py-3 text-muted">Loading...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

            </div>

            <div class="modal-footer py-2 px-3 bg-light border-top">
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function() {

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

    $(document).on('click', '.delay-link', function() {
        document.getElementById('modalLot').innerText = this.dataset.lot || '-';
        document.getElementById('modalAllowed').innerText = formatDateTime(this.dataset.allowed);
        document.getElementById('modalCurrent').innerText = formatDateTime(this.dataset.current);
        document.getElementById('modalStatus').innerHTML =
            this.dataset.status === 'Yes' ?
            '<span class="erp-badge-status erp-badge-status-danger">Delayed</span>' :
            '<span class="erp-badge-status erp-badge-status-success">On time</span>';

        if (window.jQuery) {
            $('#delayModal').modal('show');
        } else if (window.bootstrap && bootstrap.Modal) {
            new bootstrap.Modal(document.getElementById('delayModal')).show();
        }
    });

    $(document).on('click', '.view-lot', function () {
        let lotNo = this.dataset.lot;
        let orderNo = this.dataset.order;

        document.getElementById('modalLotNo').innerText = lotNo || '-';
        document.getElementById('modalOrderNo').innerText = orderNo || '-';

        let tbody = document.getElementById('lotTrackingBody');
        tbody.innerHTML = `
            <tr>
                <td colspan="3" class="text-center py-3 text-muted">Loading...</td>
            </tr>
        `;

        fetch(`{{ route('admin.report.lotTrackingDetails') }}?lot_no=${lotNo}&order_no=${orderNo}`)
            .then(res => res.json())
            .then(res => {
                tbody.innerHTML = '';
                let sr = 1;

                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = `
                        <tr>
                            <td colspan="3" class="text-center py-3 text-danger">
                                No stage tracking found
                            </td>
                        </tr>
                    `;
                    return;
                }

                res.data.forEach(row => {
                    let isCurrent = row.stage_name === res.current_stage;
                    tbody.innerHTML += `
                        <tr class="${isCurrent ? 'table-success font-weight-bold' : ''}">
                            <td class="text-center">${sr++}</td>
                            <td>${row.stage_name}</td>
                            <td>${row.expected_time ?? '-'}</td>
                        </tr>
                    `;
                });
            })
            .catch(err => {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="3" class="text-center py-3 text-danger">
                            Error loading tracking data
                        </td>
                    </tr>
                `;
            });

        if (window.jQuery) {
            $('#lotTrackingModal').modal('show');
        } else if (window.bootstrap && bootstrap.Modal) {
            new bootstrap.Modal(document.getElementById('lotTrackingModal')).show();
        }
    });

});
</script>

@endsection