@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Sales Agent Ledger: {{ $agent->name }}</h1>
            <div class="erp-report-meta">
                <span>{{ $agent->email }}</span>
                <span class="meta-divider">|</span>
                <span>{{ $agent->phone }}</span>
            </div>
        </div>
        <div class="no-print d-flex align-items-center" style="gap: 8px;">
            <button onclick="window.print()" class="btn-erp btn-erp-outline">
                <i class="fas fa-print mr-1"></i> Print
            </button>
            <a href="{{ route('admin.report.agent-ledger.index') }}" class="btn-erp btn-erp-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    {{-- FILTERS --}}
    <div class="erp-card mb-3 no-print">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.report.agent-ledger.show', $agent->id) }}">
                <div class="row align-items-end">
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Shop</label>
                        <select name="shop_id" class="form-control select2">
                            <option value="">All Shops</option>
                            @foreach($shops as $shop)
                                <option value="{{ $shop->id }}" {{ $shopId == $shop->id ? 'selected' : '' }}>
                                    {{ $shop->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">Start Date</label>
                        <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-form-label">End Date</label>
                        <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                    </div>
                    <div class="col-md-3 mb-2 d-flex align-items-center" style="gap: 8px;">
                        <button type="submit" class="btn-erp btn-erp-primary">
                            <i class="fas fa-search mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.report.agent-ledger.show', $agent->id) }}" class="btn-erp btn-erp-secondary">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- SHOP SUMMARY CARD --}}
    <div class="erp-card mb-3">
        <div class="card-header bg-white py-2 px-3 border-bottom">
            <span class="font-weight-bold text-dark"><i class="fas fa-store mr-1 text-muted"></i> Shop-wise Performance Summary</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="erp-table mb-0">
                    <thead>
                        <tr>
                            <th>Shop Name</th>
                            <th class="text-right">Total Orders</th>
                            <th class="text-right">Total Received</th>
                            <th class="text-right">Pending Balance</th>
                            <th class="text-center no-print" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shopSummary as $summary)
                            <tr>
                                <td class="font-weight-bold">{{ $summary->name }}</td>
                                <td class="text-right">₹{{ number_format($summary->total_orders, 2) }}</td>
                                <td class="text-right text-success font-weight-bold">₹{{ number_format($summary->total_received, 2) }}</td>
                                <td class="text-right {{ $summary->pending_payment > 0 ? 'text-danger font-weight-bold' : 'text-success font-weight-bold' }}">
                                    ₹{{ number_format($summary->pending_payment, 2) }}
                                </td>
                                <td class="text-center no-print">
                                    <a href="{{ route('admin.report.agent-ledger.show', [$agent->id, 'shop_id' => $summary->id]) }}" 
                                       class="btn-erp btn-erp-outline" title="View Details">
                                        <i class="fas fa-eye mr-1"></i> Details
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <td>GRAND TOTAL</td>
                            <td class="text-right">₹{{ number_format($shopSummary->sum('total_orders'), 2) }}</td>
                            <td class="text-right">₹{{ number_format($shopSummary->sum('total_received'), 2) }}</td>
                            <td class="text-right">₹{{ number_format($shopSummary->sum('pending_payment'), 2) }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

    {{-- CHRONOLOGICAL LEDGER CARD --}}
    <div class="erp-card mb-3">
        <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
            <span class="font-weight-bold text-dark"><i class="fas fa-list-ul mr-1 text-muted"></i> Detailed Chronological Ledger</span>
            @if($shopId)
                <span class="erp-stat-pill">Filtered by: <strong>{{ $shops->find($shopId)->name }}</strong></span>
            @endif
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="erp-table mb-0">
                    <thead>
                        <tr>
                            <th style="width: 12%;">Date</th>
                            <th>Shop</th>
                            <th>Description</th>
                            <th style="width: 15%;" class="text-right">Debit (Order)</th>
                            <th style="width: 15%;" class="text-right">Credit (Payment)</th>
                            <th style="width: 18%;" class="text-right">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $currentBalance = 0; @endphp
                        @forelse($transactions as $tx)
                            <tr>
                                <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                                <td>
                                    <span class="badge badge-light border">{{ $tx->shop_name }}</span>
                                </td>
                                <td>
                                    <strong>{{ $tx->description }}</strong>
                                    <div class="text-muted small">
                                        {{ $tx->type == 'order' ? 'Reference ID: ' : 'Payment ID: ' }}{{ $tx->reference }}
                                    </div>
                                </td>
                                <td class="text-right font-weight-bold text-danger">
                                    {!! $tx->debit > 0 ? '₹' . number_format($tx->debit, 2) : '-' !!}
                                </td>
                                <td class="text-right font-weight-bold text-success">
                                    {!! $tx->credit > 0 ? '₹' . number_format($tx->credit, 2) : '-' !!}
                                </td>
                                <td class="text-right font-weight-bold">
                                    ₹{{ number_format($tx->running_balance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    No transactions found for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->isNotEmpty())
                        <tfoot>
                            <tr>
                                <td colspan="3" class="text-right">Summary</td>
                                <td class="text-right">₹{{ number_format($transactions->sum('debit'), 2) }}</td>
                                <td class="text-right">₹{{ number_format($transactions->sum('credit'), 2) }}</td>
                                <td class="text-right">₹{{ number_format($transactions->last()->running_balance, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <div class="p-3 bg-white border rounded no-print">
        <h6 class="font-weight-bold mb-2">Note:</h6>
        <p class="small text-muted mb-0">
            * <strong>Shop Summary</strong> shows the aggregate financial position of each associated shop. <br>
            * <strong>Detailed Ledger</strong> shows the chronological flow of all transactions (Orders and Payments). <br>
            * Use the "Details" button or the shop filter to focus on a specific shop's ledger.
        </p>
    </div>
</div>
@endsection
