@extends('admin.layouts.app')

@section('content')
    <style>
        .ledger-header {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
            margin-bottom: 20px;
        }
        .balance-card {
            background: #f8f9fa;
            border-left: 5px solid #007bff;
            padding: 15px;
        }
        .table-ledger thead th {
            background: #343a40;
            color: #fff;
            vertical-align: middle;
        }
        .text-inward { color: #28a745; font-weight: 600; }
        .text-outward { color: #dc3545; font-weight: 600; }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row align-items-center">
                    <div class="col-sm-6">
                        <h1>Fabric Ledger Details</h1>
                    </div>
                    <div class="col-sm-6 text-sm-right">
                        <a href="{{ route('admin.ledger.fabric.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                
                <div class="ledger-header">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="balance-card">
                                <h6 class="text-muted mb-1">Fabric Information</h6>
                                <h4 class="mb-0">{{ $fabric->name }}</h4>
                            </div>
                        </div>
                        <div class="col-md-8">
                            <form method="GET" class="row g-2 align-items-end justify-content-end">
                                <div class="col-md-3">
                                    <label class="small text-muted">Vendor / Supplier</label>
                                    <select name="vendor_id" class="form-control form-control-sm">
                                        <option value="">All Vendors</option>
                                        @foreach($vendors as $v)
                                            <option value="{{ $v->id }}" {{ $vendorId == $v->id ? 'selected' : '' }}>{{ $v->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="small text-muted">Customer / Party</label>
                                    <select name="customer_id" class="form-control form-control-sm">
                                        <option value="">All Customers</option>
                                        @foreach($customers as $c)
                                            <option value="{{ $c->id }}" {{ $customerId == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="small text-muted">From Date</label>
                                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                                </div>
                                <div class="col-md-2">
                                    <label class="small text-muted">To Date</label>
                                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                                </div>
                                <div class="col-md-1">
                                    <button type="submit" class="btn btn-primary btn-sm btn-block">Filter</button>
                                </div>
                                <div class="col-md-1">
                                    <a href="{{ route('admin.ledger.fabric.show', $fabric->id) }}" class="btn btn-default btn-sm btn-block">Reset</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-ledger mb-0">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Particulars</th>
                                        <th>Party / Department</th>
                                        <th class="text-right">Inward (Mtrs)</th>
                                        <th class="text-right">Outward (Mtrs)</th>
                                        <th class="text-right">Running Balance</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $openingBalance = 0; 
                                        $totalInward = 0;
                                        $totalOutward = 0;
                                    @endphp
                                    <tr class="table-light">
                                        <td colspan="5" class="text-right fw-bold">Opening Balance</td>
                                        <td class="text-right fw-bold">{{ number_format($openingBalance, 2) }}</td>
                                    </tr>
                                    @forelse($transactions as $tx)
                                        @php
                                            $totalInward += $tx->inward;
                                            $totalOutward += $tx->outward;
                                        @endphp
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                                            <td>{{ $tx->particulars }}</td>
                                            <td>{{ $tx->party }}</td>
                                            <td class="text-right text-inward">{{ $tx->inward > 0 ? number_format($tx->inward, 2) : '-' }}</td>
                                            <td class="text-right text-outward">{{ $tx->outward > 0 ? number_format($tx->outward, 2) : '-' }}</td>
                                            <td class="text-right fw-bold">{{ number_format($tx->running_balance, 2) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-4 text-muted">No transactions found for the selected period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if($transactions->count() > 0)
                                    <tfoot class="bg-light">
                                        <tr>
                                            <th colspan="3" class="text-right">TOTAL</th>
                                            <th class="text-right text-success">{{ number_format($totalInward, 2) }}</th>
                                            <th class="text-right text-danger">{{ number_format($totalOutward, 2) }}</th>
                                            <th class="text-right">{{ number_format($transactions->last()->running_balance, 2) }}</th>
                                        </tr>
                                    </tfoot>
                                @endif
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
