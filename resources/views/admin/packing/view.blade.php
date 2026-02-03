@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper bg-light">
    <!-- 1. PAGE HEADER (ORDER DATA) -->
    <section class="content-header py-4">
        <div class="container-fluid">
            <div class="card shadow-sm border-0 overflow-hidden" style="border-radius: 20px;">
                <div class="card-body p-0">
                    <div class="row no-gutters">
                        <div class="col-md-8 p-4 d-flex align-items-center">
                            <div class="customer-avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mr-4" style="width: 70px; height: 70px; font-size: 1.8rem; font-weight: 700;">
                                {{ substr($order->customer->name ?? 'C', 0, 1) }}
                            </div>
                            <div>
                                <h1 class="m-0 font-weight-bold h3 text-dark">Order #{{ $order->sku }}</h1>
                                <p class="text-muted mb-0"><i class="fas fa-user-tie mr-1"></i> Customer: <strong>{{ $order->customer->name ?? 'N/A' }}</strong> &bull; <i class="fas fa-calendar-alt ml-2 mr-1"></i> Order Date: {{ date('d M, Y', strtotime($order->created_at)) }}</p>
                            </div>
                        </div>
                        <div class="col-md-4 bg-primary d-flex align-items-center justify-content-center p-4">
                            <div class="text-center text-white">
                                <span class="text-uppercase small font-weight-bold d-block mb-1" style="letter-spacing: 1px; opacity: 0.8;">Current Status</span>
                                <h4 class="font-weight-bold mb-0"><i class="fas fa-check-double mr-2"></i> Packing Finalized</h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- 2. QUICK STATS (PACKING DATA SUMMARY) -->
    <section class="content mb-4">
        <div class="container-fluid">
            @php
                $totalCartons = 0;
                $totalBoxes = 0;
                $totalItems = 0;
                $slipIds = [];
                foreach($order->packingMains as $main) {
                    $slipIds[] = $main->slip_id;
                    $totalCartons += $main->cartons->count();
                    foreach($main->cartons as $carton) {
                        $totalBoxes += $carton->boxes->count();
                        $totalItems += $carton->items->sum('quantity');
                    }
                }
            @endphp
            <div class="row">
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3" style="border-radius: 15px;">
                        <div class="icon-box bg-soft-primary text-primary mr-3 rounded-lg d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-file-invoice"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase x-small font-weight-bold d-block">Packing Slips</span>
                            <h5 class="font-weight-bold mb-0">{{ count($slipIds) }} Slips</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3" style="border-radius: 15px;">
                        <div class="icon-box bg-soft-warning text-warning mr-3 rounded-lg d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-archive"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase x-small font-weight-bold d-block">Cartons</span>
                            <h5 class="font-weight-bold mb-0">{{ $totalCartons }} Units</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3" style="border-radius: 15px;">
                        <div class="icon-box bg-soft-info text-info mr-3 rounded-lg d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-boxes"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase x-small font-weight-bold d-block">Internal Boxes</span>
                            <h5 class="font-weight-bold mb-0">{{ $totalBoxes }} Boxes</h5>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="stat-card shadow-sm p-3 bg-white d-flex align-items-center mb-3" style="border-radius: 15px;">
                        <div class="icon-box bg-soft-success text-success mr-3 rounded-lg d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 1.2rem;">
                            <i class="fas fa-tshirt"></i>
                        </div>
                        <div>
                            <span class="text-muted text-uppercase x-small font-weight-bold d-block">Total Pcs</span>
                            <h5 class="font-weight-bold mb-0">{{ $totalItems }} Pcs</h5>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Link Slips Summary -->
            <div class="alert alert-light border shadow-none mt-2 d-flex align-items-center" style="border-radius: 12px; background: rgba(255,255,255,0.5);">
                <i class="fas fa-link text-muted mr-3"></i>
                <span class="text-muted small">Associated Packing Slips: </span>
                @foreach($slipIds as $sid)
                    <a href="{{ route('admin.uploaded-slips.show', $sid) }}" class="badge badge-light border ml-2 text-primary" target="_blank">#{{ $sid }}</a>
                @endforeach
            </div>
        </div>
    </section>

    <!-- 3. CARTON DATA FEED -->
    <section class="content pb-5">
        <div class="container-fluid">
            <div class="d-flex align-items-center mb-4 pt-2">
                <div style="width: 30px; height: 2px; background: #dee2e6; margin-right: 15px;"></div>
                <h4 class="font-weight-bold text-dark mb-0">Detailed Carton Breakdown</h4>
                <div class="flex-grow-1 ml-3" style="height: 2px; background: #dee2e6;"></div>
            </div>

            <div class="row">
                @php $cartonNo = 0; @endphp
                @foreach($order->packingMains as $main)
                    @foreach($main->cartons as $carton)
                        @php $cartonNo++; @endphp
                        <div class="col-lg-6 mb-4">
                            <div class="carton-card shadow-sm border-0 bg-white h-100" style="border-radius: 20px;">
                                <div class="p-4">
                                    <div class="d-flex align-items-start justify-content-between mb-3">
                                        <div class="d-flex align-items-center">
                                            <div class="carton-badge bg-primary text-white mr-3 d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; border-radius: 12px; font-weight: 700; font-size: 1.1rem;">
                                                #{{ $carton->carton_no }}
                                            </div>
                                            <div>
                                                <h5 class="font-weight-bold mb-0 text-dark">Carton Record</h5>
                                                <span class="text-muted small">ID: {{ $carton->id }} &bull; Slip <a href="{{ route('admin.uploaded-slips.show', $main->slip_id) }}" class="text-primary font-weight-bold" target="_blank">#{{ $main->slip_id }}</a></span>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <span class="badge badge-pill badge-soft-primary px-3 py-2">{{ $carton->boxes->count() }} Boxes</span>
                                        </div>
                                    </div>

                                    <!-- Boxes Row -->
                                    @if($carton->boxes->count() > 0)
                                        <div class="mb-4">
                                            <label class="text-uppercase x-small font-weight-bold text-muted letter-spacing-1 d-block mb-2">BOX SERIALS</label>
                                            <div class="d-flex flex-wrap" style="gap: 8px;">
                                                @foreach($carton->boxes as $box)
                                                    <span class="badge border bg-light text-dark py-2 px-2" style="border-radius: 8px; font-weight: 500;">
                                                       <i class="fas fa-box text-warning mr-1"></i> {{ $box->box_no }}
                                                    </span>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif

                                    <!-- Items Table -->
                                    @if($carton->items->count() > 0)
                                        <label class="text-uppercase x-small font-weight-bold text-muted letter-spacing-1 d-block mb-2">CONTENTS ({{ $carton->items->sum('quantity') }} Pcs)</label>
                                        <div class="table-responsive" style="border-radius: 12px; border: 1px solid #f0f0f0;">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="bg-light text-muted x-small font-weight-bold">
                                                    <tr>
                                                        <th class="border-0 px-3 py-2">SIZE</th>
                                                        <th class="border-0 px-3 py-2 text-right">QTY</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($carton->items->groupBy(fn($item) => $item->detail ? $item->detail->size : ($item->size ? $item->size->name : 'ID:' . $item->size_id)) as $sizeName => $items)
                                                        <tr>
                                                            <td class="px-3 py-2 font-weight-bold text-dark">{{ $sizeName }}</td>
                                                            <td class="px-3 py-2 text-right text-primary font-weight-bold">{{ $items->sum('quantity') }} Pcs</td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
            
            @if($totalCartons == 0)
                <div class="text-center py-5 bg-white shadow-sm" style="border-radius: 20px;">
                    <img src="https://cdni.iconscout.com/illustration/premium/thumb/empty-box-illustration-download-in-svg-png-gif-file-formats--no-item-package-products-not-found-state-pack-user-interface-illustrations-4547926.png" style="width: 200px; opacity: 0.5;" alt="Empty">
                    <p class="text-muted mt-3">No packed cartons found for this order.</p>
                </div>
            @endif
        </div>
    </section>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap');

    .content-wrapper {
        font-family: 'Outfit', sans-serif !important;
        background-color: #f7f9fc !important;
    }

    .bg-soft-primary { background: rgba(0, 123, 255, 0.1); }
    .bg-soft-warning { background: rgba(255, 193, 7, 0.1); }
    .bg-soft-info { background: rgba(23, 162, 184, 0.1); }
    .bg-soft-success { background: rgba(40, 167, 69, 0.1); }
    
    .badge-soft-primary { background: rgba(0, 123, 255, 0.08); color: #007bff; border: 1px solid rgba(0, 123, 255, 0.1); }

    .stat-card {
        border: 1px solid rgba(0,0,0,0.03);
        transition: transform 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-3px);
    }

    .carton-card {
        transition: all 0.3s ease;
        border: 1px solid transparent;
    }
    .carton-card:hover {
        border-color: rgba(0, 123, 255, 0.2);
        box-shadow: 0 15px 30px rgba(0, 123, 255, 0.05) !important;
    }

    .x-small { font-size: 0.7rem; }
    .letter-spacing-1 { letter-spacing: 1px; }

    .btn-white {
        background: #fff;
        color: #444;
        font-weight: 600;
        border: 1px solid #dee2e6;
    }
    .btn-white:hover {
        background: #f8f9fa;
        color: #000;
    }
</style>
@endsection
