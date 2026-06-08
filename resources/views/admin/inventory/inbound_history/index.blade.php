@extends('admin.layouts.app')

@section('content')
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            --bg-main: #f8fafc;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }

        .content-wrapper {
            background-color: var(--bg-main);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
        }

        .premium-page-header {
            padding: 1.5rem 0;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 1.75rem;
            font-weight: 800;
            color: var(--text-main);
            letter-spacing: -0.025em;
            margin: 0;
        }

        .card-premium {
            border: none;
            border-radius: 1rem;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            background: #fff;
        }

        .table thead th {
            background: #f8fafc;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.05em;
            font-weight: 700;
            color: var(--text-muted);
            border-top: none;
            padding: 1rem;
        }

        .table td {
            padding: 1rem;
            vertical-align: middle;
            color: var(--text-main);
            font-size: 0.875rem;
            border-color: #f1f5f9;
        }

        .btn-soft-primary {
            background: #eef2ff;
            color: #4f46e5;
            border: none;
        }

        .btn-soft-primary:hover { background: #e0e7ff; color: #4338ca; }

        .badge-soft-success { background: #ecfdf5; color: #059669; }

        .animate-in {
            animation: slideUp 0.4s ease-out forwards;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>

    <div class="content-wrapper">
        <div class="premium-page-header">
            <div class="container-fluid">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h1 class="page-title">Inbound Stock Session History</h1>
                        <p class="text-muted mb-0 small">Track and view all manual stock entry sessions</p>
                    </div>
                    <a href="{{ route('admin.inventory.create') }}" class="btn btn-primary shadow-sm" style="border-radius: 0.75rem; padding: 0.6rem 1.5rem; font-weight: 600;">
                        <i class="fas fa-plus mr-2"></i>New Stock Generate
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-4">
                        <div class="row align-items-end">
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Start Date</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">End Date</label>
                                <input type="date" id="end_date" class="form-control">
                            </div>
                            <div class="col-md-2 mt-3 mt-md-0">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block">
                                    <i class="fas fa-undo"></i> Reset Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-premium animate-in">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="entryHistoryTable" class="table mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th class="pl-4">#</th>
                                        <th>Date</th>
                                        <th>Session ID</th>
                                        <th>Total Boxes</th>
                                        <th class="text-right pr-4">Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

@push('scripts')
<script>
    $(function () {
        const table = $('#entryHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.inventory.inbound_history.list') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                }
            },
            order: [[0, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'pl-4', orderable: false, searchable: false },
                { 
                    data: 'date', 
                    name: 'date', 
                    render: function(data) {
                        return `<div class="font-weight-bold text-primary">${data}</div>`;
                    }
                },
                { 
                    data: 'id', 
                    name: 'id',
                    render: function(data) {
                        return `<b>SESSION-${data}</b>`;
                    }
                },
                { 
                    data: 'total_boxes', 
                    name: 'total_boxes',
                    searchable: false,
                    orderable: false,
                    render: (d) => `<span class="badge badge-soft-success px-2 py-1 font-weight-bold">${d || 0} Boxes</span>`
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right pr-4' }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            },
            drawCallback: function() {
                $('.dataTables_paginate > .pagination').addClass('pagination-rounded');
            }
        });

        // Filter triggers
        $('#start_date, #end_date').on('change keyup', function () {
            table.draw();
        });

        // Reset filter
        $('#reset_filters').on('click', function () {
            $('#start_date, #end_date').val('');
            table.draw();
        });
    });
</script>
@endpush
@endsection
