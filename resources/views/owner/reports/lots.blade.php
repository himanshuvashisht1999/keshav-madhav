@extends('owner.layouts.app')

@section('content')
    <style>
        /* MOBILE APP STYLES */
        @media (max-width: 991.98px) {
            .app-container {
                padding: 15px;
            }

            .lot-card {
                background: white;
                border: 1px solid #f1f5f9;
                border-radius: 16px;
                padding: 18px;
                margin-bottom: 16px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
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
                background: #eff6ff;
                color: #2563eb;
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

            .qty-value {
                color: var(--primary);
                font-weight: 900;
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

            .btn-track-app {
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

            .table thead th {
                background: #f8fafc;
                border-top: none;
                color: #64748b;
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.5px;
            }
        }
    </style>

    <!-- MOBILE CONTENT -->
    <div class="mobile-only">
        <div class="app-container" style="padding-top: 20px;">
            <h5 class="mb-4 font-weight-bold" style="color: #1e293b;">Production Lots</h5>

            @foreach ($lots as $row)
                <div class="lot-card">
                    <div class="card-header-top">
                        <div class="sku-label">Lot #{{ $row['lot_no'] }}</div>
                        <div class="status-pill">ACTIVE LOT</div>
                    </div>

                    <div class="card-grid">
                        <div class="info-item">
                            <label>Order SKU</label>
                            <div class="value">{{ $row['order_no'] }}</div>
                        </div>
                        <div class="info-item">
                            <label>Customer</label>
                            <div class="value">{{ \Illuminate\Support\Str::limit($row['customer_name'], 15) }}</div>
                        </div>
                        <div class="info-item">
                            <label>Quantity</label>
                            <div class="value qty-value">{{ number_format($row['lot_quantity']) }} Pcs</div>
                        </div>
                        <div class="info-item">
                            <label>Date</label>
                            <div class="value" style="font-size: 12px;">{{ $row['date'] }}</div>
                        </div>
                    </div>

                    <div class="card-action">
                        <div class="date-text">
                            <i class="fas fa-barcode"></i> Tracking Active
                        </div>
                        <a href="{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}" class="btn-track-app">
                            Track <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
            @endforeach

            <div class="pagination-wrapper mt-4">
                {{ $lots->links() }}
            </div>
        </div>
    </div>

    <!-- DESKTOP CONTENT -->
    <div class="desktop-only desktop-p">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 style="font-weight: 800; color: var(--text-main);">Production Lot Tracking</h2>
        </div>

        <div class="card table-card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Lot No</th>
                                <th>Order No</th>
                                <th>Customer</th>
                                <th class="text-right">Quantity</th>
                                <th class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($lots as $row)
                                <tr>
                                    <td>{{ $row['date'] }}</td>
                                    <td class="font-weight-bold text-primary">#{{ $row['lot_no'] }}</td>
                                    <td>{{ $row['order_no'] }}</td>
                                    <td>{{ $row['customer_name'] }}</td>
                                    <td class="text-right font-weight-bold">{{ number_format($row['lot_quantity']) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('owner.lot-details', ['lot_no' => $row['lot_no']]) }}"
                                            class="btn btn-sm btn-outline-primary" style="border-radius: 6px;">
                                            Track Progress
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $lots->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection