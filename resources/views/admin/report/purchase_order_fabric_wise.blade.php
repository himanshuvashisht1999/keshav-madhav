@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">

    {{-- Header Bar --}}
    <div class="erp-header-bar mb-2">
        <div>
            <h1 class="erp-header-title">
                <i class="fas fa-layer-group"></i> Fabric wise purchase report
            </h1>
            <div class="erp-report-meta">
                <span class="meta-item"><i class="fas fa-hashtag text-muted"></i> Report: POFW-1</span>
                <span class="meta-divider">&bull;</span>
                <span class="meta-item"><i class="far fa-clock text-muted"></i> Generated: {{ now()->format('d M Y, h:i A') }}</span>
            </div>
        </div>
        <div class="erp-header-actions">
            <a href="{{ route('admin.report.purchase_order') }}" class="btn-erp btn-erp-outline">
                <i class="fas fa-file-invoice mr-1"></i> PO summary report
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
    <div class="erp-card mb-2 p-2">
        <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise') }}" class="mb-0">
            <div class="form-row align-items-end">
                <div class="col-md-5 col-sm-8 mb-1">
                    <label class="small text-muted font-weight-bold mb-1">Search fabric</label>
                    <div class="input-group input-group-sm">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="search" class="form-control form-control-sm erp-filter-input border-left-0" placeholder="Search by fabric name..." value="{{ $filters['search'] ?? '' }}">
                    </div>
                </div>
                <div class="col-md-3 col-sm-4 mb-1 d-flex gap-1">
                    <button type="submit" class="btn-erp btn-erp-primary btn-sm flex-fill" title="Apply filter">
                        <i class="fas fa-filter mr-1"></i> Apply
                    </button>
                    <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn-erp btn-erp-outline btn-sm" title="Reset filter">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Report Table Card --}}
    <div class="erp-card p-0">
        <div class="table-responsive">
            <table class="table table-sm table-hover erp-table mb-0">
                <thead>
                    <tr>
                        <th style="width: 45px;" class="text-center">#</th>
                        <th>Fabric name</th>
                        <th class="text-center">Purchase orders</th>
                        <th class="text-right">Meters purchased</th>
                        <th class="text-right">Avg rate</th>
                        <th class="text-center" style="width: 180px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                    @forelse($data as $fabric)
                        <tr>
                            <td class="text-center text-muted">{{ $sr++ }}</td>
                            <td><strong>{{ $fabric->name }}</strong></td>
                            <td class="text-center">
                                <span class="badge badge-light border font-weight-bold px-2">{{ $fabric->total_purchase_orders }}</span>
                            </td>
                            <td class="text-right font-weight-bold">
                                {{ number_format($fabric->total_meters, 2) }} <span class="text-muted font-weight-normal">m</span>
                            </td>
                            <td class="text-right text-success font-weight-bold">
                                ₹ {{ number_format($fabric->avg_rate, 2) }}
                            </td>
                            <td class="text-center text-nowrap">
                                <a href="{{ route('admin.report.purchase_order_fabric_wise', ['fabric_id' => $fabric->id]) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2 mr-1" style="font-size: 11px;" title="Purchase history">
                                    <i class="fas fa-history mr-1"></i> Purchases
                                </a>
                                <a href="{{ route('admin.report.purchase_order_fabric_wise_shipments', ['fabric_id' => $fabric->id]) }}" class="btn-erp btn-erp-outline btn-sm py-0 px-2" style="font-size: 11px;" title="Shipment history">
                                    <i class="fas fa-truck mr-1"></i> Shipments
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">No fabric purchase data found</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($data->hasPages())
            <div class="p-2 border-top bg-light">
                {{ $data->appends(request()->query())->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
