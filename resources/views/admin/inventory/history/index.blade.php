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
                                        <span class="input-group-text bg-white border-right-0"><i class="fas fa-search text-muted"></i></span>
                                    </div>
                                    <input type="text" id="design_search" class="form-control border-left-0" placeholder="Search Design (Old or New)...">
                                </div>
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
                                        <th width="5%" class="text-center py-3">#</th>
                                        <th class="py-3">Old Attributes</th>
                                        <th class="py-3 text-center"><i class="fas fa-arrow-right"></i></th>
                                        <th class="py-3">New Attributes</th>
                                        <th class="py-3 text-center">Qty (Boxes)</th>
                                        <th class="py-3">Date</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
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
            let table = $('#historyTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('admin.inventory.attribute-history.list') }}",
                    data: function(d) {
                        d.design_search = $('#design_search').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', className: 'text-center text-muted', orderable: false, searchable: false },
                    { data: 'old_details', orderable: false, searchable: false },
                    { 
                        data: null, 
                        className: 'text-center',
                        orderable: false,
                        searchable: false,
                        render: function() {
                            return '<i class="fas fa-arrow-right text-primary"></i>';
                        }
                    },
                    { data: 'new_details', orderable: false, searchable: false },
                    { data: 'box_quantity', className: 'text-center' },
                    { 
                        data: 'created_at',
                        render: function(data) {
                            return moment(data).format('DD-MM-YYYY HH:mm');
                        }
                    }
                ],
                order: [[5, 'desc']], // Default order by created_at column (index 5 now)
                language: {
                    processing: '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>'
                }
            });

            $('#design_search').on('keyup change', function() {
                table.draw();
            });

            $('#reset_filters').on('click', function() {
                $('#design_search').val('');
                table.draw();
            });
        });
    </script>
@endsection
