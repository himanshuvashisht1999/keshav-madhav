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
            font-size: 13px;
        }

        .order-row-group {
            background: #f1f5f9;
            font-weight: 800;
            color: #1e3a8a;
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

            .mobile-order-list {
                padding-top: 10px;
            }

            .order-card {
                background: white;
                border-radius: 15px;
                padding: 15px;
                margin-bottom: 15px;
                box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
            }

            .order-card-header {
                border-bottom: 1px dashed #eee;
                padding-bottom: 8px;
                margin-bottom: 8px;
                display: flex;
                justify-content: space-between;
            }

            .lot-item {
                background: #f8fafc;
                border-radius: 8px;
                padding: 8px 12px;
                margin-bottom: 5px;
                font-size: 12px;
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .status-pill {
                padding: 2px 8px;
                border-radius: 10px;
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
            }
        }
    </style>

    <div class="report-header">
        <div>
            <h3>Detailed Sales Report</h3>
        </div>
        <div class="desktop-only text-muted">Generated: {{ now()->format('d M Y') }}</div>
    </div>

    <div class="card report-card">
        <div class="card-body">
            <!-- FILTERS -->
            <form method="GET" action="{{ route('owner.orders') }}" class="mb-4">
                <div class="row g-2">
                    <div class="col-md-3 mb-2">
                        <label class="text-xs font-weight-bold">Order No</label>
                        <input type="text" name="order_no" class="form-control form-control-sm" placeholder="SKU..."
                            value="{{ $filters['order_no'] ?? '' }}">
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="text-xs font-weight-bold">Customer</label>
                        <select name="customer_id" class="form-control form-control-sm select2">
                            <option value="">All Customers</option>
                            @foreach($customers as $c)
                                <option value="{{ $c->id }}" {{ ($filters['customer_id'] ?? '') == $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="text-xs font-weight-bold">Status</label>
                        <select name="delay_status" class="form-control form-control-sm">
                            <option value="">All Status</option>
                            <option value="Yes" {{ ($filters['delay_status'] ?? '') == 'Yes' ? 'selected' : '' }}>Delayed
                            </option>
                            <option value="No" {{ ($filters['delay_status'] ?? '') == 'No' ? 'selected' : '' }}>On Track
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 mb-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary btn-sm w-100">Apply</button>
                        <a href="{{ route('owner.orders') }}" class="btn btn-link btn-sm ml-1">Reset</a>
                    </div>
                </div>
            </form>

            <!-- DESKTOP TABLE -->
            <div class="desktop-only">
                <div class="table-responsive">
                    <table class="table table-bordered table-report">
                        <thead>
                            <tr>
                                <th>Order Date</th>
                                <th>Customer</th>
                                <th>Order SKU</th>
                                <th>Lot No</th>
                                <th class="text-center">Order Qty</th>
                                <th class="text-center">Lot Qty</th>
                                <th>Current Stage</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $sku => $lots)
                                @foreach($lots as $idx => $lot)
                                    <tr>
                                        @if($idx === 0)
                                            <td rowspan="{{ count($lots) }}">{{ date('d M, Y', strtotime($lot['order_date'])) }}</td>
                                            <td rowspan="{{ count($lots) }}">{{ $lot['customer'] }}</td>
                                            <td rowspan="{{ count($lots) }}"><b>{{ $sku }}</b></td>
                                            <td rowspan="{{ count($lots) }}" class="text-center font-weight-bold">
                                                {{ $lot['total_pcs_in_order'] }}</td>
                                        @endif
                                        <td class="text-primary font-weight-bold">{{ $lot['lot_no'] }}</td>
                                        <td class="text-center">{{ $lot['pieces_in_lot'] }}</td>
                                        <td><span class="badge bg-light border">{{ $lot['stage_name'] }}</span></td>
                                        <td class="text-center">
                                            @if($lot['isDelayed'] == 'Yes')
                                                <span class="badge bg-danger">DELAYED</span>
                                            @else
                                                <span class="badge bg-success">ON TRACK</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted">No orders matching filters found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MOBILE LIST -->
            <div class="mobile-only mobile-order-list">
                @forelse($orders as $sku => $lots)
                    <div class="order-card">
                        <div class="order-card-header">
                            <span class="font-weight-bold text-primary">{{ $sku }}</span>
                            <span class="text-xs text-muted">{{ date('d M', strtotime($lots[0]['order_date'])) }}</span>
                        </div>
                        <div class="mb-2 text-sm"><b>{{ $lots[0]['customer'] }}</b></div>
                        <div class="text-xs text-muted mb-2">Total Order: {{ $lots[0]['total_pcs_in_order'] }} Pcs</div>

                        @foreach($lots as $lot)
                            <div class="lot-item"
                                onclick="window.location.href='{{ route('owner.lot-details', ['lot_no' => $lot['lot_no']]) }}'">
                                <div>
                                    <span class="font-weight-bold">#{{ $lot['lot_no'] }}</span>
                                    <span class="text-muted ml-2">({{ $lot['pieces_in_lot'] }} Pcs)</span>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="status-pill {{ $lot['isDelayed'] == 'Yes' ? 'bg-danger text-white' : 'bg-success text-white' }}">
                                        {{ $lot['stage_name'] }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @empty
                    <div class="text-center p-4 text-muted">No orders found.</div>
                @endforelse
            </div>
        </div>
    </div>

@endsection