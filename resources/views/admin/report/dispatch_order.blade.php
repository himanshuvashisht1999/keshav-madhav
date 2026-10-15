@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-truck text-primary mr-2"></i> Dispatch Order Report
        </div>
        <div class="erp-header-actions">
            <div class="hud-stat-box mr-1">
                <span class="hud-stat-label">Report No</span>
                <span class="hud-stat-value">RJ-5</span>
            </div>
            <div class="hud-stat-box">
                <span class="hud-stat-label">Generated</span>
                <span class="hud-stat-value">{{ now()->format('d M Y h:i A') }}</span>
            </div>
        </div>
    </div>

    {{-- ================= FILTER ================= --}}
    <div class="erp-filter-bar mb-2">
        <form method="GET" action="{{ route('admin.report.dispatch-order') }}">
            <div class="row align-items-end">
                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-hashtag mr-1"></i> Order No</label>
                    <input type="text"
                        name="order_no"
                        class="form-control erp-input"
                        placeholder="Enter order no"
                        value="{{ request('order_no') }}">
                </div>

                <div class="col-lg-3 col-md-4 col-sm-6 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-user-tie mr-1"></i> Customer</label>
                    <select name="customer_id" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL CUSTOMERS --</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}"
                                {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                {{ $customer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.dispatch-order') }}" class="btn-erp btn-erp-outline">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">#</th>
                            <th>Dispatch no</th>
                            <th>Order no</th>
                            <th>Customer</th>
                            <th>Dispatch date</th>
                            <th class="text-right">Total cartons</th>
                            <th class="text-right">Total pieces</th>
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
                                <td class="order-cell">
                                    {{ $row['order_dispatch_data']['dispatch_date'] }}
                                </td>
                                <td class="order-cell text-right">
                                    <span class="erp-badge-status erp-badge-status-neutral">{{ $row['order_dispatch_data']['total_cartons'] }}</span>
                                </td>
                                <td class="order-cell text-right">
                                    <span class="font-weight-bold">{{ $row['order_dispatch_data']['total_pieces'] }}</span>
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
                                <td colspan="8" class="text-center py-4 text-muted">
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

        const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({'&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'}[c]));

        cartons.forEach(carton => {

            html += `
            <div class="card erp-card mb-3">
                <div class="card-header bg-light py-2 px-3 d-flex justify-content-between align-items-center">
                    <span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">
                        <i class="fas fa-box mr-1"></i> ${String(carton.id).startsWith('PO-') ? '' : 'Carton '}${esc(carton.carton_no)}
                    </span>
                    <span class="badge badge-success px-2 py-1">
                        Pieces: ${carton.total_pieces}
                    </span>
                </div>

                <div class="card-body p-0">
                    <table class="table erp-table mb-0">
                        <thead>
                            <tr>
                                <th>Design no</th>
                                <th>Color</th>
                                <th>Size set</th>
                                <th>Sizes</th>
                                <th class="text-right">Pieces</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            const rows = carton.rows || [];

            if (rows.length === 0) {
                html += `
                    <tr>
                        <td colspan="5" class="text-center py-2 text-muted">
                            No items in this carton
                        </td>
                    </tr>
                `;
            } else {
                rows.forEach(r => {
                    html += `
                    <tr>
                        <td>${esc(r.design_number) || '-'}</td>
                        <td>${esc(r.color) || '-'}</td>
                        <td>${esc(r.size_set) || '-'}</td>
                        <td>${esc(r.sizes) || '-'}</td>
                        <td class="text-right"><strong>${r.pieces || 0}</strong></td>
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

        if (cartons.length === 0) {
            html = '<div class="text-center py-3 text-muted">No cartons in this dispatch</div>';
        }

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
