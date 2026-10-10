@extends('admin.layouts.app')
@section('title', 'Adjustment Batch Details')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Header Bar -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title">Adjustment Batch Details</h1>
                <p class="erp-page-subtitle">Batch reference: <code class="font-weight-bold text-primary">{{ $batchId }}</code></p>
            </div>
            <div class="d-flex align-items-center" style="gap: 6px;">
                <a href="{{ route('admin.payment.adjustment.edit', $batchId) }}" class="btn-erp btn-erp-primary btn-sm">
                    <i class="fas fa-edit mr-1"></i> Edit Batch
                </a>
                <a href="{{ route('admin.payment.adjustment.index') }}" class="btn-erp btn-erp-secondary btn-sm">
                    <i class="fas fa-arrow-left mr-1"></i> Back to List
                </a>
            </div>
        </div>

        @php 
            $first = $adjustments->first();
            $total = $adjustments->sum('amount');
        @endphp

        <!-- Batch Summary Card -->
        <div class="erp-card p-3 mb-2">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <div class="erp-label">Transaction date</div>
                    <div class="font-weight-bold" style="color: #0f172a; font-size: 0.95rem;">
                        {{ date('d-M-Y', strtotime($first->date)) }}
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="erp-label">To account</div>
                    <div class="font-weight-bold" style="color: #0f172a; font-size: 0.95rem;">
                        {{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}
                        <small class="text-muted text-uppercase d-block" style="font-size: 0.7rem;">Mode: {{ $first->payment_mode }}</small>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="erp-label">Adjustment type</div>
                    <div>
                        @if($first->type == 'credit')
                            <span class="badge badge-success px-2 py-1 font-weight-bold">Credit (+)</span>
                        @else
                            <span class="badge badge-danger px-2 py-1 font-weight-bold">Debit (-)</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-3 text-right">
                    <div class="erp-label">Total amount</div>
                    <div class="font-weight-bold text-success" style="font-size: 1.3rem;">
                        ₹{{ number_format($total, 2) }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Detail Table Card -->
        <div class="erp-card p-0">
            <div class="p-2 border-bottom d-flex align-items-center justify-content-between">
                <span class="font-weight-bold" style="font-size: 0.85rem; color: #0f172a;">
                    Batch line items ({{ $adjustments->count() }})
                </span>
            </div>
            <div class="table-responsive">
                <table class="table erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 200px;">Master type</th>
                            <th>Item (From entity)</th>
                            <th class="text-right" style="width: 160px;">Amount</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($adjustments as $adj)
                        <tr>
                            <td><span class="badge badge-secondary px-2 py-1">{{ $adj->master->name ?? 'N/A' }}</span></td>
                            <td class="font-weight-bold">{{ $adj->entity_name }}</td>
                            <td class="text-right font-weight-bold">₹{{ number_format($adj->amount, 2) }}</td>
                            <td class="text-muted">{{ str_replace('[Dist] ', '', $adj->remarks) ?: '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #f8fafc;">
                            <th colspan="2" class="text-right font-weight-bold">Total:</th>
                            <th class="text-right font-weight-bold text-success" style="font-size: 1rem;">₹{{ number_format($total, 2) }}</th>
                            <th></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
