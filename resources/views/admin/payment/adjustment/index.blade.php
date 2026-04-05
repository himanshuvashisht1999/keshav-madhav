@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Payment Adjust List</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Payment Adjust List</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Recent Adjustments</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payment.adjustment.create') }}" class="btn btn-primary btn-sm">Record New Adjustment</a>
                    </div>
                </div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-hover" id="adjust_table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Batch ID</th>
                                <th>To Account</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Type</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($grouped as $batchId => $items)
                            @php $first = $items->first(); @endphp
                            <tr>
                                <td>{{ date('d-M-Y', strtotime($first->date)) }}</td>
                                <td><code>{{ $batchId }}</code></td>
                                <td>{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</td>
                                <td><span class="badge badge-info">{{ $items->count() }} Items</span></td>
                                <td>{{ number_format($items->sum('amount'), 2) }}</td>
                                <td>
                                    @if($first->type == 'credit')
                                        <span class="badge badge-success">Credit (+)</span>
                                    @else
                                        <span class="badge badge-danger">Debit (-)</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.payment.adjustment.show', $batchId) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye"></i> View Details
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
    $(function () {
        $('#adjust_table').DataTable({
            "order": [[0, "desc"]]
        });
    });
</script>
@endpush
