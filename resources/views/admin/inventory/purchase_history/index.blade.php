@extends('admin.layouts.app')

@section('content')
<div class="content-wrapper erp-page p-2">
    <div class="container-fluid p-0">
        <!-- PAGE HEADER -->
        <div class="erp-header-bar mb-2">
            <div>
                <h1 class="erp-page-title m-0">Purchase History</h1>
                <p class="erp-page-subtitle m-0">Manage and track all inventory stock inward entries</p>
            </div>
            <div class="d-flex align-items-center" style="gap: 8px;">
                <a href="{{ route('admin.inventory.purchase') }}" class="btn-erp btn-erp-primary">
                    <i class="fas fa-plus mr-1"></i> New purchase
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="btn-erp btn-erp-secondary">
                    <i class="fas fa-arrow-left mr-1"></i> Back to inventory
                </a>
            </div>
        </div>

        <!-- FILTER CARD -->
        <div class="erp-card mb-2 p-2">
            <div class="row align-items-end">
                <div class="col-md-2 mb-1">
                    <label class="erp-label mb-1">Start date</label>
                    <input type="date" id="start_date" class="form-control form-control-sm erp-input">
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-label mb-1">End date</label>
                    <input type="date" id="end_date" class="form-control form-control-sm erp-input">
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-label mb-1">Vendor</label>
                    <select id="vendor_filter" class="form-control form-control-sm select2">
                        <option value="">All vendors</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}">{{ $vendor->company_name ?? $vendor->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-label mb-1">Customer</label>
                    <select id="customer_filter" class="form-control form-control-sm select2">
                        <option value="">All customers</option>
                        @foreach($customers as $customer)
                            <option value="{{ $customer->id }}">{{ $customer->company_name ?? $customer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 mb-1">
                    <label class="erp-label mb-1">Bill no.</label>
                    <input type="text" id="bill_no_filter" class="form-control form-control-sm erp-input" placeholder="Search bill...">
                </div>
                <div class="col-md-1 mb-1">
                    <label class="erp-label mb-1">PO no.</label>
                    <input type="text" id="po_number_filter" class="form-control form-control-sm erp-input" placeholder="Search PO...">
                </div>
                <div class="col-md-1 mb-1">
                    <button id="reset_filters" class="btn-erp btn-erp-secondary w-100" title="Reset Filters" style="height: 29px;">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- TABLE CARD -->
        <div class="erp-card p-0">
            <div class="table-responsive">
                <table id="purchaseHistoryTable" class="erp-table table-bordered table-hover mb-0 w-100">
                    <thead>
                        <tr>
                            <th style="width: 40px; text-align: center;">#</th>
                            <th>Purchase date</th>
                            <th>Bill no.</th>
                            <th>Source</th>
                            <th>Production PO</th>
                            <th style="text-align: center;">Total boxes</th>
                            <th style="text-align: right;">Total amount</th>
                            <th style="width: 130px; text-align: right;">Action</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
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
                    d.bill_no = $('#bill_no_filter').val();
                }
            },
            order: [[1, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', className: 'text-center font-weight-bold', orderable: false, searchable: false },
                { 
                    data: 'purchase_date', 
                    name: 'purchase_date', 
                    render: function(data, type, row) {
                        let dateStr = data ? data : row.created_at;
                        return `<div class="font-weight-bold text-dark">${moment(dateStr).format('DD MMM YYYY')}</div>`;
                    }
                },
                { 
                    data: 'bill_no', 
                    name: 'bill_no',
                    render: function(data) {
                        return data ? `<span class="font-weight-bold text-dark">${data}</span>` : '<span class="text-muted">-</span>';
                    }
                },
                { 
                    data: 'source', 
                    name: 'source',
                    orderable: false, 
                    searchable: false,
                    render: function(data) {
                        return `<span class="badge badge-light border px-2 py-1 text-uppercase">${data}</span>`;
                    }
                },
                { 
                    data: 'production_po', 
                    name: 'production_po',
                    render: function(data) {
                        return data.includes('Manual') ? `<span class="text-muted">${data}</span>` : `<span class="badge badge-light border text-primary font-weight-bold px-2 py-1">${data}</span>`;
                    }
                },
                { 
                    data: 'total_boxes', 
                    name: 'total_boxes',
                    searchable: false,
                    className: 'text-center',
                    render: (d) => `<span class="badge badge-light border font-weight-bold px-2 py-1">${d || 0} Boxes</span>`
                },
                { 
                    data: 'total_amount', 
                    name: 'total_amount', 
                    className: 'text-right',
                    render: (d) => `<span class="font-weight-bold" style="color: #05421c; font-size: 0.95rem;">₹${parseFloat(d).toLocaleString()}</span>` 
                },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-right' }
            ],
            language: {
                search: "_INPUT_",
                searchPlaceholder: "Search records...",
                paginate: {
                    previous: '<i class="fas fa-chevron-left"></i>',
                    next: '<i class="fas fa-chevron-right"></i>'
                }
            }
        });

        // Filter triggers
        $('#start_date, #end_date, #po_number_filter, #bill_no_filter').on('change keyup', function () {
            table.draw();
        });

        $('#vendor_filter, #customer_filter').on('change', function () {
            table.draw();
        });

        // Reset filter
        $('#reset_filters').on('click', function () {
            $('#start_date, #end_date, #po_number_filter, #bill_no_filter').val('');
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
                confirmButtonColor: '#dc2626',
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
