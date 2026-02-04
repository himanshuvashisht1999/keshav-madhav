@extends('owner.layouts.app')

@section('content')
    <style>
        /* MOBILE APP STYLES */
        @media (max-width: 991.98px) {
            .app-header {
                padding: 20px 15px;
                background: white;
                border-bottom: 1px solid #f1f5f9;
                position: sticky;
                top: 0;
                z-index: 100;
            }

            .tab-nav-container {
                padding: 10px 15px;
                background: #f8fafc;
                display: flex;
                gap: 8px;
                overflow-x: auto;
                scrollbar-width: none;
            }

            .tab-pill {
                padding: 8px 16px;
                background: white;
                border-radius: 10px;
                border: 1px solid #e2e8f0;
                font-size: 13px;
                font-weight: 700;
                color: #64748b;
                white-space: nowrap;
            }

            .tab-pill.active {
                background: var(--primary);
                color: white;
                border-color: var(--primary);
                box-shadow: 0 4px 10px rgba(111, 66, 193, 0.2);
            }

            .content-card {
                background: white;
                border-radius: 15px;
                margin: 15px;
                padding: 20px;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
                border: 1px solid #f1f5f9;
            }

            .manifest-item {
                padding: 12px 0;
                border-bottom: 1px solid #f8fafc;
            }

            .manifest-grid {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 10px;
                margin-top: 8px;
            }

            .manifest-stat {
                background: #f8fafc;
                border-radius: 8px;
                padding: 8px;
                text-align: center;
            }
        }

        @media (min-width: 992px) {
            .desktop-p {
                padding: 25px;
            }

            .card {
                border-radius: 12px;
                border: none;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            }

            .card-header {
                background: var(--primary) !important;
            }
        }
    </style>

    <!-- MOBILE CONTENT -->
    <div class="mobile-only">
        <div class="app-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="font-weight-bold" style="color: #1e293b; margin: 0;">Order Manifest</h5>
                <a href="javascript:void(0)" onclick="window.print()" class="text-primary"><i
                        class="fas fa-file-pdf"></i></a>
            </div>
            <div class="text-xs font-weight-bold text-primary">SKU: #{{ $order->sku }}</div>
        </div>

        <div class="tab-nav-container">
            <div class="tab-pill active" onclick="toggleSection('manifest')">Manifest</div>
            <div class="tab-pill" onclick="toggleSection('history')">History</div>
            <div class="tab-pill" onclick="toggleSection('packing')">Packing</div>
            <div class="tab-pill" onclick="toggleSection('dispatch')">Dispatch</div>
        </div>

        <div id="manifest-section" class="content-card">
            <h6 class="font-weight-bold mb-3" style="color: #1e293b;">Product Details</h6>
            @foreach($order->OrderProductSets as $set)
                <div class="manifest-item">
                    <div style="font-weight: 700; color: #1e293b; font-size: 14px;">{{ $set->design_number }}</div>
                    <div class="text-xs text-muted">{{ $set->colors->name ?? 'N/A' }} | {{ $set->fabric->sku ?? 'N/A' }}</div>
                    <div class="manifest-grid">
                        <div class="manifest-stat">
                            <div style="font-size: 8px; color: #94a3b8; font-weight: 700;">ORDERED</div>
                            <div style="font-size: 14px; font-weight: 800;">{{ $set->no_of_pcs }}</div>
                        </div>
                        <div class="manifest-stat">
                            <div style="font-size: 8px; color: #94a3b8; font-weight: 700;">SCAN / BAL</div>
                            <div style="font-size: 14px; font-weight: 800; color: #6f42c1;">
                                {{ $data['history_data'][$set->id]['current_scanned'] ?? 0 }} /
                                {{ $set->no_of_pcs - ($data['history_data'][$set->id]['current_scanned'] ?? 0) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- DESKTOP CONTENT -->
    <div class="desktop-only desktop-p">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title text-white">Order Manifest: {{ $order->sku }}</h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <p class="text-muted">Viewing manifest for customer: <b>{{ $order->customer->name ?? 'N/A' }}</b>
                        </p>
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
            $(event.target).addClass('active');
        }
    </script>
@endsection