@extends('owner.layouts.app')

@section('content')
@section('styles')
    <style>
        :root {
            --slate-50: #f8fafc;
            --slate-100: #f1f5f9;
            --slate-200: #e2e8f0;
            --slate-300: var(--text-muted);
            --slate-600: var(--text-main);
            --slate-700: var(--text-main);
            --slate-800: var(--text-main);
            --slate-900: var(--text-main);
            --navy-500: var(--text-main);
            --navy-600: #1e40af;
            --navy-700: #1d4ed8;
            --local-navy: var(--text-main);
            --KM-purple: var(--text-main);
        }

        body {
            background-color: #f4f7fa;
            color: var(--slate-800);
        }

        /* APP STYLES */
            .app-header {
                padding: 24px 20px;
                background: white;
                border-bottom: 2px solid var(--KM-purple);
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .tab-nav-container {
                padding: 12px 20px;
                background: white;
                display: flex;
                gap: 12px;
                overflow-x: auto;
                scrollbar-width: none;
                border-bottom: 1px solid var(--slate-200);
            }

            .tab-pill {
                padding: 8px 16px;
                border-radius: 20px;
                font-size: 13px;
                font-weight: 700;
                color: var(--slate-600);
                background: var(--slate-100);
                white-space: nowrap;
                transition: all 0.2s ease;
                border: 1px solid transparent;
            }

            .tab-pill.active {
                background: var(--KM-purple);
                color: white;
            }

            .content-card {
                background: white;
                border-radius: 16px;
                margin: 20px;
                padding: 24px;
                box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
                border: 1px solid var(--slate-200);
            }

            .manifest-item {
                padding: 16px 0;
                border-bottom: 1px solid var(--slate-50);
            }

            .manifest-item:last-child {
                border-bottom: none;
            }

            .manifest-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 12px;
                margin-top: 12px;
            }

            .manifest-stat {
                background: var(--slate-50);
                border-radius: 12px;
                padding: 10px;
                text-align: center;
                border: 1px solid var(--slate-100);
            }
        @media (min-width: 992px) {
            .desktop-p {
                padding: 40px;
                max-width: 1200px;
                margin: 0 auto;
            }

            .card {
                border-radius: 16px;
                border: 1px solid var(--slate-200);
                box-shadow: 0 10px 30px rgba(15, 23, 42, 0.04);
                overflow: hidden;
                background: white;
            }

            .card-header {
                background: white !important;
                border-bottom: 1px solid var(--slate-100);
                padding: 24px 30px;
                border-top: 3px solid var(--KM-purple);
            }

            .card-title {
                color: var(--slate-900) !important;
                font-weight: 800;
                font-size: 1.5rem;
            }

            .nav-tabs-modern {
                display: flex;
                border-bottom: 1px solid var(--slate-200);
                padding: 0 30px;
                background: white;
            }

            .nav-tabs-modern .nav-item {
                margin-right: 40px;
            }

            .nav-tabs-modern .nav-link {
                padding: 20px 0;
                border: none;
                color: var(--slate-600);
                font-weight: 700;
                font-size: 14px;
                position: relative;
                transition: color 0.3s ease;
                background: transparent;
            }

            .nav-tabs-modern .nav-link:hover {
                color: var(--KM-purple);
            }

            .nav-tabs-modern .nav-link.active {
                color: var(--KM-purple);
                background: transparent;
            }

            .nav-tabs-modern .nav-link.active::after {
                content: '';
                position: absolute;
                bottom: -1px;
                left: 0;
                width: 100%;
                height: 3px;
                background: var(--KM-purple);
                border-radius: 3px 3px 0 0;
            }

            .table thead th {
                background: var(--slate-50);
                border-top: none;
                border-bottom: 1px solid var(--slate-200);
                text-transform: uppercase;
                font-size: 11px;
                letter-spacing: 0.05em;
                font-weight: 800;
                color: var(--slate-600);
                padding: 16px 20px;
            }

            .table td {
                padding: 18px 20px;
                vertical-align: middle;
                border-bottom: 1px solid var(--slate-50);
            }

            .badge-custom {
                padding: 6px 12px;
                border-radius: 6px;
                font-weight: 700;
                font-size: 11px;
                text-transform: uppercase;
            }
        }
    </style>
