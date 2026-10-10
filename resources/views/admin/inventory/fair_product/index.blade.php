@extends('admin.layouts.app')

@section('content')

<div class="content-wrapper erp-page p-2">

    {{-- ================= HEADER ================= --}}
    <div class="erp-header-bar mb-3">
        <div class="d-flex align-items-center">
            <h1 class="erp-page-title mb-0">Sample sets</h1>
            <div class="erp-report-meta ml-3">
                <span class="meta-item"><i class="fas fa-layer-group mr-1 text-muted"></i> Grouped sample sets for customers</span>
            </div>
        </div>
        <div class="d-flex align-items-center" style="gap: 6px;">
            <button type="button" class="btn-erp btn-erp-outline" data-toggle="modal" data-target="#designSearchModal">
                <i class="fas fa-search mr-1"></i> Search by design no
            </button>
            <button type="button" class="btn-erp btn-erp-outline" data-toggle="modal" data-target="#prnModal">
                <i class="fas fa-barcode mr-1"></i> Generate PRN
            </button>
            <a href="{{ route('admin.inventory.fair-product.create') }}" class="btn-erp btn-erp-primary">
                <i class="fas fa-plus mr-1"></i> Create new sample set
            </a>
        </div>
    </div>

    {{-- ================= FILTERS ================= --}}
    <div class="card erp-card mb-3">
        <div class="card-body p-2">
            <form action="{{ route('admin.inventory.fair-product.index') }}" method="GET" class="row g-2 align-items-end">
                <div class="col-md mb-2">
                    <label class="erp-label mb-1">Design number</label>
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
                    <label class="erp-label mb-1">Batch no</label>
                    <input type="text" name="batch_no" class="form-control form-control-sm" value="{{ request('batch_no') }}" placeholder="FAIR-...">
                </div>
                <div class="col-md mb-2">
                    <label class="erp-label mb-1">Sales agent</label>
                    <select name="sales_agent_ids[]" class="form-control select2 form-control-sm" multiple>
                        @foreach($salesAgents as $agent)
                            <option value="{{ $agent->id }}" {{ is_array(request('sales_agent_ids')) && in_array($agent->id, request('sales_agent_ids')) ? 'selected' : '' }}>{{ $agent->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md mb-2">
                    <label class="erp-label mb-1">From date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ request('from_date') }}">
                </div>
                <div class="col-md mb-2">
                    <label class="erp-label mb-1">To date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-auto mb-2 d-flex">
                    <button type="submit" class="btn-erp btn-erp-primary mr-2">
                        <i class="fas fa-filter mr-1"></i> Filter
                    </button>
                    <a href="{{ route('admin.inventory.fair-product.index') }}" class="btn-erp btn-erp-outline">
                        Reset
                    </a>
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
                            <th style="width: 120px;">Date</th>
                            <th>Batch no</th>
                            <th>Sales agents</th>
                            <th class="text-center" style="width: 140px;">Total items</th>
                            <th class="text-center" style="width: 90px;">Status</th>
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
                                <span class="erp-stat-pill font-weight-bold" style="padding: 2px 8px; font-size: 11px;">
                                    <strong>{{ $batch->products_count }}</strong> products
                                </span>
                            </td>
                            <td class="align-middle text-center">
                                <form action="{{ route('admin.inventory.fair-product.toggle-status', $batch->id) }}" method="POST" class="d-inline" title="Toggle status">
                                    @csrf
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" class="custom-control-input" id="statusSwitch{{ $batch->id }}" onchange="this.form.submit()" {{ $batch->status == 1 ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="statusSwitch{{ $batch->id }}" style="cursor: pointer;"></label>
                                    </div>
                                </form>
                            </td>
                            <td class="align-middle text-right">
                                <a href="{{ route('admin.inventory.fair-product.show', $batch->id) }}" class="erp-action-btn erp-btn-view" title="View sample set">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.inventory.fair-product.edit', $batch->id) }}" class="erp-action-btn erp-btn-edit" title="Edit sample set">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <div class="dropdown d-inline-block mr-1">
                                    <button class="btn-erp btn-erp-outline btn-erp-xs dropdown-toggle" type="button" id="downloadDropdown{{ $batch->id }}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Download options">
                                        <i class="fas fa-download mr-1"></i> Downloads
                                    </button>
                                    <div class="dropdown-menu dropdown-menu-right" aria-labelledby="downloadDropdown{{ $batch->id }}">
                                        <h6 class="dropdown-header">PDF downloads</h6>
                                        <a class="dropdown-item text-success" href="{{ route('admin.inventory.fair-product.generate-pdf-batch', $batch->id) }}?show_wsp=yes" target="_blank">
                                            <i class="fas fa-file-invoice-dollar mr-2"></i> PDF with WSP
                                        </a>
                                        <a class="dropdown-item text-primary" href="{{ route('admin.inventory.fair-product.generate-pdf-batch', $batch->id) }}?show_wsp=no" target="_blank">
                                            <i class="fas fa-file-pdf mr-2"></i> PDF No WSP
                                        </a>
                                        <div class="dropdown-divider"></div>
                                        <h6 class="dropdown-header">PRN downloads</h6>
                                        <a class="dropdown-item text-dark" href="{{ route('admin.inventory.fair-product.download-prn') }}?batch_id={{ $batch->id }}">
                                            <i class="fas fa-barcode mr-2"></i> PRN (Barcode)
                                        </a>
                                        <a class="dropdown-item text-info" href="{{ route('admin.inventory.fair-product.download-prn') }}?batch_id={{ $batch->id }}&type=qrcode">
                                            <i class="fas fa-qrcode mr-2"></i> PRN (QR code)
                                        </a>
                                    </div>
                                </div>
                                <form action="{{ route('admin.inventory.fair-product.destroy', $batch->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this entire sample set batch?')">
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
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fas fa-boxes fa-3x mb-3 text-secondary d-block"></i>
                                <p class="mb-0">No sample sets found.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($batches->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $batches->appends(request()->query())->links() }}
            </div>
            @endif
        </div>
    </div>

