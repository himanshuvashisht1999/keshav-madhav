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
            padding: 1rem 0;
            background: #fff;
            border-bottom: 1px solid #e2e8f0;
            margin-bottom: 1rem;
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

        .btn-soft-danger {
            background: #fef2f2;
            color: #ef4444;
            border: none;
        }

        .btn-soft-primary:hover { background: #e0e7ff; color: #4338ca; }
        .btn-soft-danger:hover { background: #fee2e2; color: #dc2626; }

        .badge-soft-success { background: #ecfdf5; color: #059669; }
        .badge-soft-info { background: #eff6ff; color: #2563eb; }

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
                        <h1 class="page-title">Purchase History</h1>
                        <p class="text-muted mb-0 small">Manage and track all inventory stock entries</p>
                    </div>
                    <a href="{{ route('admin.inventory.purchase') }}" class="btn btn-primary shadow-sm" style="border-radius: 0.75rem; padding: 0.6rem 1.5rem; font-weight: 600;">
                        <i class="fas fa-plus mr-2"></i>New Purchase
                    </a>
                </div>
            </div>
        </div>

        <section class="content">
            <div class="container-fluid">
                <!-- FILTER CARD -->
                <div class="card shadow-sm border-0 mb-3" style="border-radius: 12px;">
                    <div class="card-body bg-light rounded p-3">
                        <div class="row align-items-end">
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Start Date</label>
                                <input type="date" id="start_date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">End Date</label>
                                <input type="date" id="end_date" class="form-control">
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Vendor</label>
                                <select id="vendor_filter" class="form-control select2">
                                    <option value="">All Vendors</option>
                                    @foreach($vendors as $vendor)
                                        <option value="{{ $vendor->id }}">{{ $vendor->company_name ?? $vendor->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="small font-weight-bold text-muted mb-1">Customer</label>
                                <select id="customer_filter" class="form-control select2">
                                    <option value="">All Customers</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="small font-weight-bold text-muted mb-1">Production PO Number</label>
                                <input type="text" id="po_number_filter" class="form-control" placeholder="Search PO...">
                            </div>
                            <div class="col-md-1 mt-3 mt-md-0">
                                <button id="reset_filters" class="btn btn-secondary shadow-sm btn-block">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card card-premium animate-in">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table id="purchaseHistoryTable" class="table mb-0 w-100">
                                <thead>
                                    <tr>
                                        <th class="pl-4">#</th>
                                        <th>Purchase Date</th>
                                        <th>Source</th>
                                        <th>Production PO</th>
                                        <th>Total Boxes</th>
                                        <th>Total Amount</th>
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
        // Initialize Select2
        $('.select2').select2({
            theme: 'bootstrap4',
            width: '100%'
        });

        const table = $('#purchaseHistoryTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('admin.inventory.purchase_history.list') }}",
                data: function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.vendor_id = $('#vendor_filter').val();
                    d.customer_id = $('#customer_filter').val();
                    d.po_number = $('#po_number_filter').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'pl-4', orderable: false, searchable: false },
                { 
                    data: 'purchase_date', 
                    name: 'purchase_date', 
                    render: function(data, type, row) {
                        let dateStr = data ? data : row.created_at;
                        return `<div class="font-weight-bold">${moment(dateStr).format('DD MMM YYYY')}</div>`;
                    }
                },
                { 
                    data: 'source', 
                    name: 'source',
                    orderable: false, 
                    searchable: false,
                    render: function(data) {
                        return `<span class="badge badge-soft-primary px-2 py-1">${data}</span>`;
                    }
                },
                { 
                    data: 'production_po', 
                    name: 'production_po',
                    render: function(data) {
                        return data.includes('Manual') ? data : `<span class="badge badge-soft-info px-2 py-1">${data}</span>`;
                    }
                },
                { 
                    data: 'total_boxes', 
                    name: 'total_boxes',
                    searchable: false,
                    render: (d) => `<span class="badge badge-soft-success px-2 py-1 font-weight-bold">${d || 0} Boxes</span>`
                },
                { 
                    data: 'total_amount', 
                    name: 'total_amount', 
                    render: (d) => `<span class="text-primary font-weight-bold" style="font-size: 1rem;">₹${parseFloat(d).toLocaleString()}</span>` 
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
        $('#start_date, #end_date, #po_number_filter').on('change keyup', function () {
            table.draw();
        });

        $('#vendor_filter, #customer_filter').on('change', function () {
            table.draw();
        });

        // Reset filter
        $('#reset_filters').on('click', function () {
            $('#start_date, #end_date, #po_number_filter').val('');
            $('#vendor_filter, #customer_filter').val('').trigger('change');
            table.draw();
        });

        // Delete functionality
        $(document).on('click', '.btn-delete-purchase', function() {
            const id = $(this).data('id');
            Swal.fire({
                title: 'Are you sure?',
                text: "This will delete the purchase and revert the added stock levels!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('admin/inventory/purchase-history') }}/${id}/delete`,
                        type: 'DELETE',
                        data: { _token: '{{ csrf_token() }}' },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                table.ajax.reload();
                            } else {
                                toastr.error(response.message);
                            }
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
@endsection
