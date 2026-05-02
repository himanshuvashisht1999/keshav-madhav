@extends('owner.layouts.app')

@section('title', 'Adjustment Details')

@section('styles')
<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #1e293b 0%, #334155 100%);
    }

    body {
        background: #f8fafc;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 30px 20px 60px;
        color: white;
    }

    .summary-card-container {
        margin-top: -40px;
        padding: 0 20px;
    }

    .summary-card {
        background: white;
        border-radius: 28px;
        padding: 24px;
        box-shadow: 0 20px 40px rgba(0,0,0,0.08);
    }

    .batch-label {
        font-size: 10px;
        font-weight: 800;
        background: #f1f5f9;
        color: #64748b;
        padding: 4px 12px;
        border-radius: 8px;
        text-transform: uppercase;
        margin-bottom: 12px;
        display: inline-block;
    }

    .total-amount {
        font-size: 32px;
        font-weight: 900;
        color: #0f172a;
        margin-bottom: 5px;
    }

    .meta-row {
        display: flex;
        justify-content: space-between;
        margin-top: 20px;
        padding-top: 20px;
        border-top: 1px solid #f1f5f9;
    }

    .meta-item label {
        font-size: 10px;
        color: #94a3b8;
        font-weight: 800;
        text-transform: uppercase;
        display: block;
    }

    .meta-item span {
        font-size: 14px;
        font-weight: 700;
        color: #1e293b;
    }

    .entries-container {
        padding: 30px 20px;
    }

    .entry-card {
        background: white;
        border-radius: 20px;
        padding: 16px;
        margin-bottom: 12px;
        border: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .entry-main {
        display: flex;
        flex-direction: column;
    }

    .entry-title {
        font-size: 14px;
        font-weight: 800;
        color: #1e293b;
    }

    .entry-subtitle {
        font-size: 11px;
        color: #64748b;
        font-weight: 600;
        margin-top: 2px;
    }

    .entry-amount {
        font-size: 16px;
        font-weight: 900;
        color: #0f172a;
    }

    .section-title {
        font-size: 13px;
        font-weight: 800;
        color: #94a3b8;
        text-transform: uppercase;
        margin-bottom: 15px;
        padding-left: 5px;
    }
</style>
@endsection

@section('content')
<div class="mobile-only">
    @php $first = $adjustments->first(); @endphp
    
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('owner.payment-adjustment.index') }}" class="text-white opacity-75"><i class="fas fa-arrow-left"></i></a>
            <span class="font-weight-bold" style="font-size: 14px; opacity: 0.8;">Distribution Detail</span>
            <div style="width: 20px;"></div>
        </div>
    </div>

    <div class="summary-card-container">
        <div class="summary-card">
            <span class="batch-label">Batch #{{ str_replace('unique_', 'ID-', $batchId) }}</span>
            <div class="total-amount">₹{{ number_format($adjustments->sum('amount'), 2) }}</div>
            <div class="badge badge-{{ $first->type == 'credit' ? 'success' : 'danger' }} px-3" style="border-radius: 10px;">
                {{ ucfirst($first->type) }} Adjustment
            </div>

            <div class="meta-row">
                <div class="meta-item">
                    <label>Date</label>
                    <span>{{ date('d M Y', strtotime($first->date)) }}</span>
                </div>
                <div class="meta-item text-right">
                    <label>Account</label>
                    <span>{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="entries-container">
        <h2 class="section-title">Distributed Items ({{ $adjustments->count() }})</h2>
        
        @foreach($adjustments as $adj)
            <div class="entry-card">
                <div class="entry-main">
                    <span class="entry-title">{{ $adj->entity_name }}</span>
                    <span class="entry-subtitle">{{ $adj->master->name }} | ID: {{ $adj->ref_id }}</span>
                    @if($adj->remarks)
                        <span class="entry-subtitle mt-1 font-italic text-muted"><i class="fas fa-info-circle mr-1"></i> {{ $adj->remarks }}</span>
                    @endif
                </div>
                <div class="entry-amount">
                    ₹{{ number_format($adj->amount, 2) }}
                </div>
            </div>
        @endforeach
    </div>
</div>

<div class="desktop-only p-5">
    <div class="mb-4 d-flex align-items-center">
        <a href="{{ route('owner.payment-adjustment.index') }}" class="btn btn-light mr-3"><i class="fas fa-arrow-left"></i></a>
        <h3>Adjustment Batch Details: {{ $batchId }}</h3>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px;">
                <div class="card-body">
                    <h5 class="font-weight-bold mb-4">Summary</h5>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Total Distributed</span>
                        <span class="font-weight-bold">₹{{ number_format($adjustments->sum('amount'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Type</span>
                        <span class="badge badge-{{ $first->type == 'credit' ? 'success' : 'danger' }}">{{ ucfirst($first->type) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-3">
                        <span class="text-muted">Date</span>
                        <span class="font-weight-bold">{{ date('d-M-Y', strtotime($first->date)) }}</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Payment Account</span>
                        <span class="font-weight-bold">{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-8">
            <div class="card shadow-sm border-0" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="card-title font-weight-bold mb-0">Distributed Line Items</h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="border-0 px-4">Entity</th>
                                <th class="border-0">Master Type</th>
                                <th class="border-0">Reference ID</th>
                                <th class="border-0">Remarks</th>
                                <th class="border-0 px-4 text-right">Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($adjustments as $adj)
                            <tr>
                                <td class="px-4 font-weight-bold">{{ $adj->entity_name }}</td>
                                <td>{{ $adj->master->name }}</td>
                                <td><code>{{ $adj->ref_id }}</code></td>
                                <td class="text-muted small">{{ $adj->remarks }}</td>
                                <td class="px-4 text-right font-weight-bold">₹{{ number_format($adj->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
