@extends('admin.layouts.app')

@section('content')
    <div class="content-wrapper">
        <!-- Title & Breadcrumbs -->
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1>Ledger: {{ $good->design_number }} ({{ $good->series?->name }} {{ $good->name_of_garment }})</h1>
                    </div>
                    <div class="col-sm-6">
                        <div class="float-sm-right text-right text-end">
                            <a href="{{ route('admin.ledger.production-goods.index') }}" class="btn btn-secondary btn-sm"><i class="mdi mdi-arrow-left"></i> Back to List</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <div class="card">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.ledger.production-goods.show', $good->id) }}" class="mb-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Start Date</label>
                                    <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
                                </div>
                                <div class="col-md-3">
                                    <label>End Date</label>
                                    <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
                                </div>
                                <div class="col-md-3 mt-3">
                                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                                </div>
                                <div class="col-md-3 mt-3">
                                    <a href="{{ route('admin.ledger.production-goods.show', $good->id) }}" class="btn btn-secondary w-100">Reset</a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped dt-responsive nowrap w-100">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Particulars</th>
                                        <th>Remarks</th>
                                        <th class="text-end">Inward (Boxes)</th>
                                        <th class="text-end">Outward (Boxes)</th>
                                        <th class="text-end">Balance (Boxes)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if($startDate)
                                    <tr>
                                        <td colspan="4" class="text-end fw-bold">Opening Balance</td>
                                        <td class="text-end"></td>
                                        <td class="text-end"></td>
                                        <td class="text-end fw-bold">{{ number_format($openingBalanceAmount, 0) }}</td>
                                    </tr>
                                    @endif

                                    @forelse($transactions as $tx)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}</td>
                                            <td>
                                                @if($tx->type == 'Inward')
                                                    <span class="badge bg-success">Inward</span>
                                                @else
                                                    <span class="badge bg-danger">Outward</span>
                                                @endif
                                            </td>
                                            <td>{{ $tx->particulars }}</td>
                                            <td>
                                                @if(isset($tx->link) && $tx->link)
                                                    <a href="{{ $tx->link }}" target="_blank">{{ $tx->remarks ?? '-' }}</a>
                                                @else
                                                    {{ $tx->remarks ?? '-' }}
                                                @endif
                                            </td>
                                            <td class="text-end text-success">{{ $tx->inward > 0 ? number_format($tx->inward, 0) : '-' }}</td>
                                            <td class="text-end text-danger">{{ $tx->outward > 0 ? number_format($tx->outward, 0) : '-' }}</td>
                                            <td class="text-end fw-bold text-primary">{{ number_format($tx->running_balance, 0) }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center">No transactions found for the selected period.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                <tfoot>
                                    @php
                                        $totalInward = $transactions->sum('inward');
                                        $totalOutward = $transactions->sum('outward');
                                        $closingBalance = $transactions->last()->running_balance ?? $openingBalanceAmount;
                                    @endphp
                                    <tr class="table-light">
                                        <th colspan="4" class="text-end fw-bold">Total</th>
                                        <th class="text-end text-success">{{ number_format($totalInward, 0) }}</th>
                                        <th class="text-end text-danger">{{ number_format($totalOutward, 0) }}</th>
                                        <th class="text-end text-primary">{{ number_format($closingBalance, 0) }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
