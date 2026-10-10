@extends('admin.layouts.app')
@section('title', 'Roll Tracking Details')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-barcode"></i> Roll tracking ledger
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-layer-group text-muted"></i> Fabric: <strong>{{ $fabric->name ?? 'Unknown' }}</strong></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="fas fa-tag text-muted"></i> Roll: <span class="badge badge-light border font-weight-bold">{{ $rollNo }}</span></span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.report.stock.rolls') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-arrow-left mr-1"></i> Back to rolls
            </a>
        </div>
    </div>

    {{-- Table Card --}}
    <div class="erp-card p-0">
        <div class="px-3 py-2 border-bottom bg-light">
            <h6 class="m-0 font-weight-bold text-dark" style="font-size: 13px;">
                <i class="fas fa-list-alt text-success mr-1"></i> Chronological roll usage history
            </h6>
        </div>
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 150px;">Date & time</th>
                        <th style="width: 140px;">Action / type</th>
                        <th>Order reference</th>
                        <th>Lot / shipment no</th>
                        <th>Tracking description</th>
                        <th class="text-right" style="width: 150px;">Transaction (m)</th>
                    </tr>
                </thead>
                <tbody>
                    @php 
                        $runningBalance = 0; 
                        $reversed = $data->sortBy('date');
                        foreach($reversed as $item) {
                            $runningBalance += $item->qty;
                        }
                    @endphp

                    @forelse($data as $row)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($row->date)->format('d M Y, h:i A') }}</td>
                            <td>
                                @if(str_contains($row->type, 'Shipping'))
                                    <span class="erp-badge-status erp-badge-status-closed"><i class="fas fa-truck-loading mr-1"></i> Shipping</span>
                                @elseif(str_contains($row->type, 'Production'))
                                    <span class="erp-badge-status erp-badge-status-open"><i class="fas fa-cut mr-1"></i> Production</span>
                                @else
                                    <span class="erp-badge-status erp-badge-status-neutral"><i class="fas fa-shopping-bag mr-1"></i> Agent sales</span>
                                @endif
                            </td>
                            <td><strong>{{ $row->order_no }}</strong></td>
                            <td><code>{{ $row->lot_no }}</code></td>
                            <td><span class="text-muted">{{ $row->details }}</span></td>
                            <td class="text-right font-weight-bold {{ $row->qty > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $row->qty > 0 ? '+' : '' }}{{ number_format($row->qty, 2) }} m
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No data available for this roll.</td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="5" class="text-right">Total remaining quantity</th>
                        <th class="text-right text-success font-weight-bold" style="font-size: 14px;">{{ number_format($runningBalance, 2) }} m</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>

</div>
@endsection
