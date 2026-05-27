@extends('owner.layouts.app')

@section('content')
    <style>
        /* APP LIST STYLES */
        .app-container {
                padding: 15px;
            }

            .order-card {
                background: white;
                border: 1px solid #f1f5f9;
                border-radius: 16px;
                padding: 18px;
                margin-bottom: 16px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
                transition: transform 0.2s ease;
            }

            .order-card:active {
                transform: scale(0.98);
                background: #fdfdfd;
            }

            .card-header-top {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 15px;
            }

            .sku-label {
                font-size: 15px;
                font-weight: 800;
                color: #1e293b;
            }

            .status-pill {
                padding: 4px 10px;
                border-radius: 20px;
                font-size: 10px;
                font-weight: 800;
                letter-spacing: 0.5px;
            }

            .sp-active {
                background: #dcfce7;
                color: #166534;
            }

            .sp-closed {
                background: #f1f5f9;
                color: #64748b;
            }

            .sp-partial {
                background: #fef9c3;
                color: #854d0e;
            }

            .card-grid {
                display: grid;
                grid-template-columns: 1.2fr 1fr;
                gap: 15px;
                padding-bottom: 15px;
                border-bottom: 1px solid #f1f5f9;
                margin-bottom: 15px;
            }

            .info-item label {
                display: block;
                font-size: 10px;
                color: #94a3b8;
                text-transform: uppercase;
                font-weight: 700;
                margin-bottom: 2px;
            }

            .info-item .value {
                font-size: 14px;
                font-weight: 700;
                color: #334155;
            }

            .progress-value {
                color: var(--primary);
                font-weight: 900;
                font-size: 13px;
            }

            .card-action {
                display: flex;
                justify-content: space-between;
                align-items: center;
            }

            .date-text {
                font-size: 11px;
                color: #94a3b8;
                font-weight: 600;
            }

            .btn-view-app {
                background: var(--primary);
                color: white !important;
                padding: 8px 16px;
                border-radius: 10px;
                font-size: 12px;
                font-weight: 700;
                display: flex;
                align-items: center;
                gap: 6px;
                text-decoration: none !important;
                box-shadow: 0 4px 10px rgba(111, 66, 193, 0.2);
            }
        /* DESKTOP STYLES */
        @media (min-width: 992px) {
            .desktop-p {
                padding: 25px;
            }

            .table-card {
                background: white;
                border-radius: 12px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
                border: none;
            }
        }
    </style>

    <!-- MOBILE CONTENT -->
    <div class="mobile-only">
        <div class="app-container" style="padding-top: 20px;">
            <h5 class="mb-4 font-weight-bold" style="color: #1e293b;">Order Summary</h5>

            <!-- Mobile Filters -->
            <div class="card mb-3" style="border-radius: 12px; border: 1px solid #f1f5f9;">
                <div class="card-body p-3">
                    <form action="{{ route('owner.order-summary.index') }}" method="GET">
                        <div class="mb-2">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Order SKU</label>
                            <input type="text" name="order_no" class="form-control" style="border-radius: 8px; font-size: 13px;"
                                placeholder="Search Order SKU..." value="{{ request('order_no') }}">
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Customer</label>
                            <select name="customer_id" class="form-control" style="border-radius: 8px; font-size: 13px;">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="border-radius: 8px;">Search</button>
                            <a href="{{ route('owner.order-summary.index') }}" class="btn btn-light btn-sm flex-fill" style="border-radius: 8px;">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

            @foreach ($salesOrders as $row)
                <div class="order-card">
                    <div class="card-header-top">
                        <div class="sku-label">#{{ $row['order_no'] }}</div>
                        @php
                            $remaining = $row['total_pcs'] - $row['scanned_pcs'];
                        @endphp
                        <div
                            class="status-pill 
                                            @if($remaining <= 0) sp-closed @elseif($row['scanned_pcs'] > 0) sp-partial @else sp-active @endif">
                            @if($remaining <= 0) COMPLETED @elseif($row['scanned_pcs'] > 0) PARTIAL @else ACTIVE @endif
                        </div>
                    </div>

                    <div class="card-grid">
                        <div class="info-item">
                            <label>Customer</label>
                            <div class="value">{{ \Illuminate\Support\Str::limit($row['customer'], 20) }}</div>
                        </div>
                        <div class="info-item">
                            <label>Quantity</label>
                            <div class="value">{{ $row['total_pcs'] }} Pcs</div>
                        </div>
                    </div>

                    <div class="card-action">
                        <div class="date-text">
                            <i class="far fa-calendar-alt"></i> {{ date('d M Y', strtotime($row['created_at'])) }}
                        </div>
                        <a href="{{ route('owner.order-summary.view', $row['id']) }}" class="btn-view-app">
                            Details <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach

            <div class="pagination-wrapper mt-4">
                {{ $salesOrders->links() }}
            </div>
        </div>
    </div>

    <!-- DESKTOP CONTENT -->
    <div class="desktop-only desktop-p">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-weight: 800; color: var(--text-main);">Order Manifest Reports</h2>
        </div>

        <!-- Desktop Filters -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <div class="card-body p-4">
                <form action="{{ route('owner.order-summary.index') }}" method="GET">
                    <div class="row align-items-end">
                        <div class="col-md-5">
                            <label class="small font-weight-bold text-muted mb-2 d-block">ORDER SKU</label>
                            <input type="text" name="order_no" class="form-control" value="{{ request('order_no') }}" placeholder="Enter SKU..." style="height: 38px;">
                        </div>
                        <div class="col-md-5">
                            <label class="small font-weight-bold text-muted mb-2 d-block">CUSTOMER</label>
                            <select name="customer_id" class="form-control select2">
                                <option value="">All Customers</option>
                                @foreach($customers as $customer)
                                    <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                        {{ $customer->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary px-4 mr-2" style="border-radius: 8px; height: 38px;">Search</button>
                            <a href="{{ route('owner.order-summary.index') }}" class="btn btn-outline-secondary px-4" style="border-radius: 8px; height: 38px; line-height: 24px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="bg-light">
                            <tr>
                                <th>Date</th>
                                <th>Order No</th>
                                <th>Customer</th>
                                <th>Total Pcs</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($salesOrders as $row)
                                <tr>
                                    <td>{{ date('d-m-Y', strtotime($row['created_at'])) }}</td>
                                    <td><b>{{ $row['order_no'] }}</b></td>
                                    <td>{{ $row['customer'] }}</td>
                                    <td>{{ $row['total_pcs'] }}</td>
                                    <td>
                                        @php
                                            $remaining = $row['total_pcs'] - $row['scanned_pcs'];
                                        @endphp
                                        @if($remaining <= 0)
                                            <span class="badge badge-success">Completed</span>
                                        @elseif($row['scanned_pcs'] > 0)
                                            <span class="badge badge-warning">Partial</span>
                                        @else
                                            <span class="badge badge-primary">In Progress</span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('owner.order-summary.view', $row['id']) }}"
                                            class="btn btn-sm btn-primary" style="border-radius: 6px;">
                                            View Manifest
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $salesOrders->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            if ($.fn.select2) {
                $('.select2').select2({
                    placeholder: "Select Customer",
                    allowClear: true
                });
            }
        });
    </script>
@endsection
