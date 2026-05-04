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
            white-space: nowrap;
            vertical-align: middle;
        }

        .fabric-cell {
            background: #f8f9fa;
            font-weight: 600;
        }
    </style>

    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="report-header">
                    <div>
                        <div class="report-meta">Report No : POFW-1</div>
                    </div>
                    <div>
                        <h3>Purchase Order Fabric Wise Report</h3>
                    </div>
                    <div class="report-meta">
                        Date : {{ now()->format('d M Y h:i A') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                {{-- ================= FILTERS ================= --}}
                <div class="card mb-3">
                    <div class="card-body">
                        <form method="GET" action="{{ route('admin.report.purchase_order_fabric_wise') }}">
                            <div class="row g-2 align-items-end">
                                <div class="col-md-4">
                                    <label class="form-label">Search Fabric</label>
                                    <input type="text" name="search" class="form-control" placeholder="Search by fabric name..."
                                        value="{{ $filters['search'] ?? '' }}">
                                </div>
                                <div class="col-md-3 d-flex gap-1">
                                    <button class="btn btn-primary flex-fill">
                                        <i class="fas fa-filter"></i> Apply
                                    </button>
                                    <a href="{{ route('admin.report.purchase_order_fabric_wise') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-undo"></i>
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- ================= TABLE ================= --}}
                <div class="card report-card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-report">
                                <thead>
                                    <tr>
                                        <th width="50">#</th>
                                        <th>Fabric Name</th>
                                        <th class="text-center">Total Purchase Orders</th>
                                        <th class="text-end">Total Meters Purchased</th>
                                        <th class="text-end">Avg. Rate</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $sr = ($data->currentPage() - 1) * $data->perPage() + 1; @endphp
                                    @forelse($data as $fabric)
                                        <tr>
                                            <td>{{ $sr++ }}</td>
                                            <td class="fabric-cell">{{ $fabric->name }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $fabric->total_purchase_orders }}</span>
                                            </td>
                                            <td class="text-end fw-bold">{{ number_format($fabric->total_meters, 2) }}</td>
                                            <td class="text-end">₹ {{ number_format($fabric->avg_rate, 2) }}</td>
                                            <td class="text-center">
                                                <a href="{{ route('admin.report.purchase_order_fabric_wise', ['fabric_id' => $fabric->id]) }}"
                                                    class="btn btn-sm btn-outline-primary" title="Purchase History">
                                                    <i class="fas fa-history"></i> Purchase
                                                </a>
                                                <a href="{{ route('admin.report.purchase_order_fabric_wise_shipments', ['fabric_id' => $fabric->id]) }}"
                                                    class="btn btn-sm btn-outline-info" title="Shipment History">
                                                    <i class="fas fa-truck"></i> Shipments
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-4">
                                                No fabric purchase data found
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-3">
                            {{ $data->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection
