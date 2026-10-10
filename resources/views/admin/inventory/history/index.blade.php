@extends('admin.layouts.app')
@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Attribute Change History</h1>
                <p class="erp-page-subtitle m-0">Detailed log of all manual inventory attribute modifications</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to inventory
                </a>
            </div>
        </div>

        <!-- FILTER CARD -->
        <div class="erp-card mb-2 p-2">
            <div class="row align-items-end">
                <div class="col-md-4 mb-1">
                    <label class="erp-label mb-1">Search design no.</label>
                    <input type="text" id="design_search" class="form-control form-control-sm erp-input"
                        placeholder="Search design (old or new)...">
                </div>
                <div class="col-md-3 mb-1">
                    <label class="erp-label mb-1">Movement type</label>
                    <select id="type_filter" class="form-control form-control-sm custom-select">
                        <option value="">All types</option>
                        <option value="creation">Entry</option>
                        <option value="packing">Packing</option>
                        <option value="attribute_change">Update</option>
                        <option value="stock_consume">Consume</option>
                        <option value="transfer">Transfer</option>
                        <option value="deletion">Deletion</option>
                    </select>
                </div>
                <div class="col-md-2 mb-1">
                    <button id="reset_filters" class="btn-erp btn-erp-secondary w-100" style="height: 31px;">
                        <i class="fas fa-undo mr-1"></i> Reset
                    </button>
                </div>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table id="historyTable" class="erp-table table-bordered table-hover mb-0 w-100">
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
                <p class="text-muted small mt-2">Loading more records...</p>
            </div>
            <div id="no-more-records" class="text-center py-4" style="display: none;">
                <hr class="w-25">
                <p class="text-muted small">No more history records found.</p>
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
            if (isLoading || (!hasMore && !reset)) return;

            if (reset) {
                currentPage = 1;
                hasMore = true;
                $('#historyTable tbody').html('');
            }

            isLoading = true;
            $('#loading-indicator').show();
            $('#no-more-records').hide();

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
                    $('#historyTable tbody').append(response.html);

                    if (response.next_page) {
                        currentPage = response.next_page;
                    } else {
                        hasMore = false;
                        if ($('#historyTable tbody tr').length > 0) {
                            $('#no-more-records').show();
                        }
                    }
                },
                error: function () {
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

        // Scroll Event
        $(window).on('scroll', function () {
            if ($(window).scrollTop() + $(window).height() >= $(document).height() - 50) {
                loadMore();
            }
        });

        // Filters
        $('#design_search, #type_filter').on('keyup change', function () {
            loadMore(true);
        });

        $('#reset_filters').on('click', function () {
            $('#design_search').val('');
            $('#type_filter').val('');
            loadMore(true);
        });
    });
</script>
@endpush
@endsection