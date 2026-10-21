@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    <!-- Slim ERP Header Bar -->
    <div class="erp-header-bar mb-2">
        <div class="erp-header-title">
            <i class="fas fa-layer-group text-primary"></i> Sample Catalogs
        </div>
        <div class="erp-header-actions">
            <div class="hud-stat-box">
                <span class="hud-stat-label">Total catalogs</span>
                <span class="hud-stat-value text-success">{{ number_format($totalCatalogs ?? 0) }}</span>
            </div>
            <div class="hud-stat-box">
                <span class="hud-stat-label">Total items</span>
                <span class="hud-stat-value text-primary">{{ number_format($totalItems ?? 0) }}</span>
            </div>
            <a href="{{ route('admin.inventory.sample-product.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus"></i> Create New Catalog
            </a>
        </div>
    </div>

    <!-- Compact ERP Filter Bar -->
    <div class="erp-filter-bar mb-2">
        <form action="{{ route('admin.inventory.sample-product.index') }}" method="GET">
            <div class="row align-items-end">
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-barcode mr-1"></i> Batch No</label>
                    <input type="text" name="batch_no" class="form-control erp-input" placeholder="Search batch no..." value="{{ request('batch_no') }}" autocomplete="off">
                </div>
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-alt mr-1"></i> Date From</label>
                    <input type="date" name="date_from" class="form-control erp-input" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-calendar-check mr-1"></i> Date To</label>
                    <input type="date" name="date_to" class="form-control erp-input" value="{{ request('date_to') }}">
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="submit" class="btn-erp btn-erp-primary"><i class="fas fa-filter"></i> Filter</button>
                        <a href="{{ route('admin.inventory.sample-product.index') }}" class="btn-erp btn-erp-outline"><i class="fas fa-undo"></i> Reset</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Table Card -->
    <div class="erp-card">
        <div class="erp-card-body p-2 table-responsive">
            <table class="erp-table table table-bordered table-hover mb-0 w-100">
                <thead>
                    <tr>
                        <th style="width: 130px;">Date</th>
                        <th>Batch no</th>
                        <th class="text-center" style="width: 140px;">Total items</th>
                        <th class="text-right" style="width: 220px;">Actions</th>
                    </tr>
                </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td class="text-muted small align-middle">
                                {{ $batch->created_at->format('d M Y') }}
                            </td>
                            <td class="align-middle">
                                <span class="barcode-display">{{ $batch->batch_no }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="erp-stat-pill font-weight-bold" style="padding: 2px 8px; font-size: 11px;">
                                    <strong>{{ $batch->products_count }}</strong> products
                                </span>
                            </td>
                            <td class="align-middle text-right">
                                <a href="{{ route('admin.inventory.sample-product.show', $batch->id) }}" class="erp-action-btn erp-btn-view" title="View catalog">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inventory.sample-product.edit', $batch->id) }}" class="erp-action-btn erp-btn-edit" title="Edit catalog">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.inventory.sample-product.generate-pdf-batch', $batch->id) }}?show_wsp=yes" class="btn-erp btn-erp-outline btn-erp-xs mr-1" title="PDF with WSP" target="_blank">
                                    <i class="fas fa-file-invoice-dollar text-success mr-1"></i> WSP
                                </a>
                                <a href="{{ route('admin.inventory.sample-product.generate-pdf-batch', $batch->id) }}?show_wsp=no" class="btn-erp btn-erp-outline btn-erp-xs mr-1" title="PDF without price" target="_blank">
                                    <i class="fas fa-file-pdf text-danger mr-1"></i> No WSP
                                </a>
                                <form action="{{ route('admin.inventory.sample-product.destroy', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entire catalog batch?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="erp-action-btn erp-btn-delete border-0" title="Delete batch">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fas fa-boxes fa-3x mb-3 text-secondary d-block"></i>
                                <p class="mb-0">No sample catalogs found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $batches->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

@endsection
