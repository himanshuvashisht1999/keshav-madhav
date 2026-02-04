@extends('owner.layouts.app')

@section('content')
    <style>
        /* ===== COMMON STYLE ===== */
        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            background: white;
            border-bottom: 1px solid #eee;
        }

        .report-header h3 {
            font-weight: 700;
            margin: 0;
            color: #1e3a8a;
        }

        .report-card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, .05);
            border: none;
            margin: 0 20px;
        }

        .table-report thead th {
            background: #1e3a8a;
            color: #fff !important;
            font-weight: 600;
            white-space: nowrap;
            vertical-align: middle;
            padding: 12px;
        }

        .table-report tbody td {
            vertical-align: middle;
            font-size: 14px;
            padding: 12px;
        }

        /* MOBILE RESPONSIVE TWEAKS */
        @media (max-width: 991.98px) {
            .report-header {
                background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
                color: white;
                border: none;
            }

            .report-header h3 {
                color: white;
            }

            .report-card {
                margin: -30px 15px 20px;
            }

            .mobile-lot-list {
                padding-top: 10px;
            }

            .lot-card {
                background: white;
                border-radius: 15px;
                padding: 15px;
                margin-bottom: 12px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            .lot-card-title {
                font-weight: 800;
                color: #1e3a8a;
                display: flex;
                justify-content: space-between;
                margin-bottom: 5px;
            }

            .lot-card-qty {
                background: #e0f2fe;
                color: #0369a1;
                padding: 2px 10px;
                border-radius: 20px;
                font-size: 12px;
                font-weight: 700;
            }

            .lot-card-meta {
                font-size: 12px;
                color: #64748b;
            }
        }
    </style>

    <div class="report-header">
        <div>
            <h3>Production Lot History</h3>
        </div>
        <div class="desktop-only text-muted">Total Lots: {{ $lots->total() }}</div>
    </div>

    <div class="card report-card">
        <div class="card-body">
            <!-- FILTERS -->
            <form method="GET" action="{{ route('owner.lots') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-4 mb-2">
                        <label class="text-xs font-weight-bold">Order No</label>
                        <select name="order_id" class="form-control form-control-sm select2">
                            <option value="">All Orders</option>
                            @foreach(collect($lotNos)->unique('order_id') as $row)
                                <option value="{{ $row['order_id'] }}" {{ request('order_id') == $row['order_id'] ? 'selected' : '' }}>
                                    {{ $row['order_no'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-xs font-weight-bold">Lot No</label>
                        <input type="text" name="lot_no" class="form-control form-control-sm" placeholder="Search Lot #"
                            value="{{ request('lot_no') }}">
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Search</button>
                    </div>
                </div>
            </form>

            <!-- DESKTOP TABLE -->
            <div class="desktop-only">
                <div class="table-responsive">
                    <table class="table table-bordered table-report">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Lot No</th>
                                <th>Order No</th>
                                <th>Customer Name</th>
                                <th class="text-right">Lot Quantity</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($lots as $index => $row)
                                <tr>
                                    <td class="text-center">{{ $lots->firstItem() + $index }}</td>
                                    <td class="font-weight-bold text-primary">{{ $row['lot_no'] }}</td>
                                    <td>{{ $row['order_no'] }}</td>
                                    <td>{{ $row['customer_name'] }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($row['lot_quantity']) }} Pcs</td>
                                    <td class="text-center">
                                        <a href="{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}"
                                            class="btn btn-xs btn-outline-primary">
                                            View Tracking
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted">No lots found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MOBILE LIST -->
            <div class="mobile-only mobile-lot-list">
                @forelse($lots as $row)
                    <div class="lot-card"
                        onclick="window.location.href='{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}'">
                        <div class="lot-card-title">
                            <span>Lot #{{ $row['lot_no'] }}</span>
                            <span class="lot-card-qty">{{ number_format($row['lot_quantity']) }} Pcs</span>
                        </div>
                        <div class="lot-card-meta mt-1">
                            <div><i class="fas fa-shopping-bag mr-1"></i> {{ $row['order_no'] }}</div>
                            <div class="mt-1"><i class="fas fa-user mr-1"></i> {{ $row['customer_name'] }}</div>
                        </div>
                        <div class="text-right mt-2">
                            <span class="text-xs text-primary">View Deep Tracking <i
                                    class="fas fa-chevron-right ml-1"></i></span>
                        </div>
                    </div>
                @empty
                    <div class="text-center p-4 text-muted">No lots found.</div>
                @endforelse
            </div>
        </div>
    </div>

    <div class="p-4 d-flex justify-content-center">
        {{ $lots->links() }}
    </div>

@endsection