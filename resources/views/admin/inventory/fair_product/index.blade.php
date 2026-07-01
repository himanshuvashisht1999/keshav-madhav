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
    <!-- PAGE HEADER -->
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2 align-items-center">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold text-dark h4">Sample Sets</h1>
                    <small class="text-muted">Manage your grouped sample sets for customers.</small>
                </div>
                <div class="col-sm-6 text-right">
                    <button type="button" class="btn btn-sm btn-info px-3 shadow-sm mr-2" data-toggle="modal" data-target="#designSearchModal">
                        <i class="fas fa-search mr-1"></i> Search by Design No.
                    </button>
                    <button type="button" class="btn btn-sm btn-dark px-3 shadow-sm mr-2" data-toggle="modal" data-target="#prnModal">
                        <i class="fas fa-barcode mr-1"></i> Generate PRN by Barcodes
                    </button>
                    <a href="{{ route('admin.inventory.fair-product.create') }}" class="btn btn-sm btn-primary px-3 shadow-sm">
                        <i class="fas fa-plus mr-1"></i> Create New Sample Set
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- CONTENT -->
    <section class="content">
        <div class="container-fluid">

        <!-- FILTER CARD -->
        <div class="card shadow-sm border-0 mb-3 premium-card">
            <div class="card-body p-2">
                <form action="{{ route('admin.inventory.fair-product.index') }}" method="GET" class="row align-items-end">
                    <div class="col-md mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Design Number</label>
                        <input type="text"
                               name="design_number"
                               class="form-control form-control-sm"
                               value="{{ request('design_number') }}"
                               placeholder="e.g. D-101"
                               list="design-number-list"
                               autocomplete="off">
                        <datalist id="design-number-list">
                            @foreach($designNumbers as $dn)
                                <option value="{{ $dn }}">
                            @endforeach
                        </datalist>
                    </div>
                    <div class="col-md mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Batch No</label>
                        <input type="text" name="batch_no" class="form-control form-control-sm" value="{{ request('batch_no') }}" placeholder="FAIR-...">
                    </div>
                    <div class="col-md mb-2">
                        <label class="small text-muted font-weight-bold mb-1">Sales Agent</label>
                        <select name="sales_agent_ids[]" class="form-control select2 form-control-sm" multiple>
                            @foreach($salesAgents as $agent)
                                <option value="{{ $agent->id }}" {{ is_array(request('sales_agent_ids')) && in_array($agent->id, request('sales_agent_ids')) ? 'selected' : '' }}>{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md mb-2">
                        <label class="small text-muted font-weight-bold mb-1">From Date</label>
                        <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                    </div>
                    <div class="col-md mb-2">
                        <label class="small text-muted font-weight-bold mb-1">To Date</label>
                        <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                    </div>
                    <div class="col-md-auto mb-2 d-flex text-right">
                        <button type="submit" class="btn btn-sm btn-primary shadow-sm mr-1 px-3">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <a href="{{ route('admin.inventory.fair-product.index') }}" class="btn btn-sm btn-outline-secondary shadow-sm px-3">
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
                                {{ $batch->created_at->format('d M Y') }}
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
                                <a href="{{ route('admin.inventory.fair-product.show', $batch->id) }}" class="btn btn-sm btn-outline-info mr-1" title="View Sample Set">
                                    <i class="fas fa-eye"></i>
                                </a>
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
    </section>

    <!-- PRN Modal -->
    <div class="modal fade" id="prnModal" tabindex="-1" role="dialog" aria-labelledby="prnModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title font-weight-bold" id="prnModalLabel"><i class="fas fa-barcode mr-2 text-primary"></i>Generate PRN by Barcodes</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('admin.inventory.fair-product.download-prn-by-barcodes') }}" method="POST" target="_blank">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group mb-0">
                            <label class="small text-muted font-weight-bold mb-1">Enter Barcodes</label>
                            <textarea class="form-control" name="barcodes" rows="5" placeholder="Paste or type sample set barcodes here (one per line, e.g. F...)" required></textarea>
                            <small class="form-text text-muted mt-2">
                                Enter multiple barcodes separated by a new line. The system will automatically generate a PRN file for all valid barcodes.
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 pt-0">
                        <button type="button" class="btn btn-sm btn-secondary shadow-sm px-3" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-dark shadow-sm px-3">
                            <i class="fas fa-print mr-1"></i> Generate Custom PRN
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         DESIGN NUMBER SEARCH MODAL
    ═══════════════════════════════════════ -->
    <div class="modal fade" id="designSearchModal" tabindex="-1" role="dialog" aria-labelledby="designSearchModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">

                <div class="modal-header border-bottom pb-3">
                    <h5 class="modal-title font-weight-bold" id="designSearchModalLabel">
                        <i class="fas fa-search mr-2 text-info"></i> Search Sample Sets
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body">

                    {{-- ── 4-column filter row ── --}}
                    <div class="row mb-3">
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Design Number</label>
                            <div class="input-group input-group-sm">
                                <input type="text"
                                       id="designSearchInput"
                                       class="form-control"
                                       placeholder="e.g. D-101"
                                       autocomplete="off"
                                       list="modalDesignList">
                                <datalist id="modalDesignList">
                                    @foreach($designNumbers as $dn)
                                        <option value="{{ $dn }}">
                                    @endforeach
                                </datalist>
                            </div>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Product (Series & Name)</label>
                            <select id="modalProductName" class="form-control form-control-sm select2">
                                <option value="">All Products</option>
                                @foreach($productsList as $pl)
                                    <option value="{{ $pl }}">{{ $pl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Size Set</label>
                            <select id="modalSizeSet" class="form-control form-control-sm select2">
                                <option value="">All Size Sets</option>
                                @foreach($sizeSets as $ss)
                                    <option value="{{ $ss->id }}">{{ $ss->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Sales Agent</label>
                            <select id="modalSalesAgent" class="form-control form-control-sm select2">
                                <option value="">All Agents</option>
                                @foreach($salesAgents as $agent)
                                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="text-right mb-3">
                        <button class="btn btn-info px-4 btn-sm" type="button" id="designSearchBtn">
                            <i class="fas fa-search mr-1"></i> Search
                        </button>
                        <button class="btn btn-outline-secondary px-3 btn-sm ml-1" type="button" id="designSearchReset">
                            <i class="fas fa-undo mr-1"></i> Reset
                        </button>
                    </div>

                    {{-- Results Area --}}
                    <div id="designSearchResults">
                        <div class="text-center text-muted py-4" id="designSearchPlaceholder">
                            <i class="fas fa-search fa-2x mb-2 d-block"></i>
                            Fill in any filter above and click Search
                        </div>
                    </div>

                </div>

                <div class="modal-footer border-top-0 pt-0">
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Close</button>
                </div>

            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const searchUrl   = '{{ route('admin.inventory.fair-product.search-by-design') }}';
    const searchBtn   = document.getElementById('designSearchBtn');
    const resetBtn    = document.getElementById('designSearchReset');
    const searchInput = document.getElementById('designSearchInput');
    const resultsDiv  = document.getElementById('designSearchResults');

    const placeholder = '<div class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2 d-block"></i>Fill in any filter above and click Search</div>';

    function doSearch() {
        const designNo    = searchInput.value.trim();
        const sizeSetId   = document.getElementById('modalSizeSet').value;
        const salesAgentId= document.getElementById('modalSalesAgent').value;
        const productName = document.getElementById('modalProductName').value;

        if (!designNo && !sizeSetId && !salesAgentId && !productName) {
            resultsDiv.innerHTML = '<div class="alert alert-warning py-2"><i class="fas fa-exclamation-triangle mr-1"></i> Please fill at least one filter.</div>';
            return;
        }

        resultsDiv.innerHTML = '<div class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin mr-1"></i> Searching...</div>';

        const params = new URLSearchParams();
        if (designNo)     params.append('design_number',  designNo);
        if (sizeSetId)    params.append('size_set_id',    sizeSetId);
        if (salesAgentId) params.append('sales_agent_id', salesAgentId);
        if (productName)  params.append('product_name',   productName);

        fetch(searchUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            if (!data || data.length === 0) {
                resultsDiv.innerHTML = '<div class="alert alert-info py-2"><i class="fas fa-info-circle mr-1"></i> No results found for the selected filters.</div>';
                return;
            }

            let html = '<div class="mb-2 text-muted small"><strong>' + data.length + '</strong> barcode(s) found</div>';
            html += '<div class="table-responsive">';
            html += '<table class="table table-sm table-bordered table-hover mb-0">';
            html += '<thead class="thead-dark"><tr>';
            html += '<th class="py-2">#</th>';
            html += '<th class="py-2">Product Name</th>';
            html += '<th class="py-2">Design No.</th>';
            html += '<th class="py-2">Size Set</th>';
            html += '<th class="py-2">Batch No.</th>';
            html += '<th class="py-2">Sales Agent(s)</th>';
            html += '<th class="py-2">Barcode</th>';
            html += '</tr></thead><tbody>';

            data.forEach(function(row, i) {
                html += '<tr>';
                html += '<td class="text-muted">' + (i + 1) + '</td>';
                html += '<td>' + row.product_name + '</td>';
                html += '<td><span class="badge badge-primary px-2 py-1">' + row.design_number + '</span></td>';
                html += '<td><span class="badge badge-secondary px-2 py-1">' + row.size_set + '</span></td>';
                html += '<td><code>' + row.batch_no + '</code></td>';
                html += '<td>' + row.sales_agents + '</td>';
                html += '<td>';
                html += '<span class="badge badge-light border font-weight-bold mr-1" style="font-family:monospace;font-size:0.85rem">' + row.barcode + '</span>';
                html += '<i class="far fa-copy text-primary copy-barcode" data-barcode="' + row.barcode + '" title="Copy Barcode" style="cursor: pointer;"></i>';
                html += '</td>';
                html += '</tr>';
            });

            html += '</tbody></table></div>';
            resultsDiv.innerHTML = html;
        })
        .catch(() => {
            resultsDiv.innerHTML = '<div class="alert alert-danger py-2">Error fetching results. Please try again.</div>';
        });
    }

    searchBtn.addEventListener('click', doSearch);
    searchInput.addEventListener('keydown', function (e) { if (e.key === 'Enter') doSearch(); });

    if (resetBtn) {
        resetBtn.addEventListener('click', function () {
            searchInput.value = '';
            document.getElementById('modalSizeSet').value    = '';
            document.getElementById('modalSalesAgent').value = '';
            document.getElementById('modalProductName').value = '';
            if (typeof $ !== 'undefined') {
                $('#modalSizeSet, #modalSalesAgent, #modalProductName').trigger('change'); // refresh select2
            }
            resultsDiv.innerHTML = placeholder;
        });
    }

    // Clear on modal close
    $('#designSearchModal').on('hidden.bs.modal', function () {
        searchInput.value = '';
        if (typeof $ !== 'undefined') {
            $('#modalSizeSet, #modalSalesAgent, #modalProductName').val('').trigger('change');
        }
        resultsDiv.innerHTML = placeholder;
    });

    $(document).on('click', '.copy-barcode', function() {
        const barcode = $(this).data('barcode');
        navigator.clipboard.writeText(barcode).then(() => {
            Toast.fire({
                icon: 'success',
                title: 'Barcode copied to clipboard!'
            });
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });

});
</script>
@endpush
