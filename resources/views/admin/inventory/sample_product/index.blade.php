@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">Sample catalogs</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item"><i class="fas fa-layer-group mr-1 text-muted"></i> Grouped sample catalogs for customers</span>
            </div>
        </div>
        <div class="d-flex align-items-center">
            <a href="{{ route('admin.inventory.sample-product.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus mr-1"></i> Create new catalog
            </a>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="card erp-card mb-3">
        <div class="card-body p-2">
            <form action="{{ route('admin.inventory.sample-product.index') }}" method="GET">
                <div class="row g-2 align-items-end">
                    <div class="col-md-3">
                        <label class="erp-label mb-1">Batch no</label>
                        <input type="text" name="batch_no" class="form-control form-control-sm" placeholder="Search batch no..." value="{{ request('batch_no') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="erp-label mb-1">Date from</label>
                        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="erp-label mb-1">Date to</label>
                        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                    </div>
                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn-erp btn-erp-primary mr-2"><i class="fas fa-search mr-1"></i> Filter</button>
                        <a href="{{ route('admin.inventory.sample-product.index') }}" class="btn-erp btn-erp-outline">Reset</a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- ================= TABLE ================= --}}
    <div class="card erp-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table erp-table mb-0">
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
