@extends('admin.layouts.app')
@section('title', 'Roll Tracking Details')

@section('content')
<div class="content-wrapper">
    <section class="content-header">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h3 class="m-0">Roll Tracking Ledger</h3>
                    <p class="text-muted mb-0">Fabric: <strong class="text-dark">{{ $fabric->name ?? 'Unknown' }}</strong> | Roll No: <span class="badge bg-danger">{{ $rollNo }}</span></p>
                </div>
                <div class="d-flex gap-2">
                    <a href="{{ route('admin.report.stock.rolls') }}" class="btn btn-sm btn-secondary"><i class="fas fa-arrow-left"></i> Back to Rolls</a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-list-alt me-2 text-primary"></i> Chronological Roll Usage History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered align-middle">
                            <thead class="table-dark">
                                <tr>
                                    <th>Date & Time</th>
                                    <th>Action / Type</th>
                                    <th>Order Reference</th>
                                    <th>Lot / Shipment No</th>
                                    <th>Tracking Descriptions</th>
                                    <th class="text-end">Transaction (m)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php 
                                    $runningBalance = 0; 
                                    // Reverse to calculate running balance chronologically
                                    $reversed = $data->sortBy('date');
                                @endphp
                                
                                @foreach($reversed as $item)
                                    @php $runningBalance += $item->qty; @endphp
                                @endforeach

                                @forelse($data as $row)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($row->date)->format('d-M-Y h:i A') }}</td>
                                        <td>
                                            @if(str_contains($row->type, 'Shipping'))
                                                <span class="badge bg-success"><i class="fas fa-truck-loading me-1"></i> Shipping</span>
                                            @elseif(str_contains($row->type, 'Production'))
                                                <span class="badge bg-primary"><i class="fas fa-cut me-1"></i> Production</span>
                                            @else
                                                <span class="badge bg-warning text-dark"><i class="fas fa-shopping-bag me-1"></i> Agent Sales</span>
                                            @endif
                                        </td>
                                        <td class="fw-bold">{{ $row->order_no }}</td>
                                        <td><span class="text-secondary">{{ $row->lot_no }}</span></td>
                                        <td><small>{{ $row->details }}</small></td>
                                        <td class="text-end fw-bold {{ $row->qty > 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $row->qty > 0 ? '+' : '' }}{{ number_format($row->qty, 2) }} m
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No data available for this roll.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="5" class="text-end">Total Remaining Quantity:</td>
                                    <td class="text-end text-success fs-5">{{ number_format($runningBalance, 2) }} m</td>
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
