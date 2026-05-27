@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Title & Breadcrumbs -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Lot Ledger: {{ $lot->lot_no }}</h1>
                        <p class="text-muted mb-0">Order SKU: {{ $lot->orderMain->sku ?? '-' }} | Customer: {{ $lot->orderMain->customer->name ?? '-' }}</p>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-right text-right text-end">
                            <a href="{{ route('admin.ledger.lot.index') }}" class="btn btn-secondary btn-sm"><i class="mdi mdi-arrow-left"></i> Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-header bg-light">
                        <h3 class="card-title font-weight-bold">Transaction History</h3>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Particulars</th>
                                        <th class="text-end">Quantity (Pcs)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $tx)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y, h:i A') }}</td>
                                            <td>
                                                @if(isset($tx->status))
                                                    @if($tx->status == 'completed')
                                                        <span class="badge badge-success">Completed</span>
                                                    @elseif($tx->status == 'progress')
                                                        <span class="badge badge-warning">Progress</span>
                                                    @else
                                                        <span class="badge badge-secondary">{{ ucfirst($tx->status) }}</span>
                                                    @endif
                                                @else
                                                    @if($tx->type == 'Inward')
                                                        <span class="badge badge-info">Assigned</span>
                                                    @elseif($tx->type == 'Outward')
                                                        <span class="badge badge-success">Completed</span>
                                                    @endif
                                                @endif
                                            </td>
                                            <td>
                                                {{ $tx->particulars }}
                                            </td>
                                            <td class="text-end fw-bold">
                                                @if(isset($tx->process_qty) && $tx->process_qty > 0)
                                                    {{ number_format($tx->process_qty, 0) }}
                                                @elseif($tx->inward > 0)
                                                    {{ number_format($tx->inward, 0) }}
                                                @elseif($tx->outward > 0)
                                                    {{ number_format($tx->outward, 0) }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center">No transactions found.</td>
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
@endsection
