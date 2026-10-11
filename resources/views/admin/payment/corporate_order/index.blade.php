@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar">
        <div class="erp-header-title">
            <i class="fas fa-building text-primary"></i> Corporate Order Payment History
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.payment.corporate-order.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Add New Payment
            </a>
        </div>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table id="corporatePaymentTable" class="erp-table table table-bordered table-hover mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th style="width: 110px;">Date</th>
                        <th>Customer</th>
                        <th style="width: 140px;">Dispatch SKU</th>
                        <th style="width: 120px;">Amount</th>
                        <th style="width: 100px;">Mode</th>
                        <th style="width: 120px;">Ref #</th>
                        <th style="width: 80px;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($payments as $payment)
                        <tr>
                            <td class="text-nowrap">
                                <span class="font-weight-500">{{ \Carbon\Carbon::parse($payment->payment_date)->format('d-m-Y') }}</span>
                            </td>
                            <td>
                                @if($payment->party)
                                    <div class="font-weight-bold">{{ $payment->party->name }}</div>
                                    <small class="text-muted">{{ $payment->party->phone }}</small>
                                @else
                                    <span class="text-danger">Unknown Customer</span>
                                @endif
                            </td>
                            <td>
                                @if($payment->paymentable)
                                    <span class="badge erp-badge-primary">
                                        {{ $payment->paymentable->sku }}
                                    </span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <span class="font-weight-bold text-dark">₹{{ number_format($payment->amount, 2) }}</span>
                            </td>
                            <td>
                                <span class="badge badge-light border">{{ ucfirst($payment->payment_mode) }}</span>
                            </td>
                            <td class="text-muted small">
                                {{ $payment->reference_id ?? '-' }}
                            </td>
                            <td class="text-center">
                                <div class="btn-group">
                                    <a href="{{ route('admin.payment.history.show', $payment->id) }}"
                                        class="btn btn-xs btn-outline-info mr-1" title="View"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.payment.history.edit', $payment->id) }}"
                                        class="btn btn-xs btn-outline-warning" title="Edit"><i class="fas fa-edit"></i></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="fas fa-receipt fa-2x mb-2 d-block opacity-25"></i>
                                No corporate payments found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
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