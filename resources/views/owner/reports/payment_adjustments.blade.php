@extends('owner.layouts.app')

@section('title', 'Payment Adjustments')

@section('styles')
<style>
    :root {
        --card-shadow: 0 10px 25px rgba(0,0,0,0.05);
    }

    body {
        background: #fdf2f2;
    }

    .app-header {
        background: var(--primary-gradient);
        padding: 40px 20px 60px;
        border-radius: 0 0 40px 40px;
        color: white;
        margin-bottom: -30px;
    }

    .app-header h1 {
        font-size: 24px;
        font-weight: 900;
    }

    .list-container {
        padding: 0 20px 100px;
        position: relative;
        z-index: 10;
    }

    .adjust-card {
        background: white;
        border-radius: 24px;
        padding: 20px;
        margin-bottom: 16px;
        box-shadow: var(--card-shadow);
        border: 1px solid rgba(239, 68, 68, 0.05);
        display: block;
        text-decoration: none !important;
        color: inherit;
        position: relative;
    }

    .card-date {
        font-size: 11px;
        font-weight: 800;
        color: var(--text-muted);
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
    }

    .batch-id {
        font-family: 'Courier New', Courier, monospace;
        font-size: 14px;
        font-weight: 700;
        color: var(--text-main);
        background: #f1f5f9;
        padding: 2px 8px;
        border-radius: 6px;
    }

    .amount-display {
        font-size: 22px;
        font-weight: 900;
        margin: 12px 0;
    }

    .amount-display.credit { color: #10b981; }
    .amount-display.debit { color: #ef4444; }

    .card-footer {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px dashed #e2e8f0;
        background: none;
    }

    .account-info {
        font-size: 12px;
        font-weight: 700;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .items-badge {
        font-size: 10px;
        font-weight: 800;
        background: #eff6ff;
        color: var(--text-main);
        padding: 4px 10px;
        border-radius: 10px;
    }

    .type-pill {
        position: absolute;
        top: 20px;
        right: 20px;
        font-size: 10px;
        font-weight: 900;
        padding: 4px 12px;
        border-radius: 20px;
        text-transform: uppercase;
    }

    .pill-credit { background: #dcfce7; color: #16a34a; }
    .pill-debit { background: #fee2e2; color: #dc2626; }

    .fab-add {
        position: fixed;
        bottom: 30px;
        right: 20px;
        width: 60px;
        height: 60px;
        background: var(--primary-gradient);
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        box-shadow: 0 10px 25px rgba(239, 68, 68, 0.3);
        z-index: 100;
    }
</style>
@endsection

@section('content')
<div class="mobile-only">
    <div class="app-header">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <a href="{{ route('owner.dashboard') }}" class="text-white opacity-75"><i class="fas fa-arrow-left"></i></a>
            <span class="font-weight-bold opacity-50" style="font-size: 12px;">FINANCE</span>
        </div>
        <h1>Adjustments</h1>
        <p class="opacity-75">Recent payment distributions</p>
    </div>

    <div class="list-container">
        @foreach($grouped as $batchId => $items)
            @php $first = $items->first(); @endphp
            <a href="{{ route('owner.payment-adjustment.show', $batchId) }}" class="adjust-card">
                <span class="card-date">{{ date('d M Y', strtotime($first->date)) }}</span>
                <span class="type-pill pill-{{ $first->type }}">{{ $first->type }}</span>
                
                <div class="batch-id">#{{ str_replace('unique_', 'ID-', $batchId) }}</div>
                
                <div class="amount-display {{ $first->type }}">
                    {{ $first->type == 'credit' ? '+' : '-' }} ₹{{ number_format($items->sum('amount'), 2) }}
                </div>

                <div class="card-footer">
                    <div class="account-info">
                        <i class="fas fa-university text-muted"></i>
                        {{ \Illuminate\Support\Str::limit($first->account->bank_name ?? $first->account->name ?? 'N/A', 20) }}
                    </div>
                    <span class="items-badge">{{ $items->count() }} Entries</span>
                </div>
            </a>
        @endforeach

        @if($grouped->isEmpty())
            <div class="text-center py-5 opacity-25">
                <i class="fas fa-balance-scale fa-4x mb-3"></i>
                <p class="h5 font-weight-bold">No adjustments found</p>
            </div>
        @endif
    </div>

    <a href="{{ route('admin.payment.adjustment.create') }}" class="fab-add">
        <i class="fas fa-plus"></i>
    </a>
</div>

<div class="desktop-only">
    <div class="p-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Payment Adjustment Records</h3>
            <a href="{{ route('admin.payment.adjustment.create') }}" class="btn btn-primary">Record New Adjustment</a>
        </div>
        
        <div class="card shadow-sm border-0" style="border-radius: 15px;">
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="border-0 px-4 py-3">Date</th>
                            <th class="border-0 py-3">Batch ID</th>
                            <th class="border-0 py-3">Account</th>
                            <th class="border-0 py-3">Entries</th>
                            <th class="border-0 py-3">Total Amount</th>
                            <th class="border-0 py-3">Type</th>
                            <th class="border-0 px-4 py-3 text-right">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($grouped as $batchId => $items)
                        @php $first = $items->first(); @endphp
                        <tr>
                            <td class="px-4">{{ date('d-M-Y', strtotime($first->date)) }}</td>
                            <td><code>{{ $batchId }}</code></td>
                            <td>{{ $first->account->bank_name ?? $first->account->name ?? 'N/A' }}</td>
                            <td><span class="badge badge-secondary">{{ $items->count() }}</span></td>
                            <td class="font-weight-bold {{ $first->type == 'credit' ? 'text-success' : 'text-danger' }}">
                                ₹{{ number_format($items->sum('amount'), 2) }}
                            </td>
                            <td>
                                <span class="badge badge-{{ $first->type == 'credit' ? 'success' : 'danger' }} px-3">
                                    {{ ucfirst($first->type) }}
                                </span>
                            </td>
                            <td class="px-4 text-right">
                                <a href="{{ route('owner.payment-adjustment.show', $batchId) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
