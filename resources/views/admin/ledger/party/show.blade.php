@extends('admin.layouts.app')

@section('content')
    <style>
        .ledger-header {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .card-detail {
            border-radius: 15px;
            border: none;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .table-ledger thead th {
            background: #f8f9fa;
            text-transform: uppercase;
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 1px;
            color: #555;
            border-top: none;
        }

        .text-debit {
            color: #d32f2f;
            font-weight: 600;
        }

        .text-credit {
            color: #2e7d32;
            font-weight: 600;
        }

        .text-balance {
            font-weight: 700;
            color: #1e3c72;
        }

        .date-badge {
            background: #f1f3f5;
            color: #495057;
            padding: 4px 10px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .particulars-text {
            font-size: 13px;
            line-height: 1.4;
        }

        .type-label {
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 4px;
            font-weight: 700;
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="ledger-header">
                    <div class="row align-items-center">
                        <div class="col-md-5">
                            <h5 class="text-uppercase mb-1 opacity-75">{{ $type }} Ledger</h5>
                            <h2 class="font-weight-bold mb-0">{{ $party->name }}</h2>
                            <p class="mb-0 opacity-75 mt-2">
                                <i class="fas fa-map-marker-alt mr-2"></i>{{ $party->address ?? 'No Address Provided' }} |
                                <i class="fas fa-phone mr-2"></i>{{ $party->phone ?? '-' }}
                            </p>
                        </div>
                        <div class="col-md-7 text-right">
                            <div class="d-flex justify-content-end align-items-center">
                                <div class="bg-white text-dark p-3 px-4 rounded shadow-sm mr-3 text-center border-left border-secondary"
                                    style="min-width: 180px; border-left-width: 4px !important;">
                                    <p class="small text-uppercase font-weight-bold mb-1 text-muted">Opening Balance</p>
                                    <h4 class="mb-0 font-weight-bold">
                                        ₹ {{ number_format(abs($openingBalAmount), 2) }}
                                        <span
                                            class="badge {{ $openingBalAmount >= 0 ? 'badge-success' : 'badge-danger' }} ml-1"
                                            style="font-size: 11px;">{{ $openingBalAmount >= 0 ? 'CR' : 'DR' }}</span>
                                    </h4>
                                </div>
                                <div class="bg-white text-dark p-3 px-4 rounded shadow-sm text-center border-left border-primary"
                                    style="min-width: 180px; border-left-width: 4px !important;">
                                    <p class="small text-uppercase font-weight-bold mb-1 text-muted">Current Balance</p>
                                    <h4 class="mb-0 font-weight-bold text-primary">
                                        ₹ {{ number_format(abs($party->balance), 2) }}
                                        <span
                                            class="badge {{ $party->balance >= 0 ? 'badge-success' : 'badge-danger' }} ml-1"
                                            style="font-size: 11px;">
                                            {{ $party->balance >= 0 ? 'CR' : 'DR' }}
                                        </span>
                                    </h4>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- FILTERS --}}
                <div class="card card-detail mb-4">
                    <div class="card-body">
                        <form action="{{ route('admin.ledger.party.show', ['type' => $type, 'id' => $party->id]) }}"
                            method="GET">
                            <div class="row align-items-end">
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">Start Date</label>
                                    <input type="date" name="start_date" value="{{ request('start_date') }}"
                                        class="form-control" style="border-radius: 10px;">
                                </div>
                                <div class="col-md-3">
                                    <label class="small font-weight-bold text-muted">End Date</label>
                                    <input type="date" name="end_date" value="{{ request('end_date') }}"
                                        class="form-control" style="border-radius: 10px;">
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-primary px-4" style="border-radius: 10px;">
                                        <i class="fas fa-filter mr-2"></i>Filter Ledger
                                    </button>
                                    <a href="{{ route('admin.ledger.party.show', ['type' => $type, 'id' => $party->id]) }}"
                                        class="btn btn-outline-secondary ml-2" style="border-radius: 10px;">Clear</a>
                                    <a href="{{ route('admin.ledger.party.download', ['type' => $type, 'id' => $party->id, 'start_date' => request('start_date'), 'end_date' => request('end_date')]) }}"
                                        class="btn btn-danger px-4 ml-2" style="border-radius: 10px;">
                                        <i class="fas fa-file-pdf mr-2"></i>Download PDF
                                    </a>
                                    <!-- <button type="button" class="btn btn-success px-4 ml-2" onclick="window.print()" style="border-radius: 10px;">
                                        <i class="fas fa-print mr-2"></i>Print
                                    </button> -->
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- LEDGER TABLE --}}
                <div class="card card-detail">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-bordered table-ledger mb-0">
                                <thead>
                                    <tr>
                                        <th width="12%" class="pl-4">Date</th>
                                        <th width="10%">Type</th>
                                        <th width="12%">Reference</th>
                                        <th>Particulars</th>
                                        <th width="12%" class="text-right">Debit (DR)</th>
                                        <th width="12%" class="text-right">Credit (CR)</th>
                                        <th width="12%" class="text-right">Balance</th>
                                        <th width="8%" class="text-center pr-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $currentBalance = 0; @endphp
                                    @forelse($transactions as $tx)
                                        @php $currentBalance = $tx->running_balance; @endphp
                                        <tr>
                                            <td class="pl-4 align-middle">
                                                <span class="date-badge">
                                                    {{ \Carbon\Carbon::parse($tx->date)->format('d M Y') }}
                                                </span>
                                            </td>
                                            <td class="align-middle">
                                                <span class="type-label bg-light border">
                                                    {{ $tx->type }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-muted small font-weight-bold">
                                                {{ $tx->ref }}
                                            </td>
                                            <td class="align-middle">
                                                <div class="particulars-text">{{ $tx->description }}</div>
                                            </td>
                                            <td class="align-middle text-right text-debit">
                                                {{ $tx->debit > 0 ? '₹ ' . number_format($tx->debit, 2) : '-' }}
                                            </td>
                                            <td class="align-middle text-right text-credit">
                                                {{ $tx->credit > 0 ? '₹ ' . number_format($tx->credit, 2) : '-' }}
                                            </td>
                                            <td class="align-middle text-right text-balance">
                                                ₹ {{ number_format(abs($currentBalance), 2) }}
                                                <small class="text-muted ml-1">{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                            </td>
                                            <td class="align-middle text-center pr-4">
                                                @if(isset($tx->view_url) && $tx->view_url !== '#')
                                                    <a href="{{ $tx->view_url }}" class="btn btn-xs btn-outline-primary" title="View Transaction Details" target="_blank">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                No transactions recorded for the selected period.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                                @if(!$transactions->isEmpty())
                                    <tfoot class="bg-light">
                                        <tr class="font-weight-bold">
                                            <td colspan="4" class="text-right py-3 pr-4">Totals:</td>
                                            <td class="text-right py-3 text-debit">₹
                                                {{ number_format($transactions->sum('debit'), 2) }}</td>
                                            <td class="text-right py-3 text-credit">₹
                                                {{ number_format($transactions->sum('credit'), 2) }}</td>
                                            <td class="text-right py-3 text-primary">
                                                ₹ {{ number_format(abs($currentBalance), 2) }}
                                                <small>{{ $currentBalance >= 0 ? 'CR' : 'DR' }}</small>
                                            </td>
                                            <td class="bg-light"></td>
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