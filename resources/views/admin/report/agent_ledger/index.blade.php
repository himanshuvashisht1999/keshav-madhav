@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Sales Agent Ledger Summary</h1>
        </div>
        <div class="erp-report-meta">
            <span>Report : AG-LEDGER</span>
            <span class="meta-divider">|</span>
            <span>Date : {{ now()->format('d M Y h:i A') }}</span>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.report.agent-ledger.index') }}">
                <div class="row align-items-end">
                    <div class="col-md-5 mb-2">
                        <label class="erp-form-label">Search Agent</label>
                        <input type="text" name="search" class="form-control" placeholder="Search Name, Email or Phone"
                            value="{{ request('search') }}">
                    </div>
                    <div class="col-md-4 mb-2 d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.agent-ledger.index') }}" class="btn-erp btn-erp-secondary">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- TABLE CARD --}}
    <div class="erp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="erp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 50px;" class="text-center">#</th>
                            <th>Sales Agent</th>
                            <th class="text-center">Shops</th>
                            <th class="text-right">Total Orders</th>
                            <th class="text-right">Total Paid</th>
                            <th class="text-right">Balance</th>
                            <th class="text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($agents as $index => $agent)
                            <tr>
                                <td class="text-center text-muted font-weight-bold">{{ $index + 1 }}</td>
                                <td>
                                    <strong>{{ $agent->name }}</strong>
                                    <div class="text-muted small">{{ $agent->email }} | {{ $agent->phone }}</div>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-light border">{{ $agent->shops_count }}</span>
                                </td>
                                <td class="text-right font-weight-bold">
                                    ₹{{ number_format($agent->total_order_value, 2) }}
                                </td>
                                <td class="text-right text-success font-weight-bold">
                                    ₹{{ number_format($agent->total_payments, 2) }}
                                </td>
                                <td class="text-right font-weight-bold {{ $agent->balance > 0 ? 'text-danger' : 'text-success' }}">
                                    ₹{{ number_format($agent->balance, 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ route('admin.report.agent-ledger.show', $agent->id) }}"
                                        class="btn-erp btn-erp-outline" title="View Ledger">
                                        <i class="fas fa-list-alt mr-1"></i> View Ledger
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No sales agents found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
