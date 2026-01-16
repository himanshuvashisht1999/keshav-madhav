@extends('admin.layouts.app')

@section('content')
<style>
    .report-header{ display:flex; justify-content:space-between; align-items:center; margin-bottom:15px; }
    .report-header h3{ font-weight:600;margin:0; }
    .card-set { border: 1px solid #dee2e6; margin-bottom: 2rem; border-radius: 0.5rem; overflow: hidden; }
    .card-set-header { background: #f8f9fa; padding: 1rem; border-bottom: 1px solid #dee2e6; display: flex; justify-content: space-between; align-items: center; }
    .card-set-body { padding: 1rem; }
    .table-lot-history th { font-size: 0.85rem; background: #e9ecef; }
    .table-lot-history td { font-size: 0.85rem; }
    .lot-card { border: 1px solid #e2e8f0; border-radius: 6px; margin-bottom: 10px; }
    .lot-header { background: #f1f5f9; padding: 8px 12px; font-weight: 600; display: flex; justify-content: space-between; }
    .lot-body { padding: 8px 12px; }
    .badge-stage { font-size: 0.75rem; padding: 4px 8px; }
</style>

<div class="content-wrapper">

<section class="content-header">
    <div class="container-fluid">
        <div class="report-header">
            <div>
                <a href="{{ route('admin.report.sales-order') }}" class="btn btn-outline-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
            <div>
                <h3>Order Details: {{ $order->sku }}</h3>
                <small class="text-muted">Customer: {{ $order->customer->name ?? '-' }} | Date: {{ $order->created_at->format('d M Y') }}</small>
            </div>
            <div>
                 {{-- Actions like Export can go here --}}
            </div>
        </div>
    </div>
</section>

<section class="content">
    <div class="row mb-3">
        <div class="col-md-3 ml-2">
            <label for="">Search Lot No</label>
            <input type="text"
                id="lotSearch"
                class="form-control form-control-sm"
                placeholder="Search Lot No">
        </div>
    </div>

<div class="container-fluid">

@forelse($order->OrderProductSets as $set)
    <div class="card card-set">
        <div class="card-set-header">
            <div>
                <h5 class="m-0">Design No: <strong>{{ $set->design_number }}</strong></h5>
                <small class="text-muted">
                    Color: {{ $set->colors->name ?? '-' }} | 
                    Size Group: {{ $set->size_measurement->size_group ?? '-' }} |
                    Total Order Qty: {{ $set->total_quantity }} pcs
                </small>
            </div>
        </div>
        <div class="card-set-body">
            
            @if($set->lots->count() > 0)
                <div class="row">
                    @foreach($set->lots as $lot)
                    <div class="col-md-12">
                        <div class="lot-card" data-lot="{{ strtolower($lot->lot_no) }}">
                            <div class="lot-header">
                                <div>
                                    <i class="fas fa-layer-group text-primary"></i> Lot No: {{ $lot->lot_no }}
                                </div>
                                <div>
                                    <span class="badge bg-info">{{ $lot->stage_master_unit->masterStage->name ?? '' }}</span>
                                </div>
                            </div>
                            <div class="lot-body">
                                
                                {{-- STAGE SUMMARY --}}
                                <h6 class="text-muted mb-2 border-bottom pb-1" style="font-size:0.9rem;">Stage Status</h6>
                                <div class="table-responsive mb-3">
                                    <table class="table table-sm table-bordered text-center mb-0" style="font-size:0.85rem;">
                                        <thead class="bg-light">
                                            <tr>
                                                <th>Stage Name</th>
                                                <th>Total Received</th>
                                                <th>Total Processed</th>
                                                <th>Balance</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @forelse($lot->stage_summary as $summary)
                                            <tr>
                                                <td class="text-start fw-bold">{{ $summary['stage_name'] }}</td>
                                                <td>{{ $summary['in'] }}</td>
                                                <td>{{ $summary['out'] }}</td>
                                                <td class="{{ $summary['balance'] > 0 ? 'text-primary fw-bold' : 'text-muted' }}">
                                                    {{ $summary['balance'] }}
                                                </td>
                                            </tr>
                                            @empty
                                            <tr><td colspan="4">No stage activity found.</td></tr>
                                            @endforelse
                                        </tbody>
                                    </table>
                                </div>

                                {{-- Transaction History (Collapsible) --}}
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="text-muted m-0" style="font-size:0.9rem;">Detailed History</h6>
                                    <button class="btn btn-xs btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#history-{{ $lot->id }}">
                                        Show/Hide
                                    </button>
                                    {{-- <button class="btn btn-xs btn-outline-secondary"
                                            type="button"
                                            data-toggle="collapse"
                                            data-target="#history-{{ $lot->id }}">
                                        Show/Hide
                                    </button> --}}
                                </div>
                                <div class="collapse" id="history-{{ $lot->id }}">
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-lot-history mb-0">
                                            <thead>
                                                <tr>
                                                    <th>Date/Time</th>
                                                    <th>From Stage</th>
                                                    <th>To Stage</th>
                                                    <th>Qty</th>
                                                    <th>Remarks</th>
                                                    <th>Processed By</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @forelse($lot->history as $history)
                                                <tr>
                                                    <td>{{ $history->created_at->format('d M Y h:i A') }}</td>
                                                    <td>{{ $history->from_stage->name ?? '-' }}</td>
                                                    <td>{{ $history->to_stage->name ?? '-' }}</td>
                                                    <td>{{ $history->quantity }}</td>
                                                    <td>{{ $history->remarks ?? '-' }}</td>
                                                    <td>{{ $history->processed_by ?? '-' }}</td>
                                                </tr>
                                                @empty
                                                <tr>
                                                    <td colspan="6" class="text-center text-muted">No history found (Initial Allocation)</td>
                                                </tr>
                                                @endforelse
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            @else
                <div class="alert alert-light text-center">
                    No lots allocated for this design yet.
                </div>
            @endif

        </div>
    </div>
@empty
    <div class="alert alert-warning">
        No product sets found for this order.
    </div>
@endforelse

</div>
</section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const searchInput = document.getElementById('lotSearch');

        searchInput.addEventListener('keyup', function () {
            const searchValue = this.value.toLowerCase().trim();
            const lotCards = document.querySelectorAll('.lot-card');

            lotCards.forEach(card => {
                const lotNo = card.getAttribute('data-lot');

                if (lotNo.includes(searchValue)) {
                    card.closest('.col-md-12').style.display = '';
                } else {
                    card.closest('.col-md-12').style.display = 'none';
                }
            });
        });

    });
</script>
@endsection
