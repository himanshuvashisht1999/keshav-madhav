@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- HEADER BAR --}}
    <div class="erp-header-bar mb-3">
        <div>
            <h1 class="erp-header-title">Order Details: {{ $order->sku }}</h1>
            <div class="erp-report-meta">
                <span>Customer: <strong>{{ $order->customer->name ?? '-' }}</strong></span>
                <span class="meta-divider">|</span>
                <span>Date: {{ $order->created_at->format('d M Y') }}</span>
            </div>
        </div>
        <div>
            <a href="{{ route('admin.report.sales-order') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back
            </a>
        </div>
    </div>

    {{-- SEARCH BAR --}}
    <div class="erp-card mb-3">
        <div class="card-body p-3">
            <div class="row">
                <div class="col-md-4">
                    <label class="erp-form-label">Search Lot Number</label>
                    <input type="text" id="lotSearch" class="form-control" placeholder="Search Lot Number...">
                </div>
            </div>
        </div>
    </div>

    @forelse($order->OrderProductSets as $set)
        <div class="erp-card mb-3">
            <div class="card-header bg-white py-2 px-3 border-bottom d-flex justify-content-between align-items-center">
                <div>
                    <span class="font-weight-bold text-dark">Design Number: {{ $set->design_number }}</span>
                    <div class="text-muted small">
                        Color: {{ isset($set->colors) ? $set->colors->name . ' (' . $set->colors->id . ')' : '-' }} | 
                        Size Group: {{ $set->size_measurement->size_group ?? '-' }}
                    </div>
                </div>
                <div>
                    <span class="erp-stat-pill">Total Order Qty: <strong>{{ number_format($set->total_quantity) }} Pcs</strong></span>
                </div>
            </div>
            <div class="card-body p-3">
                @if($set->lots->count() > 0)
                    <div class="row">
                        @foreach($set->lots as $lot)
                            <div class="col-md-12 mb-3">
                                <div class="border rounded lot-card overflow-hidden" data-lot="{{ strtolower($lot->lot_no) }}">
                                    <div class="p-2 bg-light border-bottom d-flex justify-content-between align-items-center">
                                        <div class="font-weight-bold text-dark">
                                            <i class="fas fa-layer-group text-success mr-1"></i> Lot Number: {{ $lot->lot_no }}
                                        </div>
                                        <div>
                                            <span class="badge badge-light border">{{ $lot->stage_master_unit->masterStage->name ?? '' }}</span>
                                        </div>
                                    </div>
                                    <div class="p-3 bg-white">
                                        {{-- STAGE SUMMARY --}}
                                        <h6 class="font-weight-bold text-muted mb-2 border-bottom pb-1" style="font-size: 12px;">Stage Status</h6>
                                        <div class="table-responsive mb-3">
                                            <table class="erp-table mb-0">
                                                <thead>
                                                    <tr>
                                                        <th>Stage Name</th>
                                                        <th class="text-right">Total Received</th>
                                                        <th class="text-right">Total Processed</th>
                                                        <th class="text-right">Balance</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @forelse($lot->stage_summary as $summary)
                                                        <tr>
                                                            <td class="font-weight-bold">{{ $summary['stage_name'] }}</td>
                                                            <td class="text-right">{{ number_format($summary['in']) }}</td>
                                                            <td class="text-right">{{ number_format($summary['out']) }}</td>
                                                            <td class="text-right font-weight-bold {{ $summary['balance'] > 0 ? 'text-primary' : 'text-muted' }}">
                                                                {{ number_format($summary['balance']) }}
                                                            </td>
                                                        </tr>
                                                    @empty
                                                        <tr><td colspan="4" class="text-center text-muted">No stage activity found.</td></tr>
                                                    @endforelse
                                                </tbody>
                                            </table>
                                        </div>

                                        {{-- Transaction History (Collapsible) --}}
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h6 class="font-weight-bold text-muted m-0" style="font-size: 12px;">Detailed History</h6>
                                            <button class="btn-erp btn-erp-outline btn-xs" type="button" data-toggle="collapse" data-target="#history-{{ $lot->id }}">
                                                Show / Hide History
                                            </button>
                                        </div>
                                        <div class="collapse" id="history-{{ $lot->id }}">
                                            <div class="table-responsive">
                                                <table class="erp-table mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Date / Time</th>
                                                            <th>From Stage</th>
                                                            <th>To Stage</th>
                                                            <th class="text-right">Quantity</th>
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
                                                                <td class="text-right font-weight-bold">{{ number_format($history->quantity) }}</td>
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
                    <div class="text-center text-muted py-3">
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

@push('scripts')
<script>
    $(document).ready(function () {
        $('#lotSearch').on('keyup', function () {
            const searchValue = $(this).val().toLowerCase().trim();

            $('.lot-card').each(function () {
                const lotNo = $(this).attr('data-lot');
                if (lotNo.includes(searchValue)) {
                    $(this).closest('.col-md-12').show();
                } else {
                    $(this).closest('.col-md-12').hide();
                }
            });
        });
    });
</script>
@endpush
@endsection
