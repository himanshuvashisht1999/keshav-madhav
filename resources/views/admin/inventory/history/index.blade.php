@extends('admin.layouts.app')
@section('content')
    <div class="content-wrapper">
        <section class="content-header">
            <div class="container-fluid">
                <div class="row mb-3 align-items-center">
                    <div class="col-sm-6">
                        <h1 class="m-0 font-weight-bold text-dark">Attribute Change History</h1>
                        <small class="text-muted">Detailed log of all manual inventory attribute modifications</small>
                    </div>
                </div>
            </div>
        </section>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <div class="row align-items-end">
                            <div class="col-md-4">
                                <label class="small font-weight-bold text-muted mb-1">Search Design No.</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text bg-white border-right-0"><i
                                                class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="design_search" class="form-control border-left-0"
                                        placeholder="Search Design (Old or New)...">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Movement Type</label>
                                <select id="type_filter" class="form-control custom-select">
                                    <option value="">All Types</option>
                                    <option value="creation">Entry</option>
                                    <option value="packing">Packing</option>
                                    <option value="attribute_change">Update</option>
                                    <option value="stock_consume">Consume</option>
                                    <option value="transfer">Transfer</option>
                                    <option value="deletion">Deletion</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow border-0" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="historyTable" class="table table-hover mb-0">
                                <thead class="bg-light contrast-text">
                                    <tr>
                                        <th width="3%" class="text-center py-3">#</th>
                                        <th width="10%" class="py-3">Type</th>
                                        <th width="28%" class="py-3">Old Attributes</th>
                                        <th width="5%" class="py-3 text-center"><i class="fas fa-link text-muted"></i></th>
                                        <th width="28%" class="py-3">New Attributes</th>
                                        <th width="10%" class="py-3 text-center">Movement</th>
                                        <!-- <th width="8%" class="py-3">User</th> -->
                                        <th width="8%" class="py-3">Date</th>
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
        </section>
    </div>

    <style>
        .contrast-text th {
            color: #444;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        .table tbody td {
            vertical-align: middle;
            padding: 1rem 0.75rem;
        }
    </style>

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
            $('.content-wrapper').on('scroll', function () {
                if ($(this).scrollTop() + $(this).innerHeight() >= $(this)[0].scrollHeight - 10) {
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
@endsection