@endsection

    <!-- MOBILE CONTENT -->
    <div class="mobile-only">
        <div class="app-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold" style="color: var(--slate-900); margin: 0; letter-spacing: -0.5px;">Order Summary</h5>
                <a href="{{ route('owner.order-summary.pdf', ['id' => $data['order']->id]) }}" class="text-dark">
                    <i class="fas fa-file-pdf fa-lg"></i>
                </a>
            </div>
            <div class="mt-1" style="font-size: 13px; font-weight: 700; color: var(--slate-600);">SKU: {{ $data['order']->sku }}</div>
        </div>

        <div class="tab-nav-container">
            <div class="tab-pill active" onclick="toggleSection('manifest')">Manifest</div>
            <div class="tab-pill" onclick="toggleSection('history')">History</div>
            <div class="tab-pill" onclick="toggleSection('packing')">Packing</div>
            <div class="tab-pill" onclick="toggleSection('dispatch')">Dispatch</div>
        </div>

        <!-- Manifest Section -->
        <div id="manifest-section" class="content-card">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h6 class="font-weight-bold mb-0" style="color: var(--slate-900);">Product Details</h6>
            </div>
            @foreach ($data['order']->OrderProductSets as $set)
                <div class="manifest-item">
                    <div class="d-flex justify-content-between">
                        <div style="font-weight: 800; color: var(--slate-900); font-size: 15px;">{{ $set->design_number }}</div>
                        <div class="text-muted font-weight-bold" style="font-size: 12px;">BC: {{ $set->bar_code ?? '-' }}</div>
                    </div>
                    <div class="text-xs font-weight-bold mt-1" style="color: var(--slate-600);">
                        {{ $set->colors->name ?? 'N/A' }} <span class="mx-1 text-muted">|</span> {{ $set->fabric->sku ?? ($set->fabric->name ?? 'N/A') }}
                    </div>
                    <div class="text-xs text-muted mt-1 font-weight-bold">
                        SIZE: {{ $set->size_measurement->name ?? '-' }} ({{ $set->size_measurement->size_group ?? '' }})
                    </div>
                    <div class="text-xs text-muted mt-1 font-weight-bold">
                        FITTING: {{ $set->master_product_fitting->name ?? '-' }} <span class="mx-1">|</span> PATTERN: {{ $set->master_design_pattern->name ?? '-' }}
                    </div>
                    <div class="manifest-grid" style="grid-template-columns: 1fr;">
                        <div class="manifest-stat">
                            <div style="font-size: 9px; color: var(--slate-600); font-weight: 800; text-transform: uppercase;">Total Ordered Quantity</div>
                            <div style="font-size: 16px; font-weight: 900; color: var(--slate-900);">{{ $set->total_quantity ?? ($set->no_of_pcs ?? 0) }} Pcs</div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- History/Lots Section -->
        <div id="history-section" class="content-card" style="display: none;">
            <h6 class="font-weight-bold mb-4" style="color: var(--slate-900);">Lots Details</h6>
            @forelse($data['lotsData'] as $lot)
                <div class="manifest-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-weight: 800; color: var(--slate-900);">Lot: {{ $lot['lot_no'] }}</span>
                        <span class="badge badge-dark px-3 py-2" style="border-radius: 8px;">{{ $lot['lot_quantity'] }} Pcs</span>
                    </div>
                    <div class="text-xs font-weight-bold mt-2" style="color: var(--slate-600);">Ref: {{ $lot['order_no'] }} <br> Customer: {{ $data['order']->customer->name ?? 'N/A' }} <br> Stage: {{ $lot['last_current_stage'] ?? 'N/A' }}</div>
                </div>
            @empty
                <div class="text-center py-5 text-muted font-weight-bold">No Lot data available</div>
            @endforelse
        </div>

        <!-- Packing Section -->
        <div id="packing-section" class="content-card" style="display: none;">
            <h6 class="font-weight-bold mb-4" style="color: var(--slate-900);">Packing Details</h6>
            @forelse($data['cartons'] as $carton)
                <div class="manifest-item">
                    <div class="d-flex justify-content-between align-items-center">
                        <span style="font-weight: 800; color: var(--slate-900);">Carton #{{ $carton->carton_no }}</span>
                        <span class="text-xs font-weight-bold {{ $carton->status == 2 ? 'text-success' : 'text-primary' }}">
                            {{ $carton->status == 2 ? 'DISPATCHED' : 'PACKED' }}
                        </span>
                    </div>
                    <div class="text-xs font-weight-bold mt-2" style="color: var(--slate-600); line-height: 1.5;">
                        @php
                            $summary = [];
                            foreach ($carton->items as $item) {
                                $name = $item->detail->size ?? ($item->size_id ?? 'Item');
                                if (!isset($summary[$name])) $summary[$name] = 0;
                                $summary[$name] += $item->quantity;
                            }
                            $text = [];
                            foreach ($summary as $k => $v) $text[] = "$k ($v)";
                        @endphp
                        {{ implode(', ', $text) }}
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted font-weight-bold">No Packing data available</div>
            @endforelse
        </div>

        <!-- Dispatch Section -->
        <div id="dispatch-section" class="content-card" style="display: none;">
            <h6 class="font-weight-bold mb-4" style="color: var(--slate-900);">Dispatch History</h6>
            @forelse($data['dispatches'] as $dispatch)
                <div class="manifest-item">
                    <div style="font-weight: 800; color: var(--slate-900);">{{ $dispatch->sku }}</div>
                    <div class="text-xs font-weight-bold mt-2" style="color: var(--slate-600);">
                        DATE: {{ date('d M, Y', strtotime($dispatch->dispatch_date)) }}<br>
                        STATUS: {{ $dispatch->status == 2 ? 'COMPLETE' : 'PROCESSING' }}
                    </div>
                </div>
            @empty
                <div class="text-center py-5 text-muted font-weight-bold">No Dispatch history</div>
            @endforelse
        </div>
    </div>

    <!-- DESKTOP CONTENT -->
    <div class="desktop-only desktop-p">
        <!-- Header Section -->
        <div class="d-flex justify-content-between align-items-end mb-4">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb bg-transparent p-0 mb-2">
                        <li class="breadcrumb-item"><a href="#" class="text-muted small font-weight-bold">REPORTS</a></li>
                        <li class="breadcrumb-item active small font-weight-bold text-dark" aria-current="page">ORDER SUMMARY</li>
                    </ol>
                </nav>
                <h3 class="font-weight-bold text-dark mb-0" style="letter-spacing: -1px;">Order Summary: {{ $data['order']->sku }}</h3>
            </div>
            <a href="{{ route('owner.order-summary.pdf', ['id' => $data['order']->id]) }}" class="btn btn-secondary px-4 py-2 shadow-sm font-weight-bold" style="border-radius: 6px; background: #6c757d;">
                <i class="fas fa-arrow-left mr-2"></i> Back to Report
            </a>
        </div>

        <!-- Info Grid -->
        <div class="card mb-4">
            <div class="card-header bg-white py-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold text-dark mb-0">Order Information</h5>
                    <a href="{{ route('owner.order-summary.pdf', ['id' => $data['order']->id]) }}" class="btn btn-outline-info btn-sm">
                        <i class="fas fa-file-pdf mr-1"></i> PDF
                    </a>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-2">
                        <div class="small text-muted font-weight-bold text-uppercase mb-1">Customer</div>
                        <div class="font-weight-bold text-dark">{{ $data['order']->customer->name ?? 'N/A' }}</div>
                    </div>
                    <div class="col-md-2">
                        <div class="small text-muted font-weight-bold text-uppercase mb-1">Order Date</div>
                        <div class="font-weight-bold text-dark">{{ date('d M, Y', strtotime($data['order']->created_at)) }}</div>
                    </div>
                    <div class="col-md-3">
                        <div class="small text-muted font-weight-bold text-uppercase mb-1">Expected Delivery Date</div>
                        <div class="font-weight-bold text-dark">{{ date('d M, Y', strtotime($data['order']->expected_delivery_date ?? $data['order']->created_at)) }}</div>
                    </div>

                    <div class="col-md-2">
                        <div class="small text-muted font-weight-bold text-uppercase mb-1">Order File</div>
                        <div class="font-weight-bold text-dark">-</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Card -->
        <div class="card mb-5">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="font-weight-bold text-dark mb-0">Products Details</h5>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th class="pl-4">Barcode</th>
                            <th>Design No.</th>
                            <th>Set Size (Set Group)</th>
                            <th>Colour</th>
                            <th>Fabric</th>
                            <th>Fitting</th>
                            <th>Pattern</th>
                            <th class="text-right">Set Quantity</th>
                            <th class="text-right pr-4">Total Quantity</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalSetQty = 0; $totalQty = 0; @endphp
                        @foreach ($data['order']->OrderProductSets as $set)
                            @php 
                                $totalSetQty += ($set->set_quantity ?? 0); 
                                $totalQty += ($set->total_quantity ?? 0);
                            @endphp
                            <tr>
                                <td class="pl-4">{{ $set->bar_code ?? '-' }}</td>
                                <td class="font-weight-bold">{{ $set->design_number }}</td>
                                <td>{{ $set->size_measurement->name ?? '-' }} ({{ $set->size_measurement->size_group ?? '' }})</td>
                                <td>{{ $set->colors->name ?? 'N/A' }}</td>
                                <td>{{ $set->fabric->sku ?? ($set->fabric->name ?? 'N/A') }}</td>
                                <td>{{ $set->master_product_fitting->name ?? '-' }}</td>
                                <td>{{ $set->master_design_pattern->name ?? '-' }}</td>
                                <td class="text-right font-weight-bold">{{ $set->set_quantity ?? 0 }}</td>
                                <td class="text-right pr-4 font-weight-bold">{{ $set->total_quantity ?? 0 }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="bg-light">
                        <tr>
                            <td colspan="7" class="text-right font-weight-bold">Total</td>
                            <td class="text-right font-weight-bold">{{ $totalSetQty }}</td>
                            <td class="text-right pr-4 font-weight-bold">{{ $totalQty }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <!-- Tabbed Section -->
        <div class="card">
            <ul class="nav nav-tabs-modern" id="reportTabs" role="tablist" style="border-top: 1px solid var(--slate-200);">
                <li class="nav-item">
                    <a class="nav-link active" id="lots-tab" data-toggle="tab" href="#lots" role="tab"><i class="fas fa-industry mr-1"></i> Lots Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="packing-tab" data-toggle="tab" href="#packing" role="tab"><i class="fas fa-box-open mr-1"></i> Packing Details</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dispatch-tab" data-toggle="tab" href="#dispatch" role="tab"><i class="fas fa-shipping-fast mr-1"></i> Dispatch History</a>
                </li>
            </ul>
            <div class="card-body p-0">
                <div class="tab-content" id="reportTabsContent">
                    <!-- Lots Tab -->
                    <div class="tab-pane fade show active" id="lots" role="tabpanel">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">#</th>
                                    <th>Lot No</th>
                                    <th>Order No</th>
                                    <th>Customer Name</th>
                                    <th>Current Stage</th>
                                    <th>Lot Quantity</th>
                                    <th class="text-center pr-4">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['lotsData'] as $index => $lot)
                                    <tr>
                                        <td class="pl-4">{{ $index + 1 }}</td>
                                        <td class="font-weight-bold">{{ $lot['lot_no'] }}</td>
                                        <td>{{ $lot['order_no'] }}</td>
                                        <td>{{ $data['order']->customer->name ?? 'N/A' }}</td>
                                        <td class="font-weight-bold text-dark">{{ $lot['last_current_stage'] ?? 'N/A' }}</td>
                                        <td class="font-weight-bold">{{ $lot['lot_quantity'] }} Pcs</td>
                                        <td class="text-center pr-4">
                                            <a href="{{ route('owner.lot-details', ['lot_no' => $lot['lot_no']]) }}" class="btn btn-sm btn-outline-dark px-3 font-weight-bold" style="border-radius: 6px;">View</a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center py-5 text-muted font-weight-bold">No lot data available.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Packing Tab -->
                    <div class="tab-pane fade" id="packing" role="tabpanel">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Carton #</th>
                                    <th>Item Summary</th>
                                    <th class="text-right pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['cartons'] as $carton)
                                    <tr>
                                        <td class="pl-4 font-weight-bold">#{{ $carton->carton_no }}</td>
                                        <td class="text-muted font-weight-bold">
                                            @php
                                                $summary = [];
                                                foreach($carton->items as $item) {
                                                    $name = $item->detail->size ?? $item->size_id;
                                                    if(!isset($summary[$name])) $summary[$name] = 0;
                                                    $summary[$name] += $item->quantity;
                                                }
                                                $text = [];
                                                foreach($summary as $k => $v) $text[] = "$k ($v)";
                                            @endphp
                                            {{ implode(', ', $text) }}
                                        </td>
                                        <td class="text-right pr-4">
                                            <span class="badge {{ $carton->status == 2 ? 'badge-success' : 'badge-primary' }} text-uppercase px-3 py-2" style="border-radius: 6px; font-size: 10px; font-weight: 800;">
                                                {{ $carton->status == 2 ? 'Dispatched' : 'Packed' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted font-weight-bold">No packing logs found.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <!-- Dispatch Tab -->
                    <div class="tab-pane fade" id="dispatch" role="tabpanel">
                        <table class="table mb-0">
                            <thead>
                                <tr>
                                    <th class="pl-4">Dispatch ID</th>
                                    <th>Dispatch Date</th>
                                    <th class="text-right pr-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($data['dispatches'] as $dispatch)
                                    <tr>
                                        <td class="pl-4 font-weight-bold text-dark">{{ $dispatch->sku }}</td>
                                        <td class="font-weight-bold text-muted">{{ date('d M, Y', strtotime($dispatch->dispatch_date)) }}</td>
                                        <td class="text-right pr-4">
                                            <span class="badge badge-success px-3 py-2 text-uppercase" style="border-radius: 6px; font-size: 10px; font-weight: 800;">Complete</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="3" class="text-center py-5 text-muted font-weight-bold">No dispatch history recorded.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleSection(sid) {
            $('.mobile-only .content-card').hide();
            $('#' + sid + '-section').show();
            $('.tab-pill').removeClass('active');
            $(event.currentTarget).addClass('active');
        }
    </script>
@endsection
