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

            <!-- Mobile Filters -->
            <div class="card mb-3" style="border-radius: 12px; border: 1px solid #f1f5f9;">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('owner.lots') }}">
                        <div class="mb-2">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Order No</label>
                            <select name="order_id" class="form-control lot-filter-order" style="border-radius: 8px; font-size: 13px;">
                                <option value="">Select Order</option>
                                @foreach(collect($lotNos)->unique('order_id') as $row)
                                    <option value="{{ $row['order_id'] }}" {{ request('order_id') == $row['order_id'] ? 'selected' : '' }}>
                                        {{ $row['order_no'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="small font-weight-bold text-muted mb-1 d-block">Lot No</label>
                            <select name="lot_no" class="form-control lot-filter-lot" style="border-radius: 8px; font-size: 13px;">
                                <option value="">Select Lot</option>
                                @foreach($lotNos as $row)
                                    <option value="{{ $row['lot_no'] }}" {{ request('lot_no') == $row['lot_no'] ? 'selected' : '' }}>
                                        {{ $row['lot_no'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill" style="border-radius: 8px;">Search</button>
                            <a href="{{ route('owner.lots') }}" class="btn btn-light btn-sm flex-fill" style="border-radius: 8px;">Reset</a>
                        </div>
                    </form>
                </div>
            </div>

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

        <!-- Desktop Filters -->
        <div class="card mb-4" style="border-radius: 12px; border: none; box-shadow: 0 4px 12px rgba(0,0,0,0.05);">
            <div class="card-body p-4">
                <form method="GET" action="{{ route('owner.lots') }}">
                    <div class="row align-items-end">
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-2 d-block">FILTER BY ORDER</label>
                            <select name="order_id" class="form-control lot-filter-order select2">
                                <option value="">All Orders</option>
                                @foreach(collect($lotNos)->unique('order_id') as $row)
                                    <option value="{{ $row['order_id'] }}" {{ request('order_id') == $row['order_id'] ? 'selected' : '' }}>
                                        {{ $row['order_no'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="small font-weight-bold text-muted mb-2 d-block">FILTER BY LOT NO</label>
                            <select name="lot_no" class="form-control lot-filter-lot select2">
                                <option value="">All Lots</option>
                                @foreach($lotNos as $row)
                                    <option value="{{ $row['lot_no'] }}" {{ request('lot_no') == $row['lot_no'] ? 'selected' : '' }}>
                                        {{ $row['lot_no'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary px-4 mr-2" style="border-radius: 8px; height: 38px;">Search</button>
                            <a href="{{ route('owner.lots') }}" class="btn btn-outline-secondary px-4" style="border-radius: 8px; height: 38px; line-height: 24px;">Reset</a>
                        </div>
                    </div>
                </form>
            </div>
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

@push('scripts')
<script>
    $(document).ready(function() {
        const lotData = @json($lotNos);
        const orderSelects = $('.lot-filter-order');
        const lotSelects = $('.lot-filter-lot');

        function unique(arr) {
            return [...new Set(arr)];
        }

        function fillLotDropdown(lots, $select) {
            $select.empty().append(`<option value="">All Lots</option>`);
            lots.forEach(lot => {
                $select.append(`<option value="${lot}">${lot}</option>`);
            });
            if ($select.hasClass('select2')) {
                $select.trigger('change');
            }
        }

        orderSelects.on('change', function() {
            const selectedOrderId = $(this).val();
            const parentForm = $(this).closest('form');
            const targetLotSelect = parentForm.find('.lot-filter-lot');

            if (!selectedOrderId) {
                const allLots = unique(lotData.map(i => i.lot_no));
                fillLotDropdown(allLots, targetLotSelect);
                return;
            }

            const filteredLots = lotData
                .filter(i => String(i.order_id) === String(selectedOrderId))
                .map(i => i.lot_no);

            fillLotDropdown(unique(filteredLots), targetLotSelect);
        });

        // Initialize Select2 if exists
        if ($.fn.select2) {
            $('.select2').select2({
                placeholder: "Select an option",
                allowClear: true
            });
        }
    });
</script>
@endpush
