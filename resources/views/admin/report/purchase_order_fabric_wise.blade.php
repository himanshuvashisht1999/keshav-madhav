@extends('admin.layouts.app')

@section('content')
    <style>
        .page-header-compact {
            padding: 1rem 0;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1rem;
        }
        .page-title-compact {
            font-size: 1.25rem;
            font-weight: 700;
            margin: 0;
            color: #1e293b;
        }
        .table-premium th {
            background-color: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 700;
            color: #475569;
            white-space: nowrap;
            padding: 0.75rem 1rem;
            border-bottom: 1px solid #e2e8f0;
            border-top: none;
        }
        .table-premium td {
            vertical-align: middle;
            padding: 0.75rem 1rem;
            font-size: 0.875rem;
            color: #1e293b;
            border-bottom: 1px solid #f1f5f9;
        }
        .filter-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            margin-bottom: 1rem;
        }
        .table-card {
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            background: #fff;
            overflow: hidden;
        }
        .btn-action {
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>

    <div class="content-wrapper" style="background-color: #f8fafc;">
        <div class="page-header-compact">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <h1 class="page-title-compact">Fabric Wise Purchase Report</h1>
                    <div class="text-muted font-weight-bold" style="font-size: 0.85rem;">
                        Report: <span class="text-dark">POFW-1</span> <span class="mx-2 font-weight-normal">|</span> <span class="text-dark">{{ now()->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <div class="filter-card bg-white p-3">
                    <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise') }}" class="mb-0">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted mb-1 text-uppercase">Search Fabric</label>
                                <div class="input-group input-group-sm">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-light border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" name="search" class="form-control border-left-0" style="font-size: 0.85rem;" placeholder="Search by fabric name..." value="{{ $filters['search'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <button class="btn btn-primary btn-sm px-3 shadow-sm mr-2" style="font-weight: 600;">
                                    Apply Filter
                                </button>
                                <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn btn-light btn-sm border shadow-sm" title="Reset Filters">
                                    <i class="fas fa-undo text-muted"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>

                <div class="table-card">
                    <div class="table-responsive">
                        <table class="table table-hover table-premium mb-0">
                            <thead>
                                <tr>
                                    <th width="50" class="text-center">#</th>
                                    <th>Fabric Name</th>
                                    <th class="text-center">Purchase Orders</th>
                                    <th class="text-right">Meters Purchased</th>
                                    <th class="text-right">Avg. Rate</th>
                                    <th class="text-right" style="padding-right: 1.5rem;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                                @forelse($data as $fabric)
                                    <tr>
                                        <td class="text-center text-muted">{{ $sr++ }}</td>
                                        <td class="font-weight-bold text-dark">{{ $fabric->name }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-primary px-2 py-1 shadow-sm" style="border-radius: 4px; font-size: 0.75rem;">{{ $fabric->total_purchase_orders }}</span>
                                        </td>
                                        <td class="text-right font-weight-bold text-dark">
                                            {{ number_format($fabric->total_meters, 2) }} <span class="text-muted font-weight-normal">m</span>
                                        </td>
                                        <td class="text-right text-success font-weight-bold">
                                            ₹ {{ number_format($fabric->avg_rate, 2) }}
                                        </td>
                                        <td class="text-right" style="white-space: nowrap; padding-right: 1.5rem;">
                                            <a href="{{ route('admin.report.purchase_order_fabric_wise', ['fabric_id' => $fabric->id]) }}" class="btn btn-outline-primary btn-action mr-1" title="Purchase History">
                                                <i class="fas fa-history mr-1"></i> Purchases
                                            </a>
                                            <a href="{{ route('admin.report.purchase_order_fabric_wise_shipments', ['fabric_id' => $fabric->id]) }}" class="btn btn-outline-info btn-action" title="Shipment History">
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
                    <div class="card-footer bg-white border-top p-2">
                        {{ $data->appends(request()->query())->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </section>
    </div>
@endsection
