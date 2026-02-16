@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Corporate Order Payment History</h1>
                    </div>
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('admin.payment.corporate-order.create') }}">Add
                                    Payment</a></li>
                            <li class="breadcrumb-item active">Corporate History</li>
                        </ol>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-white border-bottom-0 pt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <h3 class="card-title text-muted smaller uppercase font-weight-bold">Recent Corporate
                                        Payments</h3>
                                    <a href="{{ route('admin.payment.corporate-order.create') }}"
                                        class="btn btn-sm btn-success shadow-sm">
                                        <i class="fas fa-plus mr-1"></i> Add New Payment
                                    </a>
                                </div>
                            </div>

                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table id="corporatePaymentTable" class="table table-hover mb-0" style="width:100%">
                                        <thead class="bg-light">
                                            <tr>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-primary small font-weight-bold uppercase">
                                                    Date</th>
                                                <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">
                                                    Customer</th>
                                                <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">
                                                    Dispatch SKU</th>
                                                <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">
                                                    Amount</th>
                                                <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">
                                                    Mode</th>
                                                <th class="border-top-0 py-3 text-primary small font-weight-bold uppercase">
                                                    Ref #</th>
                                                <th
                                                    class="border-top-0 py-3 px-4 text-primary small font-weight-bold uppercase text-center">
                                                    Actions</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse ($payments as $payment)
                                                <tr>
                                                    <td class="px-4 align-middle">
                                                        <span
                                                            class="text-dark font-weight-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($payment->party)
                                                            <div class="font-weight-500">{{ $payment->party->name }}</div>
                                                            <small class="text-muted">{{ $payment->party->phone }}</small>
                                                        @else
                                                            <span class="text-danger">Unknown Customer</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        @if($payment->paymentable)
                                                            <span class="badge badge-soft-info p-2"
                                                                style="background: #e0f2f1; color: #00695c;">
                                                                {{ $payment->paymentable->sku }}
                                                            </span>
                                                        @else
                                                            <span class="text-muted">-</span>
                                                        @endif
                                                    </td>
                                                    <td class="align-middle">
                                                        <span
                                                            class="text-dark font-weight-bold">₹{{ number_format($payment->amount, 2) }}</span>
                                                    </td>
                                                    <td class="align-middle">
                                                        <span
                                                            class="badge badge-pill badge-light border py-1 px-3">{{ ucfirst($payment->payment_mode) }}</span>
                                                    </td>
                                                    <td class="align-middle text-muted small">
                                                        {{ $payment->reference_id ?? '-' }}</td>
                                                    <td class="px-4 align-middle text-center">
                                                        <div class="btn-group">
                                                            <a href="{{ route('admin.payment.history.show', $payment->id) }}"
                                                                class="btn btn-sm btn-outline-info mr-1" title="View"><i
                                                                    class="fas fa-eye"></i></a>
                                                            <a href="{{ route('admin.payment.history.edit', $payment->id) }}"
                                                                class="btn btn-sm btn-outline-warning" title="Edit"><i
                                                                    class="fas fa-edit"></i></a>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @empty
                                                <tr>
                                                    <td colspan="7" class="text-center py-5">
                                                        <div class="text-muted">
                                                            <i class="fas fa-receipt fa-3x mb-3 opacity-25"></i>
                                                            <p>No corporate payments found.</p>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>
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
            $("#corporatePaymentTable").DataTable({
                "responsive": true,
                "lengthChange": false,
                "autoWidth": false,
                "ordering": false,
                "paging": true,
                "info": true,
                "searching": true,
                "language": {
                    "emptyTable": "No data available in table"
                }
            });
        });
    </script>
@endsection