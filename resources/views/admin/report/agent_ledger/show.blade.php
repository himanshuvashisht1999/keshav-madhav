@extends('admin.layouts.app')

@section('content')
<style>
    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        border-bottom: 2px solid #343a40;
        padding-bottom: 15px;
    }

    .report-header h3 {
        font-weight: 700;
        margin: 0;
        color: #343a40;
    }

    .ledger-table thead th {
        background: #343a40;
        color: #fff;
        text-transform: uppercase;
        font-size: 13px;
        letter-spacing: 0.5px;
    }

    .tx-order {
        background-color: #fff;
    }

    .tx-payment {
        background-color: #f8f9fa;
    }

    .text-debit {
        color: #dc3545;
        font-weight: 600;
    }

    .text-credit {
        color: #28a745;
        font-weight: 600;
    }

    .balance-cell {
        font-weight: 700;
        background: #e9ecef !important;
    }

    @media print {
        .no-print {
            display: none !important;
        }

        .content-wrapper {
            margin-left: 0 !important;
            padding: 0 !important;
        }

        .card {
            border: none !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>
                    <h5 class="mb-0 text-muted small">Sales Agent Ledger</h5>
                    <h3>{{ $agent->name }}</h3>
                    <p class="mb-0 small">{{ $agent->email }} | {{ $agent->phone }}</p>
                </div>
                <div class="text-right">
                    <button onclick="window.print()" class="btn btn-outline-dark no-print mr-2">
                        <i class="fas fa-print mr-1"></i> Print
                    </button>
                    <a href="{{ route('admin.report.agent-ledger.index') }}" class="btn btn-secondary no-print">
                        <i class="fas fa-arrow-left mr-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            {{-- Filters --}}
            <div class="card mb-3 no-print">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.report.agent-ledger.show', $agent->id) }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Select Shop</label>
                                <select name="shop_id" class="form-control">
                                    <option value="">All Shops</option>
                                    @foreach($shops as $shop)
                                        <option value="{{ $shop->id }}" {{ $shopId == $shop->id ? 'selected' : '' }}>
                                            {{ $shop->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold">Start Date</label>
                                <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold">End Date</label>
                                <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-block">
                                    <i class="fas fa-filter mr-1"></i> Filter
                                </button>
                            </div>
                            <div class="col-md-1">
                                <a href="{{ route('admin.report.agent-ledger.show', $agent->id) }}"
                                    class="btn btn-outline-secondary btn-block">
                                    Clear
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-dark text-white">
                    <h5 class="mb-0"><i class="fas fa-store mr-2"></i> Shop-wise Performance Summary</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered mb-0">
                            <thead class="thead-light">
                                <tr>
                                    <th>Shop Name</th>
                                    <th class="text-right">Total Orders</th>
                                    <th class="text-right">Total Received</th>
                                    <th class="text-right">Pending Balance</th>
                                    <th class="text-center no-print">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($shopSummary as $summary)
                                    <tr>
                                        <td class="font-weight-bold">{{ $summary->name }}</td>
                                        <td class="text-right">₹{{ number_format($summary->total_orders, 2) }}</td>
                                        <td class="text-right text-success">₹{{ number_format($summary->total_received, 2) }}</td>
                                        <td class="text-right {{ $summary->pending_payment > 0 ? 'text-danger font-weight-bold' : 'text-success' }}">
                                            ₹{{ number_format($summary->pending_payment, 2) }}
                                        </td>
                                        <td class="text-center no-print">
                                            <a href="{{ route('admin.report.agent-ledger.show', [$agent->id, 'shop_id' => $summary->id]) }}" 
                                               class="btn btn-sm btn-info">
                                                <i class="fas fa-eye mr-1"></i> View Details
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="font-weight-bold bg-light">
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

            {{-- Chronological Ledger --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-list-ul mr-2"></i> Detailed Chronological Ledger</h5>
                    @if($shopId)
                        <span class="badge badge-info">Filtered by: {{ $shops->find($shopId)->name }}</span>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered ledger-table mb-0">
                            <thead>
                                <tr>
                                    <th width="12%">Date</th>
                                    <th>Shop</th>
                                    <th>Description</th>
                                    <th width="15%" class="text-right">Debit (Order)</th>
                                    <th width="15%" class="text-right">Credit (Payment)</th>
                                    <th width="18%" class="text-right">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $currentBalance = 0; @endphp
                                @forelse($transactions as $tx)
                                    <tr class="tx-{{ $tx->type }}">
                                        <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                                        <td>
                                            <span class="badge badge-light shadow-sm">{{ $tx->shop_name }}</span>
                                        </td>
                                        <td>
                                            <div class="font-weight-bold">{{ $tx->description }}</div>
                                            @if($tx->type == 'order')
                                                <small class="text-muted text-uppercase">Reference ID: {{ $tx->reference }}</small>
                                            @else
                                                <small class="text-muted text-uppercase">Payment ID: {{ $tx->reference }}</small>
                                            @endif
                                        </td>
                                        <td class="text-right text-debit text-uppercase">
                                            {!! $tx->debit > 0 ? '₹' . number_format($tx->debit, 2) : '-' !!}
                                        </td>
                                        <td class="text-right text-credit text-uppercase">
                                            {!! $tx->credit > 0 ? '₹' . number_format($tx->credit, 2) : '-' !!}
                                        </td>
                                        <td class="text-right balance-cell">
                                            ₹{{ number_format($tx->running_balance, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-5">
                                            <i class="fas fa-info-circle mr-2"></i> No transactions found for the selected
                                            period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            @if($transactions->isNotEmpty())
                                <tfoot>
                                    <tr class="bg-gray-dark text-white font-weight-bold">
                                        <td colspan="3" class="text-right">Ledger Summary</td>
                                        <td class="text-right">₹{{ number_format($transactions->sum('debit'), 2) }}</td>
                                        <td class="text-right">₹{{ number_format($transactions->sum('credit'), 2) }}</td>
                                        <td class="text-right">₹{{ number_format($transactions->last()->running_balance, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            @endif
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-light rounded border no-print">
                <h6 class="font-weight-bold mb-2">Note:</h6>
                <p class="small text-muted mb-0">
                    * <strong>Shop Summary</strong> shows the aggregate financial position of each associated shop. <br>
                    * <strong>Detailed Ledger</strong> shows the chronological flow of all transactions (Orders and Payments). <br>
                    * Use the "View Details" button or the shop filter to focus on a specific shop's ledger.
                </p>
            </div>
        </div>
    </section>
</div>
@endsection