</div>

{{-- ================= PRN MODAL ================= --}}
<div class="modal fade" id="prnModal" tabindex="-1" role="dialog" aria-labelledby="prnModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <h5 class="modal-title font-weight-bold mb-0" id="prnModalLabel" style="color: var(--brand-dark-green, #05421c); font-size: 15px;">
                    <i class="fas fa-barcode mr-2"></i> Generate PRN by barcodes
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.inventory.fair-product.download-prn-by-barcodes') }}" method="POST" target="_blank">
                @csrf
                <div class="modal-body p-3">
                    <div class="form-group mb-0">
                        <label class="erp-label mb-1">Enter barcodes</label>
                        <textarea class="form-control form-control-sm" name="barcodes" rows="5" placeholder="Paste or type sample set barcodes here (one per line, e.g. F...)" required></textarea>
                        <small class="form-text text-muted mt-2">
                            Enter multiple barcodes separated by a new line. The system will automatically generate a PRN file for all valid barcodes.
                        </small>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 bg-light border-top">
                    <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Cancel</button>
                    <button type="submit" name="type" value="barcode" class="btn-erp btn-erp-primary">
                        <i class="fas fa-print mr-1"></i> Generate PRN (Barcode)
                    </button>
                    <button type="submit" name="type" value="qrcode" class="btn-erp btn-erp-warning">
                        <i class="fas fa-qrcode mr-1"></i> Generate PRN (QR)
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ================= DESIGN NUMBER SEARCH MODAL ================= --}}
<div class="modal fade" id="designSearchModal" tabindex="-1" role="dialog" aria-labelledby="designSearchModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header bg-light py-2 px-3 border-bottom">
                <h5 class="modal-title font-weight-bold mb-0" id="designSearchModalLabel" style="color: var(--brand-dark-green, #05421c); font-size: 15px;">
                    <i class="fas fa-search mr-2"></i> Search sample sets
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-3">

                <div class="row g-2 mb-3">
                    <div class="col-md-3 mb-2">
                        <label class="erp-label mb-1">Design number</label>
                        <div class="input-group input-group-sm">
                            <input type="text"
                                   id="designSearchInput"
                                   class="form-control form-control-sm"
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
                        <label class="erp-label mb-1">Product (series & name)</label>
                        <select id="modalProductName" class="form-control form-control-sm select2">
                            <option value="">All products</option>
                            @foreach($productsList as $pl)
                                <option value="{{ $pl }}">{{ $pl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-label mb-1">Size set</label>
                        <select id="modalSizeSet" class="form-control form-control-sm select2">
                            <option value="">All size sets</option>
                            @foreach($sizeSets as $ss)
                                <option value="{{ $ss->id }}">{{ $ss->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3 mb-2">
                        <label class="erp-label mb-1">Sales agent</label>
                        <select id="modalSalesAgent" class="form-control form-control-sm select2">
                            <option value="">All agents</option>
                            @foreach($salesAgents as $agent)
                                <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="text-right mb-3">
                    <button class="btn-erp btn-erp-primary" type="button" id="designSearchBtn">
                        <i class="fas fa-search mr-1"></i> Search
                    </button>
                    <button class="btn-erp btn-erp-outline ml-1" type="button" id="designSearchReset">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>

                {{-- Results Area --}}
                <div id="designSearchResults">
                    <div class="text-center text-muted py-4" id="designSearchPlaceholder">
                        <i class="fas fa-search fa-2x mb-2 d-block text-secondary"></i>
                        Fill in any filter above and click Search
                    </div>
                </div>

            </div>

            <div class="modal-footer py-2 px-3 bg-light border-top">
                <button type="button" class="btn-erp btn-erp-outline" data-dismiss="modal">Close</button>
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

    const placeholder = '<div class="text-center text-muted py-4"><i class="fas fa-search fa-2x mb-2 d-block text-secondary"></i>Fill in any filter above and click Search</div>';

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
            html += '<table class="table erp-table mb-0">';
            html += '<thead><tr>';
            html += '<th>#</th>';
            html += '<th>Product name</th>';
            html += '<th>Design no</th>';
            html += '<th>Size set</th>';
            html += '<th>Batch no</th>';
            html += '<th>Sales agent(s)</th>';
            html += '<th>Barcode</th>';
            html += '</tr></thead><tbody>';

            data.forEach(function(row, i) {
                html += '<tr>';
                html += '<td class="text-muted">' + (i + 1) + '</td>';
                html += '<td>' + row.product_name + '</td>';
                html += '<td><span class="font-weight-bold" style="color: var(--brand-dark-green, #05421c);">' + row.design_number + '</span></td>';
                html += '<td><span class="badge badge-light border">' + row.size_set + '</span></td>';
                html += '<td><span class="barcode-display">' + row.batch_no + '</span></td>';
                html += '<td>' + row.sales_agents + '</td>';
                html += '<td>';
                html += '<span class="barcode-display mr-1">' + row.barcode + '</span>';
                html += '<i class="far fa-copy text-primary copy-barcode" data-barcode="' + row.barcode + '" title="Copy barcode" style="cursor: pointer;"></i>';
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
                $('#modalSizeSet, #modalSalesAgent, #modalProductName').trigger('change');
            }
            resultsDiv.innerHTML = placeholder;
        });
    }

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
            if (typeof Toast !== 'undefined') {
                Toast.fire({
                    icon: 'success',
                    title: 'Barcode copied to clipboard!'
                });
            } else if (typeof toastr !== 'undefined') {
                toastr.success('Barcode copied to clipboard!');
            } else {
                alert('Barcode copied: ' + barcode);
            }
        }).catch(err => {
            console.error('Failed to copy text: ', err);
        });
    });

});
</script>
@endpush
