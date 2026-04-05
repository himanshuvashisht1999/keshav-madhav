@extends('admin.layouts.app')

@section('content')
<style>
    .report-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }

    .report-header h3 {
        font-weight: 600;
        margin: 0;
    }

    .report-card {
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, .08);
    }

    .table-report thead th {
        background: #343a40;
        color: #fff;
        font-weight: 600;
        vertical-align: middle;
        white-space: nowrap;
    }

    .balance-positive {
        color: #dc3545;
        font-weight: 700;
    }

    .balance-negative {
        color: #28a745;
        font-weight: 700;
    }
</style>

<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="report-header">
                <div>
                    <div class="report-meta">Report : AG-LEDGER</div>
                </div>
                <div>
                    <h3>Sales Agent Ledger Summary</h3>
                </div>
                <div class="report-meta">
                    Date : {{ now()->format('d M Y h:i A') }}
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card mb-3">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.report.agent-ledger.index') }}">
                        <div class="row g-2 align-items-end">
                            <div class="col-md-4">
                                <label>Search Agent</label>
                                <input type="text" name="search" class="form-control" placeholder="Name, Email or Phone"
                                    value="{{ request('search') }}">
                            </div>
                            <div class="col-md-2">
                                <button class="btn btn-primary btn-block">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card report-card">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-bordered table-report mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Sales Agent</th>
                                    <th class="text-center">Shops</th>
                                    <th class="text-right">Total Orders</th>
                                    <th class="text-right">Total Paid</th>
                                    <th class="text-right">Balance</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($agents as $index => $agent)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>
                                            <div class="font-weight-bold">{{ $agent->name }}</div>
                                            
                                            <small class="text-muted">{{ $agent->email }} | {{ $agent->phone }}</small>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $agent->shops_count }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold">
                                            ₹{{ number_format($agent->total_order_value, 2) }}
                                        </td>
                                        <td class="text-right text-success font-weight-bold">
                                            ₹{{ number_format($agent->total_payments, 2) }}
                                        </td>
                                        <td class="text-right {{ $agent->balance > 0 ? 'balance-positive' : 'balance-negative' }}">
                                            ₹{{ number_format($agent->balance, 2) }}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('admin.report.agent-ledger.show', $agent->id) }}"
                                                class="btn btn-sm btn-dark">
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
    </section>
</div>
@endsection
