@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Transfer Log Details</h1>
                <p class="erp-page-subtitle m-0">History record #{{ $history->id }} &bull; Transferred on {{ $history->created_at->format('d M Y, h:i A') }}</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.warehouse_stock.download_slip', $history->id) }}" class="btn-erp btn-erp-outline" target="_blank">
                    <i class="fas fa-file-pdf mr-1"></i> Download slip
                </a>
                <a href="{{ route('admin.inventory.warehouse_stock.history') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to history
                </a>
            </div>
        </div>

        <!-- SUMMARY CARD -->
        <div class="erp-card mb-3 p-3">
            <div class="row align-items-center">
                <div class="col-md-3">
                    <label class="erp-label mb-0 text-muted">Log date</label>
                    <div class="font-weight-bold text-dark">{{ $history->created_at->format('d M Y, h:i A') }}</div>
                </div>
                <div class="col-md-3">
                    <label class="erp-label mb-0 text-muted">Boxes transferred</label>
                    <div class="font-weight-bold text-dark" style="color: #05421c !important;">{{ $history->box_quantity }} Boxes</div>
                </div>
                <div class="col-md-3">
                    <label class="erp-label mb-0 text-muted">Transferred by</label>
                    <div class="font-weight-bold text-dark">{{ $history->user->name ?? 'System' }}</div>
                </div>
                <div class="col-md-3 text-md-right mt-2 mt-md-0">
                    <span class="badge badge-success px-3 py-2 font-weight-normal" style="background-color: #05421c; font-size: 0.85rem;">
                        <i class="fas fa-check-circle mr-1"></i> Transfer completed
                    </span>
                </div>
            </div>
        </div>

        <!-- COMPARISON SECTION -->
        <div class="row">
            <!-- OLD DETAILS -->
            <div class="col-md-5 mb-3">
                <div class="erp-card h-100 p-0" style="border-left: 4px solid #dc3545 !important;">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="background-color: #fff5f5;">
                        <h6 class="m-0 font-weight-bold text-danger">
                            <i class="fas fa-history mr-2"></i> Source (Old state)
                        </h6>
                    </div>
                    <div class="p-3">
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Warehouse / Rack</label>
                            <div class="p-2 rounded font-weight-bold" style="background-color: #f8fafc; font-size: 0.9rem;">
                                {{ $history->oldRack->storeroom->name ?? 'N/A' }} / {{ $history->oldRack->name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Design & Product</label>
                            <div class="font-weight-bold text-dark">{{ $history->oldProduct->design_number ?? 'N/A' }} - {{ ($history->oldProduct->series->name ?? '') . ' ' . ($history->oldProduct->name_of_garment ?? '') }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Color</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldColor->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Size set</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldSizeSet->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="erp-label mb-1 text-muted">Fitting</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldFitting->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="erp-label mb-1 text-muted">Pattern</label>
                                <div class="font-weight-bold text-dark">{{ $history->oldPattern->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- ARROW -->
            <div class="col-md-2 mb-3 d-flex align-items-center justify-content-center">
                <div class="text-center">
                    <div class="bg-white shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center border" style="width: 48px; height: 48px;">
                        <i class="fas fa-arrow-right text-muted" style="font-size: 1.2rem;"></i>
                    </div>
                    <div class="mt-2 erp-label font-weight-bold text-muted text-uppercase" style="letter-spacing: 0.5px;">Relocated</div>
                </div>
            </div>

            <!-- NEW DETAILS -->
            <div class="col-md-5 mb-3">
                <div class="erp-card h-100 p-0" style="border-left: 4px solid #05421c !important;">
                    <div class="p-3 border-bottom d-flex align-items-center justify-content-between" style="background-color: #edf5ee;">
                        <h6 class="m-0 font-weight-bold" style="color: #05421c;">
                            <i class="fas fa-check-circle mr-2"></i> Destination (New state)
                        </h6>
                    </div>
                    <div class="p-3">
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Warehouse / Rack</label>
                            <div class="p-2 rounded font-weight-bold border" style="background-color: #f8fafc; border-color: #cce3d0 !important; font-size: 0.9rem;">
                                {{ $history->newRack->storeroom->name ?? 'N/A' }} / {{ $history->newRack->name ?? 'N/A' }}
                            </div>
                        </div>
                        <div class="detail-item mb-2">
                            <label class="erp-label mb-1 text-muted">Design & Product</label>
                            <div class="font-weight-bold text-dark">{{ $history->newProduct->design_number ?? 'N/A' }} - {{ ($history->newProduct->series->name ?? '') . ' ' . ($history->newProduct->name_of_garment ?? '') }}</div>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Color</label>
                                <div class="font-weight-bold text-dark">{{ $history->newColor->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6 mb-2">
                                <label class="erp-label mb-1 text-muted">Size set</label>
                                <div class="font-weight-bold text-dark">{{ $history->newSizeSet->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6">
                                <label class="erp-label mb-1 text-muted">Fitting</label>
                                <div class="font-weight-bold text-dark">{{ $history->newFitting->name ?? 'N/A' }}</div>
                            </div>
                            <div class="col-6">
                                <label class="erp-label mb-1 text-muted">Pattern</label>
                                <div class="font-weight-bold text-dark">{{ $history->newPattern->name ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
