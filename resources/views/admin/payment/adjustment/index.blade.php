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
                    <h3 class="card-title">Adjustment History ({{ $grouped->count() }} Batches)</h3>
                    <div class="card-tools">
                        <a href="{{ route('admin.payment.adjustment.create') }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-plus"></i> Record New Adjustment
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover mb-0" id="adjust_table">
                            <thead>
                                <tr>
                                    <th style="display:none;">ID</th>
                                    <th style="width: 120px;">Date</th>
                                    <th>Batch / Ref</th>
                                    <th>Party / Entity</th>
                                    <th>To Account</th>
                                    <th class="text-center">Items</th>
                                    <th class="text-right">Total Amount</th>
                                    <th class="text-center">Type</th>
                                    <th class="text-center" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($grouped as $batchId => $items)
                                @php 
                                    $first = $items->first(); 
                                    $uniqueParties = $items->map(function($i) { return $i->entity_name; })->unique();
                                    $uniqueMasters = $items->map(function($i) { return $i->master->name ?? 'N/A'; })->unique();
                                    $isGrouped = !str_starts_with($batchId, 'unique_');
                                @endphp
                                <tr>
                                    <td style="display:none;">{{ $first->id }}</td>
                                    <td data-sort="{{ strtotime($first->date) }}">
                                        {{ date('d-M-Y', strtotime($first->date)) }}
                                    </td>
                                    <td>
                                        @if($isGrouped)
                                            <code class="text-primary">{{ $batchId }}</code>
                                        @else
                                            <span class="text-muted">#{{ $first->id }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            @if($uniqueParties->count() > 1)
                                                <span class="font-weight-bold" title="{{ $uniqueParties->implode(', ') }}">
                                                    <i class="fas fa-users mr-1 text-muted"></i> Multiple Parties ({{ $uniqueParties->count() }})
                                                </span>
                                            @else
                                                <span class="font-weight-bold">{{ $uniqueParties->first() }}</span>
                                            @endif
                                            <small class="text-muted">
                                                {{ $uniqueMasters->implode(', ') }}
                                            </small>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span>{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</span>
                                            <small class="text-muted text-uppercase" style="font-size: 10px;">{{ $first->payment_mode }}</small>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge badge-info">{{ $items->count() }}</span>
                                    </td>
                                    <td class="text-right font-weight-bold">
                                        {{ number_format($items->sum('amount'), 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($first->type == 'credit')
                                            <span class="badge badge-success px-2">CR</span>
                                        @else
                                            <span class="badge badge-danger px-2">DR</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group">
                                            <a href="{{ route('admin.payment.adjustment.show', $batchId) }}" class="btn btn-outline-info btn-xs mx-1" title="View Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.payment.adjustment.edit', $batchId) }}" class="btn btn-outline-warning btn-xs mx-1" title="Edit Batch">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('admin.payment.adjustment.delete', $batchId) }}" class="btn btn-outline-danger btn-xs mx-1" title="Delete & Reverse" onclick="return confirm('WARNING: This will delete the batch and REVERSE all account balances. Continue?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
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
