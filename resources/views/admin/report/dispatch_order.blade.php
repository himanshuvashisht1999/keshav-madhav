@extends('admin.layouts.app')

@section('content')

<style>
    .report-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:15px;
    }
    .report-header h3{
        font-weight:600;
        margin:0;
    }
    .report-card{
        border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,.08);
    }
    .table-report thead th{
        background:#343a40;
        color:#fff;
        font-weight:600;
        white-space:nowrap;
    }
    .order-cell{
        background:#f8f9fa;
        font-weight:600;
        vertical-align:middle !important;
    }
</style>

<div class="content-wrapper">

    {{-- ================= HEADER ================= --}}
    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>Report No : RJ-5</div>
                <h3>Dispatch Order Report</h3>
                <div>Date : {{ now()->format('d M Y h:i A') }}</div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">

            <div class="card report-card">
                <div class="card-body">
                    {{-- ================= FILTER ================= --}}
                    <div class="card mb-3">
                        <div class="card-body">
                            <form method="GET" action="{{ route('admin.report.dispatch-order') }}">
                                <div class="row g-2 align-items-end">

                                    <div class="col-md-3">
                                        <label>Order No</label>
                                        <input type="text"
                                            name="order_no"
                                            class="form-control"
                                            value="{{ request('order_no') }}">
                                    </div>

                                    <div class="col-md-3">
                                        <label>Customer</label>
                                        <select name="customer_id" class="form-control">
                                            <option value="">All</option>
                                            @foreach($customers as $customer)
                                                <option value="{{ $customer->id }}"
                                                    {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                                    {{ $customer->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-2">
                                        <button class="btn btn-primary">Filter</button>
                                    </div>

                                </div>
                            </form>
                        </div>
                    </div>
                    {{-- ================= TABLE ================= --}}
                    <div class="table-responsive">
                        <table class="table table-bordered table-report">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Dispatch No</th>
                                    <th>Order No</th>
                                    <th>Customer</th>
                                    <th>Total Cartons</th>
                                    <th>Total Boxes</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                @php $sr = 1; @endphp

                                @forelse($data as $row)
                                    <tr>
                                        <td class="order-cell">{{ $sr }}</td>
                                        <td class="order-cell">
                                            {{ $row['order_dispatch_data']['order_dispatch_no'] }}
                                        </td>
                                        <td class="order-cell">
                                            {{ $row['order_dispatch_data']['order_no'] }}
                                        </td>
                                        <td class="order-cell">
                                            {{ $row['order_dispatch_data']['customer'] }}
                                        </td>
                                        <td class="order-cell">
                                            {{ $row['order_dispatch_data']['total_cartons'] }}
                                        </td>
                                        <td class="order-cell">
                                            {{ $row['order_dispatch_data']['total_boxes_dispatch'] }}
                                        </td>
                                        <td class="order-cell text-center">
                                            <button
                                                class="btn btn-sm btn-primary view-cartons"
                                                data-order='@json($row)'>
                                                View
                                            </button>
                                        </td>
                                    </tr>
                                    @php $sr++; @endphp
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
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
    </section>
</div>

{{-- ================= MODAL ================= --}}
<div class="modal fade" id="cartonModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Carton Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <div class="mb-3">
                    <strong>Dispatch No :</strong>
                    <span id="modalDispatchNo"></span><br>

                    <strong>Order No :</strong>
                    <span id="modalOrderNo"></span><br>

                    <strong>Customer :</strong>
                    <span id="modalCustomer"></span>
                </div>

                <div id="cartonModalBody"></div>

            </div>
        </div>
    </div>
</div>

{{-- ================= SCRIPT ================= --}}
<script>
document.addEventListener('DOMContentLoaded', function () {

    document.querySelectorAll('.view-cartons').forEach(btn => {
        btn.addEventListener('click', function () {

            const data = JSON.parse(this.dataset.order);

            const order = data.order_dispatch_data;
            const cartons = data.cartonsDetails;

            document.getElementById('modalDispatchNo').innerText = order.order_dispatch_no;
            document.getElementById('modalOrderNo').innerText = order.order_no;
            document.getElementById('modalCustomer').innerText = order.customer;

            let html = '';

            Object.values(cartons).forEach(carton => {

                html += `
                <div class="card mb-3">
                    <div class="card-header bg-dark text-white">
                        Carton ID : ${carton.id}
                        <span class="float-end">
                            Total Boxes : ${carton.total_boxes}
                        </span>
                    </div>

                    <div class="card-body p-0">
                        <table class="table table-bordered table-sm mb-0">
                            <thead class="table-secondary">
                                <tr>
                                    <th>Barcode</th>
                                    <th>Design No</th>
                                    <th>Set Size</th>
                                    <th>Size Group</th>
                                    <th>Color</th>
                                    <th>No of PCS</th>
                                    <th>Set Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                `;

                const boxes = carton.car_data || {};

                if (Object.keys(boxes).length === 0) {
                    html += `
                        <tr>
                            <td colspan="7" class="text-center text-muted">
                                No box data
                            </td>
                        </tr>
                    `;
                } else {
                    Object.values(boxes).forEach(box => {
                        html += `
                        <tr>
                            <td>${box.bar_code}</td>
                            <td>${box.design_number}</td>
                            <td>${box.set_size}</td>
                            <td>${box.size_group}</td>
                            <td>${box.color}</td>
                            <td>${box.no_of_pcs}</td>
                            <td><strong>${box.set_quantity}</strong></td>
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

            new bootstrap.Modal(document.getElementById('cartonModal')).show();
        });
    });

});
</script>

@endsection
