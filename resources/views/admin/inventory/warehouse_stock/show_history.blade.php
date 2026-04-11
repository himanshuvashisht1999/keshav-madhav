@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper">
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-3 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark">Transfer Log Details</h1>
                    <small class="text-muted">History ID #{{ $history->id }}</small>
                </div>
                <div class="col-sm-6 text-right">
                    <a href="{{ route('admin.inventory.warehouse_stock.download_slip', $history->id) }}" class="btn btn-outline-info shadow-sm mr-2" target="_blank">
                        <i class="fas fa-file-pdf mr-1"></i> Download Slip
                    </a>
                    <a href="{{ route('admin.inventory.warehouse_stock.history') }}" class="btn btn-outline-secondary shadow-sm">
                        <i class="fas fa-arrow-left mr-1"></i> Back to History
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">
            <!-- SUMMARY CARD -->
            <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                <div class="card-body p-4">
                    <div class="row">
                        <div class="col-md-3">
                            <label class="small text-muted mb-0 d-block">Log Date</label>
                            <span class="h6 font-weight-bold text-dark">{{ $history->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-0 d-block">Boxes Transferred</label>
                            <span class="h6 font-weight-bold text-primary">{{ $history->box_quantity }} Boxes</span>
                        </div>
                        <div class="col-md-3">
                            <label class="small text-muted mb-0 d-block">Transferred By</label>
                            <span class="h6 font-weight-bold text-dark">{{ $history->user->name ?? 'System' }}</span>
                        </div>
                        <div class="col-md-3 text-right">
                             <div class="badge badge-success px-3 py-2" style="font-size: 0.9rem;">TRANSFER COMPLETED</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- COMPARISON SECTION -->
            <div class="row">
                <!-- OLD DETAILS -->
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #dc3545 !important;">
                        <div class="card-header bg-white pt-4 px-4 border-0">
                            <h5 class="card-title font-weight-bold text-danger"><i class="fas fa-history mr-2"></i> Source (Old State)</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="detail-item mb-3">
                                <label class="small text-muted mb-1 d-block">Warehouse / Rack</label>
                                <div class="p-2 bg-light rounded font-weight-bold">
                                    {{ $history->oldRack->storeroom->name ?? 'N/A' }} / {{ $history->oldRack->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="detail-item mb-3">
                                <label class="small text-muted mb-1 d-block">Design & Product</label>
                                <div class="font-weight-bold">{{ $history->oldProduct->design_number ?? 'N/A' }} - {{ ($history->oldProduct->series->name ?? '') . ' ' . ($history->oldProduct->name_of_garment ?? '') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="small text-muted mb-1 d-block">Color</label>
                                    <div class="font-weight-bold">{{ $history->oldColor->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="small text-muted mb-1 d-block">Size Set</label>
                                    <div class="font-weight-bold">{{ $history->oldSizeSet->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small text-muted mb-1 d-block">Fitting</label>
                                    <div class="font-weight-bold">{{ $history->oldFitting->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted mb-1 d-block">Pattern</label>
                                    <div class="font-weight-bold">{{ $history->oldPattern->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ARROW -->
                <div class="col-md-2 d-flex align-items-center justify-content-center">
                    <div class="text-center">
                        <div class="bg-white shadow-sm rounded-circle d-inline-flex align-items-center justify-content-center" style="width: 50px; height: 50px;">
                            <i class="fas fa-arrow-right fa-2x text-muted"></i>
                        </div>
                        <div class="mt-2 small font-weight-bold text-muted text-uppercase">Relocated</div>
                    </div>
                </div>

                <!-- NEW DETAILS -->
                <div class="col-md-5">
                    <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-left: 4px solid #28a745 !important;">
                        <div class="card-header bg-white pt-4 px-4 border-0">
                            <h5 class="card-title font-weight-bold text-success"><i class="fas fa-check-circle mr-2"></i> Destination (New State)</h5>
                        </div>
                        <div class="card-body px-4 pb-4">
                            <div class="detail-item mb-3">
                                <label class="small text-muted mb-1 d-block">Warehouse / Rack</label>
                                <div class="p-2 bg-light rounded font-weight-bold border border-success">
                                    {{ $history->newRack->storeroom->name ?? 'N/A' }} / {{ $history->newRack->name ?? 'N/A' }}
                                </div>
                            </div>
                            <div class="detail-item mb-3">
                                <label class="small text-muted mb-1 d-block">Design & Product</label>
                                <div class="font-weight-bold">{{ $history->newProduct->design_number ?? 'N/A' }} - {{ ($history->newProduct->series->name ?? '') . ' ' . ($history->newProduct->name_of_garment ?? '') }}</div>
                            </div>
                            <div class="row">
                                <div class="col-6 mb-3">
                                    <label class="small text-muted mb-1 d-block">Color</label>
                                    <div class="font-weight-bold">{{ $history->newColor->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6 mb-3">
                                    <label class="small text-muted mb-1 d-block">Size Set</label>
                                    <div class="font-weight-bold">{{ $history->newSizeSet->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <label class="small text-muted mb-1 d-block">Fitting</label>
                                    <div class="font-weight-bold">{{ $history->newFitting->name ?? 'N/A' }}</div>
                                </div>
                                <div class="col-6">
                                    <label class="small text-muted mb-1 d-block">Pattern</label>
                                    <div class="font-weight-bold">{{ $history->newPattern->name ?? 'N/A' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
<style>
    .detail-item { border-bottom: 1px solid #f8f9fa; padding-bottom: 10px; }
    .detail-item:last-child { border-bottom: none; }
</style>
@endsection
