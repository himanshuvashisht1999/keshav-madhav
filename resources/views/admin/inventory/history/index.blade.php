@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- Slim ERP Header Bar -->
        <div class="erp-header-bar mb-2">
            <div class="erp-header-title">
                <i class="fas fa-history text-primary"></i> Attribute Change History
            </div>
            <div class="erp-header-actions">
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-outline">
                    <i class="fas fa-arrow-left"></i> Back to Inventory
                </a>
            </div>
        </div>

        <!-- Compact ERP Filter Bar -->
        <div class="erp-filter-bar mb-2">
            <div class="row align-items-end">
                <div class="col-md-4 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-search mr-1"></i> Search Design No.</label>
                    <input type="text" id="design_search" class="form-control erp-input"
                        placeholder="Search design (old or new)..." autocomplete="off">
                </div>
                <div class="col-md-3 mb-1">
                    <label class="erp-filter-label"><i class="fas fa-exchange-alt mr-1"></i> Movement Type</label>
                    <select id="type_filter" class="form-control select2 erp-input" style="width: 100%;">
                        <option value="">-- ALL TYPES --</option>
                        <option value="creation">Entry</option>
                        <option value="packing">Packing</option>
                        <option value="attribute_change">Update</option>
                        <option value="stock_consume">Consume</option>
                        <option value="transfer">Transfer</option>
                        <option value="deletion">Deletion</option>
                    </select>
                </div>
                <div class="col-auto mb-1 ml-auto">
                    <div class="erp-filter-actions">
                        <button type="button" id="apply_filters" class="btn-erp btn-erp-primary" title="Apply Filters">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                        <button id="reset_filters" class="btn-erp btn-erp-outline">
                            <i class="fas fa-undo"></i> Reset
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <div class="erp-card">
            <div class="erp-card-body p-2 table-responsive">
                <table id="historyTable" class="erp-table table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 3%; text-align: center;">#</th>
                            <th style="width: 10%;">Type</th>
                            <th style="width: 28%;">Old attributes</th>
                            <th style="width: 5%; text-align: center;"><i class="fas fa-link text-muted"></i></th>
                            <th style="width: 28%;">New attributes</th>
                            <th style="width: 10%; text-align: center;">Movement</th>
                            <th style="width: 10%;">Date</th>
                            <th style="width: 6%; text-align: center;">Action</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
            <div id="loading-indicator" class="text-center py-4" style="display: none;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    $(function () {
        let currentPage = 1;
        let isLoading = false;
        let hasMore = true;

        function loadMore(reset = false) {
            if (isLoading) return;
            if (reset) currentPage = 1;
            isLoading = true;
            $('#loading-indicator').show();
            $('#historyTable tbody').css('opacity', '0.5');

            $.ajax({
                url: "{{ route('admin.inventory.attribute-history.list') }}",
                type: "GET",
                data: {
                    page: currentPage,
                    load_more: 1,
                    design_search: $('#design_search').val(),
                    type: $('#type_filter').val()
                },
                success: function (response) {
                    const $body = $('#historyTable tbody');
                    $body.empty().css('opacity', '1').append(response.html);
                    if (!$body.children().length) {
                        $body.append('<tr><td colspan="8" class="text-center py-4 text-muted">No history records found.</td></tr>');
                    }
                    currentPage = (response.meta && response.meta.current) || 1;
                    if (window.ErpPager) ErpPager.render($('#historyTable'), response.meta, function (pg) { currentPage = pg; loadMore(false); });
                },
                error: function () {
                    $('#historyTable tbody').css('opacity', '1');
                    console.error("Failed to load records.");
                },
                complete: function () {
                    isLoading = false;
                    $('#loading-indicator').hide();
                }
            });
        }

        // Initial Load
        loadMore();

        // Filters
        $('#apply_filters').on('click', function () { loadMore(true); });
        $('#design_search').on('keydown', function (e) { if (e.which === 13) { e.preventDefault(); loadMore(true); } });

        $('#reset_filters').on('click', function () {
            $('#design_search').val('');
            $('#type_filter').val('').trigger('change.select2');
            loadMore(true);
        });
    });
</script>
@endpush
@endsection