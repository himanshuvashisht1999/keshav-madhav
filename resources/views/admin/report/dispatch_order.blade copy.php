@extends('admin.layouts.app')

@section('content')

<style>
    .report-header{
        display:flex;
        justify-content:space-between;
        align-items:center;
        margin-bottom:15px;
    }
    .report-header h3{ font-weight:600;margin:0; }

    .report-card{
        border-radius:12px;
        box-shadow:0 4px 12px rgba(0,0,0,.08);
    }

    .table-report thead th{
        background:#343a40;
        color:#fff;
        font-weight:600;
        white-space:nowrap;
        vertical-align:middle;
    }

    .order-cell{
        background:#f8f9fa;
        font-weight:600;
        vertical-align:middle !important;
    }

    .expand-btn{
        font-size:13px;
    }
</style>

<div class="content-wrapper">

{{-- ================= HEADER ================= --}}
<section class="content-header">
    <div class="container-fluid">
        <div class="report-header">
            <div>Report No : RJ 3</div>
            <div><h3>Dispatch Order Report</h3></div>
            <div>Date : {{ now()->format('d M Y h:i A') }}</div>
        </div>
    </div>
</section>

<section class="content">
<div class="container-fluid">

{{-- ================= TABLE ================= --}}
<div class="card report-card">
<div class="card-body">
<div class="table-responsive">

<table class="table table-bordered table-report">
<thead>
<tr>
    <th>#</th>
    <th>Order No</th>
    <th>Customer</th>
    <th>Total Cartons</th>
    <th>Total Boxes</th>
    <th class="text-center">Action</th>
</tr>
</thead>

    <tbody>
        @php $sr = 1; @endphp

        @forelse($data as $orders)
            @foreach($orders['cartons'] as $index => $carton)

                <tr>

                    <td  class="order-cell">{{ $sr }}</td>

                    <td  class="order-cell">
                        {{ $orders['order_no'] }}
                    </td>

                    <td class="order-cell">
                        {{ $orders['customer_name'] }}
                    </td>

                    {{-- BOX COUNT --}}
                    <td class="text-end">
                        {{ $orders['total_cartons'] }}
                    </td>

                    {{-- DISPATCHED QTY --}}
                    <td class="text-end text-success">
                        {{ $orders['total_boxes'] }}
                    </td>
                    {{-- ACTION --}}
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-primary expand-btn"
                                data-bs-toggle="modal"
                                data-bs-target="#cartonModal{{ $carton['carton_id'] }}">
                            View
                        </button>
                    </td>
                </tr>
                

            @endforeach

            @php $sr++; @endphp

        @empty
        <tr>
            <td colspan="11" class="text-center text-muted">
                No dispatch records found
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
{{-- ================= ORDER DETAILS MODAL ================= --}}
<div class="modal fade"
     id="orderModal"
     tabindex="-1"
     aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg" style="border-radius:14px;">

            {{-- HEADER --}}
            <div class="modal-header bg-dark text-white">
                <div>
                    <h5 class="modal-title mb-1">
                        Dispatch Details – 
                    </h5>
                    <small class="text-light">
                       
                    </small>
                </div>
                <button type="button" class="btn-close btn-close-white"
                        data-bs-dismiss="modal"></button>
            </div>

            {{-- SUMMARY --}}
            <div class="modal-body">

                <div class="row mb-3 text-center">
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <div class="text-muted">Total Cartons</div>
                            <h4 class="text-primary mb-0">
                                
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <div class="text-muted">Total Boxes</div>
                            <h4 class="text-success mb-0">
                                
                            </h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="border rounded p-2">
                            <div class="text-muted">Status</div>
                            <span class="badge bg-success px-3 py-2">
                                Dispatched
                            </span>
                        </div>
                    </div>
                </div>

            </div>

            {{-- FOOTER --}}
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>
                <button type="button" class="btn btn-success">
                    <i class="fas fa-print"></i> Print
                </button>
            </div>

        </div>
    </div>
</div>
<script>
let modal = new bootstrap.Modal(
        document.getElementById('orderModal')
    );
    modal.show();
</script>
@endsection
