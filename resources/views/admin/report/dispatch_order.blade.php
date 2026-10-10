@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">Dispatch order report</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item"><i class="fas fa-file-alt mr-1 text-muted"></i> Report no: RJ-5</span>
                <span class="meta-divider">•</span>
                <span class="meta-item"><i class="far fa-clock mr-1 text-muted"></i> Date: {{ now()->format('d M Y h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ================= FILTER ================= --}}
    <div class="card erp-card mb-3">
        <div class="card-body p-2">
            <form method="GET" action="{{ route('admin.report.dispatch-order') }}">
                <div class="row g-2 align-items-end">

                    <div class="col-md-3">
                        <label class="erp-label mb-1">Order no</label>
                        <input type="text"
                            name="order_no"
                            class="form-control form-control-sm"
                            placeholder="Enter order no"
                            value="{{ request('order_no') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="erp-label mb-1">Customer</label>
                        <select name="customer_id" class="form-control form-control-sm select2">
                            <option value="">All customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn-erp btn-erp-primary mr-2">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.dispatch-order') }}" class="btn-erp btn-erp-outline">
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
                            <th>Dispatch no</th>
                            <th>Order no</th>
                            <th>Customer</th>
                            <th class="text-right">Total cartons</th>
                            <th class="text-right">Total boxes</th>
                            <th class="text-center" style="width: 90px;">Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        @php $sr = 1; @endphp

                        @forelse($data as $row)
                            <tr>
                                <td class="order-cell text-center">{{ $sr }}</td>
                                <td class="order-cell">
                                    <span class="font-weight-bold">{{ $row['order_dispatch_data']['order_dispatch_no'] }}</span>
                                </td>
                                <td class="order-cell">
                                    {{ $row['order_dispatch_data']['order_no'] }}
                                </td>
                                <td class="order-cell">
                                    {{ $row['order_dispatch_data']['customer'] }}
                                </td>
                                <td class="order-cell text-right">
                                    <span class="erp-badge-status erp-badge-status-neutral">{{ $row['order_dispatch_data']['total_cartons'] }}</span>
                                </td>
                                <td class="order-cell text-right">
                                    <span class="font-weight-bold">{{ $row['order_dispatch_data']['total_boxes_dispatch'] }}</span>
                                </td>
                                <td class="order-cell text-center">
                                    <button
                                        type="button"
                                        class="btn-erp btn-erp-outline btn-erp-sm view-cartons"
                                        data-order='@json($row)'>
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                </td>
                            </tr>
                            @php $sr++; @endphp
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-2x mb-2 d-block text-secondary"></i>
                                    No records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- ================= MODAL ================= --}}
<div class="modal fade" id="cartonModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">

            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <h5 class="modal-title font-weight-bold mb-0" style="color: var(--brand-dark-green, #05421c); font-size: 15px;">
                    <i class="fas fa-boxes mr-1"></i> Carton details
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-3">

                <div class="d-flex flex-wrap mb-3 pb-2 border-bottom" style="gap: 12px;">
                    <div class="erp-stat-pill">
                        Dispatch no: <strong id="modalDispatchNo">-</strong>
                    </div>
                    <div class="erp-stat-pill">
                        Order no: <strong id="modalOrderNo">-</strong>
                    </div>
                    <div class="erp-stat-pill">
                        Customer: <strong id="modalCustomer">-</strong>
                    </div>
                </div>

                <div id="cartonModalBody"></div>

            </div>

            <div class="modal-footer py-2 px-3 bg-light border-top">
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    $(document).on('click', '.view-cartons', function () {

        const data = JSON.parse(this.dataset.order);

        const order = data.order_dispatch_data;
        const cartons = data.cartonsDetails;

        document.getElementById('modalDispatchNo').innerText = order.order_dispatch_no || '-';
        document.getElementById('modalOrderNo').innerText = order.order_no || '-';
        document.getElementById('modalCustomer').innerText = order.customer || '-';

        let html = '';

        Object.values(cartons).forEach(carton => {

            html += `
            <div class="card erp-card mb-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                        <i class="fas fa-box mr-1"></i> Carton ID: ${carton.id}
                    </span>
                    <span class="badge badge-success px-2 py-1">
                        Total boxes: ${carton.total_boxes}
                    </span>
                </div>

                <div class="card-body p-0">
                    <table class="table erp-table mb-0">
                        <thead>
                            <tr>
                                <th>Barcode</th>
                                <th>Design no</th>
                                <th>Set size</th>
                                <th>Size group</th>
                                <th>Color</th>
                                <th class="text-right">No of pcs</th>
                                <th class="text-right">Set qty</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            const boxes = carton.car_data || {};

            if (Object.keys(boxes).length === 0) {
                html += `
                    <tr>
                        <td colspan="7" class="text-center py-2 text-muted">
                            No box data
                        </td>
                    </tr>
                `;
            } else {
                Object.values(boxes).forEach(box => {
                    html += `
                    <tr>
                        <td><code>${box.bar_code || '-'}</code></td>
                        <td>${box.design_number || '-'}</td>
                        <td>${box.set_size || '-'}</td>
                        <td>${box.size_group || '-'}</td>
                        <td>${box.color || '-'}</td>
                        <td class="text-right">${box.no_of_pcs || 0}</td>
                        <td class="text-right"><strong>${box.set_quantity || 0}</strong></td>
                    </tr>
                    `;
                });
            }

            html += `
                        </tbody>
                    </table>
                </div>
            </div>
            `;
        });

        document.getElementById('cartonModalBody').innerHTML = html;

        if (window.jQuery) {
            $('#cartonModal').modal('show');
        } else if (window.bootstrap && bootstrap.Modal) {
            new bootstrap.Modal(document.getElementById('cartonModal')).show();
        }
    });

});
</script>

@endsection
