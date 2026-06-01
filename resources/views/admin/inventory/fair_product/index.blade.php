@extends('admin.layouts.app')

@section('content')
<style>
    :root {
        --primary: #6366f1;
        --bg-main: #f8fafc;
        --border: #e2e8f0;
    }
    .content-wrapper {
        background-color: var(--bg-main);
    }
    .premium-card {
        background: #fff;
        border-radius: 12px;
        border: 1px solid var(--border);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
    }
    .table thead th {
        background: #fcfdfe;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #64748b;
        border-bottom: 1px solid var(--border);
    }
    .barcode-display {
        font-family: monospace;
        background: #f1f5f9;
        padding: 0.25rem 0.5rem;
        border-radius: 4px;
        font-size: 0.875rem;
    }
</style>

<div class="content-wrapper">
    <div class="container-fluid py-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 font-weight-bold text-dark mb-0">Sample Sets</h1>
                <p class="text-muted">Manage your grouped sample sets for customers.</p>
            </div>
            <a href="{{ route('admin.inventory.fair-product.create') }}" class="btn btn-primary px-4">
                <i class="fas fa-plus mr-2"></i> Create New Sample Set
            </a>
        </div>

        <!-- FILTER CARD -->
        <div class="card shadow-sm border-0 mb-4 premium-card">
            <div class="card-body p-3">
                <form action="{{ route('admin.inventory.fair-product.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md-2">
                        <label class="small text-muted font-weight-bold">Batch No</label>
                        <input type="text" name="batch_no" class="form-control" value="{{ request('batch_no') }}" placeholder="FAIR-...">
                    </div>
                    <div class="col-md-3">
                        <label class="small text-muted font-weight-bold">Sales Agent</label>
                        <select name="sales_agent_ids[]" class="form-control select2" multiple>
                            @foreach($salesAgents as $agent)
                                <option value="{{ $agent->id }}" {{ is_array(request('sales_agent_ids')) && in_array($agent->id, request('sales_agent_ids')) ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted font-weight-bold">From Date</label>
                        <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md-2">
                        <label class="small text-muted font-weight-bold">To Date</label>
                        <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-3 mt-2 mt-md-0 d-flex">
                        <button type="submit" class="btn btn-primary w-50 mr-1">
                            <i class="fas fa-filter mr-1"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.fair-product.index') }}" class="btn btn-outline-secondary w-50 ml-1">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <div class="premium-card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="py-3">Batch No</th>
                            <th class="py-3">Sales Agents</th>
                            <th class="py-3 text-center">Total Items</th>
                            <th class="py-3 text-right px-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($batches as $batch)
                        <tr>
                            <td class="px-4 align-middle text-muted small">
                                {{ $batch->created_at->format('d M Y') }}<br>
                                {{ $batch->created_at->format('h:i A') }}
                            </td>
                            <td class="align-middle">
                                <span class="barcode-display">{{ $batch->batch_no }}</span>
                            </td>
                            <td class="align-middle">
                                @php
                                    $agentNames = [];
                                    if(is_array($batch->sales_agent_ids)) {
                                        foreach($batch->sales_agent_ids as $id) {
                                            $ag = $salesAgents->where('id', $id)->first();
                                            if($ag) {
                                                $agentNames[] = $ag->name;
                                            }
                                        }
                                    }
                                @endphp
                                @if(count($agentNames) > 0)
                                    <span class="badge badge-light border">{{ implode(', ', $agentNames) }}</span>
                                @else
                                    <span class="text-muted small">N/A</span>
                                @endif
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge badge-info px-3 py-2">{{ $batch->products_count }} Products</span>
                            </td>
                            <td class="align-middle text-right px-4">
                                <a href="{{ route('admin.inventory.fair-product.edit', $batch->id) }}" class="btn btn-sm btn-outline-warning mr-1" title="Edit Sample Set">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="{{ route('admin.inventory.fair-product.generate-pdf-batch', $batch->id) }}?show_wsp=yes" class="btn btn-sm btn-outline-success mr-1" title="PDF with WSP">
                                    <i class="fas fa-file-invoice-dollar"></i> WSP
                                </a>
                                <a href="{{ route('admin.inventory.fair-product.generate-pdf-batch', $batch->id) }}?show_wsp=no" class="btn btn-sm btn-outline-primary mr-1" title="PDF No Price">
                                    <i class="fas fa-file-pdf"></i> NO WSP
                                </a>
                                <a href="{{ route('admin.inventory.fair-product.download-prn') }}?batch_id={{ $batch->id }}" class="btn btn-sm btn-outline-dark mr-1" title="Download PRN (Printer)">
                                    <i class="fas fa-print"></i> PRN
                                </a>
                                <form action="{{ route('admin.inventory.fair-product.destroy', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entire sample set batch?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Batch">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="fas fa-history fa-3x mb-3"></i>
                                <p>No sample sets found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
            <div class="card-footer bg-white border-0">
                {{ $batches->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